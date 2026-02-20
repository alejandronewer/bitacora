<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateConfiguracionSistemaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('administrador');
    }

    public function rules(): array
    {
        $id = $this->route('configuracion')->id ?? null;

        return [
            'clave' => ['sometimes', 'string', 'max:120', 'unique:configuracion_sistema,clave,' . $id],
            'valor' => ['sometimes', 'string', 'max:255'],
            'tipo' => ['sometimes', 'in:int,bool,string'],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ];
    }
}
