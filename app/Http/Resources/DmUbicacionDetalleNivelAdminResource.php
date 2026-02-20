<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DmUbicacionDetalleNivelAdminResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nivel' => (int) $this->nivel,
            'codigo' => $this->codigo,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'rama_nivel_3' => $this->rama_nivel_3,
            'activo' => (bool) $this->activo,
            'origen' => $this->origen,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
