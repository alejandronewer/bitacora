<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\StoreDmEquipoRequest;
use App\Http\Requests\Api\Admin\UpdateDmEquipoRequest;
use App\Http\Resources\DmEquipoAdminResource;
use App\Models\ConfiguracionSistema;
use App\Models\DmEquipo;
use Illuminate\Http\Request;

class DmEquipoAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = DmEquipo::query()
            ->with(['ubicacionTecnica'])
            ->withCount(['entradasDirectas', 'entradasPivot'])
            ->orderBy('codigo');

        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $like = "%{$q}%";

            $query->where(function ($builder) use ($like) {
                $builder->where('codigo', 'like', $like)
                    ->orWhere('nombre', 'like', $like)
                    ->orWhere('area', 'like', $like)
                    ->orWhereHas('ubicacionTecnica', function ($ubicacionQuery) use ($like) {
                        $ubicacionQuery->where('codigo', 'like', $like)
                            ->orWhere('nombre', 'like', $like);
                    });
            });
        }

        if ($request->filled('ubicacion_tecnica_id')) {
            $query->where('ubicacion_tecnica_id', (int) $request->input('ubicacion_tecnica_id'));
        }

        $activo = $this->parseActivo($request->input('activo'));
        if ($activo !== null) {
            $query->where('activo', $activo);
        }

        $uso = $this->parseUso($request->input('uso'));
        if ($uso === 'usado') {
            $query->where(function ($builder) {
                $builder->whereHas('entradasDirectas')
                    ->orWhereHas('entradasPivot');
            });
        } elseif ($uso === 'no_usado') {
            $query->whereDoesntHave('entradasDirectas')
                ->whereDoesntHave('entradasPivot');
        }

        $perPage = $this->resolvePerPage($request);

        return DmEquipoAdminResource::collection(
            $query->paginate($perPage)
        );
    }

    public function store(StoreDmEquipoRequest $request)
    {
        $data = $request->validated();
        $data['fuente'] = 'Manual';
        $data['last_sync_at'] = null;

        $equipo = DmEquipo::create($data);
        return new DmEquipoAdminResource($equipo->load('ubicacionTecnica'));
    }

    public function update(UpdateDmEquipoRequest $request, DmEquipo $equipo)
    {
        $data = $request->validated();
        unset($data['fuente'], $data['last_sync_at']);

        $equipo->fill($data)->save();
        return new DmEquipoAdminResource($equipo->refresh()->load('ubicacionTecnica'));
    }

    public function destroy(DmEquipo $equipo)
    {
        $enUso = $equipo->entradasDirectas()->exists()
            || $equipo->entradasPivot()->exists();

        if ($enUso) {
            return response()->json(['message' => 'No se puede eliminar: el equipo está en uso.'], 422);
        }

        $equipo->delete();
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
