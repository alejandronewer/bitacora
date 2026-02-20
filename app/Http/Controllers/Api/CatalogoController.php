<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CatEntradaCriterioResource;
use App\Http\Resources\CatEntradaImpactoResource;
use App\Http\Resources\PmClaseActividadResource;
use App\Http\Resources\PmClaseOrdenResource;
use App\Http\Resources\PmMatrizOrdenActividadResource;
use App\Models\CatEntradaCriterio;
use App\Models\CatEntradaImpacto;
use App\Models\PmClaseActividad;
use App\Models\PmClaseOrden;
use App\Models\PmMatrizOrdenActividad;

class CatalogoController extends Controller
{
    public function criterios()
    {
        return CatEntradaCriterioResource::collection(
            CatEntradaCriterio::where('activo', 1)->orderBy('orden')->get()
        );
    }

    public function impactos()
    {
        return CatEntradaImpactoResource::collection(
            CatEntradaImpacto::where('activo', 1)->orderBy('orden')->get()
        );
    }

    public function pmOrdenes()
    {
        return PmClaseOrdenResource::collection(
            PmClaseOrden::where('activo', 1)->orderBy('nombre')->get()
        );
    }

    public function pmActividades()
    {
        return PmClaseActividadResource::collection(
            PmClaseActividad::where('activo', 1)->orderBy('nombre')->get()
        );
    }

    public function pmMatriz()
    {
        return PmMatrizOrdenActividadResource::collection(
            PmMatrizOrdenActividad::where('activo', 1)
                ->whereHas('claseOrden', fn ($q) => $q->where('activo', 1))
                ->whereHas('claseActividad', fn ($q) => $q->where('activo', 1))
                ->get()
        );
    }
}
