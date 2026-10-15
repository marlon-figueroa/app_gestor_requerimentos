<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComponentesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withViewErrors([]);
    }

    private function conOld(array $input): void
    {
        $session = $this->app['session.store'];
        $session->put('_old_input', $input);
        $this->app['request']->setLaravelSession($session);
    }

    public function test_datatable_genera_thead_y_configuracion(): void
    {
        $vista = $this->blade(
            '<x-datatable id="tabla-demo" url="/demo/data" filters="filtros-demo" :order="[[0, \'desc\']]" :columns="$columnas" />',
            ['columnas' => [
                ['data' => 'id', 'label' => 'ID'],
                ['data' => 'roles', 'label' => 'Roles', 'name' => 'roles.name', 'orderable' => false],
                ['data' => 'acciones', 'label' => 'Acciones', 'actions' => true],
            ]],
        );

        $vista->assertSee('id="tabla-demo"', false)
            ->assertSee('data-filters="filtros-demo"', false)
            ->assertSeeInOrder(['<th>ID</th>', '<th>Roles</th>', '<th class="text-end">Acciones</th>'], false)
            ->assertDontSee('class=""', false);

        preg_match('/data-datatable-config="([^"]+)"/', (string) $vista, $coincidencia);
        $config = json_decode(html_entity_decode($coincidencia[1]), true);

        $this->assertSame('/demo/data', $config['url']);
        $this->assertSame([[0, 'desc']], $config['order']);
        $this->assertSame(['data' => 'id', 'name' => 'id'], $config['columns'][0]);
        $this->assertSame(['data' => 'roles', 'name' => 'roles.name', 'orderable' => false], $config['columns'][1]);
        $this->assertSame([
            'data' => 'acciones',
            'orderable' => false,
            'searchable' => false,
            'className' => 'acciones text-end',
        ], $config['columns'][2]);
    }

    public function test_filtros_se_vinculan_a_su_tabla(): void
    {
        $this->blade('<x-filters id="filtros-demo" target="tabla-demo"><div class="col-md-4">campo</div></x-filters>')
            ->assertSee('id="filtros-demo"', false)
            ->assertSee('data-filters-for="tabla-demo"', false)
            ->assertSee('type="submit"', false)
            ->assertSee('type="reset"', false)
            ->assertSee('campo');
    }

    public function test_form_agrega_csrf_metodo_y_enctype(): void
    {
        $this->blade('<x-form action="/demo/1" method="PUT" files cancel="/demo">contenido</x-form>')
            ->assertSee('method="POST"', false)
            ->assertSee('name="_method" value="PUT"', false)
            ->assertSee('name="_token"', false)
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('href="/demo"', false)
            ->assertSee('Guardar');

        $this->blade('<x-form action="/demo" method="GET">contenido</x-form>')
            ->assertSee('method="GET"', false)
            ->assertDontSee('name="_token"', false)
            ->assertDontSee('name="_method"', false);
    }

    public function test_input_usa_old_y_muestra_errores(): void
    {
        $this->conOld(['name' => 'Valor anterior']);
        $this->withViewErrors(['name' => 'El nombre es obligatorio.']);

        $this->blade('<x-form.input name="name" label="Nombre" value="Original" required autofocus />')
            ->assertSee('value="Valor anterior"', false)
            ->assertDontSee('Original')
            ->assertSee('class="form-control is-invalid"', false)
            ->assertSee('El nombre es obligatorio.')
            ->assertSee('<span class="text-danger">*</span>', false)
            ->assertSee('autofocus', false)
            ->assertSee('required', false);
    }

    public function test_input_de_fecha_usa_datetimepicker(): void
    {
        $this->blade('<x-form.input name="fecha_desde" label="Desde" type="datetime" />')
            ->assertSee('type="text"', false)
            ->assertSee('form-control datetimepicker', false)
            ->assertSee('data-time="true"', false)
            ->assertSee('placeholder="dd/mm/aaaa hh:mm"', false)
            ->assertSee('bi-calendar-event', false);
    }

    public function test_password_no_conserva_valor(): void
    {
        $this->blade('<x-form.input name="password" type="password" value="secreto" />')
            ->assertDontSee('secreto');
    }

    public function test_select_multiple_usa_old_y_errores_de_elementos(): void
    {
        $this->conOld(['roles' => ['Editor']]);
        $this->withViewErrors(['roles.0' => 'El rol no es válido.']);

        $this->blade(
            '<x-form.select name="roles" label="Roles" :options="$roles" :selected="[\'Administrador\']" multiple placeholder="Seleccione" />',
            ['roles' => collect(['Administrador', 'Editor'])],
        )
            ->assertSee('name="roles[]"', false)
            ->assertSee('data-placeholder="Seleccione"', false)
            ->assertSee('<option value="Editor" selected>', false)
            ->assertSee('<option value="Administrador" >', false)
            ->assertSee('El rol no es válido.')
            ->assertSee('is-invalid', false)
            ->assertDontSee('<option value="">', false);
    }

    public function test_select_simple_con_opciones_asociativas(): void
    {
        $this->blade(
            '<x-form.select name="estado" :options="[1 => \'Activo\', 2 => \'Inactivo\']" :selected="2" :select2="false" />',
        )
            ->assertSee('<option value="">Seleccione una opción</option>', false)
            ->assertSee('<option value="2" selected>Inactivo</option>', false)
            ->assertDontSee('select2', false);
    }

    public function test_checkbox_respeta_checked_y_old(): void
    {
        $this->blade('<x-form.checkbox name="activo" label="Activo" checked switch unchecked-value="0" />')
            ->assertSee('form-check form-switch', false)
            ->assertSee('type="hidden" name="activo" value="0"', false)
            ->assertSee('checked', false);

        $this->conOld(['activo' => '0']);

        $this->blade('<x-form.checkbox name="activo" label="Activo" checked unchecked-value="0" />')
            ->assertDontSee(' checked', false);
    }

    public function test_action_bar_y_eliminar_respetan_permisos(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $plantilla = '<x-action-bar create="/demo/crear" create-label="Nuevo demo" create-permission="usuarios.crear"
                            export="/demo/pdf" export-permission="usuarios.exportar" table="tabla-demo" />
                      <x-delete-button url="/demo/1" permission="usuarios.eliminar" confirm="¿Eliminar demo?" redirect="/demo" label="Eliminar" />';

        $this->actingAs(User::factory()->create());

        $this->blade($plantilla)
            ->assertDontSee('Nuevo demo')
            ->assertDontSee('data-export-table', false)
            ->assertDontSee('data-delete-url', false);

        $this->actingAs(User::factory()->create()->givePermissionTo(['usuarios.crear', 'usuarios.exportar', 'usuarios.eliminar']));

        $this->blade($plantilla)
            ->assertSee('Nuevo demo')
            ->assertSee('data-export-table="tabla-demo"', false)
            ->assertSee('data-delete-url="/demo/1"', false)
            ->assertSee('data-confirm="¿Eliminar demo?"', false)
            ->assertSee('data-redirect="/demo"', false);
    }

    public function test_row_actions_se_renderiza_desde_view(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->actingAs(User::factory()->create()->givePermissionTo('usuarios.editar'));

        $html = view('components.row-actions', [
            'editUrl' => '/demo/1/edit',
            'editPermission' => 'usuarios.editar',
            'deleteUrl' => '/demo/1',
            'deletePermission' => 'usuarios.eliminar',
            'confirm' => '¿Eliminar?',
        ])->render();

        $this->assertStringContainsString('href="/demo/1/edit"', $html);
        $this->assertStringNotContainsString('data-delete-url', $html);
    }
}
