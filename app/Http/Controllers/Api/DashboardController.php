<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CatEntradaCriterio;
use App\Models\CatEntradaImpacto;
use App\Models\DmUbicacionTecnica;
use App\Models\EntradaBitacora;
use App\Models\EntradaEventoDetectado;
use App\Models\PmClaseActividad;
use App\Models\PmClaseOrden;
use App\Models\PmMatrizOrdenActividad;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function resumen(Request $request)
    {
        $anioSolicitado = (int) $request->query('anio', now()->format('Y'));
        $anioSolicitado = $anioSolicitado > 0 ? $anioSolicitado : (int) now()->format('Y');

        $now = now();
        $inicioMes = $now->copy()->startOfMonth();
        $inicioAnio = $now->copy()->startOfYear();
        $anioActual = (int) $now->format('Y');

        $publicadasMes = EntradaBitacora::where('publicado', 1)
            ->whereBetween('fecha_inicio', [$inicioMes, $now])
            ->count();

        $publicadasAnio = EntradaBitacora::where('publicado', 1)
            ->whereBetween('fecha_inicio', [$inicioAnio, $now])
            ->count();

        $total = EntradaBitacora::count();
        $publicadas = EntradaBitacora::where('publicado', 1)->count();
        $borradores = EntradaBitacora::where('publicado', 0)->count();

        $usuariosActivosBase = User::query()
            ->where('activo', 1)
            ->withCount([
                'entradas as publicadas_count' => function ($query) {
                    $query->where('publicado', 1);
                },
            ]);

        $usuariosTop = (clone $usuariosActivosBase)
            ->orderByDesc('publicadas_count')
            ->orderBy('nombre')
            ->limit(5)
            ->get(['id', 'nombre', 'correo', 'publicadas_count']);

        $usuariosBaja = (clone $usuariosActivosBase)
            ->orderBy('publicadas_count')
            ->orderBy('nombre')
            ->limit(5)
            ->get(['id', 'nombre', 'correo', 'publicadas_count']);

        $widgetsAnio = (int) $request->query('widgets_anio', $anioActual);
        $widgetsAnio = $widgetsAnio > 0 ? $widgetsAnio : $anioActual;
        $widgetsMes = (int) $request->query('widgets_mes', $now->month);
        if ($widgetsMes < 1 || $widgetsMes > 12) {
            $widgetsMes = $now->month;
        }
        $widgetsInicio = $now->copy()->setDate($widgetsAnio, $widgetsMes, 1)->startOfMonth();
        $widgetsFin = $widgetsInicio->copy()->endOfMonth();
        $impactoCatalogo = CatEntradaImpacto::query()
            ->orderBy('severidad')
            ->get(['id', 'nombre', 'severidad']);
        $impactoCounts = EntradaBitacora::query()
            ->selectRaw('entrada_impacto_id, COUNT(*) as total')
            ->where('publicado', 1)
            ->whereBetween('fecha_inicio', [$widgetsInicio, $widgetsFin])
            ->groupBy('entrada_impacto_id')
            ->pluck('total', 'entrada_impacto_id');
        $impactoLabels = [];
        $impactoData = [];
        foreach ($impactoCatalogo as $impacto) {
            $impactoLabels[] = $impacto->nombre;
            $impactoData[] = (int) ($impactoCounts[$impacto->id] ?? 0);
        }
        $impactoNull = EntradaBitacora::query()
            ->where('publicado', 1)
            ->whereBetween('fecha_inicio', [$widgetsInicio, $widgetsFin])
            ->whereNull('entrada_impacto_id')
            ->count();
        if ($impactoNull > 0) {
            $impactoLabels[] = 'Sin impacto registrado';
            $impactoData[] = $impactoNull;
        }

        $eventosRows = EntradaEventoDetectado::query()
            ->selectRaw('entrada_eventos_detectados.tipo_evento, COUNT(*) as total')
            ->join('entradas_bitacora', 'entradas_bitacora.id', '=', 'entrada_eventos_detectados.entrada_id')
            ->where('entradas_bitacora.publicado', 1)
            ->whereBetween('entradas_bitacora.fecha_inicio', [$widgetsInicio, $widgetsFin])
            ->groupBy('entrada_eventos_detectados.tipo_evento')
            ->pluck('total', 'tipo_evento');
        $eventosFalla = (int) ($eventosRows['FALLA'] ?? 0);
        $eventosAnomalia = (int) ($eventosRows['ANOMALIA'] ?? 0);
        $totalEventos = $eventosFalla + $eventosAnomalia;

        $impactoConSeveridad = EntradaBitacora::query()
            ->join('cat_entrada_impacto', 'cat_entrada_impacto.id', '=', 'entradas_bitacora.entrada_impacto_id')
            ->where('entradas_bitacora.publicado', 1)
            ->whereBetween('entradas_bitacora.fecha_inicio', [$widgetsInicio, $widgetsFin])
            ->where('cat_entrada_impacto.severidad', '>', 0)
            ->count();
        $eventosPorcentaje = $impactoConSeveridad > 0
            ? round(($totalEventos / $impactoConSeveridad) * 100, 1)
            : 0;

        $topUbicaciones = DmUbicacionTecnica::query()
            ->select('dm_ubicaciones_tecnicas.id', 'dm_ubicaciones_tecnicas.codigo', 'dm_ubicaciones_tecnicas.nombre')
            ->selectRaw('COUNT(entradas_bitacora.id) as total')
            ->join('entradas_bitacora', 'entradas_bitacora.ubicacion_tecnica_id', '=', 'dm_ubicaciones_tecnicas.id')
            ->where('entradas_bitacora.publicado', 1)
            ->whereBetween('entradas_bitacora.fecha_inicio', [$widgetsInicio, $widgetsFin])
            ->groupBy('dm_ubicaciones_tecnicas.id', 'dm_ubicaciones_tecnicas.codigo', 'dm_ubicaciones_tecnicas.nombre')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $rows = EntradaBitacora::query()
            ->selectRaw('MONTH(fecha_inicio) as mes, tipo_registro, COUNT(*) as total')
            ->whereYear('fecha_inicio', $anioSolicitado)
            ->groupBy('mes', 'tipo_registro')
            ->get();

        $labels = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        $serieOperativo = array_fill(0, 12, 0);
        $serieInventario = array_fill(0, 12, 0);

        foreach ($rows as $row) {
            $index = max(1, min(12, (int) $row->mes)) - 1;
            if ($row->tipo_registro === 'inventario') {
                $serieInventario[$index] = (int) $row->total;
            } else {
                $serieOperativo[$index] = (int) $row->total;
            }
        }

        $pmAnio = (int) $request->query('pm_anio', $anioActual);
        $pmAnio = $pmAnio > 0 ? $pmAnio : $anioActual;
        $pmCuatrimestre = (int) $request->query('pm_cuatrimestre', (int) floor(($now->month - 1) / 4) + 1);
        if (! in_array($pmCuatrimestre, [1, 2, 3], true)) {
            $pmCuatrimestre = (int) floor(($now->month - 1) / 4) + 1;
        }
        $pmOrdenId = $request->query('pm_orden_id');
        $pmOrdenId = $pmOrdenId !== null && $pmOrdenId !== '' ? (int) $pmOrdenId : null;

        $inicioCuatrimestre = $now->copy()->setDate($pmAnio, ($pmCuatrimestre - 1) * 4 + 1, 1)->startOfMonth();
        $finCuatrimestre = $inicioCuatrimestre->copy()->addMonths(3)->endOfMonth();
        $monthKeys = [];
        $monthLabels = [];
        $cursor = $inicioCuatrimestre->copy();
        for ($i = 0; $i < 4; $i++) {
            $monthKeys[] = $cursor->format('Y-m');
            $monthLabels[] = ucfirst($cursor->locale('es')->translatedFormat('M Y'));
            $cursor->addMonthNoOverflow();
        }

        $ordenDatasets = [];
        if ($pmOrdenId) {
            $actividadIds = PmMatrizOrdenActividad::where('pm_clase_orden_id', $pmOrdenId)
                ->pluck('pm_clase_actividad_id');
            $actividades = PmClaseActividad::whereIn('id', $actividadIds)->pluck('nombre', 'id');

            $rowsActividad = EntradaBitacora::query()
                ->selectRaw('DATE_FORMAT(fecha_inicio, "%Y-%m") as ym, pm_clase_actividad_id, COUNT(*) as total')
                ->whereBetween('fecha_inicio', [$inicioCuatrimestre, $finCuatrimestre])
                ->where('pm_clase_orden_id', $pmOrdenId)
                ->groupBy('ym', 'pm_clase_actividad_id')
                ->get();

            foreach ($rowsActividad as $row) {
                $key = $row->pm_clase_actividad_id ? 'actividad_' . $row->pm_clase_actividad_id : 'sin_actividad';
                $label = $row->pm_clase_actividad_id ? ($actividades[$row->pm_clase_actividad_id] ?? ('Actividad #' . $row->pm_clase_actividad_id)) : 'Sin actividad';
                if (! array_key_exists($key, $ordenDatasets)) {
                    $ordenDatasets[$key] = [
                        'label' => $label,
                        'data' => array_fill(0, count($monthKeys), 0),
                    ];
                }
                $index = array_search($row->ym, $monthKeys, true);
                if ($index !== false) {
                    $ordenDatasets[$key]['data'][$index] = (int) $row->total;
                }
            }
        } else {
            $ordenes = PmClaseOrden::pluck('nombre', 'id');
            $ordenRows = EntradaBitacora::query()
                ->selectRaw('DATE_FORMAT(fecha_inicio, "%Y-%m") as ym, pm_clase_orden_id, COUNT(*) as total')
                ->whereBetween('fecha_inicio', [$inicioCuatrimestre, $finCuatrimestre])
                ->groupBy('ym', 'pm_clase_orden_id')
                ->get();

            foreach ($ordenRows as $row) {
                $key = $row->pm_clase_orden_id ? 'orden_' . $row->pm_clase_orden_id : 'sin_orden';
                $label = $row->pm_clase_orden_id ? ($ordenes[$row->pm_clase_orden_id] ?? ('Orden #' . $row->pm_clase_orden_id)) : 'Sin orden';
                if (! array_key_exists($key, $ordenDatasets)) {
                    $ordenDatasets[$key] = [
                        'label' => $label,
                        'data' => array_fill(0, count($monthKeys), 0),
                    ];
                }
                $index = array_search($row->ym, $monthKeys, true);
                if ($index !== false) {
                    $ordenDatasets[$key]['data'][$index] = (int) $row->total;
                }
            }
        }

        $criterioAnio = (int) $request->query('criterio_anio', $anioActual);
        $criterioAnio = $criterioAnio > 0 ? $criterioAnio : $anioActual;
        $criterioCuatrimestre = (int) $request->query('criterio_cuatrimestre', (int) floor(($now->month - 1) / 4) + 1);
        if (! in_array($criterioCuatrimestre, [1, 2, 3], true)) {
            $criterioCuatrimestre = (int) floor(($now->month - 1) / 4) + 1;
        }
        $criterioInicio = $now->copy()->setDate($criterioAnio, ($criterioCuatrimestre - 1) * 4 + 1, 1)->startOfMonth();
        $criterioFin = $criterioInicio->copy()->addMonths(3)->endOfMonth();
        $criterioMonthKeys = [];
        $criterioMonthLabels = [];
        $cursorCriterio = $criterioInicio->copy();
        for ($i = 0; $i < 4; $i++) {
            $criterioMonthKeys[] = $cursorCriterio->format('Y-m');
            $criterioMonthLabels[] = ucfirst($cursorCriterio->locale('es')->translatedFormat('M Y'));
            $cursorCriterio->addMonthNoOverflow();
        }

        $criterios = CatEntradaCriterio::pluck('nombre', 'id');
        $criterioRows = EntradaBitacora::query()
            ->selectRaw('DATE_FORMAT(fecha_inicio, "%Y-%m") as ym, entrada_criterio_id, COUNT(*) as total')
            ->whereBetween('fecha_inicio', [$criterioInicio, $criterioFin])
            ->groupBy('ym', 'entrada_criterio_id')
            ->get();

        $criterioDatasets = [];
        foreach ($criterioRows as $row) {
            $key = $row->entrada_criterio_id ? 'criterio_' . $row->entrada_criterio_id : 'sin_criterio';
            $label = $row->entrada_criterio_id ? ($criterios[$row->entrada_criterio_id] ?? ('Criterio #' . $row->entrada_criterio_id)) : 'Sin criterio';
            if (! array_key_exists($key, $criterioDatasets)) {
                $criterioDatasets[$key] = [
                    'label' => $label,
                    'data' => array_fill(0, count($criterioMonthKeys), 0),
                ];
            }
            $index = array_search($row->ym, $criterioMonthKeys, true);
            if ($index !== false) {
                $criterioDatasets[$key]['data'][$index] = (int) $row->total;
            }
        }

        return response()->json([
            'publicadas_mes' => $publicadasMes,
            'publicadas_anio' => $publicadasAnio,
            'total' => $total,
            'publicadas' => $publicadas,
            'borradores' => $borradores,
            'usuarios_activos_top' => $usuariosTop,
            'usuarios_activos_baja' => $usuariosBaja,
            'widgets_periodo' => [
                'anio' => $widgetsAnio,
                'mes' => $widgetsMes,
            ],
            'impacto_severidad' => [
                'labels' => $impactoLabels,
                'data' => $impactoData,
            ],
            'eventos_detectados' => [
                'labels' => ['FALLA', 'ANOMALÍA'],
                'data' => [$eventosFalla, $eventosAnomalia],
                'total_eventos' => $totalEventos,
                'total_impacto' => $impactoConSeveridad,
                'porcentaje' => $eventosPorcentaje,
            ],
            'top_ubicaciones' => $topUbicaciones,
            'series' => [
                'labels' => $labels,
                'operativo' => $serieOperativo,
                'inventario' => $serieInventario,
                'anio' => $anioSolicitado,
                'anio_actual' => $anioActual,
            ],
            'pm_series' => [
                'labels' => $monthLabels,
                'datasets' => array_values($ordenDatasets),
                'mode' => $pmOrdenId ? 'actividad' : 'orden',
                'anio' => $pmAnio,
                'cuatrimestre' => $pmCuatrimestre,
            ],
            'criterio_series' => [
                'labels' => $criterioMonthLabels,
                'datasets' => array_values($criterioDatasets),
                'anio' => $criterioAnio,
                'cuatrimestre' => $criterioCuatrimestre,
            ],
        ]);
    }
}
