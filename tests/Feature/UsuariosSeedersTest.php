<?php

namespace Tests\Feature;

use App\Http\Requests\Proyectos\ProyectoRequest;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\UsuariosSeeders;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsuariosSeedersTest extends TestCase
{
    use RefreshDatabase;

    public function test_crea_administrador_y_gestores_sin_duplicarlos(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(UsuariosSeeders::class);
        $this->seed(UsuariosSeeders::class);

        $this->assertDatabaseCount('users', 5);
        $this->assertEqualsCanonicalizing(
            array_keys(UsuariosSeeders::ADMINISTRADORES),
            User::role('Administrador')->pluck('email')->all(),
        );
        $this->assertEqualsCanonicalizing(
            array_keys(UsuariosSeeders::GESTORES),
            User::role('Gestor')->pluck('email')->all(),
        );
        $this->assertCount(4, ProyectoRequest::responsables());
    }
}
