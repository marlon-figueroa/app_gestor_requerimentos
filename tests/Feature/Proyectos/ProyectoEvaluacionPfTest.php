<?php

namespace Tests\Feature\Proyectos;

use App\Enums\EstadoProyecto;
use App\Enums\TipoComponentePf;
use App\Models\Proyecto;
use App\Models\ProyectoEvaluacionPf;
use App\Models\User;
use App\Services\ProyectoPuntosFuncionService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProyectoEvaluacionPfTest extends TestCase
{
    use RefreshDatabase;

    private User $gestor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->gestor = User::factory()->create()->assignRole('Gestor');
    }

    private function payload(): array
    {
        return [
            'nombre' => 'Estimación inicial',
            'notas' => 'Alcance del portal.',
            'caracteristicas' => array_fill(0, 14, 3),
            'componentes' => [
                ['tipo' => 'ei', 'nombre' => 'Alta de usuario', 'descripcion' => '', 'det' => 4, 'referencias' => 1],
                ['tipo' => 'ilf', 'nombre' => 'Usuarios', 'descripcion' => '', 'det' => 20, 'referencias' => 2],
            ],
        ];
    }

    public function test_gestor_crea_evaluacion_con_totales_ifpug(): void
    {
        $proyecto = Proyecto::factory()->create(['responsable_id' => $this->gestor->id]);
        $servicio = app(ProyectoPuntosFuncionService::class);

        $ei = $servicio->evaluarComponente(TipoComponentePf::Ei, 4, 1);
        $ilf = $servicio->evaluarComponente(TipoComponentePf::Ilf, 20, 2);
        $sinAjuste = $ei['puntos'] + $ilf['puntos'];
        $vaf = $servicio->factorAjuste(array_fill(0, 14, 3))['factor_ajuste'];

        $this->actingAs($this->gestor)
            ->post(route('proyectos.evaluaciones-pf.store', $proyecto), $this->payload())
            ->assertRedirect();

        $evaluacion = ProyectoEvaluacionPf::firstOrFail();

        $this->assertSame($sinAjuste, $evaluacion->total_sin_ajuste);
        $this->assertSame(42, $evaluacion->suma_influencia);
        $this->assertSame($vaf, (float) $evaluacion->factor_ajuste);
        $this->assertCount(2, $evaluacion->componentes);
    }

    public function test_usuario_no_puede_evaluar(): void
    {
        $usuario = User::factory()->create()->assignRole('Usuario');
        $proyecto = Proyecto::factory()->create();

        $this->actingAs($usuario)
            ->get(route('proyectos.evaluaciones-pf.index', $proyecto))
            ->assertForbidden();

        $this->actingAs($usuario)
            ->post(route('proyectos.evaluaciones-pf.store', $proyecto), $this->payload())
            ->assertForbidden();
    }

    public function test_proyecto_cerrado_no_edita_pero_si_ve(): void
    {
        $proyecto = Proyecto::factory()->estado(EstadoProyecto::Finalizado)->create(['responsable_id' => $this->gestor->id]);
        $evaluacion = ProyectoEvaluacionPf::create([
            'proyecto_id' => $proyecto->id,
            'nombre' => 'Histórica',
            'creado_por' => $this->gestor->id,
            'total_sin_ajuste' => 10,
            'suma_influencia' => 42,
            'factor_ajuste' => 1.07,
            'total_ajustado' => 10.7,
            'caracteristicas' => array_fill(0, 14, 3),
        ]);

        $this->actingAs($this->gestor)
            ->get(route('proyectos.evaluaciones-pf.show', [$proyecto, $evaluacion]))
            ->assertOk();

        $this->actingAs($this->gestor)
            ->get(route('proyectos.evaluaciones-pf.edit', [$proyecto, $evaluacion]))
            ->assertForbidden();
    }

    public function test_pdf_de_evaluacion(): void
    {
        $proyecto = Proyecto::factory()->create(['responsable_id' => $this->gestor->id]);

        $this->actingAs($this->gestor)->post(route('proyectos.evaluaciones-pf.store', $proyecto), $this->payload());
        $evaluacion = ProyectoEvaluacionPf::firstOrFail();

        $this->actingAs($this->gestor)
            ->get(route('proyectos.evaluaciones-pf.pdf', [$proyecto, $evaluacion]))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_la_vista_incluye_el_diagnostico_de_viabilidad(): void
    {
        $proyecto = Proyecto::factory()->create(['responsable_id' => $this->gestor->id, 'fecha_finalizacion' => today()->addMonths(3)]);
        $this->actingAs($this->gestor)->post(route('proyectos.evaluaciones-pf.store', $proyecto), $this->payload());
        $evaluacion = ProyectoEvaluacionPf::firstOrFail();

        $this->actingAs($this->gestor)
            ->get(route('proyectos.evaluaciones-pf.show', [$proyecto, $evaluacion]))
            ->assertOk()
            ->assertSee('Diagnóstico de viabilidad e implementación', false)
            ->assertSee('Esfuerzo estimado', false);
    }
}
