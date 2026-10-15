<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proyecto_evaluaciones_pf', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->constrained('proyectos')->cascadeOnDelete();
            $table->string('nombre');
            $table->text('notas')->nullable();
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('total_sin_ajuste')->default(0);
            $table->unsignedSmallInteger('suma_influencia')->default(0);
            $table->decimal('factor_ajuste', 4, 2)->default(0.65);
            $table->decimal('total_ajustado', 10, 2)->default(0);
            $table->json('caracteristicas');
            $table->timestamps();

            $table->index('proyecto_id');
        });

        Schema::create('proyecto_evaluacion_componentes_pf', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluacion_id')->constrained('proyecto_evaluaciones_pf')->cascadeOnDelete();
            $table->string('tipo', 8);
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->unsignedSmallInteger('det')->default(0);
            $table->unsignedSmallInteger('referencias')->default(0);
            $table->string('complejidad', 8);
            $table->unsignedSmallInteger('puntos');
            $table->unsignedSmallInteger('orden')->default(1);
            $table->timestamps();

            $table->index(['evaluacion_id', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proyecto_evaluacion_componentes_pf');
        Schema::dropIfExists('proyecto_evaluaciones_pf');
    }
};
