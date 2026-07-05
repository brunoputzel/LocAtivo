<?php

namespace App\Enums;

enum TipoCliente: string
{
    case PF = 'PF';
    case PJ = 'PJ';

    public function tamanhoDocumento(): int
    {
        return match ($this) {
            self::PF => 11,
            self::PJ => 14,
        };
    }
}
