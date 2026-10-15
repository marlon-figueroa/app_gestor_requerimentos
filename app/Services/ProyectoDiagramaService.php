<?php

namespace App\Services;

use App\Enums\EstadoProyecto;
use App\Models\Proyecto;
use App\Models\ProyectoRevision;

class ProyectoDiagramaService
{
    public const INICIO = 'inicio';

    public const FIN = 'fin';

    /**
     * Máquina de estados UML del proyecto, con el mismo formato que el diagrama del requerimiento.
     *
     * @return array{estados: list<array<string, mixed>>, transiciones: list<array<string, mixed>>, pasos: list<array<string, mixed>>, ubicacion: array<string, array{int, int}>}
     */
    public function diagrama(Proyecto $proyecto): array
    {
        $pasos = $this->pasos($proyecto);
        $visitados = array_filter(array_column($pasos, 'destino'));
        $eventosPorEstado = [];

        foreach ($pasos as $paso) {
            $eventosPorEstado[$paso['destino']][] = [
                'numero' => $paso['numero'],
                'accion' => $paso['accion'],
                'actor' => $paso['actor'],
                'fecha' => $paso['fecha'],
                'observaciones' => $paso['observaciones'],
                'rechazo' => $paso['rechazo'],
            ];
        }

        $estados = array_map(fn (EstadoProyecto $estado) => [
            'id' => $estado->value,
            'nombre' => $estado->label(),
            'color' => $estado->hex(),
            'visitado' => in_array($estado->value, $visitados, true),
            'actual' => $estado === $proyecto->estado,
            'eventos' => $eventosPorEstado[$estado->value] ?? [],
        ], EstadoProyecto::cases());

        $transiciones = [];
        $agregar = function (string $origen, string $destino) use (&$transiciones): string {
            $clave = "{$origen}>{$destino}";
            $transiciones[$clave] ??= [
                'origen' => $origen,
                'destino' => $destino,
                'eventos' => [],
                'pasos' => [],
                'rechazo' => false,
            ];

            return $clave;
        };

        $agregar(self::INICIO, EstadoProyecto::Activo->value);

        foreach (EstadoProyecto::cases() as $estado) {
            $destinos = $estado->transicionesPermitidas();

            if ($destinos === []) {
                $agregar($estado->value, self::FIN);
            }

            foreach ($destinos as $destino) {
                $agregar($estado->value, $destino->value);
            }
        }

        foreach ($pasos as $paso) {
            $clave = $agregar($paso['origen'], $paso['destino']);
            $transiciones[$clave]['pasos'][] = $paso['numero'];
            $transiciones[$clave]['rechazo'] = $transiciones[$clave]['rechazo'] || $paso['rechazo'];

            if (! in_array($paso['evento'], $transiciones[$clave]['eventos'], true)) {
                $transiciones[$clave]['eventos'][] = $paso['evento'];
            }
        }

        return [
            'estados' => $estados,
            'transiciones' => array_values($transiciones),
            'pasos' => $pasos,
            'ubicacion' => EstadoProyecto::ubicacionDiagrama(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function pasos(Proyecto $proyecto): array
    {
        $proyecto->loadMissing(['revisiones.usuario']);

        $revisiones = $proyecto->revisiones
            ->sortBy([['created_at', 'asc'], ['id', 'asc']])
            ->values();

        $ultimo = $revisiones->count() - 1;

        return $revisiones
            ->map(fn (ProyectoRevision $revision, int $indice) => [
                'numero' => $indice + 1,
                'origen' => $revision->estado_anterior?->value ?? self::INICIO,
                'origenNombre' => $revision->estado_anterior?->label() ?? 'Inicio',
                'destino' => $revision->estado_nuevo->value,
                'etapa' => $revision->estado_nuevo->label(),
                'evento' => $revision->accion,
                'accion' => $revision->etiquetaAccion(),
                'actor' => $revision->usuario?->name ?? 'Usuario eliminado',
                'fecha' => $revision->created_at?->format('d/m/Y H:i'),
                'observaciones' => $revision->observaciones,
                'rechazo' => $revision->accion === 'cancelar',
                'actual' => $indice === $ultimo,
            ])
            ->all();
    }
}
