<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreEntradaRequest;
use App\Http\Requests\Api\UpdateEntradaRequest;
use App\Http\Resources\EntradaResource;
use App\Models\Adjunto;
use App\Models\ConfiguracionSistema;
use App\Models\EntradaBitacora;
use App\Models\EntradaEventoDetectado;
use App\Models\PmMatrizOrdenActividad;
use App\Models\ReferenciaExterna;
use App\Support\ResumenTecnicoBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EntradaController extends Controller
{
    public function index(Request $request)
    {
        $query = EntradaBitacora::query()
            ->with(['usuario', 'criterio', 'impacto'])
            ->orderByDesc('fecha_inicio');

        $user = $request->user();
        if (! $user && ! $this->bitacoraPublica()) {
            return response()->json(['message' => 'Debes iniciar sesión para consultar la bitácora.'], 401);
        }

        if (! $user) {
            $query->publicadas();
        } elseif (! $user->hasAnyRole(['administrador', 'operador', 'invitado'])) {
            $query->publicadas();
        }

        if ($request->boolean('publicado')) {
            $query->publicadas();
        }

        if ($request->filled('desde') && $request->filled('hasta')) {
            $query->enRango($request->input('desde'), $request->input('hasta'));
        }

        if ($request->filled('criterio_id')) {
            $query->where('entrada_criterio_id', $request->input('criterio_id'));
        }

        if ($request->filled('impacto_id')) {
            $query->where('entrada_impacto_id', $request->input('impacto_id'));
        }

        if ($request->filled('pm_clase_orden_id')) {
            $query->where('pm_clase_orden_id', $request->input('pm_clase_orden_id'));
        }

        if ($request->filled('pm_clase_actividad_id')) {
            $query->where('pm_clase_actividad_id', $request->input('pm_clase_actividad_id'));
        }

        if ($request->filled('tipo_registro')) {
            $query->where('tipo_registro', $request->input('tipo_registro'));
        }

        if ($request->filled('externo_id')) {
            $like = '%' . $request->input('externo_id') . '%';
            $query->whereHas('referenciasExternas', function ($q) use ($like) {
                $q->where('externo_id', 'like', $like);
            });
        }

        if ($request->filled('inventario_elemento_id')) {
            $inventarioElementoId = (int) $request->input('inventario_elemento_id');
            $query->whereHas('inventarioElementos', function ($q) use ($inventarioElementoId) {
                $q->whereKey($inventarioElementoId);
            });
        }

        if ($request->filled('equipo_id')) {
            $equipoId = (int) $request->input('equipo_id');
            $query->where(function ($q) use ($equipoId) {
                $q->where('equipo_id', $equipoId)
                    ->orWhereHas('equipos', function ($q2) use ($equipoId) {
                        $q2->whereKey($equipoId);
                    });
            });
        }

        if ($request->filled('texto')) {
            $texto = $request->input('texto');
            $like = "%{$texto}%";
            $query->where(function ($q) use ($like) {
                $q->where('titulo', 'like', $like)
                    ->orWhere('cuerpo_texto', 'like', $like)
                    ->orWhere('resumen_tecnico', 'like', $like)
                    ->orWhereHas('pmClaseOrden', function ($q) use ($like) {
                        $q->where('nombre', 'like', $like);
                    })
                    ->orWhereHas('pmClaseActividad', function ($q) use ($like) {
                        $q->where('nombre', 'like', $like);
                    })
                    ->orWhereHas('ubicacionTecnica', function ($q) use ($like) {
                        $q->where('nombre', 'like', $like);
                    })
                    ->orWhereHas('ubicaciones', function ($q) use ($like) {
                        $q->where('nombre', 'like', $like);
                    })
                    ->orWhereHas('equipo', function ($q) use ($like) {
                        $q->where('codigo', 'like', $like)
                            ->orWhere('nombre', 'like', $like)
                            ->orWhere('area', 'like', $like);
                    })
                    ->orWhereHas('equipos', function ($q) use ($like) {
                        $q->where('codigo', 'like', $like)
                            ->orWhere('nombre', 'like', $like)
                            ->orWhere('area', 'like', $like);
                    })
                    ->orWhereHas('inventarioElementos', function ($q) use ($like) {
                        $q->where('tipo', 'like', $like)
                            ->orWhere('nombre', 'like', $like)
                            ->orWhere('codigo', 'like', $like)
                            ->orWhereHas('red', function ($q) use ($like) {
                                $q->where('nombre', 'like', $like)
                                    ->orWhere('codigo', 'like', $like)
                                    ->orWhereHas('dominios', function ($q) use ($like) {
                                        $q->where('nombre', 'like', $like)
                                            ->orWhere('codigo', 'like', $like);
                                    });
                            });
                    })
                    ->orWhereHas('referenciasExternas', function ($q) use ($like) {
                        $q->where('externo_id', 'like', $like)
                            ->orWhereHas('sistema', function ($q) use ($like) {
                                $q->where('nombre', 'like', $like);
                            });
                    });
            });
        }

        return EntradaResource::collection($query->paginate(20));
    }

    public function gestion(Request $request)
    {
        $user = $request->user();
        if (! $user || ! $user->hasAnyRole(['administrador', 'operador'])) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $query = EntradaBitacora::query()
            ->with(['usuario', 'criterio', 'impacto', 'pmClaseOrden', 'pmClaseActividad'])
            ->orderByDesc('fecha_inicio');

        if ($user->hasRole('operador') && ! $user->hasRole('administrador')) {
            $query->porUsuario($user->id);
        } elseif ($request->filled('usuario_id')) {
            $query->where('usuario_id', $request->input('usuario_id'));
        }

        if ($request->filled('publicado')) {
            $query->where('publicado', (int) $request->input('publicado'));
        }

        if ($request->filled('desde') && $request->filled('hasta')) {
            $query->enRango($request->input('desde'), $request->input('hasta'));
        }

        if ($request->filled('criterio_id')) {
            $query->where('entrada_criterio_id', $request->input('criterio_id'));
        }

        if ($request->filled('impacto_id')) {
            $query->where('entrada_impacto_id', $request->input('impacto_id'));
        }

        if ($request->filled('pm_clase_orden_id')) {
            $query->where('pm_clase_orden_id', $request->input('pm_clase_orden_id'));
        }

        if ($request->filled('pm_clase_actividad_id')) {
            $query->where('pm_clase_actividad_id', $request->input('pm_clase_actividad_id'));
        }

        if ($request->filled('tipo_registro')) {
            $query->where('tipo_registro', $request->input('tipo_registro'));
        }

        if ($request->filled('externo_id')) {
            $like = '%' . $request->input('externo_id') . '%';
            $query->whereHas('referenciasExternas', function ($q) use ($like) {
                $q->where('externo_id', 'like', $like);
            });
        }

        if ($request->filled('inventario_elemento_id')) {
            $inventarioElementoId = (int) $request->input('inventario_elemento_id');
            $query->whereHas('inventarioElementos', function ($q) use ($inventarioElementoId) {
                $q->whereKey($inventarioElementoId);
            });
        }

        if ($request->filled('equipo_id')) {
            $equipoId = (int) $request->input('equipo_id');
            $query->where(function ($q) use ($equipoId) {
                $q->where('equipo_id', $equipoId)
                    ->orWhereHas('equipos', function ($q2) use ($equipoId) {
                        $q2->whereKey($equipoId);
                    });
            });
        }

        if ($request->filled('texto')) {
            $texto = $request->input('texto');
            $like = "%{$texto}%";
            $query->where(function ($q) use ($like) {
                $q->where('titulo', 'like', $like)
                    ->orWhere('cuerpo_texto', 'like', $like)
                    ->orWhere('resumen_tecnico', 'like', $like)
                    ->orWhereHas('pmClaseOrden', function ($q) use ($like) {
                        $q->where('nombre', 'like', $like);
                    })
                    ->orWhereHas('pmClaseActividad', function ($q) use ($like) {
                        $q->where('nombre', 'like', $like);
                    })
                    ->orWhereHas('ubicacionTecnica', function ($q) use ($like) {
                        $q->where('nombre', 'like', $like);
                    })
                    ->orWhereHas('ubicaciones', function ($q) use ($like) {
                        $q->where('nombre', 'like', $like);
                    })
                    ->orWhereHas('equipo', function ($q) use ($like) {
                        $q->where('codigo', 'like', $like)
                            ->orWhere('nombre', 'like', $like)
                            ->orWhere('area', 'like', $like);
                    })
                    ->orWhereHas('equipos', function ($q) use ($like) {
                        $q->where('codigo', 'like', $like)
                            ->orWhere('nombre', 'like', $like)
                            ->orWhere('area', 'like', $like);
                    })
                    ->orWhereHas('inventarioElementos', function ($q) use ($like) {
                        $q->where('tipo', 'like', $like)
                            ->orWhere('nombre', 'like', $like)
                            ->orWhere('codigo', 'like', $like)
                            ->orWhereHas('red', function ($q) use ($like) {
                                $q->where('nombre', 'like', $like)
                                    ->orWhere('codigo', 'like', $like)
                                    ->orWhereHas('dominios', function ($q) use ($like) {
                                        $q->where('nombre', 'like', $like)
                                            ->orWhere('codigo', 'like', $like);
                                    });
                            });
                    })
                    ->orWhereHas('referenciasExternas', function ($q) use ($like) {
                        $q->where('externo_id', 'like', $like)
                            ->orWhereHas('sistema', function ($q) use ($like) {
                                $q->where('nombre', 'like', $like);
                            });
                    });
            });
        }

        $perPage = (int) $request->input('per_page', 20);
        if ($perPage < 1 || $perPage > 100) {
            $perPage = 20;
        }

        return EntradaResource::collection($query->paginate($perPage));
    }

    public function show(Request $request, EntradaBitacora $entrada)
    {
        if (! $request->user() && ! $this->bitacoraPublica()) {
            return response()->json(['message' => 'Debes iniciar sesión para consultar la bitácora.'], 401);
        }

        $this->authorize('view', $entrada);

        $baseQuery = EntradaBitacora::query()->orderByDesc('fecha_inicio')->orderByDesc('id');

        if (! $request->user()) {
            $baseQuery->publicadas();
        }

        $prevId = (clone $baseQuery)
            ->where(function ($q) use ($entrada) {
                $q->where('fecha_inicio', '>', $entrada->fecha_inicio)
                    ->orWhere(function ($q2) use ($entrada) {
                        $q2->where('fecha_inicio', $entrada->fecha_inicio)
                            ->where('id', '>', $entrada->id);
                    });
            })
            ->orderBy('fecha_inicio')
            ->orderBy('id')
            ->value('id');

        $nextId = (clone $baseQuery)
            ->where(function ($q) use ($entrada) {
                $q->where('fecha_inicio', '<', $entrada->fecha_inicio)
                    ->orWhere(function ($q2) use ($entrada) {
                        $q2->where('fecha_inicio', $entrada->fecha_inicio)
                            ->where('id', '<', $entrada->id);
                    });
            })
            ->orderByDesc('fecha_inicio')
            ->orderByDesc('id')
            ->value('id');

        $entrada->setAttribute('prev_id', $prevId);
        $entrada->setAttribute('next_id', $nextId);

        return new EntradaResource($entrada->load([
            'usuario',
            'criterio',
            'impacto',
            'pmClaseOrden',
            'pmClaseActividad',
            'eventoDetectado',
            'adjuntos',
            'referenciasExternas.sistema',
            'ubicacionTecnica',
            'ubicaciones',
            'equipo.ubicacionTecnica',
            'equipos',
            'equipos.ubicacionTecnica',
            'inventarioElementos.red',
            'inventarioElementos.detalleNodo',
            'inventarioElementos.detalleEnlace',
            'inventarioElementos.detalleServicio',
            'inventarioElementos.detalleTunel',
        ]));
    }

    public function store(StoreEntradaRequest $request)
    {
        $this->authorize('create', EntradaBitacora::class);

        $data = $request->validated();

        if (! $this->pmCombinacionValida($data['pm_clase_orden_id'] ?? null, $data['pm_clase_actividad_id'] ?? null)) {
            return response()->json(['message' => 'Combinacion PM invalida'], 422);
        }

        return DB::transaction(function () use ($request, $data) {
            $entrada = EntradaBitacora::create(array_merge($data, [
                'usuario_id' => $request->user()->id,
            ]));

            $this->syncRelaciones($entrada, $data, $request->user()->id);

            if (! empty($data['evento_detectado'])) {
                EntradaEventoDetectado::updateOrCreate(
                    ['entrada_id' => $entrada->id],
                    array_merge($data['evento_detectado'], ['entrada_id' => $entrada->id])
                );
            }

            $entrada->resumen_tecnico = ResumenTecnicoBuilder::build($entrada->refresh());
            $entrada->save();

            return new EntradaResource($entrada->refresh());
        });
    }

    public function update(UpdateEntradaRequest $request, EntradaBitacora $entrada)
    {
        $this->authorize('update', $entrada);

        $data = $request->validated();

        if (! $this->pmCombinacionValida($data['pm_clase_orden_id'] ?? null, $data['pm_clase_actividad_id'] ?? null)) {
            return response()->json(['message' => 'Combinacion PM invalida'], 422);
        }

        return DB::transaction(function () use ($entrada, $data, $request) {
            $entrada->fill($data)->save();

            $this->syncRelaciones($entrada, $data, $request->user()->id);

            if (array_key_exists('evento_detectado', $data)) {
                if ($data['evento_detectado']) {
                    EntradaEventoDetectado::updateOrCreate(
                        ['entrada_id' => $entrada->id],
                        array_merge($data['evento_detectado'], ['entrada_id' => $entrada->id])
                    );
                } else {
                    $entrada->eventoDetectado()->delete();
                }
            }

            if (array_key_exists('adjuntos_eliminar', $data)) {
                $ids = collect($data['adjuntos_eliminar'] ?? [])
                    ->filter()
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values()
                    ->all();

                if (! empty($ids)) {
                    $adjuntos = Adjunto::where('entrada_id', $entrada->id)
                        ->whereIn('id', $ids)
                        ->get();

                    foreach ($adjuntos as $adjunto) {
                        if ($adjunto->ruta) {
                            Storage::disk('public')->delete($adjunto->ruta);
                        }
                        $adjunto->delete();
                    }
                }
            }

            if (array_key_exists('referencias_eliminar', $data)) {
                $ids = collect($data['referencias_eliminar'] ?? [])
                    ->filter()
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values()
                    ->all();

                if (! empty($ids)) {
                    ReferenciaExterna::where('entrada_id', $entrada->id)
                        ->whereIn('id', $ids)
                        ->delete();
                }
            }

            $entrada->resumen_tecnico = ResumenTecnicoBuilder::build($entrada->refresh());
            $entrada->save();

            return new EntradaResource($entrada->refresh());
        });
    }

    public function destroy(EntradaBitacora $entrada)
    {
        $this->authorize('delete', $entrada);
        $entrada->delete();

        return response()->noContent();
    }

    public function publicar(EntradaBitacora $entrada)
    {
        $this->authorize('publish', $entrada);

        // publicado_at representa la primera publicación histórica de la entrada.
        // No se vuelve a actualizar en republicaciones.
        if ($entrada->publicado_at === null) {
            $entrada->publicado_at = now();
        }

        $entrada->publicado = 1;
        $entrada->save();

        return new EntradaResource($entrada->refresh());
    }

    public function despublicar(EntradaBitacora $entrada)
    {
        $this->authorize('publish', $entrada);

        // No se limpia publicado_at: conserva la última fecha/hora de publicación.
        $entrada->publicado = 0;
        $entrada->save();

        return new EntradaResource($entrada->refresh());
    }

    private function pmCombinacionValida(?int $ordenId, ?int $actividadId): bool
    {
        if (! $ordenId && ! $actividadId) {
            return true;
        }

        if (! $ordenId || ! $actividadId) {
            return false;
        }

        return PmMatrizOrdenActividad::where('pm_clase_orden_id', $ordenId)
            ->where('pm_clase_actividad_id', $actividadId)
            ->exists();
    }

    private function syncRelaciones(EntradaBitacora $entrada, array $data, int $usuarioId): void
    {
        if (array_key_exists('ubicaciones', $data)) {
            $ids = collect($data['ubicaciones'] ?? [])
                ->map(fn ($item) => (int) ($item['id'] ?? 0))
                ->filter()
                ->unique()
                ->values()
                ->all();
            $entrada->ubicaciones()->sync($ids);
        }

        if (array_key_exists('equipos', $data)) {
            $ids = collect($data['equipos'] ?? [])
                ->map(fn ($item) => (int) ($item['id'] ?? 0))
                ->filter()
                ->unique()
                ->values()
                ->all();
            $entrada->equipos()->sync($ids);
        }

        if (array_key_exists('inventario_elementos', $data)) {
            $ids = collect($data['inventario_elementos'] ?? [])
                ->map(function ($item) {
                    if (is_array($item)) {
                        return $item['id'] ?? null;
                    }
                    return $item;
                })
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();
            $entrada->inventarioElementos()->sync($ids);
        }

        if (array_key_exists('adjuntos', $data)) {
            $ids = collect($data['adjuntos'] ?? [])
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();

            if ($ids->isNotEmpty()) {
                Adjunto::whereIn('id', $ids)
                    ->whereNull('entrada_id')
                    ->where('usuario_id', $usuarioId)
                    ->update(['entrada_id' => $entrada->id]);
            }
        }
    }

    private function bitacoraPublica(): bool
    {
        $valor = ConfiguracionSistema::query()
            ->where('clave', 'bitacora_publica')
            ->value('valor');

        if ($valor === null) {
            return true;
        }

        if (is_bool($valor)) {
            return $valor;
        }

        if (is_numeric($valor)) {
            return (int) $valor === 1;
        }

        return in_array(mb_strtolower(trim((string) $valor)), ['1', 'true', 'yes', 'si', 'sí'], true);
    }
}
