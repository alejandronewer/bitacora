<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CatEntradaImpactoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'codigo' => $this->codigo,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'severidad' => $this->severidad,
            'orden' => $this->orden,
            'activo' => (bool) $this->activo,
            'entradas_count' => $this->entradas_count ?? 0,
            'eventos_count' => $this->eventos_detectados_count ?? 0,
            'in_use' => (($this->entradas_count ?? 0) > 0) || (($this->eventos_detectados_count ?? 0) > 0),
        ];
    }
}
