<?php

namespace Tests\Feature;

use App\Models\Proyecto;
use App\Models\Requerimiento;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoadmapGestionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_usuario_accede_a_mis_proyectos_con_permiso_limitado(): void
    {
        $usuario = User::factory()->create()->assignRole('Usuario');
        $proyecto = Proyecto::factory()->create();
        Requerimiento::factory()->create([
            'proyecto_id' => $proyecto->id,
            'solicitante_id' => $usuario->id,
        ]);

        $this->actingAs($usuario)
            ->get(route('proyectos.mis-proyectos'))
            ->assertOk()
            ->assertSee($proyecto->codigo);
    }

    public function test_crear_requerimiento_desde_proyecto_precarga_formulario(): void
    {
        $usuario = User::factory()->create()->assignRole('Usuario');
        $proyecto = Proyecto::factory()->create();

        $this->actingAs($usuario)
            ->get(route('requerimientos.create', ['proyecto_id' => $proyecto->id]))
            ->assertOk()
            ->assertSee($proyecto->nombre, false);
    }

    public function test_api_lista_proyectos_autenticada(): void
    {
        $gestor = User::factory()->create()->assignRole('Gestor');
        Proyecto::factory()->count(2)->create(['responsable_id' => $gestor->id]);

        $this->actingAs($gestor)
            ->getJson('/api/v1/proyectos')
            ->assertOk()
            ->assertJsonStructure(['data']);
    }
}
