<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inv_redes', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 40)->unique();
            $table->string('nombre', 120);
            $table->string('descripcion', 255)->nullable();
            $table->tinyInteger('activo')->default(1);
            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();

            $table->index('activo');
        });

        Schema::create('inv_dominios', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 40)->unique();
            $table->string('nombre', 120);
            $table->string('descripcion', 255)->nullable();
            $table->integer('orden')->nullable();
            $table->tinyInteger('activo')->default(1);
            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();

            $table->index('orden');
            $table->index('activo');
        });

        Schema::create('inv_red_dominios', function (Blueprint $table) {
            $table->unsignedBigInteger('red_id');
            $table->unsignedBigInteger('dominio_id');

            $table->primary(['red_id', 'dominio_id']);
            $table->index('dominio_id');

            $table->foreign('red_id')
                ->references('id')
                ->on('inv_redes')
                ->cascadeOnDelete();

            $table->foreign('dominio_id')
                ->references('id')
                ->on('inv_dominios')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_red_dominios');
        Schema::dropIfExists('inv_dominios');
        Schema::dropIfExists('inv_redes');
    }
};
