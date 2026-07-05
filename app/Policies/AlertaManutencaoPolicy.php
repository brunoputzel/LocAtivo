<?php

namespace App\Policies;

use App\Enums\PerfilUsuario;
use App\Models\AlertaManutencao;
use App\Models\User;
use App\Policies\Concerns\AutorizaPorPerfil;

class AlertaManutencaoPolicy
{
    use AutorizaPorPerfil;

    public function viewAny(User $usuario): bool
    {
        return $this->perfilEhAlgumDe($usuario, PerfilUsuario::GESTOR, PerfilUsuario::TECNICO_MANUTENCAO);
    }

    public function view(User $usuario, AlertaManutencao $alerta): bool
    {
        return $this->perfilEhAlgumDe($usuario, PerfilUsuario::GESTOR, PerfilUsuario::TECNICO_MANUTENCAO);
    }

    public function resolver(User $usuario, AlertaManutencao $alerta): bool
    {
        return $this->perfilEhAlgumDe($usuario, PerfilUsuario::GESTOR, PerfilUsuario::TECNICO_MANUTENCAO);
    }
}
