<?php

namespace App\Support;

use App\Models\EntradaBitacora;

class ResumenTecnicoBuilder
{
    public static function build(EntradaBitacora $entrada): string
    {
        $entrada->loadMissing([
            'criterio',
            'impacto',
            'pmClaseOrden',
            'pmClaseActividad',
            'ubicacionTecnica',
            'equipo',
            'inventarioElementos',
            'referenciasExternas.sistema',
            'eventoDetectado',
        ]);

        $lineas = [];
        $tipoRegistro = 'sin tipo registrado';
        if ($entrada->tipo_registro === 'inventario') {
            $tipoRegistro = 'de inventario';
        } elseif ($entrada->tipo_registro === 'operativo') {
            $tipoRegistro = 'operativa';
        }
        $criterio = $entrada->criterio?->nombre;
        $impacto = $entrada->impacto?->nombre;
        $isSinImpacto = false;

        if ($entrada->impacto) {
            $nombreImpacto = mb_strtolower((string) $impacto);
            $isSinImpacto = ($entrada->impacto->severidad === 0) || str_contains($nombreImpacto, 'sin impacto');
        }

        $extras = [];
        $extras[] = $criterio ? "referente al criterio {$criterio}" : 'sin criterio técnico registrado';
        if ($impacto) {
            $extras[] = $isSinImpacto ? 'sin nivel de impacto registrado' : "con nivel de impacto {$impacto}";
        } else {
            $extras[] = 'sin nivel de impacto registrado';
        }

        $base = "Entrada {$tipoRegistro}";
        $lineas[] = $extras
            ? $base . ', ' . self::joinConY($extras) . '.'
            : $base . '.';

        if ($impacto && ! $isSinImpacto) {
            if ($entrada->eventoDetectado?->tipo_evento) {
                $tipoEvento = $entrada->eventoDetectado->tipo_evento === 'ANOMALIA' ? 'ANOMALÍA' : 'FALLA';
                $lineas[] = "Con {$tipoEvento} detectada.";
            } else {
                $lineas[] = 'Sin FALLA o ANOMALÍA detectada.';
            }
        }

        $inicio = self::formatFecha($entrada->fecha_inicio);
        $fin = self::formatFecha($entrada->fecha_fin);
        if ($inicio && $fin) {
            $lineas[] = "Periodo operativo: desde {$inicio} hasta {$fin}.";
        } elseif ($inicio) {
            $lineas[] = "Inicio registrado: {$inicio}.";
        } elseif ($fin) {
            $lineas[] = "Fin registrado: {$fin}.";
        }

        if ($entrada->ubicacion_manual || $entrada->equipo_manual) {
            $partes = [];
            if ($entrada->ubicacion_manual) {
                $partes[] = "Ubicación manual registrada: {$entrada->ubicacion_manual}";
            }
            if ($entrada->equipo_manual) {
                $partes[] = "Equipo manual registrado: {$entrada->equipo_manual}";
            }
            if ($partes) {
                $lineas[] = implode('. ', $partes) . '.';
            }
        } else {
            $partes = [];
            if ($entrada->ubicacionTecnica?->nombre) {
                $partes[] = "Ubicación técnica: {$entrada->ubicacionTecnica->nombre}";
            }
            if ($entrada->equipo?->nombre) {
                $partes[] = "Equipo asociado: {$entrada->equipo->nombre}";
            }
            if ($partes) {
                $lineas[] = implode('. ', $partes) . '.';
            }
        }

        if ($entrada->pmClaseOrden || $entrada->pmClaseActividad) {
            $orden = $entrada->pmClaseOrden?->nombre;
            $actividad = $entrada->pmClaseActividad?->nombre;
            $pmLine = "La entrada {$tipoRegistro}";
            if ($orden && $actividad) {
                $pmLine .= " es de {$orden} para {$actividad}.";
            } elseif ($orden) {
                $pmLine .= " es de {$orden}.";
            } elseif ($actividad) {
                $pmLine .= " es para {$actividad}.";
            }
            $lineas[] = $pmLine;
        }

        if ($entrada->tipo_registro === 'inventario' && $entrada->accion_inventario) {
            $lineas[] = "Se registra un movimiento de {$entrada->accion_inventario} en la red.";
        }

        $elementos = $entrada->inventarioElementos;
        if ($elementos && $elementos->count() > 0) {
            $nombres = $elementos->pluck('nombre')->filter()->values()->all();
            $lista = self::joinConY($nombres);
            if ($lista) {
                $lineas[] = "Se involucran elementos: {$lista}.";
            }
        } elseif ($entrada->tipo_registro === 'inventario') {
            $lineas[] = 'Sin elementos asociados.';
        }

        $referencias = $entrada->referenciasExternas;
        if ($referencias && $referencias->count() > 0) {
            $segments = $referencias->map(function ($ref) {
                $sistema = $ref->sistema?->nombre ?? 'Sistema';
                return "{$sistema} (folio {$ref->externo_id})";
            })->values()->all();

            $lista = self::joinConY($segments);
            if ($lista) {
                $lineas[] = "Para esta actividad se registran las referencias externas: {$lista}.";
            }
        }

        return trim(implode("\n", $lineas));
    }

    private static function formatFecha($fecha): ?string
    {
        return $fecha ? $fecha->format('Y-m-d H:i') : null;
    }

    private static function joinConY(array $items): string
    {
        $items = array_values(array_filter($items, fn ($item) => trim((string) $item) !== ''));
        $count = count($items);
        if ($count === 0) {
            return '';
        }
        if ($count === 1) {
            return $items[0];
        }
        if ($count === 2) {
            return $items[0] . ' y ' . $items[1];
        }
        $last = array_pop($items);
        return implode(', ', $items) . ' y ' . $last;
    }
}
