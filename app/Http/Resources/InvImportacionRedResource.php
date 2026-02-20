<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvImportacionRedResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'red_id' => $this->red_id,
            'red' => $this->whenLoaded('red', function () {
                return [
                    'id' => $this->red->id,
                    'codigo' => $this->red->codigo,
                    'nombre' => $this->red->nombre,
                ];
            }),
            'regla_id' => $this->regla_id,
            'regla' => $this->whenLoaded('regla', function () {
                return [
                    'id' => $this->regla->id,
                    'nombre' => $this->regla->nombre,
                    'tipo_elemento' => $this->regla->tipo_elemento,
                ];
            }),
            'usuario_id' => $this->usuario_id,
            'archivo_nombre' => $this->archivo_nombre,
            'fuente' => $this->fuente,
            'hash_archivo' => $this->hash_archivo,
            'estado' => $this->estado,
            'total_registros' => (int) ($this->total_registros ?? 0),
            'procesados' => (int) ($this->procesados ?? 0),
            'creados' => (int) ($this->creados ?? 0),
            'actualizados' => (int) ($this->actualizados ?? 0),
            'marcados_baja' => (int) ($this->marcados_baja ?? 0),
            'errores_count' => $this->errores_count ?? 0,
            'es_revertible' => (bool) ($this->es_revertible ?? false),
            'reversion_status' => $this->reversion_status ?? null,
            'reversion_candidatos' => (int) ($this->reversion_candidatos ?? 0),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
