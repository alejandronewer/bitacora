<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreSistemaExternoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('administrador');
    }

    public function rules(): array
    {
        return [
            'codigo' => ['required', 'string', 'max:80', 'unique:sistemas_externos,codigo'],
            'nombre' => ['required', 'string', 'max:120'],
            'patron_regex' => ['nullable', 'string', 'max:255'],
            'activo' => ['boolean'],
        ];
    }
}
