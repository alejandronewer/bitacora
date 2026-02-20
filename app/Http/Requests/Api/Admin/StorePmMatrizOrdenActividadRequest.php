<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePmMatrizOrdenActividadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('administrador');
    }

    public function rules(): array
    {
        return [
            'pm_clase_orden_id' => [
                'required',
                'integer',
                Rule::exists('pm_clase_orden', 'id')->where('activo', 1),
            ],
            'pm_clase_actividad_id' => [
                'required',
                'integer',
                Rule::exists('pm_clase_actividad', 'id')->where('activo', 1),
            ],
            'activo' => ['boolean'],
        ];
    }
}
