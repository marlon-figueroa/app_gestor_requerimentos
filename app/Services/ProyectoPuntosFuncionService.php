<?php

namespace App\Services;

use App\Enums\CaracteristicaGeneralPf;
use App\Enums\ComplejidadPf;
use App\Enums\TipoComponentePf;
use App\Models\ProyectoEvaluacionComponentePf;
use App\Models\ProyectoEvaluacionPf;
use Illuminate\Support\Facades\DB;

class ProyectoPuntosFuncionService
{
    public const CARACTERISTICAS_TOTAL = 14;

    /** Matriz DET × FTR (filas: 1-4, 5-15, 16+; columnas: 0-1, 2, 3+ FTR). */
    private const MATRIZ_TRANSACCION = [
        [
            ComplejidadPf::Baja,
            ComplejidadPf::Baja,
            ComplejidadPf::Media,
        ],
        [
            ComplejidadPf::Baja,
            ComplejidadPf::Media,
            ComplejidadPf::Alta,
        ],
        [
            ComplejidadPf::Media,
            ComplejidadPf::Alta,
            ComplejidadPf::Alta,
        ],
    ];

    /** Matriz DET × RET para ILF/EIF (filas: 1-19, 20-50, 51+; columnas: 1, 2-5, 6+ RET). */
    private const MATRIZ_ARCHIVO = [
        [
            ComplejidadPf::Baja,
            ComplejidadPf::Baja,
            ComplejidadPf::Media,
        ],
        [
            ComplejidadPf::Baja,
            ComplejidadPf::Media,
            ComplejidadPf::Alta,
        ],
        [
            ComplejidadPf::Media,
            ComplejidadPf::Alta,
            ComplejidadPf::Alta,
        ],
    ];

    /** @var array<string, array<int, int>> */
    private const PESOS = [
        'ei' => [3, 4, 6],
        'eo' => [4, 5, 7],
        'eq' => [3, 4, 6],
        'ilf' => [7, 10, 15],
        'eif' => [5, 7, 10],
    ];

    /**
     * @return array{complejidad: ComplejidadPf, puntos: int}
     */
    public function evaluarComponente(TipoComponentePf $tipo, int $det, int $referencias): array
    {
        $complejidad = $tipo->usaFtr()
            ? self::MATRIZ_TRANSACCION[$this->indiceDetTransaccion($det)][$this->indiceFtr($referencias)]
            : self::MATRIZ_ARCHIVO[$this->indiceDetArchivo($det)][$this->indiceRet($referencias)];

        $indice = match ($complejidad) {
            ComplejidadPf::Baja => 0,
            ComplejidadPf::Media => 1,
            ComplejidadPf::Alta => 2,
        };

        return [
            'complejidad' => $complejidad,
            'puntos' => self::PESOS[$tipo->value][$indice],
        ];
    }

    /**
     * @param  iterable<ProyectoEvaluacionComponentePf|array{puntos: int}>  $componentes
     */
    public function totalSinAjuste(iterable $componentes): int
    {
        $total = 0;

        foreach ($componentes as $componente) {
            $total += is_array($componente) ? ($componente['puntos'] ?? 0) : $componente->puntos;
        }

        return $total;
    }

    /**
     * @param  array<int, int>  $caracteristicas  Exactamente 14 valores 0-5
     * @return array{suma_influencia: int, factor_ajuste: float}
     */
    public function factorAjuste(array $caracteristicas): array
    {
        $suma = (int) array_sum($caracteristicas);

        return [
            'suma_influencia' => $suma,
            'factor_ajuste' => round(0.65 + ($suma * 0.01), 2),
        ];
    }

    public function totalAjustado(int $sinAjuste, float $factorAjuste): float
    {
        return round($sinAjuste * $factorAjuste, 2);
    }

    /**
     * @param  array<int, array{tipo: string, nombre: string, descripcion?: ?string, det: int, referencias: int}>  $componentes
     * @param  array<int, int>  $caracteristicas
     */
    public function sincronizarEvaluacion(
        ProyectoEvaluacionPf $evaluacion,
        array $componentes,
        array $caracteristicas,
        array $datosEvaluacion = [],
    ): ProyectoEvaluacionPf {
        return DB::transaction(function () use ($evaluacion, $componentes, $caracteristicas, $datosEvaluacion) {
            $evaluacion->fill($datosEvaluacion);
            $evaluacion->caracteristicas = $this->normalizarCaracteristicas($caracteristicas);

            $preparados = [];
            $orden = 1;

            foreach ($componentes as $fila) {
                $tipo = TipoComponentePf::from($fila['tipo']);
                $resultado = $this->evaluarComponente($tipo, (int) $fila['det'], (int) $fila['referencias']);

                $preparados[] = [
                    'tipo' => $tipo->value,
                    'nombre' => $fila['nombre'],
                    'descripcion' => $fila['descripcion'] ?? null,
                    'det' => (int) $fila['det'],
                    'referencias' => (int) $fila['referencias'],
                    'requerimiento_id' => filled($fila['requerimiento_id'] ?? null) ? (int) $fila['requerimiento_id'] : null,
                    'complejidad' => $resultado['complejidad']->value,
                    'puntos' => $resultado['puntos'],
                    'orden' => $orden++,
                ];
            }

            $evaluacion->total_sin_ajuste = $this->totalSinAjuste($preparados);
            $ajuste = $this->factorAjuste($evaluacion->caracteristicas);
            $evaluacion->suma_influencia = $ajuste['suma_influencia'];
            $evaluacion->factor_ajuste = $ajuste['factor_ajuste'];
            $evaluacion->total_ajustado = $this->totalAjustado($evaluacion->total_sin_ajuste, $evaluacion->factor_ajuste);
            $evaluacion->save();

            $evaluacion->componentes()->delete();
            $evaluacion->componentes()->createMany($preparados);

            return $evaluacion->load('componentes');
        });
    }

    /**
     * @return array<int, int>
     */
    public function caracteristicasPorDefecto(): array
    {
        return array_fill(0, self::CARACTERISTICAS_TOTAL, 3);
    }

    /**
     * @return array<int, int>
     */
    public function normalizarCaracteristicas(array $caracteristicas): array
    {
        $normalizadas = [];

        for ($i = 0; $i < self::CARACTERISTICAS_TOTAL; $i++) {
            $valor = (int) ($caracteristicas[$i] ?? 3);
            $normalizadas[$i] = max(0, min(5, $valor));
        }

        return $normalizadas;
    }

    /**
     * @return array<string, array{conteo: int, puntos: int}>
     */
    public function resumenPorTipo(ProyectoEvaluacionPf $evaluacion): array
    {
        $resumen = [];

        foreach (TipoComponentePf::cases() as $tipo) {
            $resumen[$tipo->value] = ['conteo' => 0, 'puntos' => 0];
        }

        foreach ($evaluacion->componentes as $componente) {
            $clave = $componente->tipo->value;
            $resumen[$clave]['conteo']++;
            $resumen[$clave]['puntos'] += $componente->puntos;
        }

        return $resumen;
    }

    /**
     * Matrices y pesos para la vista previa en el cliente (mismos valores que PHP).
     *
     * @return array<string, mixed>
     */
    public function definicionCliente(): array
    {
        return [
            'matrizTransaccion' => array_map(
                fn (array $fila) => array_map(fn (ComplejidadPf $c) => $c->value, $fila),
                self::MATRIZ_TRANSACCION,
            ),
            'matrizArchivo' => array_map(
                fn (array $fila) => array_map(fn (ComplejidadPf $c) => $c->value, $fila),
                self::MATRIZ_ARCHIVO,
            ),
            'pesos' => self::PESOS,
            'caracteristicas' => array_map(
                fn (CaracteristicaGeneralPf $c) => [
                    'label' => $c->label(),
                    'descripcion' => $c->descripcion(),
                ],
                CaracteristicaGeneralPf::ordenadas(),
            ),
        ];
    }

    private function indiceDetTransaccion(int $det): int
    {
        if ($det <= 4) {
            return 0;
        }

        if ($det <= 15) {
            return 1;
        }

        return 2;
    }

    private function indiceFtr(int $ftr): int
    {
        if ($ftr <= 1) {
            return 0;
        }

        if ($ftr === 2) {
            return 1;
        }

        return 2;
    }

    private function indiceDetArchivo(int $det): int
    {
        if ($det <= 19) {
            return 0;
        }

        if ($det <= 50) {
            return 1;
        }

        return 2;
    }

    private function indiceRet(int $ret): int
    {
        if ($ret <= 1) {
            return 0;
        }

        if ($ret <= 5) {
            return 1;
        }

        return 2;
    }
}
