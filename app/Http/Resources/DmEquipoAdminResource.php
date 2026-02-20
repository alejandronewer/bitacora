<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DmEquipoAdminResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $entradasDirectas = $this->entradas_directas_count ?? 0;
        $entradasPivot = $this->entradas_pivot_count ?? 0;

        return [
            'id' => $this->id,
            'codigo' => $this->codigo,
            'nombre' => $this->nombre,
            'ubicacion_tecnica_id' => $this->ubicacion_tecnica_id,
            'ubicacion' => $this->whenLoaded('ubicacionTecnica', function () {
                return [
                    'id' => $this->ubicacionTecnica?->id,
                    'codigo' => $this->ubicacionTecnica?->codigo,
                    'nombre' => $this->ubicacionTecnica?->nombre,
                ];
            }),
            'area' => $this->area,
            'activo' => (bool) $this->activo,
            'fuente' => $this->fuente,
            'last_sync_at' => $this->last_sync_at,
            'entradas_directas_count' => $entradasDirectas,
            'entradas_pivot_count' => $entradasPivot,
            'in_use' => ($entradasDirectas + $entradasPivot) > 0,
        ];
    }
}
