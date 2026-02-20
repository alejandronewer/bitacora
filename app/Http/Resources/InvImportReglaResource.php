<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvImportReglaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'red_id' => $this->red_id,
            'red' => $this->whenLoaded('red', function () {
                return [
                    'id' => $this->red->id,
                    'codigo' => $this->red->codigo,
                    'nombre' => $this->red->nombre,
                ];
            }),
            'nombre' => $this->nombre,
            'tipo_elemento' => $this->tipo_elemento,
            'tabla_destino' => $this->tabla_destino,
            'archivo_patron' => $this->archivo_patron,
            'delimitador' => $this->delimitador,
            'usa_comillas' => (bool) $this->usa_comillas,
            'tiene_encabezado' => (bool) $this->tiene_encabezado,
            'encoding' => $this->encoding,
            'activo' => (bool) $this->activo,
            'campos' => InvImportReglaCampoResource::collection($this->whenLoaded('campos')),
            'importaciones_count' => $this->importaciones_count ?? 0,
            'in_use' => (($this->importaciones_count ?? 0) > 0),
        ];
    }
}
