<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cat_entrada_criterio', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 60)->unique();
            $table->string('nombre', 120);
            $table->string('descripcion', 255)->nullable();
            $table->integer('orden')->nullable();
            $table->tinyInteger('activo')->default(1);
            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();

            $table->index('activo');
            $table->index('orden');
        });

        Schema::create('cat_entrada_impacto', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 60)->unique();
            $table->string('nombre', 120);
            $table->string('descripcion', 255)->nullable();
            $table->integer('severidad')->nullable();
            $table->integer('orden')->nullable();
            $table->tinyInteger('activo')->default(1);
            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();

            $table->index('activo');
            $table->index('orden');
            $table->index('severidad');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cat_entrada_impacto');
        Schema::dropIfExists('cat_entrada_criterio');
    }
};
