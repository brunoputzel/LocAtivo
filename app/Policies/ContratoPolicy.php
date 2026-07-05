<?php

namespace App\Policies;

use App\Enums\PerfilUsuario;
use App\Models\Contrato;
use App\Models\User;
use App\Policies\Concerns\AutorizaPorPerfil;

class ContratoPolicy
{
    use AutorizaPorPerfil;

    public function viewAny(User $usuario): bool
    {
        return $this->perfilEhAlgumDe(
            $usuario,
            PerfilUsuario::GESTOR,
            PerfilUsuario::OPERADOR_LOGISTICO,
            PerfilUsuario::CLIENTE
        );
    }

    public function view(User $usuario, Contrato $contrato): bool
    {
        if ($usuario->perfil === PerfilUsuario::CLIENTE) {
            return $contrato->cliente_id === $usuario->cliente_id;
        }

        return $this->perfilEhAlgumDe($usuario, PerfilUsuario::GESTOR, PerfilUsuario::OPERADOR_LOGISTICO);
    }

    public function create(User $usuario): bool
    {
        return $this->perfilEhAlgumDe($usuario, PerfilUsuario::GESTOR, PerfilUsuario::OPERADOR_LOGISTICO);
    }

    public function encerrar(User $usuario, Contrato $contrato): bool
    {
        return $this->perfilEhAlgumDe($usuario, PerfilUsuario::GESTOR, PerfilUsuario::OPERADOR_LOGISTICO);
    }
}
