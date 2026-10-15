<?php

namespace App\Services;

use App\Enums\EstadoRequerimiento;
use App\Models\Requerimiento;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class RequerimientoDashboardService
{
    private const SEMANAS = 12;

    private const MESES = 12;

    private const ANIOS = 5;

    /**
     * Series de barras (evolución) y pastel (estado) para los periodos del inicio.
     *
     * @return array<string, array<string, mixed>>
     */
    public function graficas(User $usuario): array
    {
        $hoy = now()->startOfDay();
        $desde = $hoy->copy()->startOfYear()->subYears(self::ANIOS - 1);

        $filas = Requerimiento::query()
            ->visiblesPara($usuario)
            ->whereDate('fecha_solicitud', '>=', $desde->toDateString())
            ->whereDate('fecha_solicitud', '<=', $hoy->toDateString())
            ->get(['fecha_solicitud', 'estado']);

        return [
            'semanal' => $this->periodo($filas, $this->cubetas('semanal', $hoy), 'Últimas '.self::SEMANAS.' semanas'),
            'mensual' => $this->periodo($filas, $this->cubetas('mensual', $hoy), 'Últimos '.self::MESES.' meses'),
            'anual' => $this->periodo($filas, $this->cubetas('anual', $hoy), 'Últimos '.self::ANIOS.' años'),
        ];
    }

    /**
     * @param  Collection<int, Requerimiento>  $filas
     * @param  list<array{inicio: string, fin: string, etiqueta: string}>  $cubetas
     * @return array<string, mixed>
     */
    private function periodo($filas, array $cubetas, string $descripcion): array
    {
        $barras = array_fill(0, count($cubetas), 0);
        $porEstado = [];

        foreach ($filas as $fila) {
            $dia = $fila->fecha_solicitud->toDateString();
            $indice = $this->indiceCubeta($dia, $cubetas);

            if ($indice === null) {
                continue;
            }

            $barras[$indice]++;
            $clave = $fila->estado->value;
            $porEstado[$clave] = ($porEstado[$clave] ?? 0) + 1;
        }

        $pastel = [];

        foreach (EstadoRequerimiento::cases() as $estado) {
            $valor = $porEstado[$estado->value] ?? 0;

            if ($valor === 0) {
                continue;
            }

            $pastel[] = [
                'etiqueta' => $estado->label(),
                'valor' => $valor,
                'color' => $estado->hex(),
            ];
        }

        $total = array_sum($barras);

        return [
            'descripcion' => $descripcion,
            'resumen' => $total === 1
                ? "1 requerimiento · {$descripcion}"
                : "{$total} requerimientos · {$descripcion}",
            'etiquetas' => array_column($cubetas, 'etiqueta'),
            'barras' => $barras,
            'pastel' => $pastel,
            'total' => $total,
        ];
    }

    /**
     * @return list<array{inicio: string, fin: string, etiqueta: string}>
     */
    private function cubetas(string $periodo, Carbon $hoy): array
    {
        $cubetas = match ($periodo) {
            'semanal' => collect(range(self::SEMANAS - 1, 0))->map(function (int $atras) use ($hoy) {
                $inicio = $hoy->copy()->startOfWeek(Carbon::MONDAY)->subWeeks($atras);

                return [
                    'inicio' => $inicio->toDateString(),
                    'fin' => $inicio->copy()->endOfWeek(Carbon::SUNDAY)->toDateString(),
                    'etiqueta' => $inicio->translatedFormat('d M'),
                ];
            }),
            'mensual' => collect(range(self::MESES - 1, 0))->map(function (int $atras) use ($hoy) {
                $inicio = $hoy->copy()->startOfMonth()->subMonths($atras);

                return [
                    'inicio' => $inicio->toDateString(),
                    'fin' => $inicio->copy()->endOfMonth()->toDateString(),
                    'etiqueta' => $inicio->translatedFormat('M Y'),
                ];
            }),
            'anual' => collect(range(self::ANIOS - 1, 0))->map(function (int $atras) use ($hoy) {
                $inicio = $hoy->copy()->startOfYear()->subYears($atras);

                return [
                    'inicio' => $inicio->toDateString(),
                    'fin' => $inicio->copy()->endOfYear()->toDateString(),
                    'etiqueta' => $inicio->format('Y'),
                ];
            }),
        };

        return $cubetas->all();
    }

    /**
     * @param  list<array{inicio: string, fin: string, etiqueta: string}>  $cubetas
     */
    private function indiceCubeta(string $dia, array $cubetas): ?int
    {
        foreach ($cubetas as $indice => $cubeta) {
            if ($dia >= $cubeta['inicio'] && $dia <= $cubeta['fin']) {
                return $indice;
            }
        }

        return null;
    }
}
