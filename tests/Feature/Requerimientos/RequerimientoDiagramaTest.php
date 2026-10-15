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
            ->assertSee('Diagrama de pasos', false)
            ->assertSee('id="diagrama-pasos"', false)
            ->assertSee('Ana Solicitante', false)
            ->assertSee('Solicitante', false);
    }
}
