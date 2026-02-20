<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvDominioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'codigo' => $this->codigo,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'orden' => $this->orden,
            'activo' => (bool) $this->activo,
            'redes_count' => $this->redes_count ?? 0,
            'in_use' => (($this->redes_count ?? 0) > 0),
        ];
    }
}
