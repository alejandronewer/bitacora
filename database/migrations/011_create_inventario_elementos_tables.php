<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inv_elementos_redes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('red_id');

            $table->enum('tipo', ['nodo', 'enlace', 'servicio', 'tunel', 'trail']);
            $table->string('codigo', 120);
            $table->string('nombre', 200)->nullable();

            $table->enum('estado', ['activo', 'baja', 'planificado', 'desconocido'])->default('activo');
            $table->dateTime('fecha_alta')->nullable();
            $table->dateTime('fecha_baja')->nullable();
            $table->dateTime('updated_at_fuente')->nullable();

            $table->enum('origen', ['manual', 'csv', 'integracion'])->default('manual');
            $table->string('observaciones', 255)->nullable();

            $table->unsignedBigInteger('last_seen_importacion_id')->nullable();

            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();

            $table->unique(['red_id', 'tipo', 'codigo']);
            $table->index(['red_id', 'estado']);
            $table->index(['red_id', 'tipo']);
            $table->index('last_seen_importacion_id');

            $table->foreign('red_id')
                ->references('id')
                ->on('inv_redes')
                ->cascadeOnDelete();

            $table->foreign('last_seen_importacion_id')
                ->references('id')
                ->on('inv_importaciones_redes')
                ->nullOnDelete();
        });

        Schema::create('inv_detalle_nodos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('elemento_red_id')->unique();

            $table->unsignedBigInteger('ne_id')->nullable();
            $table->unsignedBigInteger('ne_dbid')->nullable();
            $table->string('dn_externo', 255)->nullable();
            $table->string('native_name', 180)->nullable();
            $table->string('user_label', 180)->nullable();
            $table->string('nombre_producto', 120)->nullable();
            $table->string('tipo_equipo', 120)->nullable();
            $table->string('version_me', 120)->nullable();
            $table->string('direccion_red', 120)->nullable();
            $table->string('nombre_grupo', 120)->nullable();

            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();

            $table->index('ne_dbid');
            $table->index('native_name');
            $table->index('direccion_red');

            $table->foreign('elemento_red_id')
                ->references('id')
                ->on('inv_elementos_redes')
                ->cascadeOnDelete();
        });

        Schema::create('inv_detalle_servicios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('elemento_red_id')->unique();

            $table->unsignedBigInteger('instancia_servicio_id')->nullable();
            $table->string('user_label', 180)->nullable();
            $table->string('cliente', 180)->nullable();
            $table->string('tipo_servicio', 80)->nullable();
            $table->string('ethvpn_id', 120)->nullable();

            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();

            $table->index('instancia_servicio_id');
            $table->index('tipo_servicio');
            $table->index('ethvpn_id');

            $table->foreign('elemento_red_id')
                ->references('id')
                ->on('inv_elementos_redes')
                ->cascadeOnDelete();
        });

        Schema::create('inv_detalle_enlaces', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('elemento_red_id')->unique();

            $table->unsignedBigInteger('instancia_enlace_id')->nullable();
            $table->string('motlink_label', 180)->nullable();
            $table->unsignedBigInteger('trail_id')->nullable();
            $table->unsignedBigInteger('nodo_a_ne_id')->nullable();
            $table->unsignedBigInteger('nodo_z_ne_id')->nullable();

            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();

            $table->index('instancia_enlace_id');
            $table->index('trail_id');
            $table->index('nodo_a_ne_id');
            $table->index('nodo_z_ne_id');

            $table->foreign('elemento_red_id')
                ->references('id')
                ->on('inv_elementos_redes')
                ->cascadeOnDelete();
        });

        Schema::create('inv_detalle_tuneles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('elemento_red_id')->unique();

            $table->unsignedBigInteger('instancia_tunel_id')->nullable();
            $table->string('user_label', 180)->nullable();
            $table->string('cliente', 180)->nullable();
            $table->string('tipo_tunel', 80)->nullable();
            $table->string('ethvpn_id', 120)->nullable();

            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();

            $table->index('instancia_tunel_id');
            $table->index('tipo_tunel');
            $table->index('ethvpn_id');

            $table->foreign('elemento_red_id')
                ->references('id')
                ->on('inv_elementos_redes')
                ->cascadeOnDelete();
        });

        Schema::create('inv_entrada_elementos_red', function (Blueprint $table) {
            $table->unsignedBigInteger('entrada_id');
            $table->unsignedBigInteger('elemento_red_id');

            $table->primary(['entrada_id', 'elemento_red_id']);
            $table->index('elemento_red_id');

            $table->foreign('entrada_id')
                ->references('id')
                ->on('entradas_bitacora')
                ->cascadeOnDelete();

            $table->foreign('elemento_red_id')
                ->references('id')
                ->on('inv_elementos_redes')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_entrada_elementos_red');
        Schema::dropIfExists('inv_detalle_tuneles');
        Schema::dropIfExists('inv_detalle_enlaces');
        Schema::dropIfExists('inv_detalle_servicios');
        Schema::dropIfExists('inv_detalle_nodos');
        Schema::dropIfExists('inv_elementos_redes');
    }
};
