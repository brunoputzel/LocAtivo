<?php

namespace App\Services;

use App\Enums\StatusAtivo;
use App\Enums\TipoAlerta;
use App\Exceptions\BusinessException;
use App\Models\AlertaManutencao;
use App\Models\Ativo;
use Illuminate\Support\Facades\DB;

class AtivoService
{
    // intervalo fixo pra todo ativo por enquanto - RF007 não pede configuração
    // por tipo de equipamento nesta fase, só o gatilho de horímetro em si
    private const LIMIAR_HORIMETRO_MANUTENCAO = 500.0;

    public function criar(array $dados): Ativo
    {
        // default fica também aqui, não só na coluna do banco - senão o model
        // recém-criado em memória (e a resposta JSON) fica com status nulo
        return Ativo::create($dados + ['status' => StatusAtivo::DISPONIVEL]);
    }

    public function atualizar(Ativo $ativo, array $dados): Ativo
    {
        // horímetro passa pela checagem de limiar de manutenção - qualquer
        // caminho que atualize esse campo (API ou tela) reage igual
        if (array_key_exists('horimetro', $dados) && (float) $dados['horimetro'] !== (float) $ativo->horimetro) {
            $novoHorimetro = (float) $dados['horimetro'];
            unset($dados['horimetro']);

            if ($dados !== []) {
                $ativo->update($dados);
            }

            return $this->atualizarHorimetro($ativo, $novoHorimetro);
        }

        $ativo->update($dados);

        return $ativo;
    }

    /**
     * RF007: reage direto na atualização, não é cron job - compara o valor
     * antigo com o novo e dispara alerta se cruzar o limiar de manutenção.
     */
    public function atualizarHorimetro(Ativo $ativo, float $novoHorimetro): Ativo
    {
        return DB::transaction(function () use ($ativo, $novoHorimetro) {
            $antigo = (float) $ativo->horimetro;

            $ativo->update(['horimetro' => $novoHorimetro]);

            if ($this->cruzouLimiarDeManutencao($antigo, $novoHorimetro)) {
                AlertaManutencao::create([
                    'ativo_id' => $ativo->id,
                    'tipo' => TipoAlerta::HORIMETRO,
                    'descricao' => "Horímetro atingiu {$novoHorimetro}h, cruzando o limiar de manutenção preventiva.",
                    'data_alerta' => now(),
                ]);

                $ativo->update(['status' => StatusAtivo::EM_MANUTENCAO]);
            }

            return $ativo->fresh();
        });
    }

    private function cruzouLimiarDeManutencao(float $antigo, float $novo): bool
    {
        return floor($antigo / self::LIMIAR_HORIMETRO_MANUTENCAO) < floor($novo / self::LIMIAR_HORIMETRO_MANUTENCAO);
    }

    /**
     * @throws BusinessException
     */
    public function excluir(Ativo $ativo): void
    {
        if ($ativo->status === StatusAtivo::EM_LOCACAO) {
            throw new BusinessException('Este ativo está em locação e não pode ser excluído.');
        }

        // restrict na FK contratos.ativo_id já bloquearia isso no banco -
        // aqui devolvemos uma mensagem de negócio em vez de erro de SQL
        if ($ativo->contratos()->exists()) {
            throw new BusinessException('Este ativo possui contratos no histórico e não pode ser excluído.');
        }

        $ativo->delete();
    }
}
