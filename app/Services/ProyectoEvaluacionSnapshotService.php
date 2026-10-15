<?php

namespace App\Services;

use App\Enums\EstadoEvaluacionPf;
use App\Models\Proyecto;
use App\Models\ProyectoEvaluacionPf;

class ProyectoEvaluacionSnapshotService
{
    public function __construct(
        private ProyectoEvaluacionDiagnosticoService $diagnostico,
    ) {}

    public function persistirDiagnostico(Proyecto $proyecto, ProyectoEvaluacionPf $evaluacion): void
    {
        $proyecto->loadMissing('requerimientos');
        $evaluacion->loadMissing('componentes');

        $resultado = $this->diagnostico->diagnosticar($proyecto, $evaluacion);
        $snapshot = $resultado;
        $snapshot['viabilidad'] = $resultado['viabilidad']->value;

        $evaluacion->forceFill([
            'viabilidad_diagnostico' => $resultado['viabilidad']->value,
            'puntaje_diagnostico' => $resultado['puntaje'],
            'diagnostico_snapshot' => $snapshot,
        ])->saveQuietly();
    }

    public function marcarBaseline(Proyecto $proyecto, ProyectoEvaluacionPf $evaluacion): void
    {
        $proyecto->evaluacionesPf()->update(['es_baseline' => false]);
        $evaluacion->forceFill([
            'es_baseline' => true,
            'estado_evaluacion' => EstadoEvaluacionPf::Aprobada,
        ])->save();
    }

    public function duplicar(ProyectoEvaluacionPf $origen, int $usuarioId): ProyectoEvaluacionPf
    {
        $origen->load('componentes');

        $copia = $origen->replicate([
            'es_baseline',
            'viabilidad_diagnostico',
            'puntaje_diagnostico',
            'diagnostico_snapshot',
        ]);
        $copia->nombre = $origen->nombre.' (copia)';
        $copia->estado_evaluacion = EstadoEvaluacionPf::Borrador;
        $copia->es_baseline = false;
        $copia->creado_por = $usuarioId;
        $copia->save();

        foreach ($origen->componentes as $componente) {
            $nuevo = $componente->replicate();
            $nuevo->evaluacion_id = $copia->id;
            $nuevo->save();
        }

        return $copia->load('componentes');
    }

    public static function evaluacionVigente(Proyecto $proyecto): ?ProyectoEvaluacionPf
    {
        $evaluaciones = $proyecto->relationLoaded('evaluacionesPf')
            ? $proyecto->evaluacionesPf
            : $proyecto->evaluacionesPf()->with('componentes')->get();

        return $evaluaciones->firstWhere('es_baseline', true)
            ?? $evaluaciones->firstWhere('estado_evaluacion', EstadoEvaluacionPf::Aprobada)
            ?? $evaluaciones->first();
    }
}
