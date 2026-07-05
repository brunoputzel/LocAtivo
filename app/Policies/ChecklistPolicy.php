<?php

namespace App\Policies;

use App\Enums\PerfilUsuario;
use App\Models\Checklist;
use App\Models\User;
use App\Policies\Concerns\AutorizaPorPerfil;

class ChecklistPolicy
{
    use AutorizaPorPerfil;

    public function viewAny(User $usuario): bool
    {
        return $this->perfilEhAlgumDe($usuario, PerfilUsuario::GESTOR, PerfilUsuario::OPERADOR_LOGISTICO);
    }

    public function view(User $usuario, Checklist $checklist): bool
    {
        return $this->perfilEhAlgumDe($usuario, PerfilUsuario::GESTOR, PerfilUsuario::OPERADOR_LOGISTICO);
    }

    public function create(User $usuario): bool
    {
        return $this->perfilEhAlgumDe($usuario, PerfilUsuario::GESTOR, PerfilUsuario::OPERADOR_LOGISTICO);
    }
}
