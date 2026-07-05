<?php

namespace App\Services;

use App\Models\Cliente;

class ClienteService
{
    public function criar(array $dados): Cliente
    {
        return Cliente::create($dados);
    }

    public function atualizar(Cliente $cliente, array $dados): Cliente
    {
        $cliente->update($dados);

        return $cliente;
    }

    public function inativar(Cliente $cliente): void
    {
        $cliente->update(['ativo' => false]);
    }
}
