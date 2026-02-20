<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreDmEquipoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('administrador');
    }

    public function rules(): array
    {
        return [
            'codigo' => ['required', 'string', 'max:80', 'unique:dm_equipos,codigo'],
            'nombre' => ['required', 'string', 'max:200'],
            'ubicacion_tecnica_id' => ['nullable', 'integer', 'exists:dm_ubicaciones_tecnicas,id'],
            'area' => ['nullable', 'string', 'max:40'],
            'activo' => ['boolean'],
            'fuente' => ['prohibited'],
            'last_sync_at' => ['prohibited'],
        ];
    }
}
