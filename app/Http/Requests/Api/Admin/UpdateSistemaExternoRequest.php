<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSistemaExternoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('administrador');
    }

    public function rules(): array
    {
        $id = $this->route('sistema')->id ?? null;

        return [
            'codigo' => ['sometimes', 'string', 'max:80', 'unique:sistemas_externos,codigo,' . $id],
            'nombre' => ['sometimes', 'string', 'max:120'],
            'patron_regex' => ['nullable', 'string', 'max:255'],
            'activo' => ['boolean'],
        ];
    }
}
