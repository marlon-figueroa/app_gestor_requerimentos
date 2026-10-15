<?php

namespace App\Enums;

enum EstadoProyecto: string
{
    case Activo = 'activo';
    case Finalizado = 'finalizado';
    case Cancelado = 'cancelado';

    public function label(): string
    {
        return match ($this) {
            self::Activo => 'Activo',
            self::Finalizado => 'Finalizado',
            self::Cancelado => 'Cancelado',
        };
    }

    /**
     * Color contextual de Bootstrap.
     */
    public function color(): string
    {
        return match ($this) {
            self::Activo => 'primary',
            self::Finalizado => 'success',
            self::Cancelado => 'secondary',
        };
    }

    public function hex(): string
    {
        return match ($this) {
            self::Activo => '#0d6efd',
            self::Finalizado => '#198754',
            self::Cancelado => '#6c757d',
        };
    }

    public function icono(): string
    {
        return match ($this) {
            self::Activo => 'play-circle',
            self::Finalizado => 'flag',
            self::Cancelado => 'slash-circle',
        };
    }

    /**
     * @return list<self>
     */
    public function transicionesPermitidas(): array
    {
        return match ($this) {
            self::Activo => [self::Finalizado, self::Cancelado],
            self::Finalizado, self::Cancelado => [],
        };
    }

    public function puedeTransicionarA(self $destino): bool
    {
        return in_array($destino, $this->transicionesPermitidas(), true);
    }

    public function estaCerrado(): bool
    {
        return $this !== self::Activo;
    }

    /**
     * Posición [eje principal, eje cruzado] de cada nodo en el diagrama de estados.
     *
     * @return array<string, array{int, int}>
     */
    public static function ubicacionDiagrama(): array
    {
        return [
            'inicio' => [0, 0],
            self::Activo->value => [1, 0],
            self::Finalizado->value => [2, 0],
            self::Cancelado->value => [2, 1],
            'fin' => [3, 0],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function opciones(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $estado) => [$estado->value => $estado->label()])->all();
    }
}
