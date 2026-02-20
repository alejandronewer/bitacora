<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\StoreInvRedRequest;
use App\Http\Requests\Api\Admin\UpdateInvRedRequest;
use App\Http\Resources\InvRedResource;
use App\Models\InvRed;

class InvRedAdminController extends Controller
{
    public function index()
    {
        return InvRedResource::collection(
            InvRed::with(['dominios'])
                ->withCount(['elementos', 'importaciones'])
                ->orderBy('codigo')
                ->get()
        );
    }

    public function store(StoreInvRedRequest $request)
    {
        $data = $request->validated();
        $dominios = $data['dominio_ids'] ?? [];
        unset($data['dominio_ids']);

        $red = InvRed::create($data);
        if (is_array($dominios)) {
            $red->dominios()->sync($dominios);
        }

        return new InvRedResource($red->load('dominios'));
    }

    public function update(UpdateInvRedRequest $request, InvRed $red)
    {
        $data = $request->validated();
        $dominios = $data['dominio_ids'] ?? null;
        unset($data['dominio_ids']);

        $red->fill($data)->save();
        if (is_array($dominios)) {
            $red->dominios()->sync($dominios);
        }

        return new InvRedResource($red->refresh()->load('dominios'));
    }

    public function destroy(InvRed $red)
    {
        $enUso = $red->elementos()->exists() || $red->importaciones()->exists();

        if ($enUso) {
            return response()->json(['message' => 'No se puede eliminar: la red tiene elementos o importaciones.'], 422);
        }

        $red->delete();
        return response()->noContent();
    }
}
