<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerfilTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_usuario_autenticado_ve_su_perfil(): void
    {
        $usuario = User::factory()->create()->assignRole('Usuario');

        $this->actingAs($usuario)
            ->get(route('perfil.show'))
            ->assertOk()
            ->assertSee($usuario->name)
            ->assertSee('Información personal')
            ->assertSee('Los roles los administra');
    }

    public function test_usuario_actualiza_nombre_y_correo(): void
    {
        $usuario = User::factory()->create()->assignRole('Usuario');

        $this->actingAs($usuario)
            ->put(route('perfil.update'), [
                'name' => 'Nombre Actualizado',
                'email' => 'nuevo@example.com',
            ])
            ->assertRedirect(route('perfil.show'));

        $usuario->refresh();
        $this->assertSame('Nombre Actualizado', $usuario->name);
        $this->assertSame('nuevo@example.com', $usuario->email);
    }

    public function test_usuario_cambia_contrasena_con_actual_correcta(): void
    {
        $usuario = User::factory()->create([
            'password' => 'anterior-12',
        ])->assignRole('Usuario');

        $this->actingAs($usuario)
            ->put(route('perfil.password'), [
                'current_password' => 'anterior-12',
                'password' => 'nueva-clave-12',
                'password_confirmation' => 'nueva-clave-12',
            ])
            ->assertRedirect(route('perfil.show').'#seguridad');

        $this->assertTrue(
            auth()->validate(['email' => $usuario->email, 'password' => 'nueva-clave-12'])
        );
    }

    public function test_invitado_no_accede_al_perfil(): void
    {
        $this->get(route('perfil.show'))->assertRedirect(route('login'));
    }
}
