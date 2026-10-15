<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requerimientos', function (Blueprint $table) {
            // Nullable: los requerimientos previos al módulo no tienen proyecto.
            $table->foreignId('proyecto_id')->nullable()->after('id')->constrained('proyectos')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('requerimientos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('proyecto_id');
        });
    }
};
