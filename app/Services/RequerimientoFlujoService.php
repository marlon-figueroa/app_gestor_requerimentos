<?php

namespace App\Services;

use App\Enums\EstadoRequerimiento;
use App\Exceptions\TransicionInvalidaException;
use App\Models\Requerimiento;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Único punto para cambiar el estado de un requerimiento.
 */
class RequerimientoFlujoService
{
    /**
     * Acción => estado destino.
     */
    public const ACCIONES = [
        'enviar' => EstadoRequerimiento::EnRevision,
        'tomar' => EstadoRequerimiento::EnRevision,
        'aprobar' => EstadoRequerimiento::Aprobado,
        'rechazar' => EstadoRequerimiento::Rechazado,
        'iniciar' => EstadoRequerimiento::EnDesarrollo,
        'finalizar' => EstadoRequerimiento::Finalizado,
    ];

    public const ETIQUETAS = [
        'enviar' => 'Enviar a revisión',
        'tomar' => 'Tomar para revisión',
        'aprobar' => 'Aprobar',
        'rechazar' => 'Rechazar',
        'iniciar' => 'Iniciar desarrollo',
        'finalizar' => 'Finalizar',
    ];

    public function registrarCreacion(Requerimiento $requerimiento, User $usuario): void
    {
        $requerimiento->revisiones()->create([
            'usuario_id' => $usuario->id,
            'accion' => 'crear',
            'estado_anterior' => null,
            'estado_nuevo' => $requerimiento->estado,
        ]);
    }

    /**
     * Acción que lleva de un estado a otro (usada por el kanban al arrastrar tarjetas).
     */
    public static function accionEntre(EstadoRequerimiento $origen, EstadoRequerimiento $destino): ?string
    {
        if (! $origen->puedeTransicionarA($destino)) {
            return null;
        }

        return collect(self::ACCIONES)
            ->except('tomar')
            ->search(fn (EstadoRequerimiento $estado) => $estado === $destino) ?: null;
    }

    /**
     * Acciones aplicables por estado, sin considerar permisos del usuario.
     *
     * @return list<string>
     */
    public static function accionesPosibles(Requerimiento $requerimiento): array
    {
        return array_values(array_filter(array_keys(self::ACCIONES), function (string $accion) use ($requerimiento) {
            if ($accion === 'tomar') {
                return $requerimiento->enBandeja();
            }

            return $requerimiento->estado->puedeTransicionarA(self::ACCIONES[$accion]);
        }));
    }

    public function ejecutar(Requerimiento $requerimiento, string $accion, User $usuario, ?string $observaciones = null): Requerimiento
    {
        if (! array_key_exists($accion, self::ACCIONES)) {
            throw new TransicionInvalidaException('La acción solicitada no existe.');
        }

        $observaciones = filled($observaciones) ? trim($observaciones) : null;

        if ($accion === 'rechazar' && $observaciones === null) {
            throw ValidationException::withMessages([
                'observaciones' => 'Indique las observaciones del rechazo.',
            ]);
        }

        return DB::transaction(function () use ($requerimiento, $accion, $usuario, $observaciones) {
            $actual = Requerimiento::query()->lockForUpdate()->findOrFail($requerimiento->getKey());

            $this->validar($actual, $accion);

            $anterior = $actual->estado;
            $this->aplicar($actual, $accion, $usuario);
            $actual->save();

            $actual->revisiones()->create([
                'usuario_id' => $usuario->id,
                'accion' => $accion,
                'estado_anterior' => $anterior,
                'estado_nuevo' => $actual->estado,
                'observaciones' => $observaciones,
            ]);

            $requerimiento->setRawAttributes($actual->getAttributes(), true);

            return $requerimiento;
        });
    }

    private function validar(Requerimiento $requerimiento, string $accion): void
    {
        if ($accion === 'tomar') {
            if (! $requerimiento->enBandeja()) {
                throw new TransicionInvalidaException('El requerimiento ya no está disponible en la bandeja de revisión.');
            }

            return;
        }

        $destino = self::ACCIONES[$accion];

        if (! $requerimiento->estado->puedeTransicionarA($destino)) {
            throw new TransicionInvalidaException(sprintf(
                'No se puede pasar de "%s" a "%s".',
                $requerimiento->estado->label(),
                $destino->label(),
            ));
        }
    }

    private function aplicar(Requerimiento $requerimiento, string $accion, User $usuario): void
    {
        $requerimiento->estado = self::ACCIONES[$accion];

        match ($accion) {
            'enviar' => $requerimiento->forceFill([
                'gestor_id' => null,
                'enviado_at' => now(),
                'aprobado_at' => null,
            ]),
            'tomar' => $requerimiento->forceFill(['gestor_id' => $usuario->id]),
            'aprobar' => $requerimiento->forceFill([
                'gestor_id' => $requerimiento->gestor_id ?? $usuario->id,
                'aprobado_at' => now(),
            ]),
            'rechazar' => $requerimiento->forceFill([
                'gestor_id' => $requerimiento->gestor_id ?? $usuario->id,
            ]),
            'finalizar' => $requerimiento->forceFill(['finalizado_at' => now()]),
            default => null,
        };
    }
}
