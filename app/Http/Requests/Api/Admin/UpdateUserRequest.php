<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('administrador');
    }

    public function rules(): array
    {
        $id = $this->route('user')->id ?? null;

        return [
            'nombre' => ['sometimes', 'string', 'max:200'],
            'correo' => ['sometimes', 'string', 'email', 'max:200', 'unique:usuarios,correo,' . $id],
            'password' => ['nullable', 'string', 'min:8'],
            'activo' => ['boolean'],
            'estatus_actual' => ['sometimes', 'in:Temporal,Base'],
            'rpe' => ['nullable', 'string', 'max:5', 'unique:usuarios,rpe,' . $id],
            'rtt' => ['nullable', 'string', 'max:5', 'unique:usuarios,rtt,' . $id],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string'],
        ];
    }
}
