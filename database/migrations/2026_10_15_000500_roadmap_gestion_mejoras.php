<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::table('proyectos', function (Blueprint $table) {
            $table->date('fecha_inicio')->nullable()->after('descripcion');
        });

        Schema::create('proyecto_miembros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->constrained('proyectos')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('rol', 40)->default('colaborador');
            $table->timestamps();

            $table->unique(['proyecto_id', 'user_id']);
        });

        Schema::table('requerimientos', function (Blueprint $table) {
            $table->string('prioridad', 20)->default('media')->after('estado');
        });

        Schema::create('requerimiento_comentarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requerimiento_id')->constrained('requerimientos')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
            $table->text('cuerpo');
            $table->timestamps();
        });

        Schema::table('proyecto_evaluaciones_pf', function (Blueprint $table) {
            $table->string('estado_evaluacion', 20)->default('borrador')->after('notas');
            $table->boolean('es_baseline')->default(false)->after('estado_evaluacion');
            $table->string('viabilidad_diagnostico', 20)->nullable()->after('total_ajustado');
            $table->unsignedTinyInteger('puntaje_diagnostico')->nullable()->after('viabilidad_diagnostico');
            $table->json('diagnostico_snapshot')->nullable()->after('puntaje_diagnostico');
        });

        Schema::table('proyecto_evaluacion_componentes_pf', function (Blueprint $table) {
            $table->foreignId('requerimiento_id')->nullable()->after('evaluacion_id')
                ->constrained('requerimientos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('proyecto_evaluacion_componentes_pf', function (Blueprint $table) {
            $table->dropConstrainedForeignId('requerimiento_id');
        });

        Schema::table('proyecto_evaluaciones_pf', function (Blueprint $table) {
            $table->dropColumn([
                'estado_evaluacion',
                'es_baseline',
                'viabilidad_diagnostico',
                'puntaje_diagnostico',
                'diagnostico_snapshot',
            ]);
        });

        Schema::dropIfExists('requerimiento_comentarios');

        Schema::table('requerimientos', function (Blueprint $table) {
            $table->dropColumn('prioridad');
        });

        Schema::dropIfExists('proyecto_miembros');

        Schema::table('proyectos', function (Blueprint $table) {
            $table->dropColumn('fecha_inicio');
        });

        Schema::dropIfExists('notifications');
    }
};
