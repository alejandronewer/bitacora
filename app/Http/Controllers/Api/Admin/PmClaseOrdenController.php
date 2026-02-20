<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\StorePmClaseOrdenRequest;
use App\Http\Requests\Api\Admin\UpdatePmClaseOrdenRequest;
use App\Http\Resources\PmClaseOrdenResource;
use App\Models\EntradaBitacora;
use App\Models\PmClaseOrden;
use App\Models\PmMatrizOrdenActividad;

class PmClaseOrdenController extends Controller
{
    public function index()
    {
        return PmClaseOrdenResource::collection(
            PmClaseOrden::withCount(['entradas', 'matrizOrdenes'])
                ->orderBy('nombre')
                ->get()
        );
    }

    public function store(StorePmClaseOrdenRequest $request)
    {
        return new PmClaseOrdenResource(PmClaseOrden::create($request->validated()));
    }

    public function update(UpdatePmClaseOrdenRequest $request, PmClaseOrden $orden)
    {
        $orden->fill($request->validated())->save();
        return new PmClaseOrdenResource($orden->refresh());
    }

    public function destroy(PmClaseOrden $orden)
    {
        $enUso = EntradaBitacora::where('pm_clase_orden_id', $orden->id)->exists()
            || PmMatrizOrdenActividad::where('pm_clase_orden_id', $orden->id)->exists();

        if ($enUso) {
            return response()->json(['message' => 'No se puede eliminar: clase de orden en uso'], 422);
        }

        $orden->delete();
        return response()->noContent();
    }
}
