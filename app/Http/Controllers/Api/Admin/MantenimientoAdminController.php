<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\CleanupAdjuntosTemporalesService;
use App\Services\CleanupImportFilesService;
use Illuminate\Http\Request;

class MantenimientoAdminController extends Controller
{
    public function cleanupAdjuntosTemporales(Request $request, CleanupAdjuntosTemporalesService $service)
    {
        $data = $request->validate([
            'hours' => ['nullable', 'integer', 'min:1', 'max:720'],
            'dry_run' => ['nullable', 'boolean'],
        ]);

        $hours = (int) ($data['hours'] ?? 24);
        $dryRun = (bool) ($data['dry_run'] ?? true);

        $result = $dryRun
            ? $service->preview($hours)
            : $service->cleanup($hours);

        return response()->json([
            'message' => $dryRun
                ? 'Simulación completada.'
                : 'Limpieza de adjuntos temporales completada.',
            'data' => $result,
        ]);
    }

    public function cleanupImportFiles(Request $request, CleanupImportFilesService $service)
    {
        $data = $request->validate([
            'hours' => ['nullable', 'integer', 'min:1', 'max:720'],
            'dry_run' => ['nullable', 'boolean'],
        ]);

        $hours = (int) ($data['hours'] ?? 24);
        $dryRun = (bool) ($data['dry_run'] ?? true);

        $result = $dryRun
            ? $service->preview($hours)
            : $service->cleanup($hours);

        return response()->json([
            'message' => $dryRun
                ? 'Simulación completada.'
                : 'Limpieza de archivos de importación completada.',
            'data' => $result,
        ]);
    }
}
