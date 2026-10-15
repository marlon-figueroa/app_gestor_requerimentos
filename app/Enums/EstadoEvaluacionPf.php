<?php

namespace App\Enums;

enum EstadoEvaluacionPf: string
{
    case Borrador = 'borrador';
    case Aprobada = 'aprobada';

    public function label(): string
    {
        return match ($this) {
            self::Borrador => 'Borrador',
            self::Aprobada => 'Aprobada',
        };
    }
}
