<?php

namespace App\Services;

use App\Enums\StatusAtivo;
use App\Enums\StatusOrdemServico;
use App\Exceptions\BusinessException;
use App\Models\OrdemDeServico;
use Illuminate\Support\Facades\DB;

class OrdemDeServicoService
{
    public function abrir(array $dados): OrdemDeServico
    {
        return DB::transaction(function () use ($dados) {
            $os = OrdemDeServico::create($dados + [
                'status' => StatusOrdemServico::ABERTA,
                'data_abertura' => $dados['data_abertura'] ?? now()->toDateString(),
            ]);

            // ativo fica indisponível pra locação enquanto a ordem estiver aberta -
            // sem isso "fechar libera o ativo" não faz sentido (nunca ficou preso)
            $os->ativo()->update(['status' => StatusAtivo::EM_MANUTENCAO]);

            return $os->fresh(['ativo', 'tecnico', 'alerta']);
        });
    }

    /**
     * @throws BusinessException
     */
    public function fechar(OrdemDeServico $os, float $custo, ?string $dataFechamento = null): OrdemDeServico
    {
        return DB::transaction(function () use ($os, $custo, $dataFechamento) {
            if ($os->status === StatusOrdemServico::FECHADA) {
                throw new BusinessException('Esta ordem de serviço já está fechada.');
            }

            $os->update([
                'status' => StatusOrdemServico::FECHADA,
                'custo' => $custo,
                'data_fechamento' => $dataFechamento ?? now()->toDateString(),
            ]);

            $os->ativo()->update(['status' => StatusAtivo::DISPONIVEL]);

            if ($os->alerta_id) {
                $os->alerta()->update(['resolvido' => true]);
            }

            return $os->fresh(['ativo', 'tecnico', 'alerta']);
        });
    }
}
