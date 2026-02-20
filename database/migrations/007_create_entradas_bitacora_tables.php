<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entradas_bitacora', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 255);
            $table->longText('cuerpo_html');
            $table->longText('cuerpo_texto');
            $table->longText('resumen_tecnico')->nullable();

            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin')->nullable();

            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('ubicacion_tecnica_id')->nullable();
            $table->unsignedBigInteger('equipo_id')->nullable();

            $table->string('ubicacion_manual', 255)->nullable();
            $table->string('equipo_manual', 255)->nullable();

            $table->unsignedBigInteger('entrada_criterio_id')->nullable();
            $table->unsignedBigInteger('entrada_impacto_id')->nullable();

            $table->unsignedBigInteger('pm_clase_orden_id')->nullable();
            $table->unsignedBigInteger('pm_clase_actividad_id')->nullable();

            $table->tinyInteger('publicado')->default(0);
            $table->dateTime('publicado_at')->nullable();

            $table->enum('tipo_registro', ['operativo', 'inventario'])->default('operativo');
            $table->enum('accion_inventario', ['alta', 'baja', 'cambio'])->nullable();

            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();

            $table->index(['publicado', 'fecha_inicio']);
            $table->index('fecha_inicio');
            $table->index(['usuario_id', 'fecha_inicio']);
            $table->index(['ubicacion_tecnica_id', 'fecha_inicio']);
            $table->index(['equipo_id', 'fecha_inicio']);
            $table->index(['entrada_criterio_id', 'fecha_inicio']);
            $table->index(['entrada_impacto_id', 'fecha_inicio']);
            $table->index(['pm_clase_orden_id', 'fecha_inicio']);
            $table->index(['pm_clase_actividad_id', 'fecha_inicio']);

            $table->foreign('usuario_id')
                ->references('id')
                ->on('usuarios')
                ->restrictOnDelete();

            $table->foreign('ubicacion_tecnica_id')
                ->references('id')
                ->on('dm_ubicaciones_tecnicas')
                ->nullOnDelete();

            $table->foreign('equipo_id')
                ->references('id')
                ->on('dm_equipos')
                ->nullOnDelete();

            $table->foreign('entrada_criterio_id')
                ->references('id')
                ->on('cat_entrada_criterio')
                ->nullOnDelete();

            $table->foreign('entrada_impacto_id')
                ->references('id')
                ->on('cat_entrada_impacto')
                ->nullOnDelete();

            $table->foreign('pm_clase_orden_id')
                ->references('id')
                ->on('pm_clase_orden')
                ->nullOnDelete();

            $table->foreign('pm_clase_actividad_id')
                ->references('id')
                ->on('pm_clase_actividad')
                ->nullOnDelete();
        });

        Schema::create('entrada_ubicaciones', function (Blueprint $table) {
            $table->unsignedBigInteger('entrada_id');
            $table->unsignedBigInteger('ubicacion_tecnica_id');

            $table->primary(['entrada_id', 'ubicacion_tecnica_id']);
            $table->index('ubicacion_tecnica_id');

            $table->foreign('entrada_id')
                ->references('id')
                ->on('entradas_bitacora')
                ->cascadeOnDelete();

            $table->foreign('ubicacion_tecnica_id')
                ->references('id')
                ->on('dm_ubicaciones_tecnicas')
                ->cascadeOnDelete();
        });

        Schema::create('entrada_equipos', function (Blueprint $table) {
            $table->unsignedBigInteger('entrada_id');
            $table->unsignedBigInteger('equipo_id');

            $table->primary(['entrada_id', 'equipo_id']);
            $table->index('equipo_id');

            $table->foreign('entrada_id')
                ->references('id')
                ->on('entradas_bitacora')
                ->cascadeOnDelete();

            $table->foreign('equipo_id')
                ->references('id')
                ->on('dm_equipos')
                ->cascadeOnDelete();
        });

        Schema::create('entrada_eventos_detectados', function (Blueprint $table) {
            $table->unsignedBigInteger('entrada_id')->primary();
            $table->enum('tipo_evento', ['FALLA', 'ANOMALIA']);
            $table->string('detalle', 255)->nullable();
            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();

            $table->index('tipo_evento');

            $table->foreign('entrada_id')
                ->references('id')
                ->on('entradas_bitacora')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entrada_eventos_detectados');
        Schema::dropIfExists('entrada_equipos');
        Schema::dropIfExists('entrada_ubicaciones');
        Schema::dropIfExists('entradas_bitacora');
    }
};
