<?php

namespace App\Services;

use App\Models\AlertaManutencao;

class AlertaManutencaoService
{
    /**
     * Resolver o alerta é independente do ciclo de vida da OS - fechar uma OS
     * resolve o alerta vinculado (ver OrdemDeServicoService::fechar()), mas o
     * caminho inverso não existe: resolver aqui não fecha nenhuma ordem.
     */
    public function resolver(AlertaManutencao $alerta): AlertaManutencao
    {
        $alerta->update(['resolvido' => true]);

        return $alerta;
    }
}
