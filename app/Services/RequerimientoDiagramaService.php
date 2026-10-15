<?php

namespace App\Services;

use App\Models\Requerimiento;
use App\Models\RequerimientoRevision;

class RequerimientoDiagramaService
{
    /**
     * Acciones que ejecuta el solicitante. El resto las ejecuta quien actúa como gestor
     * en esa transición, aunque sea otra persona o el mismo solicitante.
     */
    private const ACCIONES_SOLICITANTE = ['crear', 'enviar'];

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
                    'etapa' => $revision->estado_nuevo?->label() ?? 'Inicio',
                    'color' => $revision->estado_nuevo?->hex() ?? '#6c757d',
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
