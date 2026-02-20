<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\StoreDmUbicacionTecnicaRequest;
use App\Http\Requests\Api\Admin\UpdateDmUbicacionTecnicaRequest;
use App\Http\Resources\DmUbicacionTecnicaAdminResource;
use App\Models\ConfiguracionSistema;
use App\Models\DmUbicacionTecnica;
use Illuminate\Http\Request;

class DmUbicacionTecnicaAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = DmUbicacionTecnica::query()
            ->withCount(['equipos', 'entradasDirectas', 'entradasPivot'])
            ->orderBy('codigo');

        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $like = "%{$q}%";

            $query->where(function ($builder) use ($like) {
                $builder->where('codigo', 'like', $like)
                    ->orWhere('nombre', 'like', $like)
                    ->orWhere('nivel_1', 'like', $like)
                    ->orWhere('nivel_2', 'like', $like)
                    ->orWhere('nivel_3', 'like', $like)
                    ->orWhere('nivel_4', 'like', $like)
                    ->orWhere('nivel_5', 'like', $like)
                    ->orWhere('nivel_6', 'like', $like)
                    ->orWhere('nivel_7', 'like', $like)
                    ->orWhere('nivel_8', 'like', $like);
            });
        }

        if ($request->filled('nivel_1')) {
            $nivel1 = strtoupper(trim((string) $request->input('nivel_1')));
            if ($nivel1 !== '') {
                $query->where('nivel_1', substr($nivel1, 0, 2));
            }
        }

        $activo = $this->parseActivo($request->input('activo'));
        if ($activo !== null) {
            $query->where('activo', $activo);
        }

        $uso = $this->parseUso($request->input('uso'));
        if ($uso === 'usado') {
            $query->where(function ($builder) {
                $builder->whereHas('equipos')
                    ->orWhereHas('entradasDirectas')
                    ->orWhereHas('entradasPivot');
            });
        } elseif ($uso === 'no_usado') {
            $query->whereDoesntHave('equipos')
                ->whereDoesntHave('entradasDirectas')
                ->whereDoesntHave('entradasPivot');
        }

        $perPage = $this->resolvePerPage($request);

        return DmUbicacionTecnicaAdminResource::collection(
            $query->paginate($perPage)
        );
    }

    public function store(StoreDmUbicacionTecnicaRequest $request)
    {
        $data = $request->validated();
        $data['fuente'] = 'Manual';
        $data['last_sync_at'] = null;

        $ubicacion = DmUbicacionTecnica::create($data);
        return new DmUbicacionTecnicaAdminResource($ubicacion);
    }

    public function update(UpdateDmUbicacionTecnicaRequest $request, DmUbicacionTecnica $ubicacion)
    {
        $data = $request->validated();
        unset($data['fuente'], $data['last_sync_at']);

        $ubicacion->fill($data)->save();
        return new DmUbicacionTecnicaAdminResource($ubicacion->refresh());
    }

    public function destroy(DmUbicacionTecnica $ubicacion)
    {
        $enUso = $ubicacion->equipos()->exists()
            || $ubicacion->entradasDirectas()->exists()
            || $ubicacion->entradasPivot()->exists();

        if ($enUso) {
            return response()->json(['message' => 'No se puede eliminar: la ubicación está en uso.'], 422);
        }

        $ubicacion->delete();
        return response()->noContent();
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

    private function parseUso(mixed $raw): ?string
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        $value = strtolower(trim((string) $raw));
        if (in_array($value, ['usado', 'en_uso', 'used'], true)) {
            return 'usado';
        }

        if (in_array($value, ['no_usado', 'libre', 'unused'], true)) {
            return 'no_usado';
        }

        return null;
    }
}
