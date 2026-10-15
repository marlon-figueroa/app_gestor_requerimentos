<?php

namespace App\Services;

use App\Enums\ComplejidadPf;
use App\Enums\EstadoProyecto;
use App\Enums\EstadoRequerimiento;
use App\Enums\ViabilidadPf;
use App\Models\Proyecto;
use App\Models\ProyectoEvaluacionPf;
use Carbon\CarbonInterface;

class ProyectoEvaluacionDiagnosticoService
{
    /**
     * @return array{
     *     viabilidad: ViabilidadPf,
     *     puntaje: int,
     *     tiempo: array{horas: float, dias_laborables: int, semanas: float, fecha_fin_estimada: ?string},
     *     plazo: array{dias_disponibles: ?int, cumple_plazo: ?bool, holgura_dias: ?int},
     *     parametros: array<string, float|int>,
     *     hallazgos: list<array{tipo: string, mensaje: string}>
     * }
     */
    public function diagnosticar(Proyecto $proyecto, ProyectoEvaluacionPf $evaluacion): array
    {
        $evaluacion->loadMissing('componentes');
        $proyecto->loadMissing('requerimientos');

        $config = config('puntos_funcion');
        $prod = $config['productividad'];
        $umbrales = $config['viabilidad'];

        $pfAjustados = (float) $evaluacion->total_ajustado;
        $horas = round($pfAjustados * $prod['horas_por_pf'], 1);
        $diasLaborables = (int) max(1, (int) ceil($horas / max($prod['horas_jornada'], 1)));
        $semanas = round($diasLaborables / max($prod['dias_semana'], 1), 1);
        $fechaFin = $this->sumarDiasLaborables(today(), $diasLaborables);

        $hallazgos = [];
        $puntaje = 100;
        $bloqueoPlazo = false;

        if ($pfAjustados <= 0 || $evaluacion->componentes->isEmpty()) {
            return $this->resultado(
                ViabilidadPf::NoViable,
                0,
                [
                    'horas' => $horas,
                    'dias_laborables' => $diasLaborables,
                    'semanas' => $semanas,
                    'fecha_fin_estimada' => null,
                ],
                ['dias_disponibles' => null, 'cumple_plazo' => null, 'holgura_dias' => null],
                $prod,
                [['tipo' => 'critico', 'mensaje' => 'La evaluación no tiene componentes funcionales; no es posible estimar esfuerzo ni viabilidad.']],
            );
        }

        if ($pfAjustados >= $umbrales['pf_elevado']) {
            $puntaje -= 12;
            $hallazgos[] = [
                'tipo' => 'alerta',
                'mensaje' => sprintf(
                    'El tamaño funcional es elevado (%s PF ajustados). Conviene dividir entregas o replanificar el alcance.',
                    number_format($pfAjustados, 2, ',', '.'),
                ),
            ];
        }

        if ((float) $evaluacion->factor_ajuste >= $umbrales['vaf_alto']) {
            $puntaje -= 10;
            $hallazgos[] = [
                'tipo' => 'alerta',
                'mensaje' => sprintf(
                    'El factor de ajuste (VAF %s) indica alta complejidad técnica del entorno.',
                    number_format((float) $evaluacion->factor_ajuste, 2, ',', '.'),
                ),
            ];
        }

        if ($evaluacion->suma_influencia >= $umbrales['suma_influencia_alta']) {
            $puntaje -= 8;
            $hallazgos[] = [
                'tipo' => 'alerta',
                'mensaje' => 'La suma de grado de influencia supera el umbral recomendado; el riesgo de desvíos en implementación aumenta.',
            ];
        }

        $totalComponentes = $evaluacion->componentes->count();
        $altaComplejidad = $evaluacion->componentes->where('complejidad', ComplejidadPf::Alta)->count();
        $ratioAlta = $totalComponentes > 0 ? $altaComplejidad / $totalComponentes : 0;

        if ($ratioAlta >= $umbrales['ratio_complejidad_alta']) {
            $puntaje -= 12;
            $hallazgos[] = [
                'tipo' => 'alerta',
                'mensaje' => sprintf(
                    'Más del %d %% de los componentes tienen complejidad alta (%d de %d).',
                    (int) round($ratioAlta * 100),
                    $altaComplejidad,
                    $totalComponentes,
                ),
            ];
        }

        $plazo = $this->analizarPlazo($proyecto, $diasLaborables, (float) $umbrales['margen_plazo'], $hallazgos, $puntaje, $bloqueoPlazo);
        $bloqueoPlazo = $plazo['bloqueo'];
        $puntaje = $plazo['puntaje'];

        if ($proyecto->estado !== EstadoProyecto::Activo) {
            $puntaje -= 5;
            $hallazgos[] = [
                'tipo' => 'info',
                'mensaje' => 'El proyecto ya está cerrado; el diagnóstico sirve como referencia histórica.',
            ];
        }

        $pendientes = $proyecto->requerimientos->where('estado', '!=', EstadoRequerimiento::Finalizado)->count();
        if ($pendientes > 0 && $proyecto->estado === EstadoProyecto::Activo) {
            $hallazgos[] = [
                'tipo' => 'info',
                'mensaje' => sprintf(
                    'Hay %d requerimiento(s) aún sin finalizar; el tiempo estimado no sustituye el seguimiento del backlog.',
                    $pendientes,
                ),
            ];
        }

        $hallazgos[] = [
            'tipo' => 'info',
            'mensaje' => sprintf(
                'Estimación: %s horas (~%d días laborables, %s semanas con 1 recurso a tiempo completo), asumiendo %s h/PF ajustado.',
                number_format($horas, 1, ',', '.'),
                $diasLaborables,
                number_format($semanas, 1, ',', '.'),
                number_format($prod['horas_por_pf'], 1, ',', '.'),
            ),
        ];

        $viabilidad = $this->clasificarViabilidad($puntaje, $bloqueoPlazo, $umbrales);

        if ($viabilidad === ViabilidadPf::Alta) {
            $hallazgos[] = ['tipo' => 'info', 'mensaje' => 'El alcance funcional y el plazo son coherentes con la productividad de referencia.'];
        }

        return $this->resultado(
            $viabilidad,
            max(0, min(100, $puntaje)),
            [
                'horas' => $horas,
                'dias_laborables' => $diasLaborables,
                'semanas' => $semanas,
                'fecha_fin_estimada' => $fechaFin->format('d/m/Y'),
            ],
            [
                'dias_disponibles' => $plazo['dias_disponibles'],
                'cumple_plazo' => $plazo['cumple_plazo'],
                'holgura_dias' => $plazo['holgura_dias'],
            ],
            [
                'horas_por_pf' => $prod['horas_por_pf'],
                'horas_jornada' => $prod['horas_jornada'],
            ],
            $hallazgos,
        );
    }

    /**
     * @param  list<array{tipo: string, mensaje: string}>  $hallazgos
     * @return array{dias_disponibles: ?int, cumple_plazo: ?bool, holgura_dias: ?int, puntaje: int, bloqueo: bool}
     */
    private function analizarPlazo(
        Proyecto $proyecto,
        int $diasLaborables,
        float $margen,
        array &$hallazgos,
        int $puntaje,
        bool $bloqueo,
    ): array {
        if ($proyecto->fecha_finalizacion === null) {
            $hallazgos[] = [
                'tipo' => 'info',
                'mensaje' => 'El proyecto no tiene fecha de finalización; no se contrasta el plazo calendario.',
            ];

            return [
                'dias_disponibles' => null,
                'cumple_plazo' => null,
                'holgura_dias' => null,
                'puntaje' => $puntaje,
                'bloqueo' => $bloqueo,
            ];
        }

        $hoy = today();
        $diasDisponibles = $this->diasLaborablesEntre($hoy, $proyecto->fecha_finalizacion);
        $diasRequeridos = (int) ceil($diasLaborables * $margen);
        $holgura = $diasDisponibles - $diasRequeridos;
        $cumple = $holgura >= 0;

        if ($diasDisponibles < 0) {
            $bloqueo = true;
            $puntaje -= 40;
            $hallazgos[] = [
                'tipo' => 'critico',
                'mensaje' => 'La fecha de finalización del proyecto ya venció; el cronograma actual no es viable.',
            ];
        } elseif (! $cumple) {
            $bloqueo = true;
            $puntaje -= 35;
            $hallazgos[] = [
                'tipo' => 'critico',
                'mensaje' => sprintf(
                    'Se requieren al menos %d días laborables (con margen del %d %%) y solo quedan %d hasta la fecha de finalización (%s).',
                    $diasRequeridos,
                    (int) (($margen - 1) * 100),
                    max(0, $diasDisponibles),
                    $proyecto->fecha_finalizacion->format('d/m/Y'),
                ),
            ];
        } elseif ($holgura <= 5) {
            $puntaje -= 8;
            $hallazgos[] = [
                'tipo' => 'alerta',
                'mensaje' => sprintf(
                    'El plazo es ajustado: holgura de %d día(s) laborable(s) respecto a la fecha %s.',
                    $holgura,
                    $proyecto->fecha_finalizacion->format('d/m/Y'),
                ),
            ];
        } else {
            $hallazgos[] = [
                'tipo' => 'info',
                'mensaje' => sprintf(
                    'La fecha de finalización (%s) deja %d días laborables de holgura frente a la estimación con margen.',
                    $proyecto->fecha_finalizacion->format('d/m/Y'),
                    $holgura,
                ),
            ];
        }

        return [
            'dias_disponibles' => max(0, $diasDisponibles),
            'cumple_plazo' => $cumple,
            'holgura_dias' => $holgura,
            'puntaje' => $puntaje,
            'bloqueo' => $bloqueo,
        ];
    }

    /**
     * @param  array<string, float|int>  $parametros
     * @param  list<array{tipo: string, mensaje: string}>  $hallazgos
     * @return array{
     *     viabilidad: ViabilidadPf,
     *     puntaje: int,
     *     tiempo: array,
     *     plazo: array,
     *     parametros: array,
     *     hallazgos: list<array{tipo: string, mensaje: string}>
     * }
     */
    private function resultado(
        ViabilidadPf $viabilidad,
        int $puntaje,
        array $tiempo,
        array $plazo,
        array $parametros,
        array $hallazgos,
    ): array {
        return [
            'viabilidad' => $viabilidad,
            'puntaje' => $puntaje,
            'tiempo' => $tiempo,
            'plazo' => $plazo,
            'parametros' => $parametros,
            'hallazgos' => $hallazgos,
        ];
    }

    /**
     * @param  array<string, int|float>  $umbrales
     */
    private function clasificarViabilidad(int $puntaje, bool $bloqueoPlazo, array $umbrales): ViabilidadPf
    {
        if ($bloqueoPlazo) {
            return ViabilidadPf::NoViable;
        }

        if ($puntaje >= $umbrales['puntaje_alta']) {
            return ViabilidadPf::Alta;
        }

        if ($puntaje >= $umbrales['puntaje_media']) {
            return ViabilidadPf::Media;
        }

        return ViabilidadPf::Baja;
    }

    private function diasLaborablesEntre(CarbonInterface $desde, CarbonInterface $hasta): int
    {
        $signo = $desde->lte($hasta) ? 1 : -1;
        $cursor = $desde->copy()->startOfDay();
        $fin = $hasta->copy()->startOfDay();
        $dias = 0;

        if ($signo < 0) {
            [$cursor, $fin] = [$fin, $cursor];
        }

        while ($cursor->lt($fin)) {
            if (! $cursor->isWeekend()) {
                $dias++;
            }
            $cursor->addDay();
        }

        return $dias * $signo;
    }

    private function sumarDiasLaborables(CarbonInterface $fecha, int $diasLaborables): CarbonInterface
    {
        $cursor = $fecha->copy()->startOfDay();
        $pendientes = max(0, $diasLaborables);

        while ($pendientes > 0) {
            $cursor->addDay();
            if (! $cursor->isWeekend()) {
                $pendientes--;
            }
        }

        return $cursor;
    }
}
