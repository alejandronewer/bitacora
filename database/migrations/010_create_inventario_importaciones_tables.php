<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inv_import_reglas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('red_id');
            $table->string('nombre', 120);
            $table->enum('tipo_elemento', ['nodo', 'enlace', 'servicio', 'tunel']);
            $table->string('tabla_destino', 80)->default('inv_elementos_redes');
            $table->string('archivo_patron', 255)->nullable();
            $table->char('delimitador', 1)->default(',');
            $table->boolean('usa_comillas')->default(true);
            $table->boolean('tiene_encabezado')->default(false);
            $table->string('encoding', 40)->default('utf-8');
            $table->boolean('activo')->default(true);
            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();

            $table->unique(['red_id', 'nombre']);
            $table->index(['red_id', 'activo']);

            $table->foreign('red_id')
                ->references('id')
                ->on('inv_redes')
                ->cascadeOnDelete();
        });

        Schema::create('inv_importaciones_redes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('red_id');
            $table->unsignedBigInteger('regla_id')->nullable();
            $table->unsignedBigInteger('usuario_id');

            $table->string('archivo_nombre', 255);
            $table->string('fuente', 255)->nullable();
            $table->char('hash_archivo', 64)->nullable();

            $table->enum('estado', ['procesando', 'completado', 'fallido'])->default('procesando');

            $table->integer('total_registros')->nullable();
            $table->integer('procesados')->nullable();
            $table->integer('creados')->nullable();
            $table->integer('actualizados')->nullable();
            $table->integer('marcados_baja')->nullable();

            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();

            $table->index(['red_id', 'created_at']);
            $table->index(['regla_id', 'created_at']);
            $table->index(['usuario_id', 'created_at']);

            $table->foreign('red_id')
                ->references('id')
                ->on('inv_redes')
                ->cascadeOnDelete();

            $table->foreign('regla_id')
                ->references('id')
                ->on('inv_import_reglas')
                ->nullOnDelete();

            $table->foreign('usuario_id')
                ->references('id')
                ->on('usuarios')
                ->restrictOnDelete();
        });

        Schema::create('inv_import_regla_campos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('regla_id');
            $table->string('columna_fuente', 120);
            $table->string('campo_destino', 120);
            $table->string('transformacion', 80)->nullable();
            $table->string('por_defecto', 255)->nullable();
            $table->boolean('es_clave_upsert')->default(false);
            $table->unsignedSmallInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();

            $table->unique(['regla_id', 'columna_fuente', 'campo_destino'], 'inv_imp_regla_campo_unique');
            $table->index(['regla_id', 'activo', 'orden'], 'inv_imp_regla_campo_idx');

            $table->foreign('regla_id')
                ->references('id')
                ->on('inv_import_reglas')
                ->cascadeOnDelete();
        });

        Schema::create('inv_import_errores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('importacion_id');
            $table->unsignedInteger('fila_numero')->nullable();
            $table->string('campo', 120)->nullable();
            $table->string('valor', 255)->nullable();
            $table->string('mensaje', 255);
            $table->timestamp('created_at');

            $table->index(['importacion_id', 'fila_numero'], 'inv_imp_error_idx');

            $table->foreign('importacion_id')
                ->references('id')
                ->on('inv_importaciones_redes')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_import_errores');
        Schema::dropIfExists('inv_import_regla_campos');
        Schema::dropIfExists('inv_importaciones_redes');
        Schema::dropIfExists('inv_import_reglas');
    }
};
