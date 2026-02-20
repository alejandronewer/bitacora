<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class UpdateDmUbicacionDetalleNivelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('administrador');
    }

    public function rules(): array
    {
        return [
            'nivel' => ['sometimes', 'integer', 'between:1,8'],
            'codigo' => ['sometimes', 'string', 'max:20'],
            'nombre' => ['nullable', 'string', 'max:160'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'rama_nivel_3' => ['required_if:nivel,4,5,6,7,8', 'nullable', 'string', 'size:2'],
            'activo' => ['boolean'],
            'origen' => ['nullable', 'in:Detectado,Homologado'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $current = $this->route('detalleNivel');
            if (! $current) {
                return;
            }

            $nivel = (int) $this->input('nivel', $current->nivel);
            $codigo = strtoupper(trim((string) $this->input('codigo', $current->codigo)));
            $rama = strtoupper(trim((string) $this->input('rama_nivel_3', $current->rama_nivel_3)));

            if ($nivel >= 1 && $nivel <= 3) {
                $rama = '--';
            }

            if ($codigo === '' || $rama === '') {
                return;
            }

            $exists = DB::table('dm_ubicacion_detalle_nivel')
                ->where('nivel', $nivel)
                ->where('codigo', $codigo)
                ->where('rama_nivel_3', $rama)
                ->where('id', '<>', $current->id)
                ->exists();

            if ($exists) {
                $validator->errors()->add('codigo', 'Ya existe un registro con ese nivel, código y rama de nivel 3.');
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $current = $this->route('detalleNivel');

        $nivel = (int) $this->input('nivel', $current?->nivel ?? 0);
        $codigo = strtoupper(trim((string) $this->input('codigo', $current?->codigo ?? '')));
        $rama = strtoupper(trim((string) $this->input('rama_nivel_3', $current?->rama_nivel_3 ?? '')));

        if ($nivel >= 1 && $nivel <= 3) {
            $rama = '--';
        }

        $nombre = trim((string) $this->input('nombre', $current?->nombre ?? ''));
        $descripcion = trim((string) $this->input('descripcion', $current?->descripcion ?? ''));
        $origen = trim((string) $this->input('origen', $current?->origen ?? 'Homologado'));

        $this->merge([
            'codigo' => $codigo,
            'rama_nivel_3' => $rama === '' ? null : $rama,
            'nombre' => $nombre === '' ? null : $nombre,
            'descripcion' => $descripcion === '' ? null : $descripcion,
            'origen' => $origen === '' ? 'Homologado' : $origen,
        ]);
    }
}
