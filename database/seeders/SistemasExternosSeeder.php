<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SistemasExternosSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $rows = [
            ['codigo' => 'EVENTOS_RNFO', 'nombre' => 'Eventos RNFO', 'patron_regex' => '^\\d{1,12}$'],
            ['codigo' => 'INCIDENTES_CLIENTES', 'nombre' => 'Incidentes Clientes', 'patron_regex' => '^\\d{1,12}$'],
            ['codigo' => 'CONQUEST', 'nombre' => 'ConQuest NCI/NCR', 'patron_regex' => '^(?:NCI|NCR)-\\d{1,12}$'],
            ['codigo' => 'SOLICITUD_COMUNICACIONES', 'nombre' => 'Solicitud COReFO/CONaFO', 'patron_regex' => '^\\d{8}-\\d{4}$'],
            ['codigo' => 'LICENCIA_COMUNICACIONES', 'nombre' => 'Licencia COReFO/CONaFO', 'patron_regex' => '^\\d{8}-\\d{4}$'],
            ['codigo' => 'SOLICITUD_ZOT', 'nombre' => 'Solicitud Zona de Operación de Transmisión', 'patron_regex' => '^\\d{8}-\\d{4}$'],
            ['codigo' => 'LICENCIA_ZOT', 'nombre' => 'Licencia Zona de Operación de Transmisión', 'patron_regex' => '^\\d{8}-\\d{4}$'],
            ['codigo' => 'SOLICITUD_CCD', 'nombre' => 'Solicitud Centro de Control de Distribución', 'patron_regex' => '^\\d{8}-\\d{4}$'],
            ['codigo' => 'LICENCIA_CCD', 'nombre' => 'Licencia Centro de Control de Distribución', 'patron_regex' => '^\\d{8}-\\d{4}$'],
        ];

        $payload = array_map(
            static fn (array $row): array => [
                'codigo' => $row['codigo'],
                'nombre' => $row['nombre'],
                'patron_regex' => $row['patron_regex'],
                'activo' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            $rows
        );

        DB::table('sistemas_externos')->upsert(
            $payload,
            ['codigo'],
            ['nombre', 'patron_regex', 'activo', 'updated_at']
        );
    }
}
