<?php

namespace App\Policies;

use App\Enums\PerfilUsuario;
use App\Models\OrdemDeServico;
use App\Models\User;
use App\Policies\Concerns\AutorizaPorPerfil;

class OrdemDeServicoPolicy
{
    use AutorizaPorPerfil;

    public function viewAny(User $usuario): bool
    {
        return $this->perfilEhAlgumDe($usuario, PerfilUsuario::GESTOR, PerfilUsuario::TECNICO_MANUTENCAO);
    }

    public function view(User $usuario, OrdemDeServico $ordemDeServico): bool
    {
        return $this->perfilEhAlgumDe($usuario, PerfilUsuario::GESTOR, PerfilUsuario::TECNICO_MANUTENCAO);
    }

    public function create(User $usuario): bool
    {
        return $this->perfilEhAlgumDe($usuario, PerfilUsuario::GESTOR, PerfilUsuario::TECNICO_MANUTENCAO);
    }

    /**
     * Só o técnico atribuído àquela ordem (ou o Gestor) pode fechar -
     * não basta ser um técnico qualquer.
     */
    public function fechar(User $usuario, OrdemDeServico $ordemDeServico): bool
    {
        if ($usuario->perfil === PerfilUsuario::GESTOR) {
            return true;
        }

        return $usuario->perfil === PerfilUsuario::TECNICO_MANUTENCAO && $usuario->id === $ordemDeServico->tecnico_id;
    }
}
