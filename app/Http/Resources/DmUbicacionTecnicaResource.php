<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DmUbicacionTecnicaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'codigo' => $this->codigo,
            'nombre' => $this->nombre,
            'nivel_1' => $this->nivel_1,
            'nivel_2' => $this->nivel_2,
            'nivel_3' => $this->nivel_3,
            'nivel_4' => $this->nivel_4,
            'nivel_5' => $this->nivel_5,
            'nivel_6' => $this->nivel_6,
            'nivel_7' => $this->nivel_7,
            'nivel_8' => $this->nivel_8,
            'activo' => (bool) $this->activo,
        ];
    }
}
