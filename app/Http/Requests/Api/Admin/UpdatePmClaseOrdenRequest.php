<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePmClaseOrdenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('administrador');
    }

    public function rules(): array
    {
        $id = $this->route('orden')->id ?? null;

        return [
            'nombre' => ['sometimes', 'string', 'max:120', 'unique:pm_clase_orden,nombre,' . $id],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'activo' => ['boolean'],
        ];
    }
}
