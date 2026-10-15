<?php

namespace Tests\Feature\Requerimientos;

use App\Enums\EstadoRequerimiento;
use App\Models\Requerimiento;
use App\Models\User;
use App\Services\RequerimientoDashboardService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardGraficasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        Carbon::setTestNow('2026-10-14');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_las_graficas_agrupan_la_evolucion_y_respetan_la_visibilidad(): void
    {
        $gestor = User::factory()->create()->assignRole('Gestor');
        $solicitante = User::factory()->create()->assignRole('Usuario');
        $otro = User::factory()->create()->assignRole('Usuario');

        $semanaActual = Carbon::parse('2026-10-14')->startOfWeek(Carbon::MONDAY);
        $fueraDeLaSemana = $semanaActual->copy()->subWeeks(12);

        Requerimiento::factory()->create([
            'solicitante_id' => $solicitante->id,
            'fecha_solicitud' => $semanaActual->toDateString(),
            'fecha_vencimiento' => '2026-11-01',
        ]);
        Requerimiento::factory()->estado(EstadoRequerimiento::Aprobado)->create([
            'solicitante_id' => $otro->id,
            'fecha_solicitud' => $fueraDeLaSemana->toDateString(),
            'fecha_vencimiento' => '2026-11-01',
        ]);
        Requerimiento::factory()->create([
            'solicitante_id' => $solicitante->id,
            'fecha_solicitud' => $semanaActual->toDateString(),
            'fecha_vencimiento' => '2026-11-01',
        ])->delete();

        $servicio = app(RequerimientoDashboardService::class);
        $delGestor = $servicio->graficas($gestor);
        $delSolicitante = $servicio->graficas($solicitante);

        $this->assertCount(12, $delGestor['semanal']['etiquetas']);
        $this->assertCount(12, $delGestor['mensual']['etiquetas']);
        $this->assertCount(5, $delGestor['anual']['etiquetas']);

        $this->assertSame(1, $delGestor['semanal']['barras'][11]);
        $this->assertSame(2, $delGestor['mensual']['total']);
        $this->assertSame(2, $delGestor['anual']['barras'][4]);
        $this->assertEqualsCanonicalizing(
            ['Borrador', 'Aprobado'],
            array_column($delGestor['mensual']['pastel'], 'etiqueta')
        );

        $this->assertSame(1, $delSolicitante['semanal']['total']);
        $this->assertSame(1, $delSolicitante['mensual']['total']);
        $this->assertSame(['Borrador'], array_column($delSolicitante['semanal']['pastel'], 'etiqueta'));
    }

    public function test_el_inicio_muestra_los_controles_de_las_graficas(): void
    {
        $gestor = User::factory()->create()->assignRole('Gestor');

        $this->actingAs($gestor)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Evolución de requerimientos', false)
            ->assertSee('data-periodo="semanal"', false)
            ->assertSee('data-periodo="mensual"', false)
            ->assertSee('data-periodo="anual"', false)
            ->assertSee('id="grafica-barras"', false)
            ->assertSee('id="grafica-pastel"', false)
            ->assertSee('12 semanas', false);
    }
}
