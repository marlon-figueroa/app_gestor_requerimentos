<?php

namespace Tests\Feature\Requerimientos;

use App\Enums\EstadoRequerimiento;
use App\Models\Requerimiento;
use App\Models\User;
use App\Services\RequerimientoDiagramaService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RequerimientoDiagramaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_cada_paso_conserva_al_actor_que_ejecuto_la_transicion(): void
    {
        $solicitante = User::factory()->create(['name' => 'Ana Solicitante'])->assignRole('Usuario');
        $gestorInicial = User::factory()->create(['name' => 'Luis Gestor'])->assignRole('Gestor');
        $gestorSiguiente = User::factory()->create(['name' => 'Marta Gestora'])->assignRole('Gestor');

        $requerimiento = Requerimiento::factory()->create([
            'solicitante_id' => $solicitante->id,
            'gestor_id' => $gestorInicial->id,
            'estado' => EstadoRequerimiento::EnDesarrollo,
        ]);

        foreach ([
            ['usuario_id' => $solicitante->id, 'accion' => 'crear', 'estado_anterior' => null, 'estado_nuevo' => EstadoRequerimiento::Borrador],
            ['usuario_id' => $solicitante->id, 'accion' => 'enviar', 'estado_anterior' => EstadoRequerimiento::Borrador, 'estado_nuevo' => EstadoRequerimiento::EnRevision],
            ['usuario_id' => $gestorInicial->id, 'accion' => 'tomar', 'estado_anterior' => EstadoRequerimiento::EnRevision, 'estado_nuevo' => EstadoRequerimiento::EnRevision],
            ['usuario_id' => $gestorSiguiente->id, 'accion' => 'aprobar', 'estado_anterior' => EstadoRequerimiento::EnRevision, 'estado_nuevo' => EstadoRequerimiento::Aprobado],
            ['usuario_id' => $solicitante->id, 'accion' => 'iniciar', 'estado_anterior' => EstadoRequerimiento::Aprobado, 'estado_nuevo' => EstadoRequerimiento::EnDesarrollo],
        ] as $paso) {
            $requerimiento->revisiones()->create($paso);
        }

        $pasos = app(RequerimientoDiagramaService::class)->pasos($requerimiento->fresh());

        $this->assertSame(
            ['Ana Solicitante', 'Ana Solicitante', 'Luis Gestor', 'Marta Gestora', 'Ana Solicitante'],
            array_column($pasos, 'actor')
        );
        $this->assertSame(
            ['Solicitante', 'Solicitante', 'Gestor', 'Gestor', 'Gestor'],
            array_column($pasos, 'rol')
        );
        $this->assertFalse($pasos[2]['mismoSolicitante']);
        $this->assertFalse($pasos[3]['mismoSolicitante']);
        $this->assertTrue($pasos[4]['mismoSolicitante']);
        $this->assertTrue($pasos[4]['actual']);
        $this->assertFalse($pasos[3]['actual']);
        $this->assertSame('Aprobado', $pasos[3]['etapa']);
    }

    public function test_el_diagrama_es_una_maquina_de_estados_con_las_transiciones_recorridas(): void
    {
        $solicitante = User::factory()->create()->assignRole('Usuario');
        $gestor = User::factory()->create()->assignRole('Gestor');

        $requerimiento = Requerimiento::factory()->create([
            'solicitante_id' => $solicitante->id,
            'gestor_id' => $gestor->id,
            'estado' => EstadoRequerimiento::EnRevision,
        ]);

        foreach ([
            ['usuario_id' => $solicitante->id, 'accion' => 'crear', 'estado_anterior' => null, 'estado_nuevo' => EstadoRequerimiento::Borrador],
            ['usuario_id' => $solicitante->id, 'accion' => 'enviar', 'estado_anterior' => EstadoRequerimiento::Borrador, 'estado_nuevo' => EstadoRequerimiento::EnRevision],
            ['usuario_id' => $gestor->id, 'accion' => 'tomar', 'estado_anterior' => EstadoRequerimiento::EnRevision, 'estado_nuevo' => EstadoRequerimiento::EnRevision],
            ['usuario_id' => $gestor->id, 'accion' => 'rechazar', 'estado_anterior' => EstadoRequerimiento::EnRevision, 'estado_nuevo' => EstadoRequerimiento::Rechazado],
            ['usuario_id' => $solicitante->id, 'accion' => 'enviar', 'estado_anterior' => EstadoRequerimiento::Rechazado, 'estado_nuevo' => EstadoRequerimiento::EnRevision],
        ] as $paso) {
            $requerimiento->revisiones()->create($paso);
        }

        $diagrama = app(RequerimientoDiagramaService::class)->diagrama($requerimiento->fresh());

        $estados = collect($diagrama['estados'])->keyBy('id');
        $this->assertCount(count(EstadoRequerimiento::cases()), $estados);
        $this->assertTrue($estados['en_revision']['actual']);
        $this->assertTrue($estados['rechazado']['visitado']);
        $this->assertFalse($estados['aprobado']['visitado']);

        $transiciones = collect($diagrama['transiciones'])->keyBy(fn ($t) => "{$t['origen']}>{$t['destino']}");
        $this->assertSame([1], $transiciones['inicio>borrador']['pasos']);
        $this->assertSame([3], $transiciones['en_revision>en_revision']['pasos']);
        $this->assertSame(['rechazar'], $transiciones['en_revision>rechazado']['eventos']);
        $this->assertTrue($transiciones['en_revision>rechazado']['rechazo']);
        $this->assertSame([5], $transiciones['rechazado>en_revision']['pasos']);
        $this->assertSame([], $transiciones['en_revision>aprobado']['pasos']);
        $this->assertSame([], $transiciones['finalizado>fin']['pasos']);

        $this->assertSame([2, 3, 5], array_column($estados['en_revision']['eventos'], 'numero'));
        $this->assertSame('Envió a revisión', $estados['en_revision']['eventos'][0]['accion']);
        $this->assertSame('Tomó el requerimiento para revisión', $estados['en_revision']['eventos'][1]['accion']);
        $this->assertSame([], $estados['aprobado']['eventos']);
        $this->assertTrue($estados['rechazado']['eventos'][0]['rechazo']);
    }

    public function test_la_vista_del_requerimiento_incluye_el_diagrama(): void
    {
        $solicitante = User::factory()->create(['name' => 'Ana Solicitante'])->assignRole('Usuario');
        $requerimiento = Requerimiento::factory()->create(['solicitante_id' => $solicitante->id]);
        $requerimiento->revisiones()->create([
            'usuario_id' => $solicitante->id,
            'accion' => 'crear',
            'estado_nuevo' => EstadoRequerimiento::Borrador,
        ]);

        $this->actingAs($solicitante)
            ->get(route('requerimientos.show', $requerimiento))
            ->assertOk()
            ->assertSee('Diagrama de estados', false)
            ->assertSee('btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#diagrama-modal"', false)
            ->assertSee('id="diagrama-modal"', false)
            ->assertSee('modal-diagrama', false)
            ->assertSee('id="diagrama-estados"', false)
            ->assertSee('Ana Solicitante', false)
            ->assertSee('Solicitante', false);
    }
}
