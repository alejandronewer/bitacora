<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvElementoRedResource;
use App\Models\InvElementoRed;
use Illuminate\Http\Request;

class InventarioController extends Controller
{
    public function elementos(Request $request)
    {
        $query = InvElementoRed::with([
            'red.dominios',
            'detalleNodo',
            'detalleEnlace',
            'detalleServicio',
            'detalleTunel',
        ])
            ->where('estado', 'activo')
            ->orderBy('codigo');

        if ($request->filled('red_id')) {
            $query->where('red_id', $request->input('red_id'));
        }

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($builder) use ($q) {
                $builder->where('codigo', 'like', "%{$q}%")
                    ->orWhere('nombre', 'like', "%{$q}%")
                    ->orWhere('tipo', 'like', "%{$q}%");
            });
        }

        if ($request->filled('limit')) {
            $limit = max(1, min((int) $request->input('limit'), 1000));
            $query->limit($limit);
        }

        return InvElementoRedResource::collection($query->get());
    }
}
