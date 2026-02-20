<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreDmUbicacionTecnicaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('administrador');
    }

    public function rules(): array
    {
        return [
            'codigo' => ['required', 'string', 'max:80', 'unique:dm_ubicaciones_tecnicas,codigo'],
            'nombre' => ['required', 'string', 'max:200'],
            'nivel_1' => ['required', 'string', 'size:2'],
            'nivel_2' => ['nullable', 'string', 'size:4'],
            'nivel_3' => ['nullable', 'string', 'size:2'],
            'nivel_4' => ['nullable', 'string', 'size:3'],
            'nivel_5' => ['nullable', 'string', 'size:2'],
            'nivel_6' => ['nullable', 'string', 'size:5'],
            'nivel_7' => ['nullable', 'string', 'size:3'],
            'nivel_8' => ['nullable', 'string', 'size:2'],
            'activo' => ['boolean'],
            'fuente' => ['prohibited'],
            'last_sync_at' => ['prohibited'],
        ];
    }
}
