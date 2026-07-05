<?php

namespace App\Enums;

enum StatusAtivo: string
{
    case DISPONIVEL = 'DISPONIVEL';
    case EM_LOCACAO = 'EM_LOCACAO';
    case EM_MANUTENCAO = 'EM_MANUTENCAO';
    case INATIVO = 'INATIVO';

    public function label(): string
    {
        return match ($this) {
            self::DISPONIVEL => 'Disponível',
            self::EM_LOCACAO => 'Em locação',
            self::EM_MANUTENCAO => 'Em manutenção',
            self::INATIVO => 'Inativo',
        };
    }

    /**
     * Token de cor do design-system (`status.{token}` no tailwind.config.js).
     */
    public function corToken(): string
    {
        return match ($this) {
            self::DISPONIVEL => 'disponivel',
            self::EM_LOCACAO => 'locacao',
            self::EM_MANUTENCAO => 'manutencao',
            self::INATIVO => 'inativo',
        };
    }
}
