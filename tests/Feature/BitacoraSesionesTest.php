<?php

namespace Tests\Feature;

use App\Models\Bitacora;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class BitacoraSesionesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_listado_de_sesiones_activas_requiere_permiso(): void
    {
        $user = User::factory()->create()->assignRole('Usuario');

        $this->actingAs($user)->postJson(route('admin.bitacora.sesiones.data'))->assertForbidden();
    }

    public function test_vista_de_sesion_muestra_timeline(): void
    {
        $admin = User::factory()->create()->assignRole('Administrador');
        $sessionId = Str::random(40);

        DB::table('sessions')->insert([
            'id' => $sessionId,
            'user_id' => $admin->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X) Chrome/120.0',
            'payload' => base64_encode(serialize([])),
            'last_activity' => now()->timestamp,
        ]);

        Bitacora::create([
            'usuario_id' => $admin->id,
            'tipo_accion' => 'login',
            'accion' => 'Ingreso al sistema',
            'sesion_id' => $sessionId,
            'exitoso' => true,
        ]);

        Bitacora::create([
            'usuario_id' => $admin->id,
            'tipo_accion' => 'personalizada',
            'accion' => 'Consulta de usuarios',
            'modulo' => 'usuarios',
            'sesion_id' => $sessionId,
            'exitoso' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.bitacora.sesiones.show', $sessionId))
            ->assertOk()
            ->assertSee('Timeline de la sesión', false)
            ->assertSee('Ingreso al sistema', false)
            ->assertSee('Consulta de usuarios', false);
    }

    public function test_invalidar_sesion_requiere_permiso(): void
    {
        $admin = User::factory()->create()->assignRole('Administrador');
        $user = User::factory()->create()->assignRole('Usuario');
        $sessionId = Str::random(40);

        DB::table('sessions')->insert([
            'id' => $sessionId,
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test',
            'payload' => base64_encode(serialize([])),
            'last_activity' => now()->timestamp,
        ]);

        $this->actingAs($user)->postJson(route('admin.bitacora.sesiones.invalidar', $sessionId))->assertForbidden();
    }

    public function test_administrador_invalida_sesion_y_registra_bitacora(): void
    {
        $admin = User::factory()->create()->assignRole('Administrador');
        $user = User::factory()->create(['name' => 'Conectado'])->assignRole('Usuario');
        $sessionId = Str::random(40);

        DB::table('sessions')->insert([
            'id' => $sessionId,
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test',
            'payload' => base64_encode(serialize([])),
            'last_activity' => now()->timestamp,
        ]);

        $this->actingAs($admin)
            ->postJson(route('admin.bitacora.sesiones.invalidar', $sessionId))
            ->assertOk()
            ->assertJsonPath('message', 'Sesión de Conectado invalidada. El usuario será enviado al login de inmediato.');

        $this->assertDatabaseMissing('sessions', ['id' => $sessionId]);
        $this->assertDatabaseHas('bitacoras', [
            'tipo_accion' => 'sesion_invalidada',
            'usuario_id' => $user->id,
            'sesion_id' => $sessionId,
        ]);
    }

    public function test_vista_de_sesion_expirada_responde_404(): void
    {
        $admin = User::factory()->create()->assignRole('Administrador');
        $sessionId = Str::random(40);

        DB::table('sessions')->insert([
            'id' => $sessionId,
            'user_id' => $admin->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test',
            'payload' => base64_encode(serialize([])),
            'last_activity' => now()->subHours(5)->timestamp,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.bitacora.sesiones.show', $sessionId))
            ->assertNotFound();
    }
}
