<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BitacoraPestanasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_administrador_ve_las_cuatro_pestanas(): void
    {
        $admin = User::factory()->create()->assignRole('Administrador');

        $this->actingAs($admin)->get(route('admin.bitacora.index'))
            ->assertOk()
            ->assertSee('id="tab-registros"', false)
            ->assertSee('id="tab-sesiones"', false)
            ->assertSee('id="tab-monitoreo"', false)
            ->assertSee('id="tab-logs"', false)
            ->assertSee('data-src="'.route('pulse').'"', false)
            ->assertSee('data-src="'.e(route('log-viewer.index', ['embed' => 1])).'"', false);
    }

    public function test_cada_pestana_depende_de_su_permiso(): void
    {
        $user = User::factory()->create()->givePermissionTo('monitoreo.pulse');

        $this->actingAs($user)->get(route('admin.bitacora.index'))
            ->assertOk()
            ->assertSee('id="tab-monitoreo"', false)
            ->assertDontSee('id="tab-registros"', false)
            ->assertDontSee('id="tab-logs"', false)
            ->assertDontSee('id="tabla-bitacora"', false);

        $this->actingAs($user)->postJson(route('admin.bitacora.data'))->assertForbidden();
    }

    public function test_sin_permisos_no_accede_a_la_bitacora(): void
    {
        $user = User::factory()->create()->assignRole('Usuario');

        $this->actingAs($user)->get(route('admin.bitacora.index'))->assertForbidden();
    }

    public function test_log_viewer_embebido_oculta_volver_al_sistema(): void
    {
        $admin = User::factory()->create()->assignRole('Administrador');
        $urlSistema = json_encode(config('log-viewer.back_to_system_url'));

        $this->actingAs($admin)->get(route('log-viewer.index'))
            ->assertOk()
            ->assertSee($urlSistema, false);

        $this->actingAs($admin)->get(route('log-viewer.index', ['embed' => 1]))
            ->assertOk()
            ->assertDontSee($urlSistema, false);
    }
}
