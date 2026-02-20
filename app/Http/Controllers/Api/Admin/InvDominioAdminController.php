<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\StoreInvDominioRequest;
use App\Http\Requests\Api\Admin\UpdateInvDominioRequest;
use App\Http\Resources\InvDominioResource;
use App\Models\InvDominio;

class InvDominioAdminController extends Controller
{
    public function index()
    {
        return InvDominioResource::collection(
            InvDominio::withCount('redes')
                ->orderBy('orden')
                ->orderBy('nombre')
                ->get()
        );
    }

    public function store(StoreInvDominioRequest $request)
    {
        return new InvDominioResource(InvDominio::create($request->validated()));
    }

    public function update(UpdateInvDominioRequest $request, InvDominio $dominio)
    {
        $dominio->fill($request->validated())->save();
        return new InvDominioResource($dominio->refresh());
    }

    public function destroy(InvDominio $dominio)
    {
        if ($dominio->redes()->exists()) {
            return response()->json(['message' => 'No se puede eliminar: dominio en uso.'], 422);
        }

        $dominio->delete();
        return response()->noContent();
    }
}
