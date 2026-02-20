<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SistemaExternoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'codigo' => $this->codigo,
            'nombre' => $this->nombre,
            'patron_regex' => $this->patron_regex,
            'activo' => (bool) $this->activo,
            'referencias_count' => $this->referencias_externas_count ?? 0,
            'in_use' => ($this->referencias_externas_count ?? 0) > 0,
        ];
    }
}
