<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\DmEquipo;
use App\Models\DmUbicacionTecnica;
use App\Models\InvElementoRed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepuracionDatosAdminController extends Controller
{
    public function purgarNoUsados(Request $request)
    {
        $request->validate([
            'confirmaciones' => ['required', 'integer', 'in:3'],
            'alcance' => ['required', 'string', 'in:inventario,ubicaciones_equipos'],
        ]);

        $alcance = (string) $request->input('alcance');

        $resultado = DB::transaction(function () use ($alcance) {
            $inventarioCandidatos = 0;
            $inventarioEliminados = 0;
            $equiposCandidatos = 0;
            $equiposEliminados = 0;
            $ubicacionesCandidatas = 0;
            $ubicacionesEliminadas = 0;

            if ($alcance === 'inventario') {
                $inventarioQuery = InvElementoRed::query()
                    ->whereDoesntHave('entradas');
                $inventarioCandidatos = (clone $inventarioQuery)->count();
                $inventarioEliminados = $inventarioCandidatos > 0
                    ? $inventarioQuery->delete()
                    : 0;
            }

            if ($alcance === 'ubicaciones_equipos') {
                $equiposQuery = DmEquipo::query()
                    ->whereDoesntHave('entradasDirectas')
                    ->whereDoesntHave('entradasPivot');
                $equiposCandidatos = (clone $equiposQuery)->count();
                $equiposEliminados = $equiposCandidatos > 0
                    ? $equiposQuery->delete()
                    : 0;

                // Se ejecuta después de depurar equipos para no dejar ubicaciones huérfanas.
                $ubicacionesQuery = DmUbicacionTecnica::query()
                    ->whereDoesntHave('entradasDirectas')
                    ->whereDoesntHave('entradasPivot')
                    ->whereDoesntHave('equipos');
                $ubicacionesCandidatas = (clone $ubicacionesQuery)->count();
                $ubicacionesEliminadas = $ubicacionesCandidatas > 0
                    ? $ubicacionesQuery->delete()
                    : 0;
            }

            $totalEliminados = (int) $inventarioEliminados
                + (int) $equiposEliminados
                + (int) $ubicacionesEliminadas;

            return [
                'alcance' => $alcance,
                'inventario_elementos_candidatos' => (int) $inventarioCandidatos,
                'inventario_elementos_eliminados' => (int) $inventarioEliminados,
                'equipos_candidatos' => (int) $equiposCandidatos,
                'equipos_eliminados' => (int) $equiposEliminados,
                'ubicaciones_candidatas' => (int) $ubicacionesCandidatas,
                'ubicaciones_eliminadas' => (int) $ubicacionesEliminadas,
                'total_eliminados' => $totalEliminados,
            ];
        });

        $message = $alcance === 'inventario'
            ? 'Depuración de Inventario NMS/EMS completada. Solo se eliminaron elementos no usados en bitácora.'
            : 'Depuración de Ubicaciones Técnicas/Equipos completada. Solo se eliminaron registros no usados en bitácora.';

        return response()->json([
            'message' => $message,
            'data' => $resultado,
        ]);
    }
}
