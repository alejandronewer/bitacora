<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogoOperativoSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // TEMAS (cat_entrada_criterio)
        $temas = [
            ['codigo' => 'operacion', 'nombre' => 'Operación', 'descripcion' => 'Operación diaria, seguimiento, monitoreo, maniobras', 'orden' => 10],
            ['codigo' => 'seguridad', 'nombre' => 'Seguridad', 'descripcion' => 'Seguridad operativa, riesgos, condiciones inseguras', 'orden' => 20],
            ['codigo' => 'planeacion', 'nombre' => 'Planeación', 'descripcion' => 'Planeación, solicitudes, ventanas', 'orden' => 30],
            ['codigo' => 'documentacion', 'nombre' => 'Documentación', 'descripcion' => 'Actualización de diagramas, procedimientos, evidencias', 'orden' => 40],
            ['codigo' => 'mejora', 'nombre' => 'Mejora', 'descripcion' => 'Mejora continua, lecciones aprendidas, optimización', 'orden' => 50],
            ['codigo' => 'otro', 'nombre' => 'Otro', 'descripcion' => 'No clasificado', 'orden' => 99],
        ];

        $temasRows = array_map(static function (array $t) use ($now): array {
            return [
                'codigo' => $t['codigo'],
                'nombre' => $t['nombre'],
                'descripcion' => $t['descripcion'],
                'orden' => $t['orden'],
                'activo' => 1,
                'created_at' => $now,
                'updated_at' => null,
            ];
        }, $temas);

        DB::table('cat_entrada_criterio')->upsert(
            $temasRows,
            ['codigo'],
            ['nombre', 'descripcion', 'orden', 'activo', 'updated_at']
        );

        DB::table('cat_entrada_criterio')
            ->whereNotIn('codigo', array_column($temas, 'codigo'))
            ->update(['activo' => 0, 'updated_at' => null]);

        // IMPACTO (cat_entrada_impacto)
        $impactos = [
            ['codigo' => 'ninguno', 'nombre' => 'Sin impacto', 'descripcion' => 'Sin afectación confirmada', 'severidad' => 0, 'orden' => 10],
            ['codigo' => 'advertencia', 'nombre' => 'Advertencia', 'descripcion' => 'Condición anómala sin afectación al servicio', 'severidad' => 1, 'orden' => 20],
            ['codigo' => 'menor', 'nombre' => 'Menor', 'descripcion' => 'Afectación menor o localizada', 'severidad' => 2, 'orden' => 30],
            ['codigo' => 'mayor', 'nombre' => 'Mayor', 'descripcion' => 'Afectación significativa o degradación severa', 'severidad' => 3, 'orden' => 40],
            ['codigo' => 'critico', 'nombre' => 'Crítico', 'descripcion' => 'Interrupción total o impacto crítico', 'severidad' => 4, 'orden' => 50],
        ];

        $impactosRows = array_map(static function (array $i) use ($now): array {
            return [
                'codigo' => $i['codigo'],
                'nombre' => $i['nombre'],
                'descripcion' => $i['descripcion'],
                'severidad' => $i['severidad'],
                'orden' => $i['orden'],
                'activo' => 1,
                'created_at' => $now,
                'updated_at' => null,
            ];
        }, $impactos);

        DB::table('cat_entrada_impacto')->upsert(
            $impactosRows,
            ['codigo'],
            ['nombre', 'descripcion', 'severidad', 'orden', 'activo', 'updated_at']
        );

        DB::table('cat_entrada_impacto')
            ->whereNotIn('codigo', array_column($impactos, 'codigo'))
            ->update(['activo' => 0, 'updated_at' => null]);
    }
}
