<?php

namespace App\Enums;

enum EstadoRequerimiento: string
{
    case Borrador = 'borrador';
    case EnRevision = 'en_revision';
    case Rechazado = 'rechazado';
    case Aprobado = 'aprobado';
    case EnDesarrollo = 'en_desarrollo';
    case Finalizado = 'finalizado';

    public function label(): string
    {
        return match ($this) {
            self::Borrador => 'Borrador',
            self::EnRevision => 'En revisión',
            self::Rechazado => 'Rechazado',
            self::Aprobado => 'Aprobado',
            self::EnDesarrollo => 'En desarrollo',
            self::Finalizado => 'Finalizado',
        };
    }

    /**
     * Color contextual de Bootstrap.
     */
    public function color(): string
    {
        return match ($this) {
            self::Borrador => 'secondary',
            self::EnRevision => 'warning',
            self::Rechazado => 'danger',
            self::Aprobado => 'info',
            self::EnDesarrollo => 'primary',
            self::Finalizado => 'success',
        };
    }

    /**
     * Color hexadecimal para el calendario (FullCalendar no usa clases de Bootstrap).
     */
    public function hex(): string
    {
        return match ($this) {
            self::Borrador => '#6c757d',
            self::EnRevision => '#ffc107',
            self::Rechazado => '#dc3545',
            self::Aprobado => '#0dcaf0',
            self::EnDesarrollo => '#0d6efd',
            self::Finalizado => '#198754',
        };
    }

    public function icono(): string
    {
        return match ($this) {
            self::Borrador => 'pencil',
            self::EnRevision => 'hourglass-split',
            self::Rechazado => 'x-circle',
            self::Aprobado => 'check-circle',
            self::EnDesarrollo => 'gear',
            self::Finalizado => 'flag',
        };
    }

    /**
     * @return list<self>
     */
    public function transicionesPermitidas(): array
    {
        return match ($this) {
            self::Borrador, self::Rechazado => [self::EnRevision],
            self::EnRevision => [self::Aprobado, self::Rechazado],
            self::Aprobado => [self::EnDesarrollo],
            self::EnDesarrollo => [self::Finalizado],
            self::Finalizado => [],
        };
    }

    public function puedeTransicionarA(self $destino): bool
    {
        return in_array($destino, $this->transicionesPermitidas(), true);
    }

    /**
     * El solicitante solo modifica datos, casos de uso y adjuntos en estos estados.
     */
    public function esEditablePorSolicitante(): bool
    {
        return in_array($this, [self::Borrador, self::Rechazado], true);
    }

    /**
     * El gestor puede trabajar los casos de uso en estos estados.
     */
    public function esEditablePorGestor(): bool
    {
        return in_array($this, [self::EnRevision, self::Aprobado, self::EnDesarrollo], true);
    }

    /**
     * @return array<string, string>
     */
    public static function opciones(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $estado) => [$estado->value => $estado->label()])->all();
    }
}
