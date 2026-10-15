<?php

namespace Tests\Feature\Requerimientos;

use App\Models\Requerimiento;
use App\Models\RequerimientoAdjunto;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RequerimientoPdfTest extends TestCase
{
    use RefreshDatabase;

    private User $solicitante;

    private Requerimiento $requerimiento;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        Storage::fake(RequerimientoAdjunto::DISCO);

        $this->solicitante = User::factory()->create()->assignRole('Usuario');
        $this->requerimiento = Requerimiento::factory()->create([
            'solicitante_id' => $this->solicitante->id,
            'titulo' => 'Portal de proveedores',
        ]);
        $this->requerimiento->casosUso()->create(['codigo' => 'CU-01', 'nombre' => 'Registrar proveedor', 'pantalla' => 'Alta de proveedor']);
    }

    public function test_imagenes_se_incrustan_y_pdf_se_enlaza_a_descarga_protegida(): void
    {
        $imagen = RequerimientoAdjunto::guardarSubida($this->requerimiento, UploadedFile::fake()->image('pantalla.png', 40, 40), $this->solicitante);
        $documento = RequerimientoAdjunto::guardarSubida($this->requerimiento, UploadedFile::fake()->create('anexo.pdf', 10, 'application/pdf'), $this->solicitante);

        $this->actingAs($this->solicitante);
        $html = view('pdf.requerimiento', ['requerimiento' => $this->requerimiento->load(['casosUso', 'adjuntos', 'revisiones'])])->render();

        $this->assertStringContainsString('data:image/png;base64,', $html);
        $this->assertStringNotContainsString(route('requerimientos.adjuntos.descargar', [$this->requerimiento, $imagen]), $html);
        $this->assertStringContainsString(e(route('requerimientos.adjuntos.descargar', [$this->requerimiento, $documento])), $html);
        $this->assertStringNotContainsString('data:application/pdf', $html);
        $this->assertStringContainsString('Registrar proveedor', $html);
    }

    public function test_exportar_devuelve_pdf(): void
    {
        $this->actingAs($this->solicitante)->get(route('requerimientos.pdf', $this->requerimiento))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_otro_solicitante_no_puede_exportar(): void
    {
        $this->actingAs(User::factory()->create()->assignRole('Usuario'))
            ->get(route('requerimientos.pdf', $this->requerimiento))
            ->assertForbidden();
    }
}
