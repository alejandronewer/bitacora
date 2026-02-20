<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PmLikeSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // pm_clase_orden
        $ordenes = [
            ['nombre' => 'Actividad No Programada', 'descripcion' => 'Trabajo no programado (urgente/correctivo)'],
            ['nombre' => 'Actividad Programada', 'descripcion' => 'Trabajo programado (rutinas/plan)'],
            ['nombre' => 'Modernización y Puesta en Servicio', 'descripcion' => 'Trabajo de modernización y puesta en servicio'],
        ];

        $ordenRows = array_map(
            static fn (array $orden): array => [
                'nombre' => $orden['nombre'],
                'descripcion' => $orden['descripcion'],
                'activo' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            $ordenes
        );

        DB::table('pm_clase_orden')->upsert(
            $ordenRows,
            ['nombre'],
            ['descripcion', 'activo', 'updated_at']
        );

        // pm_clase_actividad
        $actividades = [
            ['nombre' => 'Preventivo', 'descripcion' => 'Mantenimiento preventivo'],
            ['nombre' => 'Predictivo / Inspección', 'descripcion' => 'Mantenimiento predictivo / inspección'],
            ['nombre' => 'Supervisión', 'descripcion' => 'Supervisión / verificación'],
            ['nombre' => 'Capacitación', 'descripcion' => 'Capacitación'],
            ['nombre' => 'Calidad', 'descripcion' => 'Calidad'],
            ['nombre' => 'Apoyo Operativo', 'descripcion' => 'Apoyo operativo a otro proceso de transmisión'],
            ['nombre' => 'Apoyo Generación', 'descripcion' => 'Apoyo a Generación'],
            ['nombre' => 'Apoyo Distribución', 'descripcion' => 'Apoyo a Distribución'],
            ['nombre' => 'Apoyo CENACE', 'descripcion' => 'Apoyo a CENACE'],
            ['nombre' => 'Correctivo', 'descripcion' => 'Atención correctiva'],
            ['nombre' => 'Modernización', 'descripcion' => 'Modernización'],
            ['nombre' => 'Puesta en Servicio', 'descripcion' => 'Puesta en Servicio'],
        ];

        $actividadRows = array_map(
            static fn (array $actividad): array => [
                'nombre' => $actividad['nombre'],
                'descripcion' => $actividad['descripcion'],
                'activo' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            $actividades
        );

        DB::table('pm_clase_actividad')->upsert(
            $actividadRows,
            ['nombre'],
            ['descripcion', 'activo', 'updated_at']
        );

        // pm_matriz_orden_actividad
        $ordenIds = DB::table('pm_clase_orden')
            ->whereIn('nombre', array_column($ordenes, 'nombre'))
            ->pluck('id', 'nombre')
            ->all();

        $actividadIds = DB::table('pm_clase_actividad')
            ->whereIn('nombre', array_column($actividades, 'nombre'))
            ->pluck('id', 'nombre')
            ->all();

        $pairs = [
            ['o' => 'Modernización y Puesta en Servicio', 'a' => 'Modernización'],
            ['o' => 'Modernización y Puesta en Servicio', 'a' => 'Puesta en Servicio'],

            ['o' => 'Actividad Programada', 'a' => 'Preventivo'],
            ['o' => 'Actividad Programada', 'a' => 'Predictivo / Inspección'],
            ['o' => 'Actividad Programada', 'a' => 'Correctivo'],
            ['o' => 'Actividad Programada', 'a' => 'Supervisión'],
            ['o' => 'Actividad Programada', 'a' => 'Capacitación'],
            ['o' => 'Actividad Programada', 'a' => 'Calidad'],
            ['o' => 'Actividad Programada', 'a' => 'Apoyo Operativo'],
            ['o' => 'Actividad Programada', 'a' => 'Apoyo Generación'],
            ['o' => 'Actividad Programada', 'a' => 'Apoyo Distribución'],
            ['o' => 'Actividad Programada', 'a' => 'Apoyo CENACE'],

            ['o' => 'Actividad No Programada', 'a' => 'Correctivo'],
            ['o' => 'Actividad No Programada', 'a' => 'Supervisión'],
            ['o' => 'Actividad No Programada', 'a' => 'Capacitación'],
            ['o' => 'Actividad No Programada', 'a' => 'Calidad'],
            ['o' => 'Actividad No Programada', 'a' => 'Apoyo Operativo'],
            ['o' => 'Actividad No Programada', 'a' => 'Apoyo Generación'],
            ['o' => 'Actividad No Programada', 'a' => 'Apoyo Distribución'],
            ['o' => 'Actividad No Programada', 'a' => 'Apoyo CENACE'],
        ];

        $matrixRows = [];
        foreach ($pairs as $p) {
            $oid = $ordenIds[$p['o']] ?? null;
            $aid = $actividadIds[$p['a']] ?? null;

            if (! $oid || ! $aid) {
                // Evita que el seeder falle si falta alguna referencia.
                continue;
            }

            $matrixRows[] = [
                'pm_clase_orden_id' => $oid,
                'pm_clase_actividad_id' => $aid,
                'activo' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($matrixRows !== []) {
            DB::table('pm_matriz_orden_actividad')->upsert(
                $matrixRows,
                ['pm_clase_orden_id', 'pm_clase_actividad_id'],
                ['activo', 'updated_at']
            );
        }
    }
}
