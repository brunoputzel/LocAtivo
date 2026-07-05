<?php

namespace App\Enums;

enum TipoChecklist: string
{
    case SAIDA = 'saida';
    case RETORNO = 'retorno';

    public function label(): string
    {
        return match ($this) {
            self::SAIDA => 'Saída',
            self::RETORNO => 'Retorno',
        };
    }
}
