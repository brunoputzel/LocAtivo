<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\User;

class UsuarioService
{
    /**
     * @param  array<string, mixed>  $dados
     */
    public function criar(array $dados): User
    {
        return User::create([
            'name' => $dados['name'],
            'email' => $dados['email'],
            'password' => $dados['password'],
            'perfil' => $dados['perfil'],
            'cliente_id' => $dados['cliente_id'] ?? null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    public function atualizar(User $usuario, array $dados): User
    {
        $usuario->update($dados);

        return $usuario;
    }

    /**
     * @throws BusinessException
     */
    public function alternarAtivo(User $usuario, bool $ativo, User $autenticado): User
    {
        if (! $ativo && $usuario->id === $autenticado->id) {
            throw new BusinessException('Você não pode desativar sua própria conta.');
        }

        $usuario->update(['ativo' => $ativo]);

        return $usuario;
    }
}
