<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DmEquipoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'codigo' => $this->codigo,
            'nombre' => $this->nombre,
            'ubicacion_tecnica_id' => $this->ubicacion_tecnica_id,
            'ubicacion_tecnica' => $this->whenLoaded('ubicacionTecnica', function () {
                return new DmUbicacionTecnicaResource($this->ubicacionTecnica);
            }),
            'area' => $this->area,
            'activo' => (bool) $this->activo,
        ];
    }
}
