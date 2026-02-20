<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\StoreDmUbicacionDetalleNivelRequest;
use App\Http\Requests\Api\Admin\UpdateDmUbicacionDetalleNivelRequest;
use App\Http\Resources\DmUbicacionDetalleNivelAdminResource;
use App\Models\ConfiguracionSistema;
use App\Models\DmUbicacionDetalleNivel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DmUbicacionDetalleNivelAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = DmUbicacionDetalleNivel::query()
            ->orderBy('nivel')
            ->orderBy('rama_nivel_3')
            ->orderBy('codigo');

        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $like = "%{$q}%";
            $query->where(function (Builder $builder) use ($like) {
                $builder->where('codigo', 'like', $like)
                    ->orWhere('nombre', 'like', $like)
                    ->orWhere('descripcion', 'like', $like);
            });
        }

        if ($request->filled('nivel')) {
            $nivel = (int) $request->input('nivel');
            if ($nivel >= 1 && $nivel <= 8) {
                $query->where('nivel', $nivel);
            }
        }

        if ($request->filled('rama_nivel_3')) {
            $rama = strtoupper(substr(trim((string) $request->input('rama_nivel_3')), 0, 2));
            if ($rama !== '') {
                $query->where('rama_nivel_3', $rama);
            }
        }

        if ($request->filled('origen')) {
            $origen = trim((string) $request->input('origen'));
            if (in_array($origen, ['Detectado', 'Homologado'], true)) {
                $query->where('origen', $origen);
            }
        }

        $activo = $this->parseActivo($request->input('activo'));
        if ($activo !== null) {
            $query->where('activo', $activo);
        }

        $this->applyUbicacionContextFilters($query, $request);

        $perPage = $this->resolvePerPage($request);

        return DmUbicacionDetalleNivelAdminResource::collection(
            $query->paginate($perPage)
        );
    }

    public function store(StoreDmUbicacionDetalleNivelRequest $request)
    {
        $data = $request->validated();
        $data = $this->normalizePayload($data);

        $row = DmUbicacionDetalleNivel::create($data);

        return new DmUbicacionDetalleNivelAdminResource($row);
    }

    public function update(UpdateDmUbicacionDetalleNivelRequest $request, DmUbicacionDetalleNivel $detalleNivel)
    {
        $data = $request->validated();
        $data = $this->normalizePayload(array_merge($detalleNivel->toArray(), $data));

        $detalleNivel->fill($data)->save();

        return new DmUbicacionDetalleNivelAdminResource($detalleNivel->refresh());
    }

    public function destroy(DmUbicacionDetalleNivel $detalleNivel)
    {
        $detalleNivel->delete();

        return response()->noContent();
    }

    private function normalizePayload(array $data): array
    {
        $nivel = (int) ($data['nivel'] ?? 0);

        $data['nivel'] = $nivel;
        $data['codigo'] = strtoupper(substr(trim((string) ($data['codigo'] ?? '')), 0, 20));
        $data['nombre'] = $this->toNullableString($data['nombre'] ?? null, 160);
        $data['descripcion'] = $this->toNullableString($data['descripcion'] ?? null, 255);

        $rama = strtoupper(substr(trim((string) ($data['rama_nivel_3'] ?? '')), 0, 2));
        $data['rama_nivel_3'] = ($nivel >= 1 && $nivel <= 3) ? '--' : $rama;

        $data['activo'] = array_key_exists('activo', $data)
            ? ((int) ((bool) $data['activo']))
            : 1;

        $origen = trim((string) ($data['origen'] ?? ''));
        $data['origen'] = in_array($origen, ['Detectado', 'Homologado'], true)
            ? $origen
            : 'Homologado';

        return $data;
    }

    private function toNullableString(mixed $value, int $max): ?string
    {
        $normalized = trim((string) $value);
        if ($normalized === '') {
            return null;
        }

        return substr($normalized, 0, $max);
    }

    private function applyUbicacionContextFilters(Builder $query, Request $request): void
    {
        $nivel1 = strtoupper(substr(trim((string) $request->input('nivel_1', '')), 0, 2));
        $nivel2 = strtoupper(substr(trim((string) $request->input('nivel_2', '')), 0, 4));
        $nivel3 = strtoupper(substr(trim((string) $request->input('nivel_3', '')), 0, 2));
        $qUbicacion = trim((string) $request->input('q_ubicacion', ''));

        if ($nivel1 === '' && $nivel2 === '' && $nivel3 === '' && $qUbicacion === '') {
            return;
        }

        $query->whereExists(function ($subQuery) use ($nivel1, $nivel2, $nivel3, $qUbicacion) {
            $subQuery->selectRaw('1')
                ->from('dm_ubicaciones_tecnicas as u')
                ->where('u.activo', 1);

            if ($nivel1 !== '') {
                $subQuery->where('u.nivel_1', $nivel1);
            }
            if ($nivel2 !== '') {
                $subQuery->where('u.nivel_2', $nivel2);
            }
            if ($nivel3 !== '') {
                $subQuery->where('u.nivel_3', $nivel3);
            }

            if ($qUbicacion !== '') {
                $like = '%' . $qUbicacion . '%';
                $subQuery->where(function ($builder) use ($like) {
                    $builder->where('u.codigo', 'like', $like)
                        ->orWhere('u.nombre', 'like', $like)
                        ->orWhere('u.nivel_1', 'like', $like)
                        ->orWhere('u.nivel_2', 'like', $like)
                        ->orWhere('u.nivel_3', 'like', $like)
                        ->orWhere('u.nivel_4', 'like', $like)
                        ->orWhere('u.nivel_5', 'like', $like)
                        ->orWhere('u.nivel_6', 'like', $like)
                        ->orWhere('u.nivel_7', 'like', $like)
                        ->orWhere('u.nivel_8', 'like', $like);
                });
            }

            $subQuery->where(function ($builder) {
                foreach (range(1, 8) as $nivel) {
                    $builder->orWhere(function ($branch) use ($nivel) {
                        $branch->where('dm_ubicacion_detalle_nivel.nivel', $nivel)
                            ->whereColumn('u.nivel_' . $nivel, 'dm_ubicacion_detalle_nivel.codigo');

                        if ($nivel <= 3) {
                            $branch->where('dm_ubicacion_detalle_nivel.rama_nivel_3', '--');
                        } else {
                            $branch->whereColumn('u.nivel_3', 'dm_ubicacion_detalle_nivel.rama_nivel_3');
                        }
                    });
                }
            });
        });
    }

    private function resolvePerPage(Request $request): int
    {
        $default = (int) ConfiguracionSistema::query()
            ->where('clave', 'paginacion.default_per_page')
            ->value('valor');

        if ($default < 1 || $default > 100) {
            $default = 20;
        }

        $perPage = (int) $request->input('per_page', $default);
        if ($perPage < 1 || $perPage > 100) {
            $perPage = $default;
        }

        return $perPage;
    }

    private function parseActivo(mixed $raw): ?int
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        $value = strtolower(trim((string) $raw));
        if (in_array($value, ['1', 'true', 'activo', 'si'], true)) {
            return 1;
        }

        if (in_array($value, ['0', 'false', 'inactivo', 'no'], true)) {
            return 0;
        }

        return null;
    }
}
