<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateConfiguracionUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['operador', 'administrador']);
    }

    public function rules(): array
    {
        return [
            'valor' => [
                'sometimes',
                'string',
                'max:255',
                Rule::when(
                    $this->input('clave') === 'tema.modo',
                    [Rule::in(['dark', 'light', 'global'])]
                ),
            ],
            'tipo' => ['sometimes', 'in:int,bool,string'],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ];
    }
}
