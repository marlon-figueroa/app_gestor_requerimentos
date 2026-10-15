<?php

namespace Tests\Feature\Proyectos;

use App\Enums\EstadoProyecto;
use App\Enums\EstadoRequerimiento;
use App\Models\Proyecto;
use App\Models\Requerimiento;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProyectoFlujoTest extends TestCase
{
    use RefreshDatabase;

    private User $gestor;

    private User $usuario;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->gestor = User::factory()->create(['name' => 'Gestor Responsable'])->assignRole('Gestor');
        $this->usuario = User::factory()->create()->assignRole('Usuario');
    }

    private function datos(array $extra = []): array
    {
        return [
            'nombre' => 'Portal de clientes',
            'descripcion' => 'Nuevo portal de autoservicio.',
            'responsable_id' => $this->gestor->id,
            'fecha_finalizacion' => today()->addMonth()->format('d/m/Y'),
            ...$extra,
        ];
    }

    private function proyecto(EstadoProyecto $estado = EstadoProyecto::Activo): Proyecto
    {
        return Proyecto::factory()->estado($estado)->create(['responsable_id' => $this->gestor->id]);
    }

    public function test_crear_deja_el_proyecto_activo_con_codigo_e_historial(): void
    {
        $respuesta = $this->actingAs($this->gestor)->post(route('proyectos.store'), $this->datos());

        $proyecto = Proyecto::firstOrFail();

        $respuesta->assertRedirect(route('proyectos.show', $proyecto));
        $this->assertSame(EstadoProyecto::Activo, $proyecto->estado);
        $this->assertSame(sprintf('PRY-%04d', $proyecto->id), $proyecto->codigo);
        $this->assertSame($this->gestor->id, $proyecto->responsable_id);
        $this->assertSame($this->gestor->id, $proyecto->creado_por);
        $this->assertSame(['crear'], $proyecto->revisiones()->pluck('accion')->all());
    }

    public function test_el_responsable_debe_ser_gestor_y_la_fecha_no_puede_ser_pasada(): void
    {
        $this->actingAs($this->gestor)->post(route('proyectos.store'), $this->datos(['responsable_id' => $this->usuario->id]))
            ->assertSessionHasErrors('responsable_id');

        $this->actingAs($this->gestor)->post(route('proyectos.store'), $this->datos(['fecha_finalizacion' => today()->subDay()->format('d/m/Y')]))
            ->assertSessionHasErrors('fecha_finalizacion');

        $this->actingAs($this->gestor)->post(route('proyectos.store'), $this->datos(['fecha_finalizacion' => '']))
            ->assertSessionHasErrors('fecha_finalizacion');

        $this->assertDatabaseCount('proyectos', 0);
    }

    public function test_no_se_puede_finalizar_con_requerimientos_sin_finalizar(): void
    {
        $proyecto = $this->proyecto();
        Requerimiento::factory()->estado(EstadoRequerimiento::Finalizado)->create(['proyecto_id' => $proyecto->id]);
        Requerimiento::factory()->estado(EstadoRequerimiento::EnDesarrollo)->create(['proyecto_id' => $proyecto->id]);

        $this->actingAs($this->gestor)
            ->from(route('proyectos.show', $proyecto))
            ->post(route('proyectos.flujo', [$proyecto, 'finalizar']))
            ->assertRedirect(route('proyectos.show', $proyecto))
            ->assertSessionHas('error', 'No se puede finalizar el proyecto: hay 1 requerimiento sin finalizar.');

        $this->assertSame(EstadoProyecto::Activo, $proyecto->fresh()->estado);

        $this->actingAs($this->gestor)
            ->get(route('proyectos.show', $proyecto))
            ->assertOk()
            ->assertSee('data-bloqueo="No se puede finalizar el proyecto: hay 1 requerimiento sin finalizar.', false);
    }

    public function test_tambien_bloquea_al_administrador(): void
    {
        $admin = User::factory()->create()->assignRole('Administrador');
        $proyecto = $this->proyecto();
        Requerimiento::factory()->create(['proyecto_id' => $proyecto->id]);

        $this->actingAs($admin)
            ->postJson(route('proyectos.flujo', [$proyecto, 'finalizar']))
            ->assertStatus(422)
            ->assertJson(['message' => 'No se puede finalizar el proyecto: hay 1 requerimiento sin finalizar.']);
    }

    public function test_finaliza_cuando_todos_los_requerimientos_estan_finalizados_o_eliminados(): void
    {
        $proyecto = $this->proyecto();
        Requerimiento::factory()->estado(EstadoRequerimiento::Finalizado)->create(['proyecto_id' => $proyecto->id]);
        Requerimiento::factory()->create(['proyecto_id' => $proyecto->id])->delete();

        $this->actingAs($this->gestor)
            ->post(route('proyectos.flujo', [$proyecto, 'finalizar']))
            ->assertRedirect(route('proyectos.show', $proyecto))
            ->assertSessionHas('success');

        $proyecto->refresh();
        $this->assertSame(EstadoProyecto::Finalizado, $proyecto->estado);
        $this->assertNotNull($proyecto->finalizado_at);
        $this->assertSame(['finalizar'], $proyecto->revisiones()->pluck('accion')->all());
    }

    public function test_cancelar_exige_motivo_y_se_permite_con_requerimientos_pendientes(): void
    {
        $proyecto = $this->proyecto();
        Requerimiento::factory()->create(['proyecto_id' => $proyecto->id]);

        $this->actingAs($this->gestor)
            ->post(route('proyectos.flujo', [$proyecto, 'cancelar']), ['observaciones' => ' '])
            ->assertSessionHasErrors('observaciones');

        $this->assertSame(EstadoProyecto::Activo, $proyecto->fresh()->estado);

        $this->actingAs($this->gestor)
            ->post(route('proyectos.flujo', [$proyecto, 'cancelar']), ['observaciones' => 'Sin presupuesto'])
            ->assertRedirect(route('proyectos.show', $proyecto));

        $proyecto->refresh();
        $this->assertSame(EstadoProyecto::Cancelado, $proyecto->estado);
        $this->assertNotNull($proyecto->cancelado_at);
        $this->assertSame('Sin presupuesto', $proyecto->revisiones()->first()->observaciones);
    }

    public function test_un_proyecto_cerrado_no_se_edita_ni_se_vuelve_a_cerrar(): void
    {
        $proyecto = $this->proyecto(EstadoProyecto::Finalizado);
        $admin = User::factory()->create()->assignRole('Administrador');

        $this->actingAs($this->gestor)->get(route('proyectos.edit', $proyecto))->assertForbidden();
        $this->actingAs($admin)->put(route('proyectos.update', $proyecto), $this->datos())->assertForbidden();
        $this->actingAs($admin)
            ->postJson(route('proyectos.flujo', [$proyecto, 'cancelar']), ['observaciones' => 'x'])
            ->assertStatus(422);
    }

    public function test_el_gestor_edita_un_proyecto_activo(): void
    {
        $proyecto = $this->proyecto();

        $this->actingAs($this->gestor)
            ->put(route('proyectos.update', $proyecto), $this->datos(['nombre' => 'Portal renovado']))
            ->assertRedirect(route('proyectos.show', $proyecto));

        $this->assertSame('Portal renovado', $proyecto->fresh()->nombre);
    }

    public function test_el_rol_usuario_no_accede_a_proyectos(): void
    {
        $proyecto = $this->proyecto();

        $this->actingAs($this->usuario)->get(route('proyectos.index'))->assertForbidden();
        $this->actingAs($this->usuario)->get(route('proyectos.show', $proyecto))->assertForbidden();
        $this->actingAs($this->usuario)->post(route('proyectos.store'), $this->datos())->assertForbidden();
        $this->actingAs($this->usuario)->post(route('proyectos.flujo', [$proyecto, 'cancelar']), ['observaciones' => 'x'])->assertForbidden();
    }

    public function test_el_listado_muestra_avance_y_filtra_por_estado(): void
    {
        $activo = $this->proyecto();
        $this->proyecto(EstadoProyecto::Cancelado);
        Requerimiento::factory()->estado(EstadoRequerimiento::Finalizado)->create(['proyecto_id' => $activo->id]);
        Requerimiento::factory()->create(['proyecto_id' => $activo->id]);

        $respuesta = $this->actingAs($this->gestor)->postJson(route('proyectos.data'), [
            'draw' => 1, 'start' => 0, 'length' => 10, 'estado' => 'activo',
            'columns' => [['data' => 'codigo', 'name' => 'codigo', 'searchable' => true, 'orderable' => true]],
        ])->assertOk();

        $this->assertSame(1, $respuesta->json('recordsFiltered'));
        $this->assertStringContainsString('1/2 finalizados', $respuesta->json('data.0.avance'));
    }

    public function test_el_detalle_lista_los_requerimientos_y_el_menu_muestra_proyectos(): void
    {
        $proyecto = $this->proyecto();
        Requerimiento::factory()->create(['proyecto_id' => $proyecto->id, 'titulo' => 'Login con SSO']);
        Requerimiento::factory()->create(['proyecto_id' => $proyecto->id, 'titulo' => 'Requerimiento borrado'])->delete();

        $this->actingAs($this->gestor)
            ->get(route('proyectos.show', $proyecto))
            ->assertOk()
            ->assertSee('Login con SSO')
            ->assertDontSee('Requerimiento borrado')
            ->assertSee('Requerimientos del proyecto (1)')
            ->assertSee(route('proyectos.index'), false);
    }
}
