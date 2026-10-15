<?php

namespace App\Services;

use App\Enums\EstadoProyecto;
use App\Exceptions\TransicionInvalidaException;
use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Único punto para cambiar el estado de un proyecto.
 */
class ProyectoFlujoService
{
    /**
     * Acción => estado destino.
     */
    public const ACCIONES = [
        'finalizar' => EstadoProyecto::Finalizado,
        'cancelar' => EstadoProyecto::Cancelado,
    ];

    public const ETIQUETAS = [
        'finalizar' => 'Finalizar proyecto',
        'cancelar' => 'Cancelar proyecto',
    ];

    public function registrarCreacion(Proyecto $proyecto, User $usuario): void
    {
        $proyecto->revisiones()->create([
            'usuario_id' => $usuario->id,
            'accion' => 'crear',
            'estado_anterior' => null,
            'estado_nuevo' => $proyecto->estado,
        ]);
    }

    /**
     * Acciones aplicables por estado, sin considerar permisos del usuario.
     *
     * @return list<string>
     */
    public static function accionesPosibles(Proyecto $proyecto): array
    {
        return array_values(array_filter(
            array_keys(self::ACCIONES),
            fn (string $accion) => $proyecto->estado->puedeTransicionarA(self::ACCIONES[$accion]),
        ));
    }

    public static function mensajePendientes(int $pendientes): string
    {
        return $pendientes === 1
            ? 'No se puede finalizar el proyecto: hay 1 requerimiento sin finalizar.'
            : "No se puede finalizar el proyecto: hay {$pendientes} requerimientos sin finalizar.";
    }

    public function ejecutar(Proyecto $proyecto, string $accion, User $usuario, ?string $observaciones = null): Proyecto
    {
        if (! array_key_exists($accion, self::ACCIONES)) {
            throw new TransicionInvalidaException('La acción solicitada no existe.');
        }

        $observaciones = filled($observaciones) ? trim($observaciones) : null;

        if ($accion === 'cancelar' && $observaciones === null) {
            throw ValidationException::withMessages([
                'observaciones' => 'Indique el motivo de la cancelación.',
            ]);
        }

        return DB::transaction(function () use ($proyecto, $accion, $usuario, $observaciones) {
            $actual = Proyecto::query()->lockForUpdate()->findOrFail($proyecto->getKey());

            $this->validar($actual, $accion);

            $anterior = $actual->estado;
            $actual->estado = self::ACCIONES[$accion];
            $actual->forceFill($accion === 'finalizar' ? ['finalizado_at' => now()] : ['cancelado_at' => now()]);
            $actual->save();

            $actual->revisiones()->create([
                'usuario_id' => $usuario->id,
                'accion' => $accion,
                'estado_anterior' => $anterior,
                'estado_nuevo' => $actual->estado,
                'observaciones' => $observaciones,
            ]);

            $proyecto->setRawAttributes($actual->getAttributes(), true);

            return $proyecto;
        });
    }

    private function validar(Proyecto $proyecto, string $accion): void
    {
        $destino = self::ACCIONES[$accion];

        if (! $proyecto->estado->puedeTransicionarA($destino)) {
            throw new TransicionInvalidaException(sprintf(
                'No se puede pasar de "%s" a "%s".',
                $proyecto->estado->label(),
                $destino->label(),
            ));
        }

        if ($accion === 'finalizar') {
            $pendientes = $proyecto->requerimientosPendientes()->count();

            if ($pendientes > 0) {
                throw new TransicionInvalidaException(self::mensajePendientes($pendientes));
            }
        }
    }
}
