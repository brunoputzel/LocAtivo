<?php

namespace App\Enums;

enum StatusContrato: string
{
    case ATIVO = 'ATIVO';
    case ENCERRADO = 'ENCERRADO';

    public function label(): string
    {
        return match ($this) {
            self::ATIVO => 'Ativo',
            self::ENCERRADO => 'Encerrado',
        };
    }

    /**
     * Token de cor do design-system (`status.{token}` no tailwind.config.js).
     * ATIVO reaproveita a cor de EM_LOCACAO (equipamento em uso, informativo);
     * ENCERRADO reaproveita o cinza de INATIVO - o design-system agrupa os dois.
     */
    public function corToken(): string
    {
        return match ($this) {
            self::ATIVO => 'locacao',
            self::ENCERRADO => 'inativo',
        };
    }
}
