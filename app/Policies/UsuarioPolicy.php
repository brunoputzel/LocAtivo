<?php

namespace App\Policies;

use App\Enums\PerfilUsuario;
use App\Models\User;
use App\Policies\Concerns\AutorizaPorPerfil;

class UsuarioPolicy
{
    use AutorizaPorPerfil;

    public function viewAny(User $usuario): bool
    {
        return $this->perfilEhAlgumDe($usuario, PerfilUsuario::GESTOR);
    }

    public function view(User $usuario, User $alvo): bool
    {
        return $this->perfilEhAlgumDe($usuario, PerfilUsuario::GESTOR);
    }

    public function create(User $usuario): bool
    {
        return $this->perfilEhAlgumDe($usuario, PerfilUsuario::GESTOR);
    }

    public function update(User $usuario, User $alvo): bool
    {
        return $this->perfilEhAlgumDe($usuario, PerfilUsuario::GESTOR);
    }

    public function delete(User $usuario, User $alvo): bool
    {
        return $this->perfilEhAlgumDe($usuario, PerfilUsuario::GESTOR);
    }
}
