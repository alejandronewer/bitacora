<?php

namespace App\Services;

use App\Support\XlsxWorkbookReader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DmZpm017ImportService
{
    private const BATCH_SIZE = 500;

    /**
     * Importa archivo XLSX de ZPM017 multi-hoja (cada hoja representa tipo/clase de objeto).
     *
     * @return array<string, mixed>
     */
    public function importFromXlsx(string $xlsxPath, string $sourceFilename = ''): array
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }

        $startedAt = microtime(true);
        $reader = new XlsxWorkbookReader($xlsxPath);
        $sheets = $reader->getSheets();
        $detalleNivelesEnabled = Schema::hasTable('dm_ubicacion_detalle_nivel');

        $sheetResults = [];
        $totals = [
            'filas_leidas' => 0,
            'filas_procesadas' => 0,
            'filas_omitidas' => 0,
            'equipos_upsertados' => 0,
            'ubicaciones_upsertadas' => 0,
            'detalle_niveles_upsertados' => 0,
            'hojas_importadas' => 0,
            'hojas_omitidas' => 0,
        ];

        foreach ($sheets as $sheet) {
            $sheetResult = $this->importSheet($reader, $sheet, $detalleNivelesEnabled);
            $sheetResults[] = $sheetResult;

            foreach (['filas_leidas', 'filas_procesadas', 'filas_omitidas', 'equipos_upsertados', 'ubicaciones_upsertadas', 'detalle_niveles_upsertados'] as $metric) {
                $totals[$metric] += (int) ($sheetResult[$metric] ?? 0);
            }

            if (($sheetResult['estado'] ?? 'omitida') === 'importada') {
                $totals['hojas_importadas']++;
            } else {
                $totals['hojas_omitidas']++;
            }
        }

        return [
            'source_filename' => $sourceFilename,
            'sheets_total' => count($sheets),
            'totals' => $totals,
            'sheets' => $sheetResults,
            'duration_seconds' => round(microtime(true) - $startedAt, 3),
        ];
    }

    /**
     * @param array{name:string, id:string, path:string} $sheet
     * @return array<string, mixed>
     */
    private function importSheet(XlsxWorkbookReader $reader, array $sheet, bool $detalleNivelesEnabled): array
    {
        $sheetName = $sheet['name'];
        $sheetPath = $sheet['path'];

        $classObject = '';
        $headerIndexes = null;
        $headerResolved = false;

        $rowsRead = 0;
        $rowsProcessed = 0;
        $rowsSkipped = 0;
        $equiposTouched = 0;
        $ubicacionesTouched = 0;
        $detalleTouched = 0;

        $batchRows = [];
        $equipCodesSeen = [];
        $ubicCodesSeen = [];

        foreach ($reader->iterSheetRows($sheetPath) as $rowData) {
            $rowNum = (int) $rowData['row'];
            $cells = $rowData['cells'];

            if ($rowNum === 1) {
                $classObject = trim((string) ($cells[2] ?? ''));
                continue;
            }

            if ($rowNum === 2) {
                $headers = $this->buildHeaderMap($cells);
                $headerIndexes = $this->resolveHeaderIndexes($headers);
                $headerResolved = true;
                continue;
            }

            if (! $headerResolved || $headerIndexes === null) {
                continue;
            }

            $rowsRead++;
            $parsed = $this->parseRow($cells, $headerIndexes);
            if ($parsed === null) {
                $rowsSkipped++;
                continue;
            }

            $parsed['sheet_name'] = $sheetName;
            $parsed['class_object'] = $classObject;
            $batchRows[] = $parsed;

            if (! isset($equipCodesSeen[$parsed['equipo_codigo']])) {
                $equipCodesSeen[$parsed['equipo_codigo']] = true;
            }
            if (! empty($parsed['ubicacion_codigo']) && ! isset($ubicCodesSeen[$parsed['ubicacion_codigo']])) {
                $ubicCodesSeen[$parsed['ubicacion_codigo']] = true;
            }

            $rowsProcessed++;

            if (count($batchRows) >= self::BATCH_SIZE) {
                $batchResult = $this->flushBatch($batchRows, $detalleNivelesEnabled);
                $equiposTouched += $batchResult['equipos_upsertados'];
                $ubicacionesTouched += $batchResult['ubicaciones_upsertadas'];
                $detalleTouched += $batchResult['detalle_niveles_upsertados'];
                $batchRows = [];
            }
        }

        if (! $headerResolved || $headerIndexes === null || ! $headerIndexes['equipo'] || ! $headerIndexes['nombre']) {
            return [
                'sheet' => $sheetName,
                'class_object' => $classObject,
                'estado' => 'omitida',
                'motivo' => 'La hoja no contiene columnas mínimas requeridas (EQUIPO y NOMBRE).',
                'filas_leidas' => 0,
                'filas_procesadas' => 0,
                'filas_omitidas' => 0,
                'equipos_upsertados' => 0,
                'ubicaciones_upsertadas' => 0,
                'detalle_niveles_upsertados' => 0,
            ];
        }

        if ($batchRows !== []) {
            $batchResult = $this->flushBatch($batchRows, $detalleNivelesEnabled);
            $equiposTouched += $batchResult['equipos_upsertados'];
            $ubicacionesTouched += $batchResult['ubicaciones_upsertadas'];
            $detalleTouched += $batchResult['detalle_niveles_upsertados'];
        }

        return [
            'sheet' => $sheetName,
            'class_object' => $classObject,
            'estado' => 'importada',
            'filas_leidas' => $rowsRead,
            'filas_procesadas' => $rowsProcessed,
            'filas_omitidas' => $rowsSkipped,
            'equipos_upsertados' => $equiposTouched,
            'ubicaciones_upsertadas' => $ubicacionesTouched,
            'detalle_niveles_upsertados' => $detalleTouched,
            'equipos_distintos' => count($equipCodesSeen),
            'ubicaciones_distintas' => count($ubicCodesSeen),
        ];
    }

    /**
     * @param array<int, string> $cells
     * @return array<string, int>
     */
    private function buildHeaderMap(array $cells): array
    {
        $map = [];
        foreach ($cells as $index => $value) {
            $normalized = $this->normalizeHeader((string) $value);
            if ($normalized === '') {
                continue;
            }
            $map[$normalized] = (int) $index;
        }

        return $map;
    }

    /**
     * @param array<string, int> $headerMap
     * @return array{equipo:int,nombre:int,ubicacion_tecnica:int,area:int,nombre_instalacion:int}
     */
    private function resolveHeaderIndexes(array $headerMap): array
    {
        return [
            'equipo' => $this->firstHeaderIndex($headerMap, ['equipo']),
            'nombre' => $this->firstHeaderIndex($headerMap, ['nombre']),
            'ubicacion_tecnica' => $this->firstHeaderIndex($headerMap, ['ubicacion_tecnica', 'ubicacion_tecnica_']),
            'area' => $this->firstHeaderIndex($headerMap, ['area']),
            'nombre_instalacion' => $this->firstHeaderIndex($headerMap, ['nombre_de_la_instalacion', 'nombre_instalacion', 'subestacion']),
        ];
    }

    /**
     * @param array<string, int> $headerMap
     * @param array<int, string> $aliases
     */
    private function firstHeaderIndex(array $headerMap, array $aliases): int
    {
        foreach ($aliases as $alias) {
            if (isset($headerMap[$alias])) {
                return (int) $headerMap[$alias];
            }
        }

        return 0;
    }

    /**
     * @param array<int, string> $cells
     * @param array{equipo:int,nombre:int,ubicacion_tecnica:int,area:int,nombre_instalacion:int} $indexes
     * @return array<string, string|null>|null
     */
    private function parseRow(array $cells, array $indexes): ?array
    {
        $equipoCodigo = $this->sanitizeValue($cells[$indexes['equipo']] ?? null, 80);
        if ($equipoCodigo === null) {
            return null;
        }

        $equipoNombre = $this->sanitizeValue($cells[$indexes['nombre']] ?? null, 200) ?? $equipoCodigo;
        $area = $this->sanitizeValue($cells[$indexes['area']] ?? null, 40);

        $ubicacionCodigoRaw = $this->sanitizeValue($cells[$indexes['ubicacion_tecnica']] ?? null, 120);
        $ubicacionCodigo = null;
        if ($ubicacionCodigoRaw !== null) {
            $ubicacionCodigoRaw = strtoupper(preg_replace('/\s+/', '', $ubicacionCodigoRaw) ?? '');
            $ubicacionCodigo = $this->sanitizeValue($ubicacionCodigoRaw, 80);
        }

        $ubicacionNombre = $this->sanitizeValue($cells[$indexes['nombre_instalacion']] ?? null, 200);
        if ($ubicacionCodigo !== null && $ubicacionNombre === null) {
            $ubicacionNombre = $ubicacionCodigo;
        }

        return [
            'equipo_codigo' => $equipoCodigo,
            'equipo_nombre' => $equipoNombre,
            'area' => $area,
            'ubicacion_codigo' => $ubicacionCodigo,
            'ubicacion_nombre' => $ubicacionNombre,
        ];
    }

    /**
     * @param array<int, array<string, string|null>> $batchRows
     * @return array{equipos_upsertados:int,ubicaciones_upsertadas:int,detalle_niveles_upsertados:int}
     */
    private function flushBatch(array $batchRows, bool $detalleNivelesEnabled): array
    {
        $now = now();

        $ubicacionesByCodigo = [];
        foreach ($batchRows as $row) {
            $codigo = $row['ubicacion_codigo'];
            if ($codigo === null) {
                continue;
            }

            $niveles = $this->parseNiveles($codigo);
            if (($niveles['nivel_1'] ?? null) === null) {
                continue;
            }

            if (! isset($ubicacionesByCodigo[$codigo])) {
                $ubicacionesByCodigo[$codigo] = [
                    'codigo' => $codigo,
                    'nombre' => $this->sanitizeValue($row['ubicacion_nombre'], 200) ?? $codigo,
                    'nivel_1' => $niveles['nivel_1'],
                    'nivel_2' => $niveles['nivel_2'],
                    'nivel_3' => $niveles['nivel_3'],
                    'nivel_4' => $niveles['nivel_4'],
                    'nivel_5' => $niveles['nivel_5'],
                    'nivel_6' => $niveles['nivel_6'],
                    'nivel_7' => $niveles['nivel_7'],
                    'nivel_8' => $niveles['nivel_8'],
                    'activo' => 1,
                    'fuente' => 'Importacion',
                    'last_sync_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $ubicacionesUpserted = 0;
        $detalleUpserted = 0;
        $ubicacionIds = [];

        if ($ubicacionesByCodigo !== []) {
            $ubicacionRows = array_values($ubicacionesByCodigo);
            DB::table('dm_ubicaciones_tecnicas')->upsert(
                $ubicacionRows,
                ['codigo'],
                ['nombre', 'nivel_1', 'nivel_2', 'nivel_3', 'nivel_4', 'nivel_5', 'nivel_6', 'nivel_7', 'nivel_8', 'activo', 'fuente', 'last_sync_at', 'updated_at']
            );
            $ubicacionesUpserted = count($ubicacionRows);

            $ubicacionIds = DB::table('dm_ubicaciones_tecnicas')
                ->whereIn('codigo', array_keys($ubicacionesByCodigo))
                ->pluck('id', 'codigo')
                ->all();

            if ($detalleNivelesEnabled) {
                $detalleUpserted = $this->upsertDetalleNiveles($ubicacionesByCodigo, $now);
            }
        }

        $equiposRows = [];
        foreach ($batchRows as $row) {
            $codigo = $row['equipo_codigo'];
            if ($codigo === null) {
                continue;
            }

            $ubicacionCodigo = $row['ubicacion_codigo'];
            $ubicacionId = $ubicacionCodigo !== null ? ($ubicacionIds[$ubicacionCodigo] ?? null) : null;

            $equiposRows[] = [
                'codigo' => $codigo,
                'nombre' => $this->sanitizeValue($row['equipo_nombre'], 200) ?? $codigo,
                'ubicacion_tecnica_id' => $ubicacionId,
                'area' => $this->sanitizeValue($row['area'], 40),
                'activo' => 1,
                'fuente' => 'Importacion',
                'last_sync_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($equiposRows !== []) {
            DB::table('dm_equipos')->upsert(
                $equiposRows,
                ['codigo'],
                ['nombre', 'ubicacion_tecnica_id', 'area', 'activo', 'fuente', 'last_sync_at', 'updated_at']
            );
        }

        return [
            'equipos_upsertados' => count($equiposRows),
            'ubicaciones_upsertadas' => $ubicacionesUpserted,
            'detalle_niveles_upsertados' => $detalleUpserted,
        ];
    }

    /**
     * @param array<string, array<string, mixed>> $ubicacionesByCodigo
     */
    private function upsertDetalleNiveles(array $ubicacionesByCodigo, mixed $now): int
    {
        $recordsMap = [];

        foreach ($ubicacionesByCodigo as $row) {
            $rama = (string) ($row['nivel_3'] ?? '--');
            if ($rama === '') {
                $rama = '--';
            }

            for ($nivel = 1; $nivel <= 8; $nivel++) {
                $col = 'nivel_' . $nivel;
                $codigo = $row[$col] ?? null;
                if ($codigo === null || $codigo === '') {
                    continue;
                }

                $ramaNivel3 = $nivel <= 3 ? '--' : $rama;
                $uniqueKey = "{$nivel}|{$codigo}|{$ramaNivel3}";

                if (! isset($recordsMap[$uniqueKey])) {
                    $recordsMap[$uniqueKey] = [
                        'nivel' => $nivel,
                        'codigo' => $codigo,
                        'nombre' => null,
                        'descripcion' => null,
                        'rama_nivel_3' => $ramaNivel3,
                        'activo' => 1,
                        'origen' => 'Detectado',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        if ($recordsMap === []) {
            return 0;
        }

        $rows = array_values($recordsMap);
        DB::table('dm_ubicacion_detalle_nivel')->upsert(
            $rows,
            ['nivel', 'codigo', 'rama_nivel_3'],
            ['activo', 'origen', 'updated_at']
        );

        return count($rows);
    }

    /**
     * @return array{nivel_1:?string,nivel_2:?string,nivel_3:?string,nivel_4:?string,nivel_5:?string,nivel_6:?string,nivel_7:?string,nivel_8:?string}
     */
    private function parseNiveles(string $codigo): array
    {
        $raw = strtoupper(trim($codigo));
        $segments = explode('-', $raw);

        $n1 = $this->limit($segments[0] ?? '', 2);
        $n2 = $this->limit($segments[1] ?? '', 4);
        $n3 = $this->limit($segments[2] ?? '', 2);
        $n4 = $this->limit($segments[3] ?? '', 3);
        $n5 = $this->limit($segments[4] ?? '', 2);

        $n6 = null;
        $n7 = null;
        $n8 = null;
        $rest = array_slice($segments, 5);

        if (count($rest) >= 3) {
            $cand8 = $this->limit(end($rest) ?: '', 2);
            $cand7 = $this->limit(prev($rest) ?: '', 3);
            $cand6 = $this->limit(implode('-', array_slice($rest, 0, -2)), 5);

            if ($cand6 !== null && $cand7 !== null) {
                $n6 = $cand6;
                $n7 = $cand7;
                $n8 = $cand8;
            }
        }

        if ($n6 === null && count($rest) >= 2) {
            $cand7 = $this->limit(end($rest) ?: '', 3);
            $cand6 = $this->limit(implode('-', array_slice($rest, 0, -1)), 5);
            if ($cand6 !== null) {
                $n6 = $cand6;
                $n7 = $cand7;
            }
        }

        if ($n6 === null && count($rest) >= 1) {
            $n6 = $this->limit(implode('-', $rest), 5);
        }

        return [
            'nivel_1' => $n1,
            'nivel_2' => $n2,
            'nivel_3' => $n3,
            'nivel_4' => $n4,
            'nivel_5' => $n5,
            'nivel_6' => $n6,
            'nivel_7' => $n7,
            'nivel_8' => $n8,
        ];
    }

    private function limit(string $value, int $max): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        return mb_substr($value, 0, $max);
    }

    private function normalizeHeader(string $header): string
    {
        $header = trim(mb_strtolower($header));
        if ($header === '') {
            return '';
        }

        $header = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ü', 'ñ'],
            ['a', 'e', 'i', 'o', 'u', 'u', 'n'],
            $header
        );

        $header = preg_replace('/[^a-z0-9]+/u', '_', $header) ?? '';
        $header = trim($header, '_');

        return $header;
    }

    private function sanitizeValue(mixed $value, int $maxLength): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        return mb_substr($value, 0, $maxLength);
    }
}
