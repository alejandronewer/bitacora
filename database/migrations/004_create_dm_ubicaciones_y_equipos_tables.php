<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dm_ubicaciones_tecnicas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 80)->unique();
            $table->string('nombre', 200);

            $table->char('nivel_1', 2);
            $table->char('nivel_2', 4)->nullable();
            $table->char('nivel_3', 2)->nullable();
            $table->char('nivel_4', 3)->nullable();
            $table->char('nivel_5', 2)->nullable();
            $table->char('nivel_6', 5)->nullable();
            $table->char('nivel_7', 3)->nullable();
            $table->char('nivel_8', 2)->nullable();

            $table->tinyInteger('activo')->default(1);
            $table->enum('fuente', ['Manual', 'Importacion'])->default('Manual');
            $table->dateTime('last_sync_at')->nullable();

            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();

            $table->index('nivel_1');
            $table->index(['nivel_1', 'nivel_2']);
            $table->index('nivel_3');
            $table->index('nivel_4');
            $table->index('nivel_5');
            $table->index('nivel_6');
            $table->index('nivel_7');
            $table->index('nivel_8');
            $table->index('activo');
        });

        Schema::create('dm_ubicacion_detalle_nivel', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('nivel');
            $table->string('codigo', 20);
            $table->string('nombre', 160)->nullable();
            $table->string('descripcion', 255)->nullable();
            $table->char('rama_nivel_3', 2)->default('--');
            $table->tinyInteger('activo')->default(1);
            $table->enum('origen', ['Detectado', 'Homologado'])->default('Homologado');
            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();

            $table->unique(['nivel', 'codigo', 'rama_nivel_3'], 'dm_ubic_det_nivel_unique');
            $table->index(['nivel', 'rama_nivel_3'], 'dm_ubic_det_nivel_rama_idx');
            $table->index('activo', 'dm_ubic_det_nivel_activo_idx');
        });

        Schema::create('dm_equipos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 80)->unique();
            $table->string('nombre', 200);
            $table->unsignedBigInteger('ubicacion_tecnica_id')->nullable();
            $table->string('area', 40)->nullable();
            $table->tinyInteger('activo')->default(1);
            $table->enum('fuente', ['Manual', 'Importacion'])->default('Manual');
            $table->dateTime('last_sync_at')->nullable();
            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();

            $table->index('ubicacion_tecnica_id');
            $table->index('area');
            $table->index('activo');

            $table->foreign('ubicacion_tecnica_id')
                ->references('id')
                ->on('dm_ubicaciones_tecnicas')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dm_equipos');
        Schema::dropIfExists('dm_ubicacion_detalle_nivel');
        Schema::dropIfExists('dm_ubicaciones_tecnicas');
    }
};
