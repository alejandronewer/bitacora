<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion_sistema', function (Blueprint $table) {
            $table->id();
            $table->string('clave', 120)->unique();
            $table->string('valor', 255);
            $table->enum('tipo', ['int', 'bool', 'string'])->default('string');
            $table->string('descripcion', 255)->nullable();
            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('configuracion_usuario', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id');
            $table->string('clave', 120);
            $table->string('valor', 255);
            $table->enum('tipo', ['int', 'bool', 'string'])->default('string');
            $table->string('descripcion', 255)->nullable();
            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();

            $table->unique(['usuario_id', 'clave']);
            $table->index('usuario_id');

            $table->foreign('usuario_id')
                ->references('id')
                ->on('usuarios')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_usuario');
        Schema::dropIfExists('configuracion_sistema');
    }
};
