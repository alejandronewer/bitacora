<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DmUbicacionDetalleNivelResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nivel' => (int) $this->nivel,
            'codigo' => $this->codigo,
            'rama_nivel_3' => $this->rama_nivel_3,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'origen' => $this->origen,
            'activo' => (bool) $this->activo,
        ];
    }
}
