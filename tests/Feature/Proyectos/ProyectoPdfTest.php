<?php

namespace Tests\Feature\Proyectos;

use App\Enums\EstadoRequerimiento;
use App\Models\Proyecto;
use App\Models\Requerimiento;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProyectoPdfTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_el_reporte_incluye_los_requerimientos_del_proyecto_sin_eliminados(): void
    {
        $gestor = User::factory()->create()->assignRole('Gestor');
        $proyecto = Proyecto::factory()->create(['responsable_id' => $gestor->id, 'nombre' => 'Portal']);
        $incluido = Requerimiento::factory()->estado(EstadoRequerimiento::Aprobado)->create(['proyecto_id' => $proyecto->id, 'titulo' => 'Pantalla de pagos']);
        Requerimiento::factory()->create(['proyecto_id' => $proyecto->id, 'titulo' => 'Requerimiento borrado'])->delete();
        Requerimiento::factory()->create(['titulo' => 'De otro proyecto']);

        $this->actingAs($gestor);

        $html = view('pdf.proyecto', [
            'proyecto' => $proyecto->load(['responsable', 'requerimientos.solicitante', 'requerimientos.gestor']),
            'totales' => collect([['estado' => EstadoRequerimiento::Aprobado, 'total' => 1]]),
        ])->render();

        $this->assertStringContainsString($incluido->codigo, $html);
        $this->assertStringContainsString('Pantalla de pagos', $html);
        $this->assertStringNotContainsString('Requerimiento borrado', $html);
        $this->assertStringNotContainsString('De otro proyecto', $html);

        $this->get(route('proyectos.pdf', $proyecto))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_el_rol_usuario_no_exporta_el_reporte(): void
    {
        $usuario = User::factory()->create()->assignRole('Usuario');
        $proyecto = Proyecto::factory()->create();

        $this->actingAs($usuario)->get(route('proyectos.pdf', $proyecto))->assertForbidden();
    }
}
