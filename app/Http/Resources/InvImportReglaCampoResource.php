<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvImportReglaCampoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'columna_fuente' => $this->columna_fuente,
            'campo_destino' => $this->campo_destino,
            'transformacion' => $this->transformacion,
            'por_defecto' => $this->por_defecto,
            'es_clave_upsert' => (bool) $this->es_clave_upsert,
            'orden' => (int) $this->orden,
            'activo' => (bool) $this->activo,
        ];
    }
}
