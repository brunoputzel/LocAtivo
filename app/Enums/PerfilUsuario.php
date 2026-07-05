<?php

namespace App\Enums;

enum PerfilUsuario: string
{
    case GESTOR = 'GESTOR';
    case OPERADOR_LOGISTICO = 'OPERADOR_LOGISTICO';
    case TECNICO_MANUTENCAO = 'TECNICO_MANUTENCAO';
    case FINANCEIRO = 'FINANCEIRO';
    case CLIENTE = 'CLIENTE';

    public function label(): string
    {
        return match ($this) {
            self::GESTOR => 'Gestor',
            self::OPERADOR_LOGISTICO => 'Operador Logístico',
            self::TECNICO_MANUTENCAO => 'Técnico de Manutenção',
            self::FINANCEIRO => 'Financeiro',
            self::CLIENTE => 'Cliente',
        };
    }
}
