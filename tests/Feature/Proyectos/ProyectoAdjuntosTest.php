<?php

namespace Tests\Feature\Proyectos;

use App\Enums\EstadoProyecto;
use App\Models\Proyecto;
use App\Models\ProyectoAdjunto;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProyectoAdjuntosTest extends TestCase
{
    use RefreshDatabase;

    private User $gestor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        Storage::fake(ProyectoAdjunto::DISCO);
        $this->gestor = User::factory()->create()->assignRole('Gestor');
    }

    public function test_adjuntos_al_crear_se_guardan_en_disco_privado(): void
    {
        $this->actingAs($this->gestor)->post(route('proyectos.store'), [
            'nombre' => 'Con documentos',
            'descripcion' => 'Detalle',
            'responsable_id' => $this->gestor->id,
            'fecha_finalizacion' => today()->addMonth()->format('d/m/Y'),
            'adjuntos' => [
                UploadedFile::fake()->image('plan.png'),
                UploadedFile::fake()->create('acta.pdf', 50, 'application/pdf'),
            ],
        ])->assertRedirect();

        $proyecto = Proyecto::firstOrFail();
        $this->assertCount(2, $proyecto->adjuntos);

        $proyecto->adjuntos->each(function (ProyectoAdjunto $adjunto) use ($proyecto) {
            $this->assertStringStartsWith("proyectos/{$proyecto->id}/adjuntos/", $adjunto->ruta);
            Storage::disk(ProyectoAdjunto::DISCO)->assertExists($adjunto->ruta);
        });
    }

    public function test_subida_directa_valida_tipos_y_se_puede_ver_y_eliminar(): void
    {
        $proyecto = Proyecto::factory()->create(['responsable_id' => $this->gestor->id]);

        $this->actingAs($this->gestor)
            ->postJson(route('proyectos.adjuntos.store', $proyecto), ['archivo' => UploadedFile::fake()->create('notas.txt', 1, 'text/plain')])
            ->assertStatus(422);

        $this->actingAs($this->gestor)
            ->postJson(route('proyectos.adjuntos.store', $proyecto), ['archivo' => UploadedFile::fake()->create('acta.pdf', 20, 'application/pdf')])
            ->assertOk();

        $adjunto = $proyecto->adjuntos()->firstOrFail();

        $this->actingAs($this->gestor)->get(route('proyectos.adjuntos.show', [$proyecto, $adjunto]))->assertOk();
        $this->actingAs($this->gestor)->get(route('proyectos.adjuntos.descargar', [$proyecto, $adjunto]))->assertOk()->assertDownload('acta.pdf');

        $this->actingAs($this->gestor)->deleteJson(route('proyectos.adjuntos.destroy', [$proyecto, $adjunto]))->assertOk();
        Storage::disk(ProyectoAdjunto::DISCO)->assertMissing($adjunto->ruta);
    }

    public function test_un_proyecto_cerrado_no_acepta_adjuntos(): void
    {
        $proyecto = Proyecto::factory()->estado(EstadoProyecto::Finalizado)->create(['responsable_id' => $this->gestor->id]);

        $this->actingAs($this->gestor)
            ->postJson(route('proyectos.adjuntos.store', $proyecto), ['archivo' => UploadedFile::fake()->image('foto.png')])
            ->assertForbidden();
    }

    public function test_el_adjunto_debe_pertenecer_al_proyecto(): void
    {
        $proyecto = Proyecto::factory()->create();
        $otro = Proyecto::factory()->create();
        $adjunto = ProyectoAdjunto::guardarSubida($otro, UploadedFile::fake()->image('ajeno.png'), $this->gestor);

        $this->actingAs($this->gestor)->get(route('proyectos.adjuntos.show', [$proyecto, $adjunto]))->assertNotFound();
    }
}
