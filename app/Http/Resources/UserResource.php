<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'correo' => $this->correo,
            'activo' => (bool) $this->activo,
            'estatus_actual' => $this->estatus_actual,
            'rpe' => $this->rpe,
            'rtt' => $this->rtt,
            'ultimo_acceso' => $this->ultimo_acceso,
            'roles' => $this->whenLoaded('roles', fn () => $this->roles->pluck('name')),
            'has_historial' => (bool) (
                ($this->entradas_count ?? 0) > 0
                || ($this->adjuntos_count ?? 0) > 0
                || ($this->importaciones_red_count ?? 0) > 0
                || ($this->configuraciones_count ?? 0) > 0
            ),
            'historial_counts' => [
                'entradas' => $this->entradas_count ?? 0,
                'adjuntos' => $this->adjuntos_count ?? 0,
                'importaciones' => $this->importaciones_red_count ?? 0,
                'configuracion' => $this->configuraciones_count ?? 0,
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
