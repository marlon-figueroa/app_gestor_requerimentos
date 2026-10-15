<?php

namespace App\Services;

use App\Enums\EstadoRequerimiento;
use App\Models\Requerimiento;
use App\Models\RequerimientoRevision;

class RequerimientoDiagramaService
{
    /**
     * Acciones que ejecuta el solicitante. El resto las ejecuta quien actúa como gestor
     * en esa transición, aunque sea otra persona o el mismo solicitante.
     */
    private const ACCIONES_SOLICITANTE = ['crear', 'enviar'];

    public const INICIO = 'inicio';

    public const FIN = 'fin';

    /**
     * Máquina de estados UML: todos los estados del flujo, las transiciones permitidas
     * y las que realmente recorrió el requerimiento, con sus pasos en orden.
     *
     * @return array{estados: list<array<string, mixed>>, transiciones: list<array<string, mixed>>, pasos: list<array<string, mixed>>}
     */
    public function diagrama(Requerimiento $requerimiento): array
    {
        $pasos = $this->pasos($requerimiento);
        $visitados = array_filter(array_column($pasos, 'destino'));

        $estados = array_map(fn (EstadoRequerimiento $estado) => [
            'id' => $estado->value,
            'nombre' => $estado->label(),
            'color' => $estado->hex(),
            'visitado' => in_array($estado->value, $visitados, true),
            'actual' => $estado === $requerimiento->estado,
        ], EstadoRequerimiento::cases());

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

        $agregar(self::INICIO, EstadoRequerimiento::Borrador->value);

        foreach (EstadoRequerimiento::cases() as $estado) {
            $destinos = $estado->transicionesPermitidas();

            if ($destinos === []) {
                $agregar($estado->value, self::FIN);
            }

            foreach ($destinos as $destino) {
                $agregar($estado->value, $destino->value);
            }
        }

        foreach ($pasos as $paso) {
            if ($paso['destino'] === null) {
                continue;
            }

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
        ];
    }

    /**
     * Pasos del diagrama en orden cronológico. Cada paso conserva al usuario que lo ejecutó.
     *
     * @return list<array<string, mixed>>
     */
    public function pasos(Requerimiento $requerimiento): array
    {
        $requerimiento->loadMissing(['revisiones.usuario']);

        $revisiones = $requerimiento->revisiones
            ->sortBy([['created_at', 'asc'], ['id', 'asc']])
            ->values();

        $ultimo = $revisiones->count() - 1;

        return $revisiones
            ->map(function (RequerimientoRevision $revision, int $indice) use ($requerimiento, $ultimo) {
                $esSolicitante = in_array($revision->accion, self::ACCIONES_SOLICITANTE, true);
                $actorEsSolicitante = $revision->usuario_id !== null
                    && $revision->usuario_id === $requerimiento->solicitante_id;

                return [
                    'numero' => $indice + 1,
                    'origen' => $revision->estado_anterior?->value ?? self::INICIO,
                    'origenNombre' => $revision->estado_anterior?->label() ?? 'Inicio',
                    'destino' => $revision->estado_nuevo?->value,
                    'etapa' => $revision->estado_nuevo?->label() ?? 'Inicio',
                    'color' => $revision->estado_nuevo?->hex() ?? '#6c757d',
                    'evento' => $revision->accion,
                    'accion' => $revision->etiquetaAccion(),
                    'actor' => $revision->usuario?->name ?? 'Usuario eliminado',
                    'rol' => $esSolicitante ? 'Solicitante' : 'Gestor',
                    'mismoSolicitante' => ! $esSolicitante && $actorEsSolicitante,
                    'fecha' => $revision->created_at?->format('d/m/Y H:i'),
                    'observaciones' => $revision->observaciones,
                    'rechazo' => $revision->accion === 'rechazar',
                    'actual' => $indice === $ultimo,
                ];
            })
            ->all();
    }
}
