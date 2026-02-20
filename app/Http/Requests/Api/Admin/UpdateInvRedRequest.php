<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvRedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('administrador');
    }

    public function rules(): array
    {
        $id = $this->route('red')->id ?? null;

        return [
            'codigo' => ['sometimes', 'string', 'max:40', 'unique:inv_redes,codigo,' . $id],
            'nombre' => ['sometimes', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'activo' => ['boolean'],
            'dominio_ids' => ['nullable', 'array'],
            'dominio_ids.*' => ['integer', 'exists:inv_dominios,id'],
        ];
    }
}
