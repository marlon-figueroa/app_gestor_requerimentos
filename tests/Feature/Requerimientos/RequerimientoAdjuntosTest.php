<?php

namespace Tests\Feature\Requerimientos;

use App\Enums\EstadoRequerimiento;
use App\Models\Proyecto;
use App\Models\Requerimiento;
use App\Models\RequerimientoAdjunto;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RequerimientoAdjuntosTest extends TestCase
{
    use RefreshDatabase;

    private User $solicitante;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        Storage::fake(RequerimientoAdjunto::DISCO);
        $this->solicitante = User::factory()->create()->assignRole('Usuario');
    }

    private function requerimientoConAdjunto(string $tipo = 'png'): array
    {
        $requerimiento = Requerimiento::factory()->create(['solicitante_id' => $this->solicitante->id]);
        $archivo = $tipo === 'png'
            ? UploadedFile::fake()->image('captura.png')
            : UploadedFile::fake()->create('manual.pdf', 20, 'application/pdf');

        return [$requerimiento, RequerimientoAdjunto::guardarSubida($requerimiento, $archivo, $this->solicitante)];
    }

    public function test_adjuntos_al_crear_se_guardan_en_disco_privado(): void
    {
        $this->actingAs($this->solicitante)->post(route('requerimientos.store'), [
            'proyecto_id' => Proyecto::factory()->create()->id,
            'titulo' => 'Con adjuntos',
            'descripcion' => 'Detalle',
            'fecha_solicitud' => '01/10/2026',
            'fecha_vencimiento' => '15/10/2026',
            'adjuntos' => [
                UploadedFile::fake()->image('pantalla.png'),
                UploadedFile::fake()->image('foto.jpg'),
                UploadedFile::fake()->create('especificacion.pdf', 50, 'application/pdf'),
            ],
        ])->assertRedirect();

        $requerimiento = Requerimiento::firstOrFail();
        $this->assertCount(3, $requerimiento->adjuntos);

        $requerimiento->adjuntos->each(function (RequerimientoAdjunto $adjunto) use ($requerimiento) {
            $this->assertStringStartsWith("requerimientos/{$requerimiento->id}/adjuntos/", $adjunto->ruta);
            Storage::disk(RequerimientoAdjunto::DISCO)->assertExists($adjunto->ruta);
        });

        $this->assertSame(['pantalla.png', 'foto.jpg', 'especificacion.pdf'], $requerimiento->adjuntos->pluck('nombre_original')->all());
    }

    public function test_solo_se_aceptan_png_jpg_y_pdf(): void
    {
        $requerimiento = Requerimiento::factory()->create(['solicitante_id' => $this->solicitante->id]);

        foreach ([UploadedFile::fake()->create('notas.txt', 1, 'text/plain'), UploadedFile::fake()->image('animacion.gif')] as $archivo) {
            $this->actingAs($this->solicitante)
                ->postJson(route('requerimientos.adjuntos.store', $requerimiento), ['archivo' => $archivo])
                ->assertStatus(422)
                ->assertJsonValidationErrors('archivo');
        }

        $this->actingAs($this->solicitante)
            ->postJson(route('requerimientos.adjuntos.store', $requerimiento), ['archivo' => UploadedFile::fake()->image('ok.jpg')])
            ->assertOk();

        $this->assertSame(1, $requerimiento->adjuntos()->count());
    }

    public function test_no_se_suben_adjuntos_fuera_de_borrador_o_rechazado(): void
    {
        $requerimiento = Requerimiento::factory()
            ->estado(EstadoRequerimiento::EnRevision)
            ->create(['solicitante_id' => $this->solicitante->id]);

        $this->actingAs($this->solicitante)
            ->postJson(route('requerimientos.adjuntos.store', $requerimiento), ['archivo' => UploadedFile::fake()->image('a.png')])
            ->assertForbidden();
    }

    public function test_ver_adjunto_requiere_sesion_y_acceso_al_requerimiento(): void
    {
        [$requerimiento, $adjunto] = $this->requerimientoConAdjunto('pdf');
        $url = route('requerimientos.adjuntos.descargar', [$requerimiento, $adjunto]);

        $this->get($url)->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create()->assignRole('Usuario'))->get($url)->assertForbidden();

        $this->actingAs($this->solicitante)->get($url)
            ->assertOk()
            ->assertDownload('manual.pdf');

        $this->actingAs(User::factory()->create()->assignRole('Gestor'))
            ->get(route('requerimientos.adjuntos.show', [$requerimiento, $adjunto]))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_tras_iniciar_sesion_se_regresa_a_la_descarga(): void
    {
        [$requerimiento, $adjunto] = $this->requerimientoConAdjunto('pdf');
        $url = route('requerimientos.adjuntos.descargar', [$requerimiento, $adjunto]);

        $this->get($url)->assertRedirect(route('login'));

        $this->post(route('login'), ['email' => $this->solicitante->email, 'password' => 'password'])
            ->assertRedirect($url);
    }

    public function test_adjunto_de_otro_requerimiento_no_se_resuelve(): void
    {
        [, $adjunto] = $this->requerimientoConAdjunto();
        $otro = Requerimiento::factory()->create(['solicitante_id' => $this->solicitante->id]);

        $this->actingAs($this->solicitante)
            ->get(route('requerimientos.adjuntos.show', [$otro, $adjunto]))
            ->assertNotFound();
    }

    public function test_eliminar_adjunto_borra_el_archivo(): void
    {
        [$requerimiento, $adjunto] = $this->requerimientoConAdjunto();

        $this->actingAs($this->solicitante)
            ->deleteJson(route('requerimientos.adjuntos.destroy', [$requerimiento, $adjunto]))
            ->assertOk();

        $this->assertModelMissing($adjunto);
        Storage::disk(RequerimientoAdjunto::DISCO)->assertMissing($adjunto->ruta);
    }

    public function test_detalle_muestra_visor_de_adjuntos(): void
    {
        [$requerimiento, $adjunto] = $this->requerimientoConAdjunto();

        $this->actingAs($this->solicitante)->get(route('requerimientos.show', $requerimiento))
            ->assertOk()
            ->assertSee('id="visor-adjunto"', false)
            ->assertSee('data-visor-tipo="imagen"', false)
            ->assertSee(route('requerimientos.adjuntos.show', [$requerimiento, $adjunto]), false);
    }
}
