<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImpersonateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_un_administrador_puede_ingresar_como_otro_usuario_y_volver(): void
    {
        $admin = User::factory()->create(['name' => 'Ana Admin'])->assignRole('Administrador');
        $usuario = User::factory()->create(['name' => 'Luis Usuario'])->assignRole('Usuario');

        $this->actingAs($admin)
            ->post(route('impersonate', $usuario))
            ->assertRedirect(route('home'));

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Está viendo la aplicación como', false)
            ->assertSee('Luis Usuario')
            ->assertSee('Volver a mi cuenta');

        $this->assertAuthenticatedAs($usuario);
        $this->assertDatabaseHas('bitacoras', [
            'tipo_accion' => 'impersonar',
            'usuario_id' => $admin->id,
            'modelo_id' => $usuario->id,
        ]);
        $this->assertDatabaseMissing('bitacoras', [
            'tipo_accion' => 'login',
            'usuario_id' => $usuario->id,
        ]);

        $this->post(route('impersonate.leave'))
            ->assertRedirect(route('admin.usuarios.index'));

        $this->assertAuthenticatedAs($admin);
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Está viendo la aplicación como', false);

        $this->assertDatabaseHas('bitacoras', [
            'tipo_accion' => 'fin_impersonacion',
            'usuario_id' => $admin->id,
            'modelo_id' => $usuario->id,
        ]);
    }

    public function test_el_listado_muestra_el_boton_solo_para_otros_usuarios(): void
    {
        $admin = User::factory()->create()->assignRole('Administrador');
        $usuario = User::factory()->create()->assignRole('Usuario');

        $acciones = collect($this->actingAs($admin)
            ->postJson(route('admin.usuarios.data'), ['draw' => 1])
            ->assertOk()
            ->json('data'))
            ->pluck('acciones', 'id');

        $this->assertMatchesRegularExpression(
            '/<div class="btn-group btn-group-sm" role="group">\s*<button type="submit" form="impersonate-'.$usuario->id.'" class="btn btn-outline-secondary"/',
            $acciones[$usuario->id]
        );
        $this->assertStringNotContainsString(route('impersonate', $admin), $acciones[$admin->id]);
    }

    public function test_un_usuario_sin_rol_administrador_no_puede_suplantar(): void
    {
        $gestor = User::factory()->create()->assignRole('Gestor');
        $usuario = User::factory()->create()->assignRole('Usuario');

        $this->actingAs($gestor)
            ->post(route('impersonate', $usuario))
            ->assertForbidden();

        $this->assertAuthenticatedAs($gestor);
    }

    public function test_un_administrador_no_puede_suplantarse_a_si_mismo(): void
    {
        $admin = User::factory()->create()->assignRole('Administrador');

        $this->actingAs($admin)
            ->post(route('impersonate', $admin))
            ->assertForbidden();
    }

    public function test_no_se_puede_suplantar_mientras_ya_se_esta_suplantando(): void
    {
        $admin = User::factory()->create()->assignRole('Administrador');
        $otroAdmin = User::factory()->create()->assignRole('Administrador');
        $usuario = User::factory()->create()->assignRole('Usuario');

        $this->actingAs($admin)->post(route('impersonate', $otroAdmin))->assertRedirect(route('home'));

        $this->post(route('impersonate', $usuario))->assertForbidden();
        $this->assertAuthenticatedAs($otroAdmin);
    }
}
