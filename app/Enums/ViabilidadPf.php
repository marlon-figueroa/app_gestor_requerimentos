<?php

namespace App\Enums;

enum ViabilidadPf: string
{
    case Alta = 'alta';
    case Media = 'media';
    case Baja = 'baja';
    case NoViable = 'no_viable';

    public function label(): string
    {
        return match ($this) {
            self::Alta => 'Alta viabilidad',
            self::Media => 'Viabilidad media',
            self::Baja => 'Baja viabilidad',
            self::NoViable => 'No viable en el plazo',
        };
    }

    public function labelCorto(): string
    {
        return match ($this) {
            self::Alta => 'Alta',
            self::Media => 'Media',
            self::Baja => 'Baja',
            self::NoViable => 'No viable',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Alta => 'success',
            self::Media => 'warning',
            self::Baja => 'danger',
            self::NoViable => 'dark',
        };
    }

    public function icono(): string
    {
        return match ($this) {
            self::Alta => 'check-circle-fill',
            self::Media => 'exclamation-circle-fill',
            self::Baja => 'x-circle-fill',
            self::NoViable => 'slash-circle-fill',
        };
    }
}
