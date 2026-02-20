<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDmUbicacionTecnicaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('administrador');
    }

    public function rules(): array
    {
        $id = $this->route('ubicacion')?->id;

        return [
            'codigo' => ['sometimes', 'string', 'max:80', 'unique:dm_ubicaciones_tecnicas,codigo,' . $id],
            'nombre' => ['sometimes', 'string', 'max:200'],
            'nivel_1' => ['sometimes', 'string', 'size:2'],
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
