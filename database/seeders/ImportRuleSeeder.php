<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImportRuleSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $now = now();
            $this->seedRibbonRules($now);
            $this->seedTxcareRules('xtran_ztc_ztv', 'TXCare Xtran ZTC/ZTV', $now);
            $this->seedTxcareRules('xtran_zts', 'TXCare Xtran ZTS', $now);
        });
    }

    private function seedRibbonRules(\DateTimeInterface $now): void
    {
        $redId = $this->findRedId('ribbon', ['Ribbon LightSoft', 'Ribbon LightSOFT']);
        if (! $redId) {
            $this->command?->warn('No se encontró la red Ribbon. Omitiendo seeder de reglas "Nodos Ribbon", "Enlaces Ribbon", "Servicios Ribbon" y "Tuneles Ribbon".');
            return;
        }

        $reglaNodoId = $this->upsertRegla(
            redId: $redId,
            nombre: 'Nodos Ribbon',
            data: [
                'tipo_elemento' => 'nodo',
                'tabla_destino' => 'inv_elementos_redes',
                'archivo_patron' => 'me.dat',
                'delimitador' => ',',
                'usa_comillas' => 1,
                'tiene_encabezado' => 1,
                'encoding' => 'utf-8',
                'activo' => 1,
            ],
            now: $now
        );

        $camposNodo = [
            ['columna_fuente' => 'NE_DBID', 'campo_destino' => 'codigo', 'transformacion' => 'trim|upper|to_int', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 10, 'activo' => 1],
            ['columna_fuente' => 'NE_M_USERLABEL', 'campo_destino' => 'nombre', 'transformacion' => 'trim|upper', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 20, 'activo' => 1],
            ['columna_fuente' => 'M_COMMENT', 'campo_destino' => 'observaciones', 'transformacion' => 'trim|upper', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 40, 'activo' => 1],
            ['columna_fuente' => 'NE_DBID', 'campo_destino' => 'ne_id', 'transformacion' => 'trim|upper|to_int', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 10, 'activo' => 1],
            ['columna_fuente' => 'NE_DBID', 'campo_destino' => 'ne_dbid', 'transformacion' => 'trim|upper|to_int', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 60, 'activo' => 1],
            ['columna_fuente' => 'NE_M_DN', 'campo_destino' => 'dn_externo', 'transformacion' => 'trim|upper', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 70, 'activo' => 1],
            ['columna_fuente' => 'M_NATIVENAME', 'campo_destino' => 'native_name', 'transformacion' => 'trim|upper', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 80, 'activo' => 1],
            ['columna_fuente' => 'NE_M_USERLABEL', 'campo_destino' => 'user_label', 'transformacion' => 'trim|upper', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 90, 'activo' => 1],
            ['columna_fuente' => 'M_PRODUCTNAME', 'campo_destino' => 'nombre_producto', 'transformacion' => 'trim|upper', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 100, 'activo' => 1],
            ['columna_fuente' => 'T_ORANETYPE_NAME', 'campo_destino' => 'tipo_equipo', 'transformacion' => 'trim|upper', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 110, 'activo' => 1],
            ['columna_fuente' => 'M_VERSION', 'campo_destino' => 'version_me', 'transformacion' => 'trim|upper', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 120, 'activo' => 1],
            ['columna_fuente' => 'M_NETWORKADDRESS', 'campo_destino' => 'direccion_red', 'transformacion' => 'trim|upper', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 130, 'activo' => 1],
            ['columna_fuente' => 'M_USERLABEL', 'campo_destino' => 'nombre_grupo', 'transformacion' => 'trim', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 140, 'activo' => 1],
        ];
        $this->syncCampos($reglaNodoId, $camposNodo, $now);

        $reglaEnlaceId = $this->upsertRegla(
            redId: $redId,
            nombre: 'Enlaces Ribbon',
            data: [
                'tipo_elemento' => 'enlace',
                'tabla_destino' => 'inv_elementos_redes',
                'archivo_patron' => 'motlinks.dat',
                'delimitador' => ',',
                'usa_comillas' => 1,
                'tiene_encabezado' => 0,
                'encoding' => 'utf-8',
                'activo' => 1,
            ],
            now: $now
        );

        $camposEnlace = [
            ['columna_fuente' => '1', 'campo_destino' => 'codigo', 'transformacion' => 'trim|to_int', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 10, 'activo' => 1],
            ['columna_fuente' => '2', 'campo_destino' => 'nombre', 'transformacion' => 'trim', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 20, 'activo' => 1],
            ['columna_fuente' => '', 'campo_destino' => 'observaciones', 'transformacion' => 'trim', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 30, 'activo' => 1],
            ['columna_fuente' => '1', 'campo_destino' => 'instancia_enlace_id', 'transformacion' => 'trim|to_int', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 40, 'activo' => 1],
            ['columna_fuente' => '2', 'campo_destino' => 'motlink_label', 'transformacion' => 'trim', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 50, 'activo' => 1],
            ['columna_fuente' => '', 'campo_destino' => 'trail_id', 'transformacion' => 'trim|to_int', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 60, 'activo' => 1],
            ['columna_fuente' => '', 'campo_destino' => 'nodo_a_ne_id', 'transformacion' => 'trim|to_int', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 70, 'activo' => 1],
            ['columna_fuente' => '', 'campo_destino' => 'nodo_z_ne_id', 'transformacion' => 'trim|to_int', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 80, 'activo' => 1],
        ];
        $this->syncCampos($reglaEnlaceId, $camposEnlace, $now);

        $reglaServicioId = $this->upsertRegla(
            redId: $redId,
            nombre: 'Servicios Ribbon',
            data: [
                'tipo_elemento' => 'servicio',
                'tabla_destino' => 'inv_elementos_redes',
                'archivo_patron' => 'serviceflow.dat',
                'delimitador' => ',',
                'usa_comillas' => 1,
                'tiene_encabezado' => 0,
                'encoding' => 'utf-8',
                'activo' => 1,
            ],
            now: $now
        );

        $camposServicio = [
            ['columna_fuente' => '1', 'campo_destino' => 'codigo', 'transformacion' => 'trim|to_int', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 10, 'activo' => 1],
            ['columna_fuente' => '2', 'campo_destino' => 'nombre', 'transformacion' => 'trim', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 20, 'activo' => 1],
            ['columna_fuente' => '', 'campo_destino' => 'observaciones', 'transformacion' => 'trim', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 30, 'activo' => 1],
            ['columna_fuente' => '1', 'campo_destino' => 'instancia_servicio_id', 'transformacion' => 'trim|to_int', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 40, 'activo' => 1],
            ['columna_fuente' => '2', 'campo_destino' => 'user_label', 'transformacion' => 'trim', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 50, 'activo' => 1],
            ['columna_fuente' => '3', 'campo_destino' => 'cliente', 'transformacion' => 'trim', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 60, 'activo' => 1],
            ['columna_fuente' => '5', 'campo_destino' => 'tipo_servicio', 'transformacion' => 'trim', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 70, 'activo' => 1],
            ['columna_fuente' => '8', 'campo_destino' => 'ethvpn_id', 'transformacion' => 'trim', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 80, 'activo' => 1],
        ];
        $this->syncCampos($reglaServicioId, $camposServicio, $now);

        $reglaTunelId = $this->upsertRegla(
            redId: $redId,
            nombre: 'Tuneles Ribbon',
            data: [
                'tipo_elemento' => 'tunel',
                'tabla_destino' => 'inv_elementos_redes',
                'archivo_patron' => 'wrkmplstunnel.dat',
                'delimitador' => ',',
                'usa_comillas' => 1,
                'tiene_encabezado' => 0,
                'encoding' => 'utf-8',
                'activo' => 1,
            ],
            now: $now
        );

        $camposTunel = [
            ['columna_fuente' => '1', 'campo_destino' => 'codigo', 'transformacion' => 'trim|to_int', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 10, 'activo' => 1],
            ['columna_fuente' => '4', 'campo_destino' => 'nombre', 'transformacion' => 'trim', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 20, 'activo' => 1],
            ['columna_fuente' => '', 'campo_destino' => 'observaciones', 'transformacion' => 'trim', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 30, 'activo' => 1],
            ['columna_fuente' => '1', 'campo_destino' => 'instancia_tunel_id', 'transformacion' => 'trim|to_int', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 40, 'activo' => 1],
            ['columna_fuente' => '4', 'campo_destino' => 'user_label', 'transformacion' => 'trim', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 50, 'activo' => 1],
            ['columna_fuente' => '19', 'campo_destino' => 'cliente', 'transformacion' => 'trim', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 60, 'activo' => 1],
            ['columna_fuente' => '6', 'campo_destino' => 'tipo_tunel', 'transformacion' => 'trim', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 70, 'activo' => 1],
            ['columna_fuente' => '', 'campo_destino' => 'ethvpn_id', 'transformacion' => 'trim', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 80, 'activo' => 1],
        ];
        $this->syncCampos($reglaTunelId, $camposTunel, $now);
    }

    private function seedTxcareRules(string $redCodigo, string $redNombre, \DateTimeInterface $now): void
    {
        $redId = $this->findRedId($redCodigo);
        if (! $redId) {
            $this->command?->warn('No se encontró la red "' . $redCodigo . '". Omitiendo seeders de reglas TXCare.');
            return;
        }

        $reglaNodoId = $this->upsertRegla(
            redId: $redId,
            nombre: 'Nodos ' . $redNombre,
            data: [
                'tipo_elemento' => 'nodo',
                'tabla_destino' => 'inv_elementos_redes',
                'archivo_patron' => '*nodos_txc*.csv',
                'delimitador' => ',',
                'usa_comillas' => 1,
                'tiene_encabezado' => 1,
                'encoding' => 'utf-8',
                'activo' => 1,
            ],
            now: $now
        );

        $camposNodo = [
            ['columna_fuente' => 'txc_xtranid', 'campo_destino' => 'codigo', 'transformacion' => 'trim|to_int', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 10, 'activo' => 1],
            ['columna_fuente' => 'txc_nename', 'campo_destino' => 'nombre', 'transformacion' => 'trim', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 20, 'activo' => 1],
            ['columna_fuente' => 'txc_ne_type', 'campo_destino' => 'observaciones', 'transformacion' => 'trim', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 30, 'activo' => 1],
            ['columna_fuente' => 'txc_xtranid', 'campo_destino' => 'ne_id', 'transformacion' => 'trim|to_int', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 40, 'activo' => 1],
            ['columna_fuente' => 'txc_dbid', 'campo_destino' => 'ne_dbid', 'transformacion' => 'trim|to_int', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 50, 'activo' => 1],
            ['columna_fuente' => 'txc_nename', 'campo_destino' => 'native_name', 'transformacion' => 'trim', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 60, 'activo' => 1],
            ['columna_fuente' => 'txc_nename', 'campo_destino' => 'user_label', 'transformacion' => 'trim', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 70, 'activo' => 1],
            ['columna_fuente' => 'txc_ne_type', 'campo_destino' => 'tipo_equipo', 'transformacion' => 'trim', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 80, 'activo' => 1],
            ['columna_fuente' => 'txc_ne_ip', 'campo_destino' => 'direccion_red', 'transformacion' => 'trim', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 90, 'activo' => 1],
        ];
        $this->syncCampos($reglaNodoId, $camposNodo, $now);

        $reglaEnlaceId = $this->upsertRegla(
            redId: $redId,
            nombre: 'Enlaces ' . $redNombre,
            data: [
                'tipo_elemento' => 'enlace',
                'tabla_destino' => 'inv_elementos_redes',
                'archivo_patron' => '*enlaces_txc*.csv',
                'delimitador' => ',',
                'usa_comillas' => 1,
                'tiene_encabezado' => 1,
                'encoding' => 'utf-8',
                'activo' => 1,
            ],
            now: $now
        );

        $camposEnlace = [
            ['columna_fuente' => 'txc_link_id', 'campo_destino' => 'codigo', 'transformacion' => 'trim|to_int', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 10, 'activo' => 1],
            ['columna_fuente' => 'txc_link_name', 'campo_destino' => 'nombre', 'transformacion' => 'trim', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 20, 'activo' => 1],
            ['columna_fuente' => 'txc_link_type', 'campo_destino' => 'observaciones', 'transformacion' => 'trim', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 30, 'activo' => 1],
            ['columna_fuente' => 'txc_link_id', 'campo_destino' => 'instancia_enlace_id', 'transformacion' => 'trim|to_int', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 40, 'activo' => 1],
            ['columna_fuente' => 'txc_link_name', 'campo_destino' => 'motlink_label', 'transformacion' => 'trim', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 50, 'activo' => 1],
            ['columna_fuente' => '', 'campo_destino' => 'trail_id', 'transformacion' => 'trim|to_int', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 60, 'activo' => 1],
            ['columna_fuente' => 'txc_neid_a', 'campo_destino' => 'nodo_a_ne_id', 'transformacion' => 'trim|to_int', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 70, 'activo' => 1],
            ['columna_fuente' => 'txc_neid_z', 'campo_destino' => 'nodo_z_ne_id', 'transformacion' => 'trim|to_int', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 80, 'activo' => 1],
        ];
        $this->syncCampos($reglaEnlaceId, $camposEnlace, $now);

        $reglaServicioId = $this->upsertRegla(
            redId: $redId,
            nombre: 'Servicios ' . $redNombre,
            data: [
                'tipo_elemento' => 'servicio',
                'tabla_destino' => 'inv_elementos_redes',
                'archivo_patron' => '*servicios_txc*.csv',
                'delimitador' => ',',
                'usa_comillas' => 1,
                'tiene_encabezado' => 1,
                'encoding' => 'utf-8',
                'activo' => 1,
            ],
            now: $now
        );

        $camposServicio = [
            ['columna_fuente' => 'txc_service_id', 'campo_destino' => 'codigo', 'transformacion' => 'trim|to_int', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 10, 'activo' => 1],
            ['columna_fuente' => 'txc_service_name', 'campo_destino' => 'nombre', 'transformacion' => 'trim', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 20, 'activo' => 1],
            ['columna_fuente' => 'txc_service_type', 'campo_destino' => 'observaciones', 'transformacion' => 'trim', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 30, 'activo' => 1],
            ['columna_fuente' => 'txc_service_id', 'campo_destino' => 'instancia_servicio_id', 'transformacion' => 'trim|to_int', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 40, 'activo' => 1],
            ['columna_fuente' => 'txc_service_user_label', 'campo_destino' => 'user_label', 'transformacion' => 'trim', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 50, 'activo' => 1],
            ['columna_fuente' => 'txc_service_type', 'campo_destino' => 'tipo_servicio', 'transformacion' => 'trim', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 60, 'activo' => 1],
            ['columna_fuente' => '', 'campo_destino' => 'cliente', 'transformacion' => 'trim', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 70, 'activo' => 1],
            ['columna_fuente' => '', 'campo_destino' => 'ethvpn_id', 'transformacion' => 'trim', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 80, 'activo' => 1],
        ];
        $this->syncCampos($reglaServicioId, $camposServicio, $now);

        $reglaTunelId = $this->upsertRegla(
            redId: $redId,
            nombre: 'Túneles ' . $redNombre,
            data: [
                'tipo_elemento' => 'tunel',
                'tabla_destino' => 'inv_elementos_redes',
                'archivo_patron' => '*tuneles_txc*.csv',
                'delimitador' => ',',
                'usa_comillas' => 1,
                'tiene_encabezado' => 1,
                'encoding' => 'utf-8',
                'activo' => 1,
            ],
            now: $now
        );

        $camposTunel = [
            ['columna_fuente' => 'txc_tunnel_id', 'campo_destino' => 'codigo', 'transformacion' => 'trim|to_int', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 10, 'activo' => 1],
            ['columna_fuente' => 'txc_tunnel_name', 'campo_destino' => 'nombre', 'transformacion' => 'trim', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 20, 'activo' => 1],
            ['columna_fuente' => 'txc_topology_type', 'campo_destino' => 'observaciones', 'transformacion' => 'trim', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 30, 'activo' => 1],
            ['columna_fuente' => 'txc_tunnel_id', 'campo_destino' => 'instancia_tunel_id', 'transformacion' => 'trim|to_int', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 40, 'activo' => 1],
            ['columna_fuente' => 'txc_tunnel_user_label', 'campo_destino' => 'user_label', 'transformacion' => 'trim', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 50, 'activo' => 1],
            ['columna_fuente' => 'txc_topology_type', 'campo_destino' => 'tipo_tunel', 'transformacion' => 'trim', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 60, 'activo' => 1],
            ['columna_fuente' => '', 'campo_destino' => 'cliente', 'transformacion' => 'trim', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 70, 'activo' => 1],
            ['columna_fuente' => '', 'campo_destino' => 'ethvpn_id', 'transformacion' => 'trim', 'por_defecto' => null, 'es_clave_upsert' => 0, 'orden' => 80, 'activo' => 1],
        ];
        $this->syncCampos($reglaTunelId, $camposTunel, $now);
    }

    private function findRedId(string $codigo, array $fallbackNombres = []): ?int
    {
        $redId = DB::table('inv_redes')->where('codigo', $codigo)->value('id');
        if ($redId) {
            return (int) $redId;
        }

        if ($fallbackNombres !== []) {
            $redId = DB::table('inv_redes')
                ->whereIn('nombre', $fallbackNombres)
                ->value('id');
            if ($redId) {
                return (int) $redId;
            }
        }

        return null;
    }

    private function upsertRegla(int $redId, string $nombre, array $data, \DateTimeInterface $now): int
    {
        $regla = DB::table('inv_import_reglas')
            ->where('red_id', $redId)
            ->where('nombre', $nombre)
            ->first();

        if ($regla) {
            DB::table('inv_import_reglas')
                ->where('id', $regla->id)
                ->update(array_merge($data, ['updated_at' => $now]));

            return (int) $regla->id;
        }

        return (int) DB::table('inv_import_reglas')->insertGetId(array_merge(
            [
                'red_id' => $redId,
                'nombre' => $nombre,
            ],
            $data,
            [
                'created_at' => $now,
                'updated_at' => null,
            ]
        ));
    }

    private function syncCampos(int $reglaId, array $campos, \DateTimeInterface $now): void
    {
        DB::table('inv_import_regla_campos')->where('regla_id', $reglaId)->delete();

        $rows = array_map(function (array $campo) use ($reglaId, $now) {
            return array_merge($campo, [
                'regla_id' => $reglaId,
                'created_at' => $now,
                'updated_at' => null,
            ]);
        }, $campos);

        DB::table('inv_import_regla_campos')->insert($rows);
    }
}
