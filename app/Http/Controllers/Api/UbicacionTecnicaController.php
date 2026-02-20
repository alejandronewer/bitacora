<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DmUbicacionTecnicaResource;
use App\Models\DmUbicacionTecnica;
use Illuminate\Http\Request;

class UbicacionTecnicaController extends Controller
{
    public function index(Request $request)
    {
        if ($request->filled('distinct_nivel')) {
            return response()->json([
                'data' => $this->distinctNivel($request),
            ]);
        }

        $query = DmUbicacionTecnica::query()
            ->where('activo', 1)
            ->orderBy('codigo');

        if ($request->filled('id')) {
            $query->whereKey((int) $request->input('id'));
        }

        $this->applyNivelFilters($query, $request);

        if ($request->filled('q')) {
            $like = '%' . trim((string) $request->input('q')) . '%';
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

        $total = (clone $query)->count();
        $limit = null;
        if ($request->filled('limit')) {
            $limit = max(1, min((int) $request->input('limit'), 1000));
            $query->limit($limit);
        }

        $rows = $query->get();

        return DmUbicacionTecnicaResource::collection($rows)->additional([
            'meta' => [
                'total' => $total,
                'shown' => $rows->count(),
                'limit' => $limit,
            ],
        ]);
    }

    private function distinctNivel(Request $request): array
    {
        $nivel = (int) $request->input('distinct_nivel');
        if (! in_array($nivel, [1, 2, 3], true)) {
            return [];
        }

        $column = 'nivel_' . $nivel;
        $query = DmUbicacionTecnica::query()
            ->where('activo', 1)
            ->whereNotNull($column)
            ->where($column, '<>', '');

        $this->applyNivelFilters($query, $request, $nivel);

        return $query->distinct()
            ->orderBy($column)
            ->pluck($column)
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->values()
            ->all();
    }

    private function applyNivelFilters($query, Request $request, ?int $maxNivelExclusive = null): void
    {
        $niveles = [
            1 => 2,
            2 => 4,
            3 => 2,
        ];

        foreach ($niveles as $nivel => $length) {
            if ($maxNivelExclusive !== null && $nivel >= $maxNivelExclusive) {
                continue;
            }

            $key = 'nivel_' . $nivel;
            if (! $request->filled($key)) {
                continue;
            }

            $value = strtoupper(trim((string) $request->input($key)));
            if ($value === '') {
                continue;
            }

            $query->where($key, substr($value, 0, $length));
        }
    }
}
