<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PmMatrizOrdenActividadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'pm_clase_orden_id' => $this->pm_clase_orden_id,
            'pm_clase_actividad_id' => $this->pm_clase_actividad_id,
            'activo' => (bool) $this->activo,
            'entradas_count' => $this->entradas_count ?? 0,
            'in_use' => ($this->entradas_count ?? 0) > 0,
        ];
    }
}
