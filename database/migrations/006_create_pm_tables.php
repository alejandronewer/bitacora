<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pm_clase_orden', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 120)->unique();
            $table->string('descripcion', 255)->nullable();
            $table->tinyInteger('activo')->default(1);
            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();

            $table->index('activo');
        });

        Schema::create('pm_clase_actividad', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 120)->unique();
            $table->string('descripcion', 255)->nullable();
            $table->tinyInteger('activo')->default(1);
            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();

            $table->index('activo');
        });

        Schema::create('pm_matriz_orden_actividad', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pm_clase_orden_id');
            $table->unsignedBigInteger('pm_clase_actividad_id');
            $table->tinyInteger('activo')->default(1);
            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();

            $table->unique(
                ['pm_clase_orden_id', 'pm_clase_actividad_id'],
                'pm_matriz_orden_actividad_uq'
            );
            $table->index('activo');

            $table->foreign('pm_clase_orden_id')
                ->references('id')
                ->on('pm_clase_orden')
                ->cascadeOnDelete();

            $table->foreign('pm_clase_actividad_id')
                ->references('id')
                ->on('pm_clase_actividad')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pm_matriz_orden_actividad');
        Schema::dropIfExists('pm_clase_actividad');
        Schema::dropIfExists('pm_clase_orden');
    }
};
