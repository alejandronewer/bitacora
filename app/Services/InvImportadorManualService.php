<?php

namespace App\Services;

use App\Models\InvDetalleEnlace;
use App\Models\InvDetalleServicio;
use App\Models\InvDetalleTunel;
use App\Models\InvDetalleNodo;
use App\Models\InvElementoRed;
use App\Models\InvImportacionRed;
use App\Models\InvImportRegla;
use Illuminate\Support\Facades\DB;

class InvImportadorManualService
{
    /**
     * @return array{total:int,procesados:int,creados:int,actualizados:int,marcados_baja:int,errores:array<int,array<string,mixed>>}
     */
    public function ejecutar(InvImportacionRed $importacion, InvImportRegla $regla, string $absolutePath): array
    {
        if ($regla->tabla_destino !== 'inv_elementos_redes') {
            throw new \RuntimeException('La regla seleccionada no apunta a inv_elementos_redes.');
        }

        $campos = $regla->campos()->where('activo', 1)->orderBy('orden')->get();
        if ($campos->isEmpty()) {
            throw new \RuntimeException('La regla no tiene campos activos configurados.');
        }

        $handle = fopen($absolutePath, 'rb');
        if (! $handle) {
            throw new \RuntimeException('No se pudo abrir el archivo para importación.');
        }

        $headers = [];
        $line = 0;
        $total = 0;
        $procesados = 0;
        $creados = 0;
        $actualizados = 0;
        $errores = [];
        $useMultilineRecovery = $this->shouldUseMultilineRecovery($campos);
        $pendingRaw = null;
        $pendingStartLine = 0;

        while (($raw = fgets($handle)) !== false) {
            $line++;
            $raw = rtrim($raw, "\r\n");

            if ($line === 1) {
                $raw = $this->stripBom($raw);
            }

            if ($raw === '') {
                continue;
            }

            if ($regla->tiene_encabezado && $headers === []) {
                $row = $this->parseLine($raw, $regla->delimitador, (bool) $regla->usa_comillas);
                $headers = array_map(fn ($v) => trim((string) $v), $row);
                continue;
            }

            if (! $useMultilineRecovery) {
                $row = $this->parseLine($raw, $regla->delimitador, (bool) $regla->usa_comillas);
                $this->processRow(
                    row: $row,
                    line: $line,
                    headers: $headers,
                    campos: $campos,
                    importacion: $importacion,
                    regla: $regla,
                    total: $total,
                    procesados: $procesados,
                    creados: $creados,
                    actualizados: $actualizados,
                    errores: $errores
                );
                continue;
            }

            if ($this->startsWithNumericToken($raw, $regla->delimitador)) {
                if ($pendingRaw !== null) {
                    $record = $this->normalizeLogicalCsvRecord($pendingRaw);
                    $row = $this->parseLine($record, $regla->delimitador, (bool) $regla->usa_comillas);
                    $this->processRow(
                        row: $row,
                        line: $pendingStartLine,
                        headers: $headers,
                        campos: $campos,
                        importacion: $importacion,
                        regla: $regla,
                        total: $total,
                        procesados: $procesados,
                        creados: $creados,
                        actualizados: $actualizados,
                        errores: $errores
                    );
                }

                $pendingRaw = $raw;
                $pendingStartLine = $line;
                continue;
            }

            if ($pendingRaw === null) {
                $errores[] = [
                    'importacion_id' => $importacion->id,
                    'fila_numero' => $line,
                    'campo' => null,
                    'valor' => null,
                    'mensaje' => 'Línea de continuación sin inicio de registro.',
                    'created_at' => now(),
                ];
                continue;
            }

            $pendingRaw .= "\n" . $raw;
        }

        if ($useMultilineRecovery && $pendingRaw !== null) {
            $record = $this->normalizeLogicalCsvRecord($pendingRaw);
            $row = $this->parseLine($record, $regla->delimitador, (bool) $regla->usa_comillas);
            $this->processRow(
                row: $row,
                line: $pendingStartLine,
                headers: $headers,
                campos: $campos,
                importacion: $importacion,
                regla: $regla,
                total: $total,
                procesados: $procesados,
                creados: $creados,
                actualizados: $actualizados,
                errores: $errores
            );
        }

        fclose($handle);

        if ($errores !== []) {
            foreach (array_chunk($errores, 500) as $chunk) {
                DB::table('inv_import_errores')->insert($chunk);
            }
        }

        return [
            'total' => $total,
            'procesados' => $procesados,
            'creados' => $creados,
            'actualizados' => $actualizados,
            'marcados_baja' => 0,
            'errores' => $errores,
        ];
    }

    /**
     * @param \Illuminate\Support\Collection<int, mixed> $campos
     * @param array<int, string> $headers
     * @param array<int, array<string, mixed>> $errores
     */
    private function processRow(
        array $row,
        int $line,
        array $headers,
        \Illuminate\Support\Collection $campos,
        InvImportacionRed $importacion,
        InvImportRegla $regla,
        int &$total,
        int &$procesados,
        int &$creados,
        int &$actualizados,
        array &$errores
    ): void {
        $total++;
        $payload = [];
        foreach ($campos as $campoRegla) {
            $hasSource = trim((string) $campoRegla->columna_fuente) !== '';
            $hasDefault = $campoRegla->por_defecto !== null;
            if (! $hasSource && ! $hasDefault) {
                continue;
            }

            $source = $this->resolveSourceValue($row, $headers, $campoRegla->columna_fuente);
            $value = $this->applyTransform($source, $campoRegla->transformacion);
            if (($value === null || $value === '') && $campoRegla->por_defecto !== null) {
                $value = $campoRegla->por_defecto;
            }

            $destino = $this->normalizeCampoDestino($campoRegla->campo_destino);
            if ($destino === null) {
                continue;
            }

            $payload[$destino] = $value;
        }

        $codigo = trim((string) ($payload['codigo'] ?? $payload['ne_id'] ?? $payload['ne_dbid'] ?? ''));
        if ($codigo === '') {
            $errores[] = [
                'importacion_id' => $importacion->id,
                'fila_numero' => $line,
                'campo' => 'codigo',
                'valor' => null,
                'mensaje' => 'No se pudo resolver el código para el registro.',
                'created_at' => now(),
            ];
            return;
        }

        DB::beginTransaction();
        try {
            $existing = InvElementoRed::where('red_id', $importacion->red_id)
                ->where('tipo', $regla->tipo_elemento)
                ->where('codigo', $codigo)
                ->first();

            $elemento = null;
            if ($existing) {
                $elementoData = $this->buildElementoData(
                    payload: $payload,
                    importacionId: (int) $importacion->id,
                    isCreate: false,
                    existing: $existing
                );
                $existing->fill($elementoData)->save();
                $elemento = $existing;
                $actualizados++;
            } else {
                $elementoData = $this->buildElementoData(
                    payload: $payload,
                    importacionId: (int) $importacion->id,
                    isCreate: true
                );
                $elemento = InvElementoRed::create(array_merge($elementoData, [
                    'red_id' => $importacion->red_id,
                    'tipo' => $regla->tipo_elemento,
                    'codigo' => $codigo,
                ]));
                $creados++;
            }

            $this->syncDetallePorTipo($regla->tipo_elemento, $elemento, $payload);

            $procesados++;
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $errores[] = [
                'importacion_id' => $importacion->id,
                'fila_numero' => $line,
                'campo' => null,
                'valor' => null,
                'mensaje' => mb_substr($e->getMessage(), 0, 255),
                'created_at' => now(),
            ];
        }
    }

    /**
     * @param \Illuminate\Support\Collection<int, mixed> $campos
     */
    private function shouldUseMultilineRecovery(\Illuminate\Support\Collection $campos): bool
    {
        foreach ($campos as $campoRegla) {
            $destino = $this->normalizeCampoDestino($campoRegla->campo_destino);
            if ($destino !== 'codigo') {
                continue;
            }

            $source = trim((string) $campoRegla->columna_fuente);
            if (! is_numeric($source)) {
                continue;
            }

            $transform = mb_strtolower(trim((string) $campoRegla->transformacion));
            return str_contains($transform, 'to_int') || preg_match('/(^|\|)int($|\|)/', $transform) === 1;
        }

        return false;
    }

    private function startsWithNumericToken(string $line, string $delimiter): bool
    {
        $pos = strpos($line, $delimiter);
        $first = $pos === false ? $line : substr($line, 0, $pos);
        $first = trim($first, " \t\n\r\0\x0B\"");

        return $first !== '' && is_numeric($first);
    }

    private function normalizeLogicalCsvRecord(string $record): string
    {
        $record = preg_replace('/\r\n|\r|\n/', ' ', $record) ?? $record;
        $record = trim($record);

        if ($this->hasUnbalancedQuotes($record)) {
            $record .= '"';
        }

        return $record;
    }

    private function hasUnbalancedQuotes(string $value): bool
    {
        $quotes = 0;
        $len = strlen($value);
        for ($i = 0; $i < $len; $i++) {
            if ($value[$i] !== '"') {
                continue;
            }

            $prevIsEscape = $i > 0 && $value[$i - 1] === '\\';
            if (! $prevIsEscape) {
                $quotes++;
            }
        }

        return ($quotes % 2) !== 0;
    }

    private function parseLine(string $line, string $delimiter, bool $usaComillas): array
    {
        if (! $usaComillas) {
            return explode($delimiter, $line);
        }

        return str_getcsv($line, $delimiter, '"', '\\');
    }

    private function resolveSourceValue(array $row, array $headers, string $key): mixed
    {
        $key = trim($key);
        if ($key === '') {
            return null;
        }

        if (is_numeric($key)) {
            $idx = (int) $key;
            // Prefer 1-based indexing for UI mapping (1,2,3...),
            // but keep 0-based compatibility if a rule already uses it.
            if ($idx > 0 && array_key_exists($idx - 1, $row)) {
                return $row[$idx - 1];
            }
            if (array_key_exists($idx, $row)) {
                return $row[$idx];
            }
            return null;
        }

        if ($headers === []) {
            return null;
        }

        $needle = mb_strtolower($key);
        foreach ($headers as $i => $header) {
            if (mb_strtolower(trim((string) $header)) === $needle) {
                return $row[$i] ?? null;
            }
        }

        return null;
    }

    private function applyTransform(mixed $value, ?string $transform): mixed
    {
        if ($value === null) {
            return null;
        }

        $transform = trim((string) $transform);
        if ($transform === '') {
            return is_string($value) ? trim($value) : $value;
        }

        $steps = array_values(array_filter(array_map(
            fn ($step) => trim(mb_strtolower($step)),
            explode('|', $transform)
        )));

        if ($steps === []) {
            return is_string($value) ? trim($value) : $value;
        }

        $current = $value;
        foreach ($steps as $step) {
            $current = $this->applyTransformStep($current, $step);
        }

        return $current;
    }

    private function applyTransformStep(mixed $value, string $step): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($step) {
            'trim' => trim((string) $value),
            'upper', 'uppercase' => mb_strtoupper(trim((string) $value)),
            'lower', 'lowercase' => mb_strtolower(trim((string) $value)),
            'int', 'to_int' => is_numeric($value) ? (int) $value : null,
            'null_if_empty' => trim((string) $value) === '' ? null : trim((string) $value),
            default => is_string($value) ? trim($value) : $value,
        };
    }

    private function nullIfEmpty(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));
        return $value === '' ? null : $value;
    }

    private function stripBom(string $value): string
    {
        return preg_replace('/^\xEF\xBB\xBF/', '', $value) ?? $value;
    }

    private function normalizeCampoDestino(?string $value): ?string
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        $key = mb_strtolower($raw);

        return match ($key) {
            'codigo', 'code', 'id' => 'codigo',
            'nombre', 'name' => 'nombre',
            'estado', 'status', 'm_actualexistingstate' => null,
            'fecha_alta', 'fecha_baja', 'updated_at_fuente', 'observaciones' => $key,
            'ne_id' => 'ne_id',
            'ne_dbid' => 'ne_dbid',
            'dn_externo', 'external_dn', 'dn', 'ne_m_dn' => 'dn_externo',
            'native_name', 'm_nativename' => 'native_name',
            'user_label', 'ne_m_userlabel' => 'user_label',
            'nombre_producto', 'm_productname', 'product_name' => 'nombre_producto',
            'tipo_equipo', 't_oranetype_name', 'm_netype', 'me_type' => 'tipo_equipo',
            'version_me', 'm_version' => 'version_me',
            'direccion_red', 'm_networkaddress', 'network_address' => 'direccion_red',
            'nombre_grupo', 'group_name', 'm_userlabel' => 'nombre_grupo',
            'instancia_enlace_id', 'link_instance_id', 'motlinkinstanceid' => 'instancia_enlace_id',
            'motlink_label', 'motlinklabel' => 'motlink_label',
            'trail_id', 'trail_subyacente_id', 'underlyingtrailid_id' => 'trail_id',
            'instancia_servicio_id', 'service_instance_id' => 'instancia_servicio_id',
            'cliente', 'customer', 'm_customer' => 'cliente',
            'tipo_servicio', 'service_type', 'm_servicetype', 'servicetype' => 'tipo_servicio',
            'ethvpn_id', 'm_ethvpnid' => 'ethvpn_id',
            'instancia_tunel_id', 'tunnel_instance_id' => 'instancia_tunel_id',
            'tipo_tunel', 'tunnel_type', 'm_tunneltype' => 'tipo_tunel',
            default => $key,
        };
    }

    private function buildElementoData(
        array $payload,
        int $importacionId,
        bool $isCreate,
        ?InvElementoRed $existing = null
    ): array
    {
        $now = now();
        $data = [
            'origen' => 'csv',
            'estado' => 'activo',
            'last_seen_importacion_id' => $importacionId,
        ];

        if ($isCreate) {
            $data['fecha_alta'] = $now;
            $data['fecha_baja'] = null;
            $data['updated_at_fuente'] = null;
        } else {
            $data['updated_at_fuente'] = $now;
            $data['fecha_baja'] = null;

            if ($existing && ! $existing->fecha_alta) {
                $data['fecha_alta'] = $now;
            }
        }

        if (array_key_exists('nombre', $payload)) {
            $data['nombre'] = $this->nullIfEmpty($payload['nombre']);
        }

        if (array_key_exists('observaciones', $payload)) {
            $data['observaciones'] = $this->nullIfEmpty($payload['observaciones']);
        }

        return $data;
    }

    private function syncDetallePorTipo(string $tipo, InvElementoRed $elemento, array $payload): void
    {
        if ($tipo === 'nodo') {
            $detalleData = $this->buildDetalleNodoData($payload);
            if ($detalleData === []) {
                return;
            }

            InvDetalleNodo::updateOrCreate(
                ['elemento_red_id' => $elemento->id],
                $detalleData
            );
        }

        if ($tipo === 'servicio') {
            $detalleData = $this->buildDetalleServicioData($payload);
            if ($detalleData === []) {
                return;
            }

            InvDetalleServicio::updateOrCreate(
                ['elemento_red_id' => $elemento->id],
                $detalleData
            );
        }

        if ($tipo === 'enlace') {
            $detalleData = $this->buildDetalleEnlaceData($payload);
            if ($detalleData === []) {
                return;
            }

            InvDetalleEnlace::updateOrCreate(
                ['elemento_red_id' => $elemento->id],
                $detalleData
            );
        }

        if ($tipo === 'tunel') {
            $detalleData = $this->buildDetalleTunelData($payload);
            if ($detalleData === []) {
                return;
            }

            InvDetalleTunel::updateOrCreate(
                ['elemento_red_id' => $elemento->id],
                $detalleData
            );
        }
    }

    private function buildDetalleNodoData(array $payload): array
    {
        $data = [];

        if (array_key_exists('ne_id', $payload)) {
            $data['ne_id'] = $this->toNullableInt($payload['ne_id']);
        }

        if (array_key_exists('ne_dbid', $payload)) {
            $data['ne_dbid'] = $this->toNullableInt($payload['ne_dbid']);
        }

        if (array_key_exists('dn_externo', $payload)) {
            $data['dn_externo'] = $this->nullIfEmpty($payload['dn_externo']);
        }

        if (array_key_exists('native_name', $payload)) {
            $data['native_name'] = $this->nullIfEmpty($payload['native_name']);
        }

        if (array_key_exists('user_label', $payload)) {
            $data['user_label'] = $this->nullIfEmpty($payload['user_label']);
        }

        if (array_key_exists('nombre_producto', $payload)) {
            $data['nombre_producto'] = $this->nullIfEmpty($payload['nombre_producto']);
        }

        if (array_key_exists('tipo_equipo', $payload)) {
            $data['tipo_equipo'] = $this->nullIfEmpty($payload['tipo_equipo']);
        }

        if (array_key_exists('version_me', $payload)) {
            $data['version_me'] = $this->nullIfEmpty($payload['version_me']);
        }

        if (array_key_exists('direccion_red', $payload)) {
            $data['direccion_red'] = $this->nullIfEmpty($payload['direccion_red']);
        }

        if (array_key_exists('nombre_grupo', $payload)) {
            $data['nombre_grupo'] = $this->nullIfEmpty($payload['nombre_grupo']);
        }

        return $data;
    }

    private function buildDetalleServicioData(array $payload): array
    {
        $data = [];

        if (array_key_exists('instancia_servicio_id', $payload)) {
            $data['instancia_servicio_id'] = $this->toNullableInt($payload['instancia_servicio_id']);
        }

        if (array_key_exists('user_label', $payload)) {
            $data['user_label'] = $this->nullIfEmpty($payload['user_label']);
        }

        if (array_key_exists('cliente', $payload)) {
            $data['cliente'] = $this->nullIfEmpty($payload['cliente']);
        }

        if (array_key_exists('tipo_servicio', $payload)) {
            $data['tipo_servicio'] = $this->nullIfEmpty($payload['tipo_servicio']);
        }

        if (array_key_exists('ethvpn_id', $payload)) {
            $data['ethvpn_id'] = $this->nullIfEmpty($payload['ethvpn_id']);
        }

        return $data;
    }

    private function buildDetalleEnlaceData(array $payload): array
    {
        $data = [];

        if (array_key_exists('instancia_enlace_id', $payload)) {
            $data['instancia_enlace_id'] = $this->toNullableInt($payload['instancia_enlace_id']);
        }

        if (array_key_exists('motlink_label', $payload)) {
            $data['motlink_label'] = $this->nullIfEmpty($payload['motlink_label']);
        }

        if (array_key_exists('trail_id', $payload)) {
            $data['trail_id'] = $this->toNullableInt($payload['trail_id']);
        }

        if (array_key_exists('nodo_a_ne_id', $payload)) {
            $data['nodo_a_ne_id'] = $this->toNullableInt($payload['nodo_a_ne_id']);
        }

        if (array_key_exists('nodo_z_ne_id', $payload)) {
            $data['nodo_z_ne_id'] = $this->toNullableInt($payload['nodo_z_ne_id']);
        }

        return $data;
    }

    private function buildDetalleTunelData(array $payload): array
    {
        $data = [];

        if (array_key_exists('instancia_tunel_id', $payload)) {
            $data['instancia_tunel_id'] = $this->toNullableInt($payload['instancia_tunel_id']);
        }

        if (array_key_exists('user_label', $payload)) {
            $data['user_label'] = $this->nullIfEmpty($payload['user_label']);
        }

        if (array_key_exists('cliente', $payload)) {
            $data['cliente'] = $this->nullIfEmpty($payload['cliente']);
        }

        if (array_key_exists('tipo_tunel', $payload)) {
            $data['tipo_tunel'] = $this->nullIfEmpty($payload['tipo_tunel']);
        }

        if (array_key_exists('ethvpn_id', $payload)) {
            $data['ethvpn_id'] = $this->nullIfEmpty($payload['ethvpn_id']);
        }

        return $data;
    }

    private function toNullableInt(mixed $value): ?int
    {
        $str = trim((string) ($value ?? ''));
        if ($str === '' || ! is_numeric($str)) {
            return null;
        }

        return (int) $str;
    }
}
