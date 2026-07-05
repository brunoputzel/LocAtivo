<?php

namespace App\Services;

use App\Enums\PerfilUsuario;
use App\Enums\StatusAtivo;
use App\Enums\StatusContrato;
use App\Exceptions\BusinessException;
use App\Models\Ativo;
use App\Models\Cobranca;
use App\Models\Contrato;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ContratoService
{
    public function listarPara(User $usuario): Collection
    {
        return Contrato::query()
            ->when(
                $usuario->perfil === PerfilUsuario::CLIENTE,
                fn ($query) => $query->where('cliente_id', $usuario->cliente_id)
            )
            ->with(['ativo', 'cliente'])
            ->get();
    }

    /**
     * @throws BusinessException
     */
    public function criar(array $dados): Contrato
    {
        return DB::transaction(function () use ($dados) {
            $ativo = Ativo::lockForUpdate()->findOrFail($dados['ativo_id']);

            if ($ativo->status !== StatusAtivo::DISPONIVEL) {
                throw new BusinessException("Ativo está com status {$ativo->status->value} e não pode ser locado.");
            }

            $contrato = Contrato::create($dados + ['status' => StatusContrato::ATIVO]);

            $ativo->update(['status' => StatusAtivo::EM_LOCACAO]);

            return $contrato;
        });
    }

    /**
     * @throws BusinessException
     */
    public function encerrar(Contrato $contrato): Contrato
    {
        return DB::transaction(function () use ($contrato) {
            if ($contrato->status === StatusContrato::ENCERRADO) {
                throw new BusinessException('Este contrato já está encerrado.');
            }

            // dias efetivos de uso, não a duração planejada - mínimo 1 dia mesmo se
            // aberto e encerrado no mesmo dia
            $diasEfetivos = max(1, (int) $contrato->data_inicio->diffInDays(now()));

            $contrato->update(['status' => StatusContrato::ENCERRADO]);

            $contrato->ativo()->update(['status' => StatusAtivo::DISPONIVEL]);

            Cobranca::create([
                'contrato_id' => $contrato->id,
                'valor' => $diasEfetivos * $contrato->valor_diaria,
                'status' => 'pendente',
            ]);

            return $contrato->fresh(['ativo', 'cliente', 'cobranca']);
        });
    }
}
