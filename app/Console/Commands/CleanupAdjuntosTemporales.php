<?php

namespace App\Console\Commands;

use App\Services\CleanupAdjuntosTemporalesService;
use Illuminate\Console\Command;

class CleanupAdjuntosTemporales extends Command
{
    protected $signature = 'adjuntos:cleanup {--hours=24 : Antigüedad mínima en horas} {--dry-run : Solo mostrar cuántos se eliminarían}';

    protected $description = 'Elimina adjuntos temporales (sin entrada_id) y archivos huérfanos en disco con antigüedad mayor al umbral indicado.';

    public function handle(): int
    {
        /** @var CleanupAdjuntosTemporalesService $service */
        $service = app(CleanupAdjuntosTemporalesService::class);

        $hours = (int) $this->option('hours');
        if ($hours <= 0) {
            $this->error('El parámetro --hours debe ser mayor a 0.');
            return self::FAILURE;
        }

        $preview = $service->preview($hours);
        $total = (int) ($preview['total'] ?? 0);
        if ($total === 0) {
            $this->info('No hay adjuntos temporales ni archivos huérfanos para limpiar.');
            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $cutoff = (string) ($preview['cutoff'] ?? '');
            $dbTotal = (int) ($preview['db_temporales_total'] ?? 0);
            $fsTotal = (int) ($preview['fs_huerfanos_total'] ?? 0);
            $this->info("Se eliminarían {$total} candidato(s) (DB temporales: {$dbTotal}, huérfanos en disco: {$fsTotal}) anteriores a {$cutoff}.");
            return self::SUCCESS;
        }

        $result = $service->cleanup($hours);
        $deleted = (int) ($result['deleted'] ?? 0);
        $dbDeleted = (int) ($result['db_temporales_deleted'] ?? 0);
        $fsDeleted = (int) ($result['fs_huerfanos_deleted'] ?? 0);

        $this->info("Eliminados {$deleted} candidato(s) (DB temporales: {$dbDeleted}, huérfanos en disco: {$fsDeleted}).");
        return self::SUCCESS;
    }
}
