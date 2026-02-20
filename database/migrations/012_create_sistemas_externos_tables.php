<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sistemas_externos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 80)->unique();
            $table->string('nombre', 120);
            $table->string('patron_regex', 255)->nullable();
            $table->tinyInteger('activo')->default(1);
            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();

            $table->index('activo');
        });

        Schema::create('referencias_externas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('entrada_id');
            $table->unsignedBigInteger('sistema_externo_id');
            $table->string('externo_id', 120);
            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();

            $table->index('entrada_id');
            $table->index('sistema_externo_id');
            $table->unique(['entrada_id', 'sistema_externo_id', 'externo_id'], 'referencias_externas_unique');

            $table->foreign('entrada_id')
                ->references('id')
                ->on('entradas_bitacora')
                ->cascadeOnDelete();

            $table->foreign('sistema_externo_id')
                ->references('id')
                ->on('sistemas_externos')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referencias_externas');
        Schema::dropIfExists('sistemas_externos');
    }
};
