<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvDominioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('administrador');
    }

    public function rules(): array
    {
        $id = $this->route('dominio')->id ?? null;

        return [
            'codigo' => ['sometimes', 'string', 'max:40', 'unique:inv_dominios,codigo,' . $id],
            'nombre' => ['sometimes', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'orden' => ['nullable', 'integer'],
            'activo' => ['boolean'],
        ];
    }
}
