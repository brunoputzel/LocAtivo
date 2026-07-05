<?php

namespace App\Policies;

use App\Enums\PerfilUsuario;
use App\Models\Ativo;
use App\Models\User;
use App\Policies\Concerns\AutorizaPorPerfil;

class AtivoPolicy
{
    use AutorizaPorPerfil;

    public function viewAny(User $usuario): bool
    {
        return true;
    }

    public function view(User $usuario, Ativo $ativo): bool
    {
        return true;
    }

    public function create(User $usuario): bool
    {
        return $this->perfilEhAlgumDe($usuario, PerfilUsuario::GESTOR);
    }

    public function update(User $usuario, Ativo $ativo): bool
    {
        return $this->perfilEhAlgumDe($usuario, PerfilUsuario::GESTOR);
    }

    public function delete(User $usuario, Ativo $ativo): bool
    {
        return $this->perfilEhAlgumDe($usuario, PerfilUsuario::GESTOR);
    }
}
