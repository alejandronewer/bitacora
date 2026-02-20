<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DmEquipoResource;
use App\Models\DmEquipo;
use Illuminate\Http\Request;

class EquipoController extends Controller
{
    public function index(Request $request)
    {
        $query = DmEquipo::query()->where('activo', 1)->orderBy('codigo');

        if ($request->filled('ubicacion_tecnica_id')) {
            $query->where('ubicacion_tecnica_id', (int) $request->input('ubicacion_tecnica_id'));
        }

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($builder) use ($q) {
                $builder->where('codigo', 'like', "%{$q}%")
                    ->orWhere('nombre', 'like', "%{$q}%");
            });
        }

        $defaultLimit = $request->filled('ubicacion_tecnica_id') ? 500 : 50;
        $limit = max(1, min((int) $request->input('limit', $defaultLimit), 1000));

        return DmEquipoResource::collection($query->limit($limit)->get());
    }
}
