<?php

namespace App\Policies\Concerns;

use App\Enums\PerfilUsuario;
use App\Models\User;

// padrão que toda Policy nova (AtivoPolicy, ContratoPolicy...) segue: autorização
// é sempre "usuário autenticado tem um destes perfis", nunca if solto no Controller.
trait AutorizaPorPerfil
{
    protected function perfilEhAlgumDe(User $usuario, PerfilUsuario ...$perfis): bool
    {
        return in_array($usuario->perfil, $perfis, true);
    }
}
