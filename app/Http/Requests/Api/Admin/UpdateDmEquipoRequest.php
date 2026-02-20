<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDmEquipoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('administrador');
    }

    public function rules(): array
    {
        $id = $this->route('equipo')?->id;

        return [
            'codigo' => ['sometimes', 'string', 'max:80', 'unique:dm_equipos,codigo,' . $id],
            'nombre' => ['sometimes', 'string', 'max:200'],
            'ubicacion_tecnica_id' => ['nullable', 'integer', 'exists:dm_ubicaciones_tecnicas,id'],
            'area' => ['nullable', 'string', 'max:40'],
            'activo' => ['boolean'],
            'fuente' => ['prohibited'],
            'last_sync_at' => ['prohibited'],
        ];
    }
}
