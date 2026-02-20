<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\StoreCatEntradaImpactoRequest;
use App\Http\Requests\Api\Admin\UpdateCatEntradaImpactoRequest;
use App\Http\Resources\CatEntradaImpactoResource;
use App\Models\CatEntradaImpacto;
use App\Models\EntradaBitacora;

class CatEntradaImpactoController extends Controller
{
    private function esImpactoProtegido(CatEntradaImpacto $impacto): bool
    {
        $nombre = mb_strtolower((string) $impacto->nombre);

        return $impacto->codigo === 'ninguno'
            || $impacto->severidad === 0
            || str_contains($nombre, 'sin impacto');
    }

    public function index()
    {
        return CatEntradaImpactoResource::collection(
            CatEntradaImpacto::withCount(['entradas'])
                ->orderBy('orden')
                ->get()
        );
    }

    public function store(StoreCatEntradaImpactoRequest $request)
    {
        return new CatEntradaImpactoResource(CatEntradaImpacto::create($request->validated()));
    }

    public function update(UpdateCatEntradaImpactoRequest $request, CatEntradaImpacto $impacto)
    {
        if ($this->esImpactoProtegido($impacto)) {
            return response()->json(['message' => 'No se puede editar: impacto protegido'], 422);
        }

        $impacto->fill($request->validated())->save();
        return new CatEntradaImpactoResource($impacto->refresh());
    }

    public function destroy(CatEntradaImpacto $impacto)
    {
        if ($this->esImpactoProtegido($impacto)) {
            return response()->json(['message' => 'No se puede eliminar: impacto protegido'], 422);
        }

        $enUso = EntradaBitacora::where('entrada_impacto_id', $impacto->id)->exists();

        if ($enUso) {
            return response()->json(['message' => 'No se puede eliminar: impacto en uso'], 422);
        }

        $impacto->delete();
        return response()->noContent();
    }
}
