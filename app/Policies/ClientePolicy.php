<?php

namespace App\Policies;

use App\Enums\PerfilUsuario;
use App\Models\Cliente;
use App\Models\User;
use App\Policies\Concerns\AutorizaPorPerfil;

class ClientePolicy
{
    use AutorizaPorPerfil;

    public function viewAny(User $usuario): bool
    {
        return $this->perfilEhAlgumDe($usuario, PerfilUsuario::GESTOR, PerfilUsuario::OPERADOR_LOGISTICO);
    }

    public function view(User $usuario, Cliente $cliente): bool
    {
        return $this->perfilEhAlgumDe($usuario, PerfilUsuario::GESTOR, PerfilUsuario::OPERADOR_LOGISTICO);
    }

    public function create(User $usuario): bool
    {
        return $this->perfilEhAlgumDe($usuario, PerfilUsuario::GESTOR, PerfilUsuario::OPERADOR_LOGISTICO);
    }

    public function update(User $usuario, Cliente $cliente): bool
    {
        return $this->perfilEhAlgumDe($usuario, PerfilUsuario::GESTOR, PerfilUsuario::OPERADOR_LOGISTICO);
    }

    public function delete(User $usuario, Cliente $cliente): bool
    {
        return $this->perfilEhAlgumDe($usuario, PerfilUsuario::GESTOR, PerfilUsuario::OPERADOR_LOGISTICO);
    }
}
