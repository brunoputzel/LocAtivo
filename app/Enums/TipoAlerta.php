<?php

namespace App\Enums;

enum TipoAlerta: string
{
    case PRAZO = 'prazo';
    case HORIMETRO = 'horimetro';

    public function label(): string
    {
        return match ($this) {
            self::PRAZO => 'Prazo',
            self::HORIMETRO => 'Horímetro',
        };
    }
}
