<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\SistemaExterno;

class StoreReferenciaExternaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['operador', 'administrador']);
    }

    public function rules(): array
    {
        return [
            'entrada_id' => ['required', 'integer', 'exists:entradas_bitacora,id'],
            'sistema_externo_id' => [
                'required',
                'integer',
                Rule::exists('sistemas_externos', 'id')->where('activo', 1),
            ],
            'externo_id' => ['required', 'string', 'max:120'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $sistemaId = $this->input('sistema_externo_id');
            $externoId = $this->input('externo_id');

            if (! $sistemaId || ! $externoId) {
                return;
            }

            $sistema = SistemaExterno::find($sistemaId);
            if (! $sistema || ! $sistema->patron_regex) {
                return;
            }

            $pattern = $sistema->patron_regex;
            $delimiter = '#';
            $escaped = str_replace($delimiter, '\\' . $delimiter, $pattern);
            $regex = $delimiter . $escaped . $delimiter;

            if (@preg_match($regex, '') === false) {
                $validator->errors()->add('externo_id', 'Patrón regex inválido en el sistema externo.');
                return;
            }

            if (! preg_match($regex, $externoId)) {
                $validator->errors()->add('externo_id', 'El identificador no cumple el patrón.');
            }
        });
    }
}
