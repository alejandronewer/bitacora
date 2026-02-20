<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCatEntradaImpactoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('administrador');
    }

    public function rules(): array
    {
        $id = $this->route('impacto')->id ?? null;

        return [
            'codigo' => ['sometimes', 'string', 'max:60', 'unique:cat_entrada_impacto,codigo,' . $id],
            'nombre' => ['sometimes', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'severidad' => ['nullable', 'integer'],
            'orden' => ['nullable', 'integer'],
            'activo' => ['boolean'],
        ];
    }
}
