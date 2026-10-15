<?php

namespace Database\Seeders;

use App\Enums\EstadoRequerimiento;
use App\Models\Proyecto;
use App\Models\Requerimiento;
use App\Models\User;
use App\Services\ProyectoFlujoService;
use App\Services\RequerimientoFlujoService;
use Illuminate\Database\Seeder;

class ProyectoSeeder extends Seeder
{
    public function run(): void
    {
        $gestor = User::role('Gestor')->first() ?? User::factory()->create()->assignRole('Gestor');
        $solicitante = User::role('Usuario')->first() ?? User::factory()->create()->assignRole('Usuario');

        $flujoReq = app(RequerimientoFlujoService::class);
        $flujoProy = app(ProyectoFlujoService::class);

        $proyecto = Proyecto::factory()->create([
            'nombre' => 'Portal interno de requerimientos',
            'descripcion' => 'Proyecto de demostración con requerimientos en distintos estados del flujo.',
            'responsable_id' => $gestor->id,
            'creado_por' => $gestor->id,
            'fecha_inicio' => today()->subWeek(),
            'fecha_finalizacion' => today()->addMonths(3),
        ]);

        $flujoProy->registrarCreacion($proyecto, $gestor);

        $borrador = Requerimiento::factory()->estado(EstadoRequerimiento::Borrador)->create([
            'proyecto_id' => $proyecto->id,
            'solicitante_id' => $solicitante->id,
            'titulo' => 'Mejora del dashboard de inicio',
        ]);
        $flujoReq->registrarCreacion($borrador, $solicitante);

        $revision = Requerimiento::factory()->estado(EstadoRequerimiento::EnRevision)->create([
            'proyecto_id' => $proyecto->id,
            'solicitante_id' => $solicitante->id,
            'titulo' => 'Exportación CSV del listado',
        ]);
        $flujoReq->registrarCreacion($revision, $solicitante);
        $flujoReq->ejecutar($revision, 'enviar', $solicitante);

        Requerimiento::factory()->estado(EstadoRequerimiento::Finalizado, $gestor)->create([
            'proyecto_id' => $proyecto->id,
            'solicitante_id' => $solicitante->id,
            'titulo' => 'Integración proyecto-requerimiento',
            'finalizado_at' => now()->subDays(2),
        ]);
    }
}
