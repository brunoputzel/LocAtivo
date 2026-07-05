<?php

namespace App\Enums;

enum StatusOrdemServico: string
{
    case ABERTA = 'aberta';
    case EM_ANDAMENTO = 'em_andamento';
    case FECHADA = 'fechada';

    public function label(): string
    {
        return match ($this) {
            self::ABERTA => 'Aberta',
            self::EM_ANDAMENTO => 'Em andamento',
            self::FECHADA => 'Fechada',
        };
    }

    /**
     * Token de cor do design-system (`status.{token}` no tailwind.config.js).
     */
    public function corToken(): string
    {
        return match ($this) {
            self::ABERTA => 'manutencao',
            self::EM_ANDAMENTO => 'locacao',
            self::FECHADA => 'inativo',
        };
    }
}
