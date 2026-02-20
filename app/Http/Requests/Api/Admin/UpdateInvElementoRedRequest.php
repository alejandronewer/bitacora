<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInvElementoRedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('administrador');
    }

    public function rules(): array
    {
        $elemento = $this->route('elemento');
        $id = $elemento?->id;
        $redId = $this->input('red_id', $elemento?->red_id);
        $tipo = $elemento?->tipo;

        return [
            'red_id' => ['sometimes', 'integer', 'exists:inv_redes,id'],
            'tipo' => ['prohibited'],
            'codigo' => [
                'sometimes',
                'string',
                'max:120',
                Rule::unique('inv_elementos_redes', 'codigo')
                    ->ignore($id)
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

    public function messages(): array
    {
        return [
            'tipo.prohibited' => 'No se permite cambiar el tipo de un elemento existente.',
        ];
    }
}
