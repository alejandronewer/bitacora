<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreConfiguracionUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['operador', 'administrador']);
    }

    public function rules(): array
    {
        return [
            'clave' => ['required', 'string', 'max:120'],
            'valor' => [
                'required',
                'string',
                'max:255',
                Rule::when(
                    $this->input('clave') === 'tema.modo',
                    [Rule::in(['dark', 'light', 'global'])]
                ),
            ],
            'tipo' => ['required', 'in:int,bool,string'],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ];
    }
}
