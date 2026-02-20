<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventarioRedesDominiosSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $dominios = [
            ['codigo' => 'mpls_tp', 'nombre' => 'MPLS-TP', 'orden' => 10],
            ['codigo' => 'ip_mpls', 'nombre' => 'IP/MPLS', 'orden' => 20],
            ['codigo' => 'carrier_ethernet', 'nombre' => 'Carrier Ethernet', 'orden' => 30],
            ['codigo' => 'sdh_sonet', 'nombre' => 'SDH/SONET', 'orden' => 40],
            ['codigo' => 'pdh', 'nombre' => 'PDH', 'orden' => 50],
        ];

        $redes = [
            ['codigo' => 'ribbon', 'nombre' => 'Ribbon LightSOFT', 'descripcion' => 'Red Nacional de equipos ECI/Ribbon MPLS-TP', 'activo' => 1],
            ['codigo' => 'xtran_ztc_ztv', 'nombre' => 'TXCare Xtran ZTC/ZTV', 'descripcion' => 'Red Zona Transmisión Costa y Valle de equipos OTN Systems (Xtran) MPLS-TP', 'activo' => 1],
            ['codigo' => 'xtran_zts', 'nombre' => 'TXCare Xtran ZTS', 'descripcion' => 'Red Zona Transmisión Sur de equipos OTN Systems (Xtran) MPLS-TP', 'activo' => 1],
        ];

        $redDominios = [
            ['red' => 'ribbon', 'dominios' => ['mpls_tp']],
            ['red' => 'xtran_ztc_ztv', 'dominios' => ['mpls_tp']],
            ['red' => 'xtran_zts', 'dominios' => ['mpls_tp']],
        ];

        $dominioRows = array_map(
            static fn (array $dominio): array => [
                'codigo' => $dominio['codigo'],
                'nombre' => $dominio['nombre'],
                'orden' => $dominio['orden'],
                'activo' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            $dominios
        );

        DB::table('inv_dominios')->upsert(
            $dominioRows,
            ['codigo'],
            ['nombre', 'orden', 'activo', 'updated_at']
        );

        $redRows = array_map(
            static fn (array $red): array => [
                'codigo' => $red['codigo'],
                'nombre' => $red['nombre'],
                'descripcion' => $red['descripcion'] ?? null,
                'activo' => $red['activo'] ?? 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            $redes
        );

        DB::table('inv_redes')->upsert(
            $redRows,
            ['codigo'],
            ['nombre', 'descripcion', 'activo', 'updated_at']
        );

        $relRows = [];
        foreach ($redDominios as $rel) {
            $redId = DB::table('inv_redes')->where('codigo', $rel['red'])->value('id');
            if (! $redId) {
                continue;
            }

            foreach ($rel['dominios'] as $dominioCodigo) {
                $dominioId = DB::table('inv_dominios')->where('codigo', $dominioCodigo)->value('id');
                if (! $dominioId) {
                    continue;
                }

                $relRows[] = [
                    'red_id' => $redId,
                    'dominio_id' => $dominioId,
                ];
            }
        }

        if ($relRows !== []) {
            DB::table('inv_red_dominios')->upsert(
                $relRows,
                ['red_id', 'dominio_id'],
                []
            );
        }
    }
}
