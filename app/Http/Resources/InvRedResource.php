<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvRedResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'codigo' => $this->codigo,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'activo' => (bool) $this->activo,
            'dominios' => $this->whenLoaded('dominios', function () {
                return $this->dominios->map(fn ($dominio) => [
                    'id' => $dominio->id,
                    'codigo' => $dominio->codigo,
                    'nombre' => $dominio->nombre,
                ]);
            }),
            'elementos_count' => $this->elementos_count ?? 0,
            'importaciones_count' => $this->importaciones_count ?? 0,
            'in_use' => (($this->elementos_count ?? 0) > 0) || (($this->importaciones_count ?? 0) > 0),
        ];
    }
}
