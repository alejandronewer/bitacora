<?php

namespace App\Services;

use App\Models\Adjunto;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Storage;

class CleanupAdjuntosTemporalesService
{
    public function preview(int $hours): array
    {
        $cutoff = now()->subHours($hours);
        $temporalesDbTotal = (int) $this->queryTemporalesDb($cutoff)->count();
        $orphanFiles = $this->findOrphanFiles($cutoff);
        $orphanFilesTotal = count($orphanFiles);
        $total = $temporalesDbTotal + $orphanFilesTotal;

        return [
            'hours' => $hours,
            'cutoff' => $cutoff->toDateTimeString(),
            'db_temporales_total' => $temporalesDbTotal,
            'fs_huerfanos_total' => $orphanFilesTotal,
            'total' => (int) $total,
            'deleted' => 0,
            'db_temporales_deleted' => 0,
            'fs_huerfanos_deleted' => 0,
            'dry_run' => true,
        ];
    }

    public function cleanup(int $hours): array
    {
        $cutoff = now()->subHours($hours);
        $query = $this->queryTemporalesDb($cutoff);
        $temporalesDbTotal = (int) $query->count();
        $orphanFiles = $this->findOrphanFiles($cutoff);
        $orphanFilesTotal = count($orphanFiles);
        $total = $temporalesDbTotal + $orphanFilesTotal;

        if ($temporalesDbTotal === 0 && $orphanFilesTotal === 0) {
            return [
                'hours' => $hours,
                'cutoff' => $cutoff->toDateTimeString(),
                'db_temporales_total' => 0,
                'fs_huerfanos_total' => 0,
                'total' => 0,
                'deleted' => 0,
                'db_temporales_deleted' => 0,
                'fs_huerfanos_deleted' => 0,
                'dry_run' => false,
            ];
        }

        $dbDeleted = 0;
        $query->chunkById(100, function ($adjuntos) use (&$dbDeleted) {
            foreach ($adjuntos as $adjunto) {
                if ($adjunto->ruta) {
                    Storage::disk('public')->delete($adjunto->ruta);
                }
                $adjunto->delete();
                $dbDeleted++;
            }
        });

        $fsDeleted = 0;
        if (! empty($orphanFiles)) {
            foreach ($orphanFiles as $path) {
                if (Storage::disk('public')->delete($path)) {
                    $fsDeleted++;
                }
            }
        }

        $deleted = $dbDeleted + $fsDeleted;

        return [
            'hours' => $hours,
            'cutoff' => $cutoff->toDateTimeString(),
            'db_temporales_total' => $temporalesDbTotal,
            'fs_huerfanos_total' => $orphanFilesTotal,
            'total' => $total,
            'deleted' => (int) $deleted,
            'db_temporales_deleted' => (int) $dbDeleted,
            'fs_huerfanos_deleted' => (int) $fsDeleted,
            'dry_run' => false,
        ];
    }

    private function queryTemporalesDb(CarbonInterface $cutoff)
    {
        return Adjunto::query()
            ->whereNull('entrada_id')
            ->where('created_at', '<', $cutoff);
    }

    private function findOrphanFiles(CarbonInterface $cutoff): array
    {
        $disk = Storage::disk('public');
        $allAdjuntoFiles = $disk->allFiles('adjuntos');

        if (empty($allAdjuntoFiles)) {
            return [];
        }

        $referenced = Adjunto::query()
            ->whereNotNull('ruta')
            ->pluck('ruta')
            ->map(fn ($ruta) => $this->normalizePath($ruta))
            ->filter()
            ->flip();

        $cutoffTimestamp = $cutoff->getTimestamp();
        $orphans = [];

        foreach ($allAdjuntoFiles as $path) {
            $normalizedPath = $this->normalizePath($path);
            if ($normalizedPath === '') {
                continue;
            }

            if ($referenced->has($normalizedPath)) {
                continue;
            }

            try {
                $lastModified = $disk->lastModified($normalizedPath);
            } catch (\Throwable $e) {
                // Ignora archivos no legibles/intermitentes; no deben frenar la limpieza.
                continue;
            }

            if (! is_int($lastModified) || $lastModified >= $cutoffTimestamp) {
                continue;
            }

            $orphans[] = $normalizedPath;
        }

        return $orphans;
    }

    private function normalizePath(?string $path): string
    {
        return ltrim((string) ($path ?? ''), '/');
    }
}
