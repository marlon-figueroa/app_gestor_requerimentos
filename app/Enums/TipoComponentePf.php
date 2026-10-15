<?php

namespace App\Enums;

enum TipoComponentePf: string
{
    case Ei = 'ei';
    case Eo = 'eo';
    case Eq = 'eq';
    case Ilf = 'ilf';
    case Eif = 'eif';

    public function label(): string
    {
        return match ($this) {
            self::Ei => 'Entrada externa (EI)',
            self::Eo => 'Salida externa (EO)',
            self::Eq => 'Consulta externa (EQ)',
            self::Ilf => 'Archivo lógico interno (ILF)',
            self::Eif => 'Archivo de interfaz externa (EIF)',
        };
    }

    public function abreviatura(): string
    {
        return strtoupper($this->value);
    }

    public function usaFtr(): bool
    {
        return match ($this) {
            self::Ei, self::Eo, self::Eq => true,
            self::Ilf, self::Eif => false,
        };
    }

    /**
     * @return list<self>
     */
    public static function opciones(): array
    {
        return self::cases();
    }
}
