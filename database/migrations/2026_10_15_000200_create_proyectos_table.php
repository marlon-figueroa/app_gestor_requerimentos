<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proyectos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->nullable()->unique();
            $table->string('nombre');
            $table->text('descripcion');
            $table->foreignId('responsable_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->date('fecha_finalizacion')->index();
            $table->string('estado', 20)->default('activo')->index();
            $table->timestamp('finalizado_at')->nullable();
            $table->timestamp('cancelado_at')->nullable();
            $table->timestamps();
        });

        Schema::create('proyecto_adjuntos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->constrained('proyectos')->cascadeOnDelete();
            $table->string('nombre_original');
            $table->string('ruta');
            $table->string('mime', 100);
            $table->unsignedBigInteger('tamano');
            $table->foreignId('subido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('proyecto_revisiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->constrained('proyectos')->cascadeOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('accion', 20);
            $table->string('estado_anterior', 20)->nullable();
            $table->string('estado_nuevo', 20);
            $table->text('observaciones')->nullable();
            $table->timestamp('created_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proyecto_revisiones');
        Schema::dropIfExists('proyecto_adjuntos');
        Schema::dropIfExists('proyectos');
    }
};
