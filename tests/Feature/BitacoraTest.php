<?php

namespace Tests\Feature;

use App\Models\Bitacora;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BitacoraTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_registra_inicio_de_sesion_y_login_fallido(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'incorrecta']);
        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $this->assertDatabaseHas('bitacoras', ['tipo_accion' => 'login_fallido', 'usuario_id' => $user->id, 'exitoso' => false]);
        $this->assertDatabaseHas('bitacoras', ['tipo_accion' => 'login', 'usuario_id' => $user->id, 'exitoso' => true]);
    }

    public function test_registra_cambios_de_usuario_y_roles_sin_guardar_la_contrasena(): void
    {
        $admin = User::factory()->create()->assignRole('Administrador');
        $user = User::factory()->create(['name' => 'Original'])->assignRole('Usuario');

        $this->actingAs($admin)->put(route('admin.usuarios.update', $user), [
            'name' => 'Modificado',
            'email' => $user->email,
            'password' => 'nueva-contrasena',
            'password_confirmation' => 'nueva-contrasena',
            'roles' => ['Administrador'],
        ])->assertRedirect(route('admin.usuarios.index'));

        $cambio = Bitacora::where('tipo_accion', 'actualizar')->whereNull('tags')->firstOrFail();
        $this->assertSame('Original', $cambio->valores_anteriores['name']);
        $this->assertSame('Modificado', $cambio->valores_nuevos['name']);
        $this->assertSame('********', $cambio->valores_nuevos['password']);

        $roles = Bitacora::where('tags', 'roles')->firstOrFail();
        $this->assertSame(['Usuario'], $roles->valores_anteriores['roles']);
        $this->assertSame(['Administrador'], $roles->valores_nuevos['roles']);
    }

    public function test_solo_usuarios_con_permiso_ven_la_bitacora(): void
    {
        $admin = User::factory()->create()->assignRole('Administrador');
        $user = User::factory()->create()->assignRole('Usuario');

        $this->actingAs($user)->get(route('admin.bitacora.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.bitacora.index'))->assertOk()->assertSee('Bitácora');
        $this->actingAs($admin)->get(route('admin.bitacora.pdf'))->assertOk()->assertHeader('content-type', 'application/pdf');
    }

    public function test_monitoreo_requiere_permiso(): void
    {
        $admin = User::factory()->create()->assignRole('Administrador');
        $user = User::factory()->create()->assignRole('Usuario');

        $this->get('/pulse')->assertRedirect('/login');
        $this->get('/log-viewer')->assertRedirect('/login');

        $this->actingAs($user)->get('/pulse')->assertForbidden();
        $this->actingAs($user)->get('/log-viewer')->assertForbidden();

        $this->actingAs($admin)->get('/log-viewer')->assertOk();
    }
}
