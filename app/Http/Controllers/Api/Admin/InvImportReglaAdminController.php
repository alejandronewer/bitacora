<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\RunInvImportacionRequest;
use App\Http\Requests\Api\Admin\StoreInvImportReglaRequest;
use App\Http\Requests\Api\Admin\UpdateInvImportReglaRequest;
use App\Http\Resources\InvImportacionRedResource;
use App\Http\Resources\InvImportReglaResource;
use App\Models\InvElementoRed;
use App\Models\InvImportacionRed;
use App\Models\InvImportRegla;
use App\Services\InvImportadorManualService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InvImportReglaAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = InvImportRegla::with(['red', 'campos'])
            ->withCount('importaciones')
            ->orderBy('red_id')
            ->orderBy('nombre');

        if ($request->filled('red_id')) {
            $query->where('red_id', $request->integer('red_id'));
        }

        return InvImportReglaResource::collection($query->get());
    }

    public function store(StoreInvImportReglaRequest $request)
    {
        $data = $request->validated();
        $data['tabla_destino'] = 'inv_elementos_redes';
        $campos = $data['campos'] ?? [];
        unset($data['campos']);

        $regla = DB::transaction(function () use ($data, $campos) {
            $regla = InvImportRegla::create($data);
            if (is_array($campos) && $campos !== []) {
                $regla->campos()->createMany($this->normalizeCampos($campos));
            }
            return $regla;
        });

        return new InvImportReglaResource($regla->load(['red', 'campos']));
    }

    public function update(UpdateInvImportReglaRequest $request, InvImportRegla $regla)
    {
        $data = $request->validated();
        $data['tabla_destino'] = 'inv_elementos_redes';
        $campos = $data['campos'] ?? null;
        unset($data['campos']);

        DB::transaction(function () use ($regla, $data, $campos) {
            $regla->fill($data)->save();

            if (is_array($campos)) {
                $regla->campos()->delete();
                if ($campos !== []) {
                    $regla->campos()->createMany($this->normalizeCampos($campos));
                }
            }
        });

        return new InvImportReglaResource($regla->refresh()->load(['red', 'campos'])->loadCount('importaciones'));
    }

    public function destroy(InvImportRegla $regla)
    {
        if ($regla->importaciones()->exists()) {
            return response()->json(['message' => 'No se puede eliminar la regla porque tiene importaciones asociadas.'], 422);
        }

        $regla->delete();
        return response()->noContent();
    }

    public function importaciones(Request $request)
    {
        $query = InvImportacionRed::with(['red', 'regla'])
            ->withCount('errores')
            ->orderByDesc('created_at');

        if ($request->filled('red_id')) {
            $query->where('red_id', $request->integer('red_id'));
        }

        $importaciones = $query->limit(100)->get();

        $latestCompletedByKey = [];
        $latestRevertibleByKey = [];
        $completedByKey = [];
        $candidatosByImportacion = [];

        foreach ($importaciones as $item) {
            if ($item->estado !== 'completado') {
                continue;
            }

            $tipo = trim((string) ($item->regla?->tipo_elemento ?? ''));
            if ($tipo === '') {
                continue;
            }

            $key = $item->red_id . '::' . $tipo;
            if (! array_key_exists($key, $latestCompletedByKey)) {
                $latestCompletedByKey[$key] = (int) $item->id;
            }

            $candidatos = $this->countRevertibleElementsForImport($item, $tipo);
            $candidatosByImportacion[(int) $item->id] = $candidatos;
            $completedByKey[$key][] = (int) $item->id;
        }

        foreach ($completedByKey as $key => $ids) {
            $latestRevertibleByKey[$key] = null;
            foreach ($ids as $id) {
                if (($candidatosByImportacion[$id] ?? 0) > 0) {
                    $latestRevertibleByKey[$key] = $id;
                    break;
                }
            }
        }

        foreach ($importaciones as $item) {
            $item->setAttribute('es_revertible', false);
            $item->setAttribute('reversion_status', 'no_disponible');
            $item->setAttribute('reversion_candidatos', 0);

            if ($item->estado !== 'completado') {
                continue;
            }

            $tipo = trim((string) ($item->regla?->tipo_elemento ?? ''));
            if ($tipo === '') {
                continue;
            }

            $key = $item->red_id . '::' . $tipo;
            $isLatestCompleted = (int) ($latestCompletedByKey[$key] ?? 0) === (int) $item->id;
            $isLatestRevertible = (int) ($latestRevertibleByKey[$key] ?? 0) === (int) $item->id;
            $candidatos = (int) ($candidatosByImportacion[(int) $item->id] ?? 0);

            $isRevertible = $isLatestRevertible && $candidatos > 0;
            $creados = (int) ($item->creados ?? 0);
            $status = $isRevertible
                ? 'revertible'
                : ($isLatestCompleted && $creados === 0
                    ? 'sin_nuevos'
                    : ($isLatestCompleted && $candidatos === 0 ? 'revertida' : 'no_disponible'));

            $item->setAttribute('es_revertible', $isRevertible);
            $item->setAttribute('reversion_status', $status);
            $item->setAttribute('reversion_candidatos', $candidatos);
        }

        return InvImportacionRedResource::collection($importaciones);
    }

    public function errores(Request $request, InvImportacionRed $importacion)
    {
        $limit = max(1, min(1000, (int) $request->input('limit', 200)));
        $erroresQuery = $importacion->errores()->orderBy('fila_numero')->orderBy('id');

        return response()->json([
            'data' => $erroresQuery
                ->limit($limit)
                ->get(['id', 'fila_numero', 'campo', 'valor', 'mensaje', 'created_at']),
            'total' => (clone $erroresQuery)->count(),
        ]);
    }

    public function revertir(InvImportacionRed $importacion)
    {
        $importacion->load('regla');

        if ($importacion->estado !== 'completado') {
            return response()->json([
                'message' => 'Solo se puede revertir una importación completada.',
            ], 422);
        }

        $tipoElemento = $importacion->regla?->tipo_elemento;
        if (! $tipoElemento) {
            return response()->json([
                'message' => 'La importación no tiene tipo de elemento asociado.',
            ], 422);
        }

        $latestRevertibleId = $this->findLatestRevertibleImportId((int) $importacion->red_id, $tipoElemento);

        if ($latestRevertibleId === null) {
            return response()->json([
                'message' => 'No hay importaciones con elementos reversibles para esa red y tipo.',
            ], 422);
        }

        if ((int) $latestRevertibleId !== (int) $importacion->id) {
            return response()->json([
                'message' => 'Solo se puede revertir la última importación reversible para esa red y tipo.',
            ], 422);
        }

        $hasLaterProcesando = InvImportacionRed::query()
            ->select('inv_importaciones_redes.id')
            ->join('inv_import_reglas', 'inv_import_reglas.id', '=', 'inv_importaciones_redes.regla_id')
            ->where('inv_importaciones_redes.red_id', $importacion->red_id)
            ->where('inv_import_reglas.tipo_elemento', $tipoElemento)
            ->where('inv_importaciones_redes.estado', 'procesando')
            ->where(function ($query) use ($importacion) {
                $query->where('inv_importaciones_redes.created_at', '>', $importacion->created_at)
                    ->orWhere(function ($inner) use ($importacion) {
                        $inner->where('inv_importaciones_redes.created_at', $importacion->created_at)
                            ->where('inv_importaciones_redes.id', '>', $importacion->id);
                    });
            })
            ->exists();

        if ($hasLaterProcesando) {
            return response()->json([
                'message' => 'Hay una importación más reciente en proceso para esa red y tipo. Intenta después.',
            ], 422);
        }

        $candidatosQuery = $this->queryRevertibleElementsForImport($importacion, $tipoElemento);

        $candidatosCount = (clone $candidatosQuery)->count();
        if ($candidatosCount === 0) {
            return response()->json([
                'message' => 'No hay elementos creados por esta importación para revertir.',
            ], 422);
        }

        $enUsoEnEntradas = DB::table('inv_entrada_elementos_red')
            ->whereIn('elemento_red_id', (clone $candidatosQuery)->select('id'))
            ->exists();

        if ($enUsoEnEntradas) {
            return response()->json([
                'message' => 'No se puede revertir: hay elementos de esa importación vinculados a entradas de bitácora.',
            ], 422);
        }

        $eliminados = 0;
        DB::transaction(function () use ($candidatosQuery, &$eliminados) {
            $eliminados = (clone $candidatosQuery)->delete();
        });

        return response()->json([
            'message' => 'Reversión completada.',
            'eliminados' => (int) $eliminados,
            'red_id' => (int) $importacion->red_id,
            'tipo_elemento' => $tipoElemento,
        ]);
    }

    private function findLatestRevertibleImportId(int $redId, string $tipoElemento): ?int
    {
        $candidatas = InvImportacionRed::query()
            ->select('inv_importaciones_redes.*')
            ->join('inv_import_reglas', 'inv_import_reglas.id', '=', 'inv_importaciones_redes.regla_id')
            ->where('inv_importaciones_redes.red_id', $redId)
            ->where('inv_import_reglas.tipo_elemento', $tipoElemento)
            ->where('inv_importaciones_redes.estado', 'completado')
            ->orderByDesc('inv_importaciones_redes.created_at')
            ->orderByDesc('inv_importaciones_redes.id')
            ->get();

        foreach ($candidatas as $importacion) {
            if ($this->countRevertibleElementsForImport($importacion, $tipoElemento) > 0) {
                return (int) $importacion->id;
            }
        }

        return null;
    }

    private function countRevertibleElementsForImport(InvImportacionRed $importacion, string $tipoElemento): int
    {
        return (int) $this->queryRevertibleElementsForImport($importacion, $tipoElemento)->count();
    }

    private function queryRevertibleElementsForImport(InvImportacionRed $importacion, string $tipoElemento)
    {
        $windowStart = $importacion->created_at;
        $windowEnd = $importacion->updated_at ?? $importacion->created_at;

        return InvElementoRed::query()
            ->where('red_id', $importacion->red_id)
            ->where('tipo', $tipoElemento)
            ->where('origen', 'csv')
            ->where('last_seen_importacion_id', $importacion->id)
            ->whereBetween('created_at', [$windowStart, $windowEnd]);
    }

    public function ejecutar(RunInvImportacionRequest $request, InvImportadorManualService $importador)
    {
        $data = $request->validated();

        $regla = InvImportRegla::with('campos')->findOrFail($data['regla_id']);
        if ((int) $regla->red_id !== (int) $data['red_id']) {
            return response()->json(['message' => 'La regla no corresponde a la red seleccionada.'], 422);
        }

        $archivo = $request->file('archivo');
        $storedPath = $archivo->store('imports/manual');
        $absolutePath = Storage::path($storedPath);

        $importacion = InvImportacionRed::create([
            'red_id' => $data['red_id'],
            'regla_id' => $regla->id,
            'usuario_id' => (int) $request->user()->id,
            'archivo_nombre' => $archivo->getClientOriginalName(),
            'fuente' => $storedPath,
            'hash_archivo' => hash_file('sha256', $absolutePath) ?: null,
            'estado' => 'procesando',
            'total_registros' => 0,
            'procesados' => 0,
            'creados' => 0,
            'actualizados' => 0,
            'marcados_baja' => 0,
        ]);

        try {
            $stats = $importador->ejecutar($importacion, $regla, $absolutePath);

            $importacion->fill([
                'estado' => 'completado',
                'total_registros' => $stats['total'],
                'procesados' => $stats['procesados'],
                'creados' => $stats['creados'],
                'actualizados' => $stats['actualizados'],
                'marcados_baja' => $stats['marcados_baja'],
            ])->save();

            return response()->json([
                'message' => 'Importación ejecutada correctamente.',
                'importacion' => new InvImportacionRedResource($importacion->load(['red', 'regla'])->loadCount('errores')),
                'errores' => count($stats['errores']),
            ]);
        } catch (\Throwable $e) {
            $importacion->fill([
                'estado' => 'fallido',
                'total_registros' => 0,
                'procesados' => 0,
                'creados' => 0,
                'actualizados' => 0,
                'marcados_baja' => 0,
            ])->save();

            return response()->json([
                'message' => 'La importación falló: ' . $e->getMessage(),
            ], 422);
        }
    }

    private function normalizeCampos(array $campos): array
    {
        $result = [];
        foreach ($campos as $i => $campo) {
            $result[] = [
                'columna_fuente' => trim((string) ($campo['columna_fuente'] ?? '')),
                'campo_destino' => trim((string) ($campo['campo_destino'] ?? '')),
                'transformacion' => $campo['transformacion'] ?? null,
                'por_defecto' => $campo['por_defecto'] ?? null,
                'es_clave_upsert' => (bool) ($campo['es_clave_upsert'] ?? false),
                'orden' => (int) ($campo['orden'] ?? ($i + 1) * 10),
                'activo' => (bool) ($campo['activo'] ?? true),
            ];
        }

        return $result;
    }
}
