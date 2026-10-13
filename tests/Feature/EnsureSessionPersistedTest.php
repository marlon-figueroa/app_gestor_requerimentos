<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EnsureSessionPersistedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_usuario_sin_fila_de_sesion_es_redirigido_al_login(): void
    {
        config(['session.driver' => 'database']);

        $user = User::factory()->create()->assignRole('Usuario');

        $this->actingAs($user)->get(route('home'));
        $sessionId = session()->getId();

        DB::table('sessions')->where('id', $sessionId)->delete();

        $this->actingAs($user)
            ->get(route('home'))
            ->assertRedirect(route('login'));
    }
}
