<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvElementoRedResource extends JsonResource
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
                    'dominios' => $this->red->relationLoaded('dominios')
                        ? $this->red->dominios->map(fn ($dominio) => [
                            'id' => $dominio->id,
                            'codigo' => $dominio->codigo,
                            'nombre' => $dominio->nombre,
                        ])
                        : [],
                ];
            }),
            'tipo' => $this->tipo,
            'codigo' => $this->codigo,
            'nombre' => $this->nombre,
            'estado' => $this->estado,
            'origen' => $this->origen,
            'observaciones' => $this->observaciones,
            'fecha_alta' => $this->fecha_alta,
            'fecha_baja' => $this->fecha_baja,
            'updated_at_fuente' => $this->updated_at_fuente,
            'detalle_nodo' => $this->when($this->tipo === 'nodo', function () {
                $detalle = $this->relationLoaded('detalleNodo') ? $this->detalleNodo : null;

                if (! $detalle) {
                    return null;
                }

                return [
                    'ne_id' => $detalle->ne_id,
                    'ne_dbid' => $detalle->ne_dbid,
                    'dn_externo' => $detalle->dn_externo,
                    'native_name' => $detalle->native_name,
                    'user_label' => $detalle->user_label,
                    'nombre_producto' => $detalle->nombre_producto,
                    'tipo_equipo' => $detalle->tipo_equipo,
                    'version_me' => $detalle->version_me,
                    'direccion_red' => $detalle->direccion_red,
                    'nombre_grupo' => $detalle->nombre_grupo,
                ];
            }),
            'detalle_enlace' => $this->when($this->tipo === 'enlace', function () {
                $detalle = $this->relationLoaded('detalleEnlace') ? $this->detalleEnlace : null;

                if (! $detalle) {
                    return null;
                }

                $trailId = $detalle->trail_id ?? $detalle->trail_subyacente_id;
                $nodoANeId = $detalle->nodo_a_ne_id ?? $detalle->nodo_a_me_id;
                $nodoZNeId = $detalle->nodo_z_ne_id ?? $detalle->nodo_b_me_id;

                return [
                    'instancia_enlace_id' => $detalle->instancia_enlace_id,
                    'motlink_label' => $detalle->motlink_label,
                    'trail_id' => $trailId,
                    'nodo_a_ne_id' => $nodoANeId,
                    'nodo_z_ne_id' => $nodoZNeId,
                ];
            }),
            'detalle_servicio' => $this->when($this->tipo === 'servicio', function () {
                $detalle = $this->relationLoaded('detalleServicio') ? $this->detalleServicio : null;

                if (! $detalle) {
                    return null;
                }

                return [
                    'instancia_servicio_id' => $detalle->instancia_servicio_id,
                    'user_label' => $detalle->user_label,
                    'cliente' => $detalle->cliente,
                    'tipo_servicio' => $detalle->tipo_servicio,
                    'ethvpn_id' => $detalle->ethvpn_id,
                ];
            }),
            'detalle_tunel' => $this->when($this->tipo === 'tunel', function () {
                $detalle = $this->relationLoaded('detalleTunel') ? $this->detalleTunel : null;

                if (! $detalle) {
                    return null;
                }

                return [
                    'instancia_tunel_id' => $detalle->instancia_tunel_id,
                    'user_label' => $detalle->user_label,
                    'cliente' => $detalle->cliente,
                    'tipo_tunel' => $detalle->tipo_tunel,
                    'ethvpn_id' => $detalle->ethvpn_id,
                ];
            }),
            'entradas_count' => $this->entradas_count ?? 0,
            'in_use' => (($this->entradas_count ?? 0) > 0),
        ];
    }
}
