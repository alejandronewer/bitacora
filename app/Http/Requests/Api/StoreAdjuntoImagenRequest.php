<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdjuntoImagenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['operador', 'administrador']);
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'image', 'max:51200'],
        ];
    }
}
