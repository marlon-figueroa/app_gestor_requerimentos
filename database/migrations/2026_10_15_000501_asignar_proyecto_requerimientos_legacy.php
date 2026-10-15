<?php

use App\Models\Proyecto;
use App\Models\Requerimiento;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (Requerimiento::query()->whereNull('proyecto_id')->doesntExist()) {
            return;
        }

        $responsableId = User::query()->orderBy('id')->value('id');

        if ($responsableId === null) {
            return;
        }

        $proyectoLegacy = Proyecto::query()->firstOrCreate(
            ['nombre' => 'Proyecto legacy (migración)'],
            [
                'descripcion' => 'Contenedor automático para requerimientos creados antes de la obligatoriedad de proyecto.',
                'responsable_id' => $responsableId,
                'creado_por' => $responsableId,
                'fecha_finalizacion' => now()->addYear()->toDateString(),
                'estado' => 'activo',
            ],
        );

        Requerimiento::query()->whereNull('proyecto_id')->update(['proyecto_id' => $proyectoLegacy->id]);
    }

    public function down(): void
    {
        //
    }
};
