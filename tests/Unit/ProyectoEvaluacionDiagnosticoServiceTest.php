<?php

namespace Tests\Unit;

use App\Enums\ViabilidadPf;
use App\Models\Proyecto;
use App\Models\ProyectoEvaluacionPf;
use App\Services\ProyectoEvaluacionDiagnosticoService;
use App\Services\ProyectoPuntosFuncionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProyectoEvaluacionDiagnosticoServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_estima_tiempo_desde_pf_ajustados(): void
    {
        $proyecto = Proyecto::factory()->create(['fecha_finalizacion' => today()->addMonths(6)]);
        $evaluacion = new ProyectoEvaluacionPf(['proyecto_id' => $proyecto->id]);

        app(ProyectoPuntosFuncionService::class)->sincronizarEvaluacion($evaluacion, [
            ['tipo' => 'ei', 'nombre' => 'Alta', 'det' => 4, 'referencias' => 1],
            ['tipo' => 'ilf', 'nombre' => 'Maestro', 'det' => 30, 'referencias' => 3],
            ['tipo' => 'eo', 'nombre' => 'Reporte', 'det' => 20, 'referencias' => 2],
        ], array_fill(0, 14, 3), ['nombre' => 'Test']);

        $evaluacion->refresh();
        $horasEsperadas = round((float) $evaluacion->total_ajustado * 8, 1);
        $diagnostico = app(ProyectoEvaluacionDiagnosticoService::class)->diagnosticar($proyecto, $evaluacion);

        $this->assertSame($horasEsperadas, $diagnostico['tiempo']['horas']);
        $this->assertGreaterThan(0, $diagnostico['tiempo']['dias_laborables']);
        $this->assertContains($diagnostico['viabilidad'], [ViabilidadPf::Alta, ViabilidadPf::Media, ViabilidadPf::Baja]);
    }

    public function test_marca_no_viable_si_el_plazo_es_insuficiente(): void
    {
        $proyecto = Proyecto::factory()->create(['fecha_finalizacion' => today()->addWeekdays(5)]);
        $evaluacion = new ProyectoEvaluacionPf(['proyecto_id' => $proyecto->id]);
        $componentes = [];
        for ($i = 0; $i < 15; $i++) {
            $componentes[] = ['tipo' => 'ilf', 'nombre' => "ILF {$i}", 'det' => 30, 'referencias' => 3];
        }

        app(ProyectoPuntosFuncionService::class)->sincronizarEvaluacion(
            $evaluacion,
            $componentes,
            array_fill(0, 14, 3),
            ['nombre' => 'Grande'],
        );

        $evaluacion->refresh();
        $diagnostico = app(ProyectoEvaluacionDiagnosticoService::class)->diagnosticar($proyecto, $evaluacion);

        $this->assertSame(ViabilidadPf::NoViable, $diagnostico['viabilidad']);
        $this->assertFalse($diagnostico['plazo']['cumple_plazo']);
    }
}
