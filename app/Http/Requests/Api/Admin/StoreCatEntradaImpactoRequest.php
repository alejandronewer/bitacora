<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCatEntradaImpactoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('administrador');
    }

    public function rules(): array
    {
        return [
            'codigo' => ['required', 'string', 'max:60', 'unique:cat_entrada_impacto,codigo'],
            'nombre' => ['required', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'severidad' => ['nullable', 'integer'],
            'orden' => ['nullable', 'integer'],
            'activo' => ['boolean'],
        ];
    }
}
