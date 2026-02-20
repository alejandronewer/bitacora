<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('administrador');
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:200'],
            'correo' => ['required', 'string', 'email', 'max:200', 'unique:usuarios,correo'],
            'password' => ['required', 'string', 'min:8'],
            'activo' => ['boolean'],
            'estatus_actual' => ['required', 'in:Temporal,Base'],
            'rpe' => ['nullable', 'string', 'max:5', 'unique:usuarios,rpe'],
            'rtt' => ['nullable', 'string', 'max:5', 'unique:usuarios,rtt'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string'],
        ];
    }
}
