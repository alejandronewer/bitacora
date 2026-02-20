<?php

namespace App\Services;

use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CleanupImportFilesService
{
    public function preview(int $hours): array
    {
        $cutoff = now()->subHours($hours);
        $candidates = $this->collectCandidates($cutoff);

        return [
            'hours' => $hours,
            'cutoff' => $cutoff->toDateTimeString(),
            'db_import_archivos_total' => count($candidates['referenced_files']),
            'db_import_registros_afectados' => count($candidates['referenced_import_ids']),
            'fs_huerfanos_total' => count($candidates['orphan_files']),
            'total' => count($candidates['referenced_files']) + count($candidates['orphan_files']),
            'deleted' => 0,
            'db_import_archivos_deleted' => 0,
            'db_import_registros_actualizados' => 0,
            'fs_huerfanos_deleted' => 0,
            'dry_run' => true,
        ];
    }

    public function cleanup(int $hours): array
    {
        $cutoff = now()->subHours($hours);
        $candidates = $this->collectCandidates($cutoff);

        $referencedFiles = $candidates['referenced_files'];
        $orphanFiles = $candidates['orphan_files'];
        $importIdsByPath = $candidates['import_ids_by_path'];

        if ($referencedFiles === [] && $orphanFiles === []) {
            return [
                'hours' => $hours,
                'cutoff' => $cutoff->toDateTimeString(),
                'db_import_archivos_total' => 0,
                'db_import_registros_afectados' => 0,
                'fs_huerfanos_total' => 0,
                'total' => 0,
                'deleted' => 0,
                'db_import_archivos_deleted' => 0,
                'db_import_registros_actualizados' => 0,
                'fs_huerfanos_deleted' => 0,
                'dry_run' => false,
            ];
        }

        $disk = Storage::disk('local');
        $dbFilesDeleted = 0;
        $orphanFilesDeleted = 0;
        $importIdsToNull = [];

        foreach ($referencedFiles as $path) {
            if ($disk->delete($path)) {
                $dbFilesDeleted++;
                foreach ($importIdsByPath[$path] ?? [] as $id) {
                    $importIdsToNull[(int) $id] = true;
                }
            }
        }

        foreach ($orphanFiles as $path) {
            if ($disk->delete($path)) {
                $orphanFilesDeleted++;
            }
        }

        $importIds = array_keys($importIdsToNull);
        $importsUpdated = 0;
        if ($importIds !== []) {
            $importsUpdated = (int) DB::table('inv_importaciones_redes')
                ->whereIn('id', $importIds)
                ->update(['fuente' => null, 'updated_at' => now()]);
        }

        $this->pruneEmptyImportDirectories($disk);

        return [
            'hours' => $hours,
            'cutoff' => $cutoff->toDateTimeString(),
            'db_import_archivos_total' => count($referencedFiles),
            'db_import_registros_afectados' => count($candidates['referenced_import_ids']),
            'fs_huerfanos_total' => count($orphanFiles),
            'total' => count($referencedFiles) + count($orphanFiles),
            'deleted' => $dbFilesDeleted + $orphanFilesDeleted,
            'db_import_archivos_deleted' => $dbFilesDeleted,
            'db_import_registros_actualizados' => $importsUpdated,
            'fs_huerfanos_deleted' => $orphanFilesDeleted,
            'dry_run' => false,
        ];
    }

    /**
     * @return array{
     *   referenced_files: array<int, string>,
     *   orphan_files: array<int, string>,
     *   referenced_import_ids: array<int, int>,
     *   import_ids_by_path: array<string, array<int, int>>
     * }
     */
    private function collectCandidates(CarbonInterface $cutoff): array
    {
        $disk = Storage::disk('local');
        $allImportFiles = $disk->allFiles('imports');
        if ($allImportFiles === []) {
            return [
                'referenced_files' => [],
                'orphan_files' => [],
                'referenced_import_ids' => [],
                'import_ids_by_path' => [],
            ];
        }

        $rows = DB::table('inv_importaciones_redes')
            ->select('id', 'fuente')
            ->whereNotNull('fuente')
            ->get();

        $importIdsByPath = [];
        foreach ($rows as $row) {
            $path = $this->normalizePath($row->fuente);
            if (! str_starts_with($path, 'imports/')) {
                continue;
            }
            if (! isset($importIdsByPath[$path])) {
                $importIdsByPath[$path] = [];
            }
            $importIdsByPath[$path][] = (int) $row->id;
        }

        $cutoffTs = $cutoff->getTimestamp();
        $referencedFiles = [];
        $referencedImportIds = [];
        $orphanFiles = [];

        foreach ($allImportFiles as $filePath) {
            $path = $this->normalizePath($filePath);
            if ($path === '') {
                continue;
            }
            if (basename($path) === '.gitignore') {
                continue;
            }

            try {
                $lastModified = $disk->lastModified($path);
            } catch (\Throwable $e) {
                continue;
            }

            if (! is_int($lastModified) || $lastModified >= $cutoffTs) {
                continue;
            }

            if (isset($importIdsByPath[$path])) {
                $referencedFiles[] = $path;
                foreach ($importIdsByPath[$path] as $id) {
                    $referencedImportIds[$id] = $id;
                }
                continue;
            }

            $orphanFiles[] = $path;
        }

        return [
            'referenced_files' => array_values(array_unique($referencedFiles)),
            'orphan_files' => array_values(array_unique($orphanFiles)),
            'referenced_import_ids' => array_values($referencedImportIds),
            'import_ids_by_path' => $importIdsByPath,
        ];
    }

    private function pruneEmptyImportDirectories($disk): void
    {
        // Recorre de mayor profundidad a menor para borrar subdirectorios primero.
        $dirs = $disk->allDirectories('imports');
        usort($dirs, static fn (string $a, string $b): int => substr_count($b, '/') <=> substr_count($a, '/'));

        foreach ($dirs as $dir) {
            try {
                $files = $disk->files($dir);
                $subDirs = $disk->directories($dir);
            } catch (\Throwable $e) {
                continue;
            }

            if ($files === [] && $subDirs === []) {
                $disk->deleteDirectory($dir);
            }
        }
    }

    private function normalizePath(?string $path): string
    {
        return ltrim((string) ($path ?? ''), '/');
    }
}
