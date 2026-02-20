<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvElementoRedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('administrador');
    }

    public function rules(): array
    {
        $redId = $this->input('red_id');
        $tipo = $this->input('tipo');
        return [
            'red_id' => ['required', 'integer', 'exists:inv_redes,id'],
            'tipo' => ['required', Rule::in(['nodo', 'enlace', 'servicio', 'tunel', 'trail'])],
            'codigo' => [
                'required',
                'string',
                'max:120',
                Rule::unique('inv_elementos_redes', 'codigo')
                    ->where(fn ($query) => $query->where('red_id', $redId)->where('tipo', $tipo)),
            ],
            'nombre' => ['nullable', 'string', 'max:200'],
            'estado' => ['nullable', Rule::in(['activo', 'baja', 'planificado', 'desconocido'])],
            'fecha_alta' => ['nullable', 'date'],
            'fecha_baja' => ['nullable', 'date'],
            'updated_at_fuente' => ['nullable', 'date'],
            'origen' => ['prohibited'],
            'observaciones' => ['nullable', 'string', 'max:255'],
        ];
    }
}
