<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCatEntradaCriterioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('administrador');
    }

    public function rules(): array
    {
        $id = $this->route('criterio')->id ?? null;

        return [
            'codigo' => ['sometimes', 'string', 'max:60', 'unique:cat_entrada_criterio,codigo,' . $id],
            'nombre' => ['sometimes', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'orden' => ['nullable', 'integer'],
            'activo' => ['boolean'],
        ];
    }
}
