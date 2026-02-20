<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\StorePmMatrizOrdenActividadRequest;
use App\Http\Requests\Api\Admin\UpdatePmMatrizOrdenActividadRequest;
use App\Http\Resources\PmMatrizOrdenActividadResource;
use App\Models\EntradaBitacora;
use App\Models\PmMatrizOrdenActividad;

class PmMatrizOrdenActividadController extends Controller
{
    public function index()
    {
        $items = PmMatrizOrdenActividad::query()
            ->select('pm_matriz_orden_actividad.*')
            ->addSelect([
                'entradas_count' => EntradaBitacora::selectRaw('count(*)')
                    ->whereColumn('pm_clase_orden_id', 'pm_matriz_orden_actividad.pm_clase_orden_id')
                    ->whereColumn('pm_clase_actividad_id', 'pm_matriz_orden_actividad.pm_clase_actividad_id'),
            ])
            ->get();

        return PmMatrizOrdenActividadResource::collection($items);
    }

    public function store(StorePmMatrizOrdenActividadRequest $request)
    {
        $data = $request->validated();

        $exists = PmMatrizOrdenActividad::where('pm_clase_orden_id', $data['pm_clase_orden_id'])
            ->where('pm_clase_actividad_id', $data['pm_clase_actividad_id'])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'La combinacion ya existe.'], 422);
        }

        return new PmMatrizOrdenActividadResource(PmMatrizOrdenActividad::create($data));
    }

    public function update(UpdatePmMatrizOrdenActividadRequest $request, PmMatrizOrdenActividad $matriz)
    {
        $data = $request->validated();
        $ordenId = $data['pm_clase_orden_id'] ?? $matriz->pm_clase_orden_id;
        $actividadId = $data['pm_clase_actividad_id'] ?? $matriz->pm_clase_actividad_id;

        $exists = PmMatrizOrdenActividad::where('pm_clase_orden_id', $ordenId)
            ->where('pm_clase_actividad_id', $actividadId)
            ->where('id', '<>', $matriz->id)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'La combinacion ya existe.'], 422);
        }

        $matriz->fill($data)->save();
        return new PmMatrizOrdenActividadResource($matriz->refresh());
    }

    public function destroy(PmMatrizOrdenActividad $matriz)
    {
        $enUso = EntradaBitacora::where('pm_clase_orden_id', $matriz->pm_clase_orden_id)
            ->where('pm_clase_actividad_id', $matriz->pm_clase_actividad_id)
            ->exists();

        if ($enUso) {
            return response()->json(['message' => 'No se puede eliminar: combinacion en uso'], 422);
        }

        $matriz->delete();
        return response()->noContent();
    }
}
