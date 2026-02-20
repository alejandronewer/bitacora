<?php

namespace App\Console\Commands;

use App\Services\CleanupImportFilesService;
use Illuminate\Console\Command;

class CleanupImportFiles extends Command
{
    protected $signature = 'imports:cleanup {--hours=24 : Antigüedad mínima en horas} {--dry-run : Solo mostrar cuántos se eliminarían}';

    protected $description = 'Elimina archivos de importación antiguos (referenciados y huérfanos) en storage/app/imports.';

    public function handle(): int
    {
        /** @var CleanupImportFilesService $service */
        $service = app(CleanupImportFilesService::class);

        $hours = (int) $this->option('hours');
        if ($hours <= 0) {
            $this->error('El parámetro --hours debe ser mayor a 0.');
            return self::FAILURE;
        }

        $preview = $service->preview($hours);
        $total = (int) ($preview['total'] ?? 0);
        if ($total === 0) {
            $this->info('No hay archivos de importación para limpiar.');
            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $cutoff = (string) ($preview['cutoff'] ?? '');
            $dbFiles = (int) ($preview['db_import_archivos_total'] ?? 0);
            $orphans = (int) ($preview['fs_huerfanos_total'] ?? 0);
            $this->info("Se eliminarían {$total} archivo(s) (referenciados: {$dbFiles}, huérfanos: {$orphans}) anteriores a {$cutoff}.");
            return self::SUCCESS;
        }

        $result = $service->cleanup($hours);
        $deleted = (int) ($result['deleted'] ?? 0);
        $dbDeleted = (int) ($result['db_import_archivos_deleted'] ?? 0);
        $orphansDeleted = (int) ($result['fs_huerfanos_deleted'] ?? 0);
        $updatedRows = (int) ($result['db_import_registros_actualizados'] ?? 0);

        $this->info("Eliminados {$deleted} archivo(s) (referenciados: {$dbDeleted}, huérfanos: {$orphansDeleted}); importaciones actualizadas: {$updatedRows}.");
        return self::SUCCESS;
    }
}

