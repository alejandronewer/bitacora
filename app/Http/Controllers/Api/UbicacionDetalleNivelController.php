<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DmUbicacionDetalleNivelResource;
use App\Models\DmUbicacionDetalleNivel;
use Illuminate\Http\Request;

class UbicacionDetalleNivelController extends Controller
{
    public function index(Request $request)
    {
        $query = DmUbicacionDetalleNivel::query()
            ->where('activo', 1)
            ->orderBy('nivel')
            ->orderBy('rama_nivel_3')
            ->orderBy('codigo');

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

        return DmUbicacionDetalleNivelResource::collection($query->get());
    }
}
