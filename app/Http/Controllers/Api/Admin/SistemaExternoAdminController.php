<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\StoreSistemaExternoRequest;
use App\Http\Requests\Api\Admin\UpdateSistemaExternoRequest;
use App\Http\Resources\SistemaExternoResource;
use App\Models\ReferenciaExterna;
use App\Models\SistemaExterno;

class SistemaExternoAdminController extends Controller
{
    public function index()
    {
        return SistemaExternoResource::collection(
            SistemaExterno::withCount('referenciasExternas')
                ->orderBy('codigo')
                ->get()
        );
    }

    public function store(StoreSistemaExternoRequest $request)
    {
        return new SistemaExternoResource(SistemaExterno::create($request->validated()));
    }

    public function update(UpdateSistemaExternoRequest $request, SistemaExterno $sistema)
    {
        $sistema->fill($request->validated())->save();
        return new SistemaExternoResource($sistema->refresh());
    }

    public function destroy(SistemaExterno $sistema)
    {
        $enUso = ReferenciaExterna::where('sistema_externo_id', $sistema->id)->exists();

        if ($enUso) {
            return response()->json(['message' => 'No se puede eliminar: sistema en uso'], 422);
        }

        $sistema->delete();
        return response()->noContent();
    }
}
