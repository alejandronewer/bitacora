<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConfiguracionUsuarioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'clave' => $this->clave,
            'valor' => $this->valor,
            'tipo' => $this->tipo,
            'descripcion' => $this->descripcion,
        ];
    }
}
