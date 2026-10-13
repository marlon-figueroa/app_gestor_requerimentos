<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MensajesFlashTest extends TestCase
{
    use RefreshDatabase;

    public function test_los_mensajes_de_sesion_se_envian_a_sweetalert_escapados(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $admin = User::factory()->create()->assignRole('Administrador');

        $response = $this->actingAs($admin)
            ->withSession(['success' => 'Guardado </script><b>ok</b>'])
            ->get(route('home'));

        $response->assertOk()
            ->assertSee('id="flash-messages"', false)
            ->assertSee('"type":"success"', false)
            ->assertDontSee('</script><b>ok</b>', false);
    }

    public function test_los_errores_de_validacion_generan_aviso(): void
    {
        $this->post('/login', ['email' => 'no-existe@example.com', 'password' => 'x']);

        $this->get('/login')
            ->assertOk()
            ->assertSee('Revise el campo marcado en el formulario.', false);
    }
}
