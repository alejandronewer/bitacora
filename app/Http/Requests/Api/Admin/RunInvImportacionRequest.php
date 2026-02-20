<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RunInvImportacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('administrador');
    }

    public function rules(): array
    {
        return [
            'red_id' => ['required', 'integer', 'exists:inv_redes,id'],
            'regla_id' => ['required', 'integer', 'exists:inv_import_reglas,id'],
            'archivo' => ['required', 'file', 'max:51200'],
        ];
    }
}
