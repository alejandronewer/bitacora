<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\StoreInvElementoRedRequest;
use App\Http\Requests\Api\Admin\UpdateInvElementoRedRequest;
use App\Http\Resources\InvElementoRedResource;
use App\Models\InvElementoRed;

class InvElementoRedAdminController extends Controller
{
    public function index()
    {
        return InvElementoRedResource::collection(
            InvElementoRed::with([
                'red.dominios',
                'detalleNodo',
                'detalleEnlace',
                'detalleServicio',
                'detalleTunel',
            ])
                ->withCount('entradas')
                ->orderBy('codigo')
                ->get()
        );
    }

    public function store(StoreInvElementoRedRequest $request)
    {
        $data = $request->validated();
        unset($data['fecha_alta'], $data['fecha_baja'], $data['updated_at_fuente']);
        $data['origen'] = 'manual';
        $estado = (string) ($data['estado'] ?? 'activo');
        $data['fecha_alta'] = now();
        $data['fecha_baja'] = $estado === 'baja' ? now() : null;
        $data['updated_at_fuente'] = null;
        $elemento = InvElementoRed::create($data);
        return new InvElementoRedResource($elemento->load([
            'red.dominios',
            'detalleNodo',
            'detalleEnlace',
            'detalleServicio',
            'detalleTunel',
        ]));
    }

    public function update(UpdateInvElementoRedRequest $request, InvElementoRed $elemento)
    {
        $data = $request->validated();
        unset($data['fecha_alta'], $data['fecha_baja'], $data['updated_at_fuente']);
        unset($data['origen']);
        unset($data['tipo']);

        $estadoFinal = (string) ($data['estado'] ?? $elemento->estado);
        $data['updated_at_fuente'] = now();

        if ($estadoFinal === 'baja') {
            $data['fecha_baja'] = $elemento->fecha_baja ?? now();
        } else {
            $data['fecha_baja'] = null;
        }

        if (! $elemento->fecha_alta) {
            $data['fecha_alta'] = now();
        }

        $elemento->fill($data)->save();
        return new InvElementoRedResource($elemento->refresh()->load([
            'red.dominios',
            'detalleNodo',
            'detalleEnlace',
            'detalleServicio',
            'detalleTunel',
        ]));
    }

    public function destroy(InvElementoRed $elemento)
    {
        if ($elemento->entradas()->exists()) {
            return response()->json(['message' => 'No se puede eliminar: el elemento está en uso.'], 422);
        }

        $elemento->delete();
        return response()->noContent();
    }
}
