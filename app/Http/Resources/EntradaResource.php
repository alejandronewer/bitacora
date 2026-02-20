<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EntradaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'cuerpo_html' => $this->cuerpo_html,
            'cuerpo_texto' => $this->cuerpo_texto,
            'resumen_tecnico' => $this->resumen_tecnico,
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_fin' => $this->fecha_fin,
            'publicado' => (bool) $this->publicado,
            'publicado_at' => $this->publicado_at,
            'tipo_registro' => $this->tipo_registro,
            'accion_inventario' => $this->accion_inventario,
            'usuario' => new UserResource($this->whenLoaded('usuario')),
            'criterio' => new CatEntradaCriterioResource($this->whenLoaded('criterio')),
            'impacto' => new CatEntradaImpactoResource($this->whenLoaded('impacto')),
            'pm_clase_orden' => new PmClaseOrdenResource($this->whenLoaded('pmClaseOrden')),
            'pm_clase_actividad' => new PmClaseActividadResource($this->whenLoaded('pmClaseActividad')),
            'evento_detectado' => new EntradaEventoDetectadoResource($this->whenLoaded('eventoDetectado')),
            'adjuntos' => AdjuntoResource::collection($this->whenLoaded('adjuntos')),
            'referencias_externas' => ReferenciaExternaResource::collection($this->whenLoaded('referenciasExternas')),
            'ubicacion_tecnica' => new DmUbicacionTecnicaResource($this->whenLoaded('ubicacionTecnica')),
            'ubicaciones' => DmUbicacionTecnicaResource::collection($this->whenLoaded('ubicaciones')),
            'equipo' => new DmEquipoResource($this->whenLoaded('equipo')),
            'equipos' => DmEquipoResource::collection($this->whenLoaded('equipos')),
            'inventario_elementos' => InvElementoRedResource::collection($this->whenLoaded('inventarioElementos')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'prev_id' => $this->getAttribute('prev_id'),
            'next_id' => $this->getAttribute('next_id'),
        ];
    }
}
