<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReferenciaExternaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'entrada_id' => $this->entrada_id,
            'sistema_externo_id' => $this->sistema_externo_id,
            'externo_id' => $this->externo_id,
            'sistema' => $this->whenLoaded('sistema', function () {
                return [
                    'id' => $this->sistema?->id,
                    'codigo' => $this->sistema?->codigo,
                    'nombre' => $this->sistema?->nombre,
                ];
            }),
        ];
    }
}
