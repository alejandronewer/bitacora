<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePmClaseActividadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('administrador');
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:120', 'unique:pm_clase_actividad,nombre'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'activo' => ['boolean'],
        ];
    }
}
