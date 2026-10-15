<?php

namespace Tests\Feature\Proyectos;

use App\Models\Proyecto;
use App\Models\User;
use App\Services\ProyectoDiagramaService;
use App\Services\ProyectoFlujoService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProyectoDiagramaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_el_diagrama_refleja_la_cancelacion_del_proyecto(): void
    {
        $gestor = User::factory()->create(['name' => 'Laura Gestora'])->assignRole('Gestor');
        $proyecto = Proyecto::factory()->create(['responsable_id' => $gestor->id]);
        $flujo = app(ProyectoFlujoService::class);
        $flujo->registrarCreacion($proyecto, $gestor);
        $flujo->ejecutar($proyecto, 'cancelar', $gestor, 'Cambio de prioridades');

        $diagrama = app(ProyectoDiagramaService::class)->diagrama($proyecto->fresh());

        $estados = collect($diagrama['estados'])->keyBy('id');
        $this->assertTrue($estados['cancelado']['actual']);
        $this->assertTrue($estados['activo']['visitado']);
        $this->assertFalse($estados['finalizado']['visitado']);
        $this->assertSame('Cambio de prioridades', $estados['cancelado']['eventos'][0]['observaciones']);
        $this->assertSame('Laura Gestora', $estados['cancelado']['eventos'][0]['actor']);

        $transiciones = collect($diagrama['transiciones'])->keyBy(fn ($t) => "{$t['origen']}>{$t['destino']}");
        $this->assertSame([1], $transiciones['inicio>activo']['pasos']);
        $this->assertSame([2], $transiciones['activo>cancelado']['pasos']);
        $this->assertTrue($transiciones['activo>cancelado']['rechazo']);
        $this->assertSame([], $transiciones['activo>finalizado']['pasos']);
        $this->assertArrayHasKey('cancelado>fin', $transiciones->all());
        $this->assertSame([2, 1], $diagrama['ubicacion']['cancelado']);
    }

    public function test_la_vista_del_proyecto_incluye_el_modal_del_diagrama(): void
    {
        $gestor = User::factory()->create()->assignRole('Gestor');
        $proyecto = Proyecto::factory()->create(['responsable_id' => $gestor->id]);
        app(ProyectoFlujoService::class)->registrarCreacion($proyecto, $gestor);

        $this->actingAs($gestor)
            ->get(route('proyectos.show', $proyecto))
            ->assertOk()
            ->assertSee('data-bs-target="#diagrama-modal"', false)
            ->assertSee('Diagrama', false)
            ->assertSee('id="diagrama-modal"', false)
            ->assertSee('id="diagrama-estados"', false);
    }
}
