<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class ConfiguracionSistemaSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        foreach (['administrador', 'operador', 'invitado'] as $roleName) {
            Role::findOrCreate($roleName, 'web');
        }

        $admin = User::firstOrCreate(
            ['correo' => 'admin@bitacora.test'],
            [
                'nombre' => 'Administrador',
                'password' => Hash::make('12345678'),
                'activo' => 1,
                'estatus_actual' => 'Base',
                'rpe' => null,
                'rtt' => null,
            ]
        );
        $admin->assignRole('administrador');

        $operador = User::firstOrCreate(
            ['correo' => 'operador@bitacora.test'],
            [
                'nombre' => 'Operador',
                'password' => Hash::make('12345678'),
                'activo' => 1,
                'estatus_actual' => 'Base',
                'rpe' => null,
                'rtt' => null,
            ]
        );
        $operador->assignRole('operador');

        $rows = [
            [
                'clave' => 'app_nombre',
                'valor' => 'Bitácora COReFO B.C.',
                'tipo' => 'string',
                'descripcion' => 'Nombre visible de la aplicación',
            ],
            [
                'clave' => 'app_siglas',
                'valor' => 'Bitácora',
                'tipo' => 'string',
                'descripcion' => 'Siglas de la aplicación',
            ],
            [
                'clave' => 'timezone',
                'valor' => 'America/Tijuana',
                'tipo' => 'string',
                'descripcion' => 'Zona horaria por defecto',
            ],
            [
                'clave' => 'bitacora_publica',
                'valor' => '0',
                'tipo' => 'bool',
                'descripcion' => 'Si la bitácora es visible para usuarios no autenticados',
            ],
            [
                'clave' => 'max_adjuntos_por_entrada',
                'valor' => '15',
                'tipo' => 'int',
                'descripcion' => 'Límite de adjuntos por entrada',
            ],
            [
                'clave' => 'paginacion.default_per_page',
                'valor' => '20',
                'tipo' => 'int',
                'descripcion' => 'Paginación por defecto (registros por página)',
            ],
            [
                'clave' => 'enlaces.gtrbc_url',
                'valor' => '#',
                'tipo' => 'string',
                'descripcion' => 'URL de G.R.T.B.C.',
            ],
            [
                'clave' => 'enlaces.subger_comunicaciones_url',
                'valor' => '#',
                'tipo' => 'string',
                'descripcion' => 'URL de Subger. de Comunicaciones',
            ],
            [
                'clave' => 'enlaces.ayuda_url',
                'valor' => '#',
                'tipo' => 'string',
                'descripcion' => 'URL de ayuda en topbar',
            ],
            [
                'clave' => 'tema.modo',
                'valor' => 'dark',
                'tipo' => 'string',
                'descripcion' => 'Tema del portal: dark | light',
            ],
            [
                'clave' => 'imagenes.max_mb',
                'valor' => '2',
                'tipo' => 'int',
                'descripcion' => 'Tamaño máximo de imagen (MB)',
            ],
            [
                'clave' => 'imagenes.max_ancho',
                'valor' => '1920',
                'tipo' => 'int',
                'descripcion' => 'Ancho máximo de imagen en px',
            ],
            [
                'clave' => 'imagenes.max_alto',
                'valor' => '1080',
                'tipo' => 'int',
                'descripcion' => 'Alto máximo de imagen en px',
            ],
            [
                'clave' => 'imagenes.calidad_png',
                'valor' => '90',
                'tipo' => 'int',
                'descripcion' => 'Calidad/compresión al convertir a PNG',
            ],
            [
                'clave' => 'archivos.max_mb',
                'valor' => '5',
                'tipo' => 'int',
                'descripcion' => 'Tamaño máximo de adjunto (MB)',
            ],
            [
                'clave' => 'archivos.permitir_pdf',
                'valor' => '1',
                'tipo' => 'bool',
                'descripcion' => 'Permitir archivos PDF',
            ],
            [
                'clave' => 'archivos.permitir_doc',
                'valor' => '1',
                'tipo' => 'bool',
                'descripcion' => 'Permitir archivos DOC',
            ],
            [
                'clave' => 'archivos.permitir_docx',
                'valor' => '1',
                'tipo' => 'bool',
                'descripcion' => 'Permitir archivos DOCX',
            ],
            [
                'clave' => 'archivos.permitir_xls',
                'valor' => '1',
                'tipo' => 'bool',
                'descripcion' => 'Permitir archivos XLS',
            ],
            [
                'clave' => 'archivos.permitir_xlsx',
                'valor' => '1',
                'tipo' => 'bool',
                'descripcion' => 'Permitir archivos XLSX',
            ],
        ];

        $rows = array_map(static function (array $r) use ($now): array {
            $r['created_at'] = $now;
            $r['updated_at'] = null;
            return $r;
        }, $rows);

        DB::table('configuracion_sistema')->upsert(
            $rows,
            ['clave'],
            ['valor', 'tipo', 'descripcion', 'updated_at']
        );
    }
}
