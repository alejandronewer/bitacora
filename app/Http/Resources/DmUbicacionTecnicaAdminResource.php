<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DmUbicacionTecnicaAdminResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $equiposCount = $this->equipos_count ?? 0;
        $entradasDirectas = $this->entradas_directas_count ?? 0;
        $entradasPivot = $this->entradas_pivot_count ?? 0;

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
            'fuente' => $this->fuente,
            'last_sync_at' => $this->last_sync_at,
            'equipos_count' => $equiposCount,
            'entradas_directas_count' => $entradasDirectas,
            'entradas_pivot_count' => $entradasPivot,
            'in_use' => ($equiposCount + $entradasDirectas + $entradasPivot) > 0,
        ];
    }
}
