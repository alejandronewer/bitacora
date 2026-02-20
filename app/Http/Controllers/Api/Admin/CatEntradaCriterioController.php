<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\StoreCatEntradaCriterioRequest;
use App\Http\Requests\Api\Admin\UpdateCatEntradaCriterioRequest;
use App\Http\Resources\CatEntradaCriterioResource;
use App\Models\CatEntradaCriterio;
use App\Models\EntradaBitacora;

class CatEntradaCriterioController extends Controller
{
    public function index()
    {
        return CatEntradaCriterioResource::collection(
            CatEntradaCriterio::withCount(['entradas'])
                ->orderBy('orden')
                ->get()
        );
    }

    public function store(StoreCatEntradaCriterioRequest $request)
    {
        return new CatEntradaCriterioResource(CatEntradaCriterio::create($request->validated()));
    }

    public function update(UpdateCatEntradaCriterioRequest $request, CatEntradaCriterio $criterio)
    {
        $criterio->fill($request->validated())->save();
        return new CatEntradaCriterioResource($criterio->refresh());
    }

    public function destroy(CatEntradaCriterio $criterio)
    {
        $enUso = EntradaBitacora::where('entrada_criterio_id', $criterio->id)->exists();

        if ($enUso) {
            return response()->json(['message' => 'No se puede eliminar: criterio en uso'], 422);
        }

        $criterio->delete();
        return response()->noContent();
    }
}
