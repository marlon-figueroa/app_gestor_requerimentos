<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bitacoras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('accion', 150);
            $table->text('descripcion')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->nullableMorphs('modelo');
            $table->string('modulo', 100)->nullable()->index();
            $table->string('tipo_accion', 30)->index();
            $table->string('metodo_http', 10)->nullable();
            $table->string('ruta')->nullable();
            $table->json('valores_anteriores')->nullable();
            $table->json('valores_nuevos')->nullable();
            $table->boolean('exitoso')->default(true)->index();
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->text('error_mensaje')->nullable();
            $table->unsignedInteger('tiempo_ejecucion')->nullable()->comment('Milisegundos');
            $table->string('sesion_id', 100)->nullable();
            $table->string('navegador', 50)->nullable();
            $table->string('sistema_operativo', 50)->nullable();
            $table->string('dispositivo', 20)->nullable();
            $table->json('datos_adicionales')->nullable();
            $table->string('tags')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bitacoras');
    }
};
