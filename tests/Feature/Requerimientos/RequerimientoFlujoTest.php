<?php

namespace Tests\Feature\Requerimientos;

use App\Enums\EstadoRequerimiento;
use App\Models\Requerimiento;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RequerimientoFlujoTest extends TestCase
{
    use RefreshDatabase;

    private User $solicitante;

    private User $gestor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->solicitante = User::factory()->create()->assignRole('Usuario');
        $this->gestor = User::factory()->create()->assignRole('Gestor');
    }

    private function requerimiento(EstadoRequerimiento $estado = EstadoRequerimiento::Borrador, ?User $gestor = null): Requerimiento
    {
        return Requerimiento::factory()
            ->estado($estado, $gestor)
            ->create(['solicitante_id' => $this->solicitante->id]);
    }

    private function flujo(Requerimiento $requerimiento, string $accion): string
    {
        return route('requerimientos.flujo', [$requerimiento, $accion]);
    }

    public function test_solicitante_crea_requerimiento_en_borrador_con_codigo_e_historial(): void
    {
        $respuesta = $this->actingAs($this->solicitante)->post(route('requerimientos.store'), [
            'titulo' => 'Reporte de ventas',
            'descripcion' => 'Se necesita un reporte mensual.',
            'fecha_solicitud' => '01/10/2026',
            'fecha_vencimiento' => '31/10/2026',
        ]);

        $requerimiento = Requerimiento::firstOrFail();

        $respuesta->assertRedirect(route('requerimientos.show', $requerimiento));
        $this->assertSame(EstadoRequerimiento::Borrador, $requerimiento->estado);
        $this->assertSame(sprintf('REQ-%04d', $requerimiento->id), $requerimiento->codigo);
        $this->assertSame($this->solicitante->id, $requerimiento->solicitante_id);
        $this->assertNull($requerimiento->gestor_id);
        $this->assertSame('2026-10-31', $requerimiento->fecha_vencimiento->toDateString());
        $this->assertSame(['crear'], $requerimiento->revisiones()->pluck('accion')->all());
    }

    public function test_vencimiento_no_puede_ser_anterior_a_la_solicitud(): void
    {
        $this->actingAs($this->solicitante)->post(route('requerimientos.store'), [
            'titulo' => 'X',
            'descripcion' => 'Y',
            'fecha_solicitud' => '10/10/2026',
            'fecha_vencimiento' => '01/10/2026',
        ])->assertSessionHasErrors('fecha_vencimiento');

        $this->assertDatabaseCount('requerimientos', 0);
    }

    public function test_enviar_a_revision_deja_el_requerimiento_en_bandeja_y_bloquea_la_edicion(): void
    {
        $requerimiento = $this->requerimiento();

        $this->actingAs($this->solicitante)->post($this->flujo($requerimiento, 'enviar'))
            ->assertRedirect(route('requerimientos.show', $requerimiento));

        $requerimiento->refresh();
        $this->assertSame(EstadoRequerimiento::EnRevision, $requerimiento->estado);
        $this->assertTrue($requerimiento->enBandeja());
        $this->assertNotNull($requerimiento->enviado_at);

        $this->actingAs($this->solicitante)->get(route('requerimientos.edit', $requerimiento))->assertForbidden();
    }

    public function test_gestor_toma_de_la_bandeja_y_otro_gestor_ya_no_puede_resolverlo(): void
    {
        $requerimiento = $this->requerimiento(EstadoRequerimiento::EnRevision);
        $otroGestor = User::factory()->create()->assignRole('Gestor');

        $this->actingAs($this->gestor)->post($this->flujo($requerimiento, 'tomar'))->assertRedirect();
        $this->assertSame($this->gestor->id, $requerimiento->refresh()->gestor_id);

        $this->actingAs($otroGestor)->post($this->flujo($requerimiento, 'tomar'))->assertForbidden();
        $this->actingAs($otroGestor)->post($this->flujo($requerimiento, 'aprobar'))->assertForbidden();
    }

    public function test_rechazar_exige_observaciones_y_se_puede_reenviar(): void
    {
        $requerimiento = $this->requerimiento(EstadoRequerimiento::EnRevision, $this->gestor);

        $this->actingAs($this->gestor)->post($this->flujo($requerimiento, 'rechazar'), ['observaciones' => '  '])
            ->assertSessionHasErrors('observaciones');
        $this->assertSame(EstadoRequerimiento::EnRevision, $requerimiento->refresh()->estado);

        $this->actingAs($this->gestor)->post($this->flujo($requerimiento, 'rechazar'), ['observaciones' => 'Falta detallar el alcance.'])
            ->assertRedirect();

        $requerimiento->refresh();
        $this->assertSame(EstadoRequerimiento::Rechazado, $requerimiento->estado);
        $this->assertDatabaseHas('requerimiento_revisiones', [
            'requerimiento_id' => $requerimiento->id,
            'accion' => 'rechazar',
            'usuario_id' => $this->gestor->id,
            'observaciones' => 'Falta detallar el alcance.',
        ]);

        $this->actingAs($this->solicitante)->get(route('requerimientos.edit', $requerimiento))->assertOk();
        $this->actingAs($this->solicitante)->get(route('requerimientos.show', $requerimiento))
            ->assertOk()
            ->assertSee('Falta detallar el alcance.');

        $this->actingAs($this->solicitante)->post($this->flujo($requerimiento, 'enviar'))->assertRedirect();

        $requerimiento->refresh();
        $this->assertSame(EstadoRequerimiento::EnRevision, $requerimiento->estado);
        $this->assertNull($requerimiento->gestor_id);
    }

    public function test_flujo_completo_hasta_finalizar(): void
    {
        $requerimiento = $this->requerimiento(EstadoRequerimiento::EnRevision);

        $this->actingAs($this->gestor)->post($this->flujo($requerimiento, 'aprobar'))->assertRedirect();
        $requerimiento->refresh();
        $this->assertSame(EstadoRequerimiento::Aprobado, $requerimiento->estado);
        $this->assertSame($this->gestor->id, $requerimiento->gestor_id, 'Aprobar desde la bandeja asigna al gestor.');
        $this->assertNotNull($requerimiento->aprobado_at);

        $this->actingAs($this->gestor)->post($this->flujo($requerimiento, 'iniciar'))->assertRedirect();
        $this->assertSame(EstadoRequerimiento::EnDesarrollo, $requerimiento->refresh()->estado);

        $this->actingAs($this->gestor)->postJson($this->flujo($requerimiento, 'finalizar'))
            ->assertOk()
            ->assertJson(['estado' => 'finalizado']);

        $requerimiento->refresh();
        $this->assertSame(EstadoRequerimiento::Finalizado, $requerimiento->estado);
        $this->assertNotNull($requerimiento->finalizado_at);
        $this->assertFalse($requerimiento->vencido);
        $this->assertSame(['aprobar', 'iniciar', 'finalizar'], $requerimiento->revisiones()->reorder('id')->pluck('accion')->all());
    }

    public function test_solicitante_no_puede_aprobar_su_requerimiento(): void
    {
        $requerimiento = $this->requerimiento(EstadoRequerimiento::EnRevision);

        $this->actingAs($this->solicitante)->post($this->flujo($requerimiento, 'aprobar'))->assertForbidden();
    }

    public function test_transicion_invalida_responde_422_aun_para_administrador(): void
    {
        $admin = User::factory()->create()->assignRole('Administrador');
        $requerimiento = $this->requerimiento();

        $this->actingAs($admin)->postJson($this->flujo($requerimiento, 'finalizar'))
            ->assertStatus(422)
            ->assertJsonPath('message', 'No se puede pasar de "Borrador" a "Finalizado".');

        $this->assertSame(EstadoRequerimiento::Borrador, $requerimiento->refresh()->estado);
    }

    public function test_accion_desconocida_no_tiene_ruta(): void
    {
        $requerimiento = $this->requerimiento();

        $this->actingAs($this->solicitante)->post(url("requerimientos/{$requerimiento->id}/flujo/borrar"))->assertNotFound();
    }

    public function test_solicitante_solo_ve_sus_requerimientos(): void
    {
        $ajeno = Requerimiento::factory()->create();
        $propio = $this->requerimiento();

        $this->actingAs($this->solicitante)->get(route('requerimientos.show', $ajeno))->assertForbidden();

        $datos = $this->actingAs($this->solicitante)->postJson(route('requerimientos.data'), ['draw' => 1])->assertOk()->json('data');
        $this->assertSame([$propio->id], array_column($datos, 'id'));

        $datos = $this->actingAs($this->gestor)->postJson(route('requerimientos.data'), ['draw' => 1])->json('data');
        $this->assertCount(2, $datos);
    }

    public function test_bandeja_lista_solo_en_revision_sin_gestor(): void
    {
        $enBandeja = $this->requerimiento(EstadoRequerimiento::EnRevision);
        $this->requerimiento(EstadoRequerimiento::EnRevision, $this->gestor);
        $this->requerimiento();

        $this->actingAs($this->gestor)->get(route('requerimientos.bandeja'))->assertOk()->assertSee('Bandeja de revisión');
        $this->actingAs($this->solicitante)->get(route('requerimientos.bandeja'))->assertForbidden();

        $datos = $this->actingAs($this->gestor)->postJson(route('requerimientos.data'), ['draw' => 1, 'bandeja' => 1])->json('data');
        $this->assertSame([$enBandeja->id], array_column($datos, 'id'));
    }

    public function test_kanban_muestra_columnas_y_solo_requerimientos_visibles(): void
    {
        $propio = $this->requerimiento(EstadoRequerimiento::EnRevision);
        $ajeno = Requerimiento::factory()->create(['titulo' => 'Requerimiento ajeno']);

        $this->actingAs($this->solicitante)->get(route('requerimientos.kanban'))
            ->assertOk()
            ->assertSeeInOrder(['Borrador', 'En revisión', 'Rechazado', 'Aprobado', 'En desarrollo', 'Finalizado'])
            ->assertSee($propio->titulo)
            ->assertDontSee($ajeno->titulo)
            ->assertSee('/flujo/__ACCION__', false);

        $this->actingAs($this->gestor)->get(route('requerimientos.kanban'))
            ->assertOk()
            ->assertSee('Tomar para revisión')
            ->assertSee('data-destinos="aprobado,rechazado"', false);
    }

    public function test_calendario_devuelve_eventos_del_rango(): void
    {
        $dentro = $this->requerimiento();
        $dentro->forceFill(['fecha_vencimiento' => '2026-10-20'])->save();
        $fuera = $this->requerimiento();
        $fuera->forceFill(['fecha_vencimiento' => '2026-12-20'])->save();

        $this->actingAs($this->solicitante)->get(route('requerimientos.calendario'))->assertOk();

        $this->actingAs($this->solicitante)
            ->getJson(route('requerimientos.calendario.eventos', ['start' => '2026-10-01', 'end' => '2026-11-01']))
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $dentro->id)
            ->assertJsonPath('0.start', '2026-10-20')
            ->assertJsonPath('0.url', route('requerimientos.show', $dentro));
    }

    public function test_casos_de_uso_se_gestionan_segun_estado(): void
    {
        $requerimiento = $this->requerimiento();

        $this->actingAs($this->solicitante)->post(route('requerimientos.casos-uso.store', $requerimiento), [
            'codigo' => 'CU-01',
            'nombre' => 'Consultar ventas',
            'pantalla' => 'Reportes > Ventas',
        ])->assertRedirect();

        $casoUso = $requerimiento->casosUso()->firstOrFail();
        $this->assertSame('CU-02', $requerimiento->siguienteCodigoCasoUso());

        $this->actingAs($this->solicitante)->post(route('requerimientos.casos-uso.store', $requerimiento), [
            'codigo' => 'CU-01',
            'nombre' => 'Duplicado',
        ])->assertSessionHasErrors('codigo');

        $requerimiento->forceFill(['estado' => EstadoRequerimiento::EnRevision])->save();

        $this->actingAs($this->solicitante)
            ->put(route('requerimientos.casos-uso.update', [$requerimiento, $casoUso]), ['codigo' => 'CU-01', 'nombre' => 'Otro'])
            ->assertForbidden();

        $this->actingAs($this->gestor)
            ->put(route('requerimientos.casos-uso.update', [$requerimiento, $casoUso]), ['codigo' => 'CU-01', 'nombre' => 'Consultar ventas por mes'])
            ->assertRedirect();
        $this->assertSame('Consultar ventas por mes', $casoUso->refresh()->nombre);

        $otro = Requerimiento::factory()->estado(EstadoRequerimiento::EnRevision)->create();
        $this->actingAs($this->gestor)
            ->deleteJson(route('requerimientos.casos-uso.destroy', [$otro, $casoUso]))
            ->assertNotFound();
    }

    public function test_solo_se_elimina_un_borrador_propio(): void
    {
        $borrador = $this->requerimiento();
        $enviado = $this->requerimiento(EstadoRequerimiento::EnRevision);

        $this->actingAs($this->solicitante)->deleteJson(route('requerimientos.destroy', $enviado))->assertForbidden();
        $this->actingAs($this->solicitante)->deleteJson(route('requerimientos.destroy', $borrador))->assertOk();

        $this->assertSoftDeleted($borrador);
    }
}
