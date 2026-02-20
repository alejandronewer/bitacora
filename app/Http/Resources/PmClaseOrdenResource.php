<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PmClaseOrdenResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'activo' => (bool) $this->activo,
            'entradas_count' => $this->entradas_count ?? 0,
            'matriz_count' => $this->matriz_ordenes_count ?? 0,
            'in_use' => (($this->entradas_count ?? 0) > 0) || (($this->matriz_ordenes_count ?? 0) > 0),
        ];
    }
}
