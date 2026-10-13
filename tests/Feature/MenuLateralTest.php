<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\MenuService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuLateralTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_la_pagina_activa_abre_sus_ramas_ancestras(): void
    {
        $admin = User::factory()->create()->assignRole('Administrador');

        $this->actingAs($admin)->get(route('admin.usuarios.index'))
            ->assertOk()
            ->assertSee('class="collapse show" id="menu-administracion"', false)
            ->assertSee('class="collapse show" id="menu-administracion-seguridad"', false)
            ->assertSee('class="collapse" id="menu-administracion-auditoria"', false)
            ->assertSee('href="'.route('admin.usuarios.index').'"', false);
    }

    public function test_las_ramas_sin_hijos_visibles_se_ocultan(): void
    {
        $user = User::factory()->create()->givePermissionTo('monitoreo.logs');

        $this->actingAs($user)->get(route('home'))
            ->assertOk()
            ->assertSee('id="menu-administracion-auditoria"', false)
            ->assertDontSee('id="menu-administracion-seguridad"', false)
            ->assertSee('href="'.route('admin.bitacora.index').'"', false)
            ->assertDontSee('href="'.route('admin.usuarios.index').'"', false);

        $sinPermisos = User::factory()->create()->assignRole('Usuario');

        $this->actingAs($sinPermisos)->get(route('home'))
            ->assertOk()
            ->assertDontSee('id="menu-administracion"', false);
    }

    public function test_el_arbol_se_limita_a_tres_niveles(): void
    {
        $menu = app(MenuService::class)->construir(null, [
            ['label' => 'Uno', 'children' => [
                ['label' => 'Dos', 'children' => [
                    ['label' => 'Tres', 'route' => 'home', 'children' => [
                        ['label' => 'Cuatro', 'route' => 'home'],
                    ]],
                ]],
            ]],
        ]);

        $tercero = $menu[0]['children'][0]['children'][0];

        $this->assertSame(3, $tercero['nivel']);
        $this->assertSame([], $tercero['children']);
        $this->assertSame(route('home'), $tercero['url']);
    }
}
