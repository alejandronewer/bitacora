<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvRedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('administrador');
    }

    public function rules(): array
    {
        return [
            'codigo' => ['required', 'string', 'max:40', 'unique:inv_redes,codigo'],
            'nombre' => ['required', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'activo' => ['boolean'],
            'dominio_ids' => ['nullable', 'array'],
            'dominio_ids.*' => ['integer', 'exists:inv_dominios,id'],
        ];
    }
}
