<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adjuntos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('entrada_id')->nullable();
            $table->unsignedBigInteger('usuario_id');

            $table->enum('tipo', ['imagen', 'archivo']);
            $table->string('nombre_original', 255);
            $table->string('mime_original', 120)->nullable();
            $table->unsignedBigInteger('tamano_bytes_original')->nullable();

            $table->string('extension_final', 10)->default('png');
            $table->string('mime_final', 120)->default('image/png');
            $table->unsignedBigInteger('tamano_bytes_final')->nullable();

            $table->integer('ancho')->nullable();
            $table->integer('alto')->nullable();

            $table->string('ruta', 255);
            $table->char('sha256', 64)->nullable();

            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();

            $table->index('entrada_id');
            $table->index(['usuario_id', 'created_at']);

            $table->foreign('entrada_id')
                ->references('id')
                ->on('entradas_bitacora')
                ->nullOnDelete();

            $table->foreign('usuario_id')
                ->references('id')
                ->on('usuarios')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adjuntos');
    }
};
