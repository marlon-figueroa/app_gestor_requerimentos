<?php

namespace App\Services;

use App\Enums\EstadoRequerimiento;
use App\Exceptions\TransicionInvalidaException;
use App\Models\Requerimiento;
use App\Models\User;
use App\Notifications\RequerimientoFlujoNotification;
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
        'liberar' => EstadoRequerimiento::EnRevision,
        'reasignar' => EstadoRequerimiento::EnRevision,
    ];

    public const ETIQUETAS = [
        'enviar' => 'Enviar a revisión',
        'tomar' => 'Tomar para revisión',
        'aprobar' => 'Aprobar',
        'rechazar' => 'Rechazar',
        'iniciar' => 'Iniciar desarrollo',
        'finalizar' => 'Finalizar',
        'liberar' => 'Liberar asignación',
        'reasignar' => 'Reasignar gestor',
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
            ->except(['tomar', 'liberar', 'reasignar'])
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

            if ($accion === 'liberar') {
                return $requerimiento->estado === EstadoRequerimiento::EnRevision
                    && $requerimiento->gestor_id !== null;
            }

            if ($accion === 'reasignar') {
                return $requerimiento->estado === EstadoRequerimiento::EnRevision
                    && $requerimiento->gestor_id !== null;
            }

            return $requerimiento->estado->puedeTransicionarA(self::ACCIONES[$accion]);
        }));
    }

    public function ejecutar(
        Requerimiento $requerimiento,
        string $accion,
        User $usuario,
        ?string $observaciones = null,
        ?int $gestorId = null,
    ): Requerimiento {
        if (! array_key_exists($accion, self::ACCIONES)) {
            throw new TransicionInvalidaException('La acción solicitada no existe.');
        }

        $observaciones = filled($observaciones) ? trim($observaciones) : null;

        if ($accion === 'rechazar' && $observaciones === null) {
            throw ValidationException::withMessages([
                'observaciones' => 'Indique las observaciones del rechazo.',
            ]);
        }

        if ($accion === 'reasignar' && $gestorId === null) {
            throw ValidationException::withMessages([
                'gestor_id' => 'Seleccione el gestor al que reasignar.',
            ]);
        }

        $resultado = DB::transaction(function () use ($requerimiento, $accion, $usuario, $observaciones, $gestorId) {
            $actual = Requerimiento::query()->lockForUpdate()->findOrFail($requerimiento->getKey());

            if ($accion === 'enviar') {
                $this->validarEnvio($actual);
            }

            $this->validar($actual, $accion);

            $anterior = $actual->estado;
            $this->aplicar($actual, $accion, $usuario, $gestorId);
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

        $this->notificarFlujo($resultado, $accion, $observaciones);

        return $resultado;
    }

    private function validarEnvio(Requerimiento $requerimiento): void
    {
        $reglas = config('requerimientos.envio');
        $errores = [];

        if (mb_strlen(trim($requerimiento->descripcion)) < ($reglas['descripcion_min'] ?? 0)) {
            $errores['descripcion'] = 'La descripción es demasiado corta para enviar a revisión.';
        }

        $casos = $requerimiento->casosUso()->count();
        if ($casos < ($reglas['casos_uso_min'] ?? 0)) {
            $errores['casos_uso'] = 'Agregue al menos '.$reglas['casos_uso_min'].' caso(s) de uso antes de enviar.';
        }

        $adjuntos = $requerimiento->adjuntos()->count();
        if ($adjuntos < ($reglas['adjuntos_min'] ?? 0)) {
            $errores['adjuntos'] = 'Agregue al menos '.$reglas['adjuntos_min'].' adjunto(s) antes de enviar.';
        }

        if ($errores !== []) {
            throw ValidationException::withMessages($errores);
        }
    }

    private function validar(Requerimiento $requerimiento, string $accion): void
    {
        if ($accion === 'tomar') {
            if (! $requerimiento->enBandeja()) {
                throw new TransicionInvalidaException('El requerimiento ya no está disponible en la bandeja de revisión.');
            }

            return;
        }

        if (in_array($accion, ['liberar', 'reasignar'], true)) {
            if ($requerimiento->estado !== EstadoRequerimiento::EnRevision) {
                throw new TransicionInvalidaException('Solo se puede gestionar la asignación en revisión.');
            }

            if ($requerimiento->gestor_id === null) {
                throw new TransicionInvalidaException('El requerimiento no tiene gestor asignado.');
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

    private function aplicar(Requerimiento $requerimiento, string $accion, User $usuario, ?int $gestorId = null): void
    {
        if ($accion === 'liberar') {
            $requerimiento->forceFill(['gestor_id' => null]);

            return;
        }

        if ($accion === 'reasignar') {
            $requerimiento->forceFill(['gestor_id' => $gestorId]);

            return;
        }

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

    private function notificarFlujo(Requerimiento $requerimiento, string $accion, ?string $observaciones): void
    {
        if (! in_array($accion, ['enviar', 'tomar', 'aprobar', 'rechazar', 'reasignar'], true)) {
            return;
        }

        $requerimiento->loadMissing(['solicitante', 'gestor']);

        match ($accion) {
            'enviar' => User::role('Gestor')->each(
                fn (User $gestor) => $gestor->notify(new RequerimientoFlujoNotification($requerimiento, 'enviar')),
            ),
            'tomar', 'reasignar' => $requerimiento->solicitante?->notify(
                new RequerimientoFlujoNotification($requerimiento, $accion),
            ),
            'rechazar' => $requerimiento->solicitante?->notify(
                new RequerimientoFlujoNotification($requerimiento, 'rechazar', $observaciones),
            ),
            'aprobar' => $requerimiento->solicitante?->notify(
                new RequerimientoFlujoNotification($requerimiento, 'aprobar', $observaciones),
            ),
            default => null,
        };
    }
}
