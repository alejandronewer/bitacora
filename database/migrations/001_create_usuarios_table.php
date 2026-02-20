<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 200);
            $table->string('correo', 200)->unique();
            $table->string('password', 255);
            $table->tinyInteger('activo')->default(1);

            $table->string('rpe', 5)->nullable()->unique();
            $table->string('rtt', 5)->nullable()->unique();
            $table->enum('estatus_actual', ['Temporal', 'Base']);

            $table->dateTime('ultimo_acceso')->nullable();

            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();

            $table->index('activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
