<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requerimientos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->nullable()->unique();
            $table->string('titulo');
            $table->text('descripcion');
            $table->date('fecha_solicitud');
            $table->date('fecha_vencimiento')->index();
            $table->string('estado', 20)->default('borrador')->index();
            $table->foreignId('solicitante_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('gestor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('enviado_at')->nullable();
            $table->timestamp('aprobado_at')->nullable();
            $table->timestamp('finalizado_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('casos_uso', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requerimiento_id')->constrained('requerimientos')->cascadeOnDelete();
            $table->string('codigo', 20);
            $table->string('nombre');
            $table->string('pantalla')->nullable();
            $table->string('actor')->nullable();
            $table->text('descripcion')->nullable();
            $table->text('precondiciones')->nullable();
            $table->text('flujo_principal')->nullable();
            $table->text('postcondiciones')->nullable();
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();

            $table->unique(['requerimiento_id', 'codigo']);
        });

        Schema::create('requerimiento_adjuntos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requerimiento_id')->constrained('requerimientos')->cascadeOnDelete();
            $table->string('nombre_original');
            $table->string('ruta');
            $table->string('mime', 100);
            $table->unsignedBigInteger('tamano');
            $table->foreignId('subido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('requerimiento_revisiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requerimiento_id')->constrained('requerimientos')->cascadeOnDelete();
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
        Schema::dropIfExists('requerimiento_revisiones');
        Schema::dropIfExists('requerimiento_adjuntos');
        Schema::dropIfExists('casos_uso');
        Schema::dropIfExists('requerimientos');
    }
};
