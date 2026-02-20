<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EntradaEventoDetectadoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'entrada_id' => $this->entrada_id,
            'tipo_evento' => $this->tipo_evento,
            'detalle' => $this->detalle,
        ];
    }
}
