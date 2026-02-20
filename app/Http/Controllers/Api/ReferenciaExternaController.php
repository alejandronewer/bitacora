<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreReferenciaExternaRequest;
use App\Http\Resources\ReferenciaExternaResource;
use App\Models\EntradaBitacora;
use App\Models\ReferenciaExterna;
use App\Models\SistemaExterno;
use App\Support\ResumenTecnicoBuilder;

class ReferenciaExternaController extends Controller
{
    public function store(StoreReferenciaExternaRequest $request)
    {
        $entrada = EntradaBitacora::findOrFail($request->input('entrada_id'));
        $this->authorize('update', $entrada);

        $this->validarRegex($request->input('sistema_externo_id'), $request->input('externo_id'));

        $referencia = ReferenciaExterna::create($request->validated());
        $entrada->resumen_tecnico = ResumenTecnicoBuilder::build($entrada->refresh());
        $entrada->save();

        return new ReferenciaExternaResource($referencia);
    }

    private function validarRegex(int $sistemaExternoId, string $externoId): void
    {
        $sistema = SistemaExterno::findOrFail($sistemaExternoId);

        if ($sistema->patron_regex) {
            if (! preg_match('/' . $sistema->patron_regex . '/', $externoId)) {
                abort(422, 'externo_id no cumple el patron');
            }
        }
    }
}
