<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\StorePmClaseActividadRequest;
use App\Http\Requests\Api\Admin\UpdatePmClaseActividadRequest;
use App\Http\Resources\PmClaseActividadResource;
use App\Models\EntradaBitacora;
use App\Models\PmClaseActividad;
use App\Models\PmMatrizOrdenActividad;

class PmClaseActividadController extends Controller
{
    public function index()
    {
        return PmClaseActividadResource::collection(
            PmClaseActividad::withCount(['entradas', 'matrizActividades'])
                ->orderBy('nombre')
                ->get()
        );
    }

    public function store(StorePmClaseActividadRequest $request)
    {
        return new PmClaseActividadResource(PmClaseActividad::create($request->validated()));
    }

    public function update(UpdatePmClaseActividadRequest $request, PmClaseActividad $actividad)
    {
        $actividad->fill($request->validated())->save();
        return new PmClaseActividadResource($actividad->refresh());
    }

    public function destroy(PmClaseActividad $actividad)
    {
        $enUso = EntradaBitacora::where('pm_clase_actividad_id', $actividad->id)->exists()
            || PmMatrizOrdenActividad::where('pm_clase_actividad_id', $actividad->id)->exists();

        if ($enUso) {
            return response()->json(['message' => 'No se puede eliminar: clase de actividad en uso'], 422);
        }

        $actividad->delete();
        return response()->noContent();
    }
}
