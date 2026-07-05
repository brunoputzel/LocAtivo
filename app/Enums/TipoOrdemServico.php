<?php

namespace App\Enums;

enum TipoOrdemServico: string
{
    case PREVENTIVA = 'preventiva';
    case CORRETIVA = 'corretiva';

    public function label(): string
    {
        return match ($this) {
            self::PREVENTIVA => 'Preventiva',
            self::CORRETIVA => 'Corretiva',
        };
    }
}
