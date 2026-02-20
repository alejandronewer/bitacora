<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEntradaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['operador', 'administrador']);
    }

    public function rules(): array
    {
        return [
            'titulo' => ['sometimes', 'string', 'max:255'],
            'cuerpo_html' => ['sometimes', 'string'],
            'cuerpo_texto' => ['sometimes', 'string'],
            'resumen_tecnico' => ['prohibited'],
            'fecha_inicio' => ['sometimes', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'ubicacion_tecnica_id' => ['nullable', 'integer', 'exists:dm_ubicaciones_tecnicas,id'],
            'equipo_id' => ['nullable', 'integer', 'exists:dm_equipos,id'],
            'ubicacion_manual' => ['nullable', 'string', 'max:255'],
            'equipo_manual' => ['nullable', 'string', 'max:255'],
            'entrada_criterio_id' => ['nullable', 'integer', 'exists:cat_entrada_criterio,id'],
            'entrada_impacto_id' => ['nullable', 'integer', 'exists:cat_entrada_impacto,id'],
            'pm_clase_orden_id' => ['nullable', 'integer', 'exists:pm_clase_orden,id'],
            'pm_clase_actividad_id' => ['nullable', 'integer', 'exists:pm_clase_actividad,id'],
            'tipo_registro' => ['sometimes', 'in:operativo,inventario'],
            'accion_inventario' => ['nullable', 'in:alta,baja,cambio'],

            'evento_detectado' => ['nullable', 'array'],
            'evento_detectado.tipo_evento' => ['required_with:evento_detectado', 'in:FALLA,ANOMALIA'],
            'evento_detectado.detalle' => ['nullable', 'string', 'max:255'],

            'ubicaciones' => ['nullable', 'array'],
            'ubicaciones.*.id' => ['required', 'integer', 'exists:dm_ubicaciones_tecnicas,id'],

            'equipos' => ['nullable', 'array'],
            'equipos.*.id' => ['required', 'integer', 'exists:dm_equipos,id'],

            'inventario_elementos' => ['nullable', 'array'],
            'inventario_elementos.*.id' => ['required', 'integer', 'exists:inv_elementos_redes,id'],

            'adjuntos' => ['nullable', 'array'],
            'adjuntos.*' => ['integer', 'exists:adjuntos,id'],
            'adjuntos_eliminar' => ['nullable', 'array'],
            'adjuntos_eliminar.*' => ['integer', 'exists:adjuntos,id'],
            'referencias_eliminar' => ['nullable', 'array'],
            'referencias_eliminar.*' => ['integer', 'exists:referencias_externas,id'],
        ];
    }
}
