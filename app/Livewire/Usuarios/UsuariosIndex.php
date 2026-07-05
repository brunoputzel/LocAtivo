<?php

namespace App\Livewire\Usuarios;

use App\Exceptions\BusinessException;
use App\Models\User;
use App\Services\UsuarioService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Usuários')]
class UsuariosIndex extends Component
{
    public string $busca = '';

    public ?string $mensagem = null;

    public ?string $erro = null;

    #[On('usuario-salvo')]
    public function usuarioSalvo(string $mensagem): void
    {
        $this->mensagem = $mensagem;
        $this->erro = null;
    }

    public function alternarAtivo(int $usuarioId, bool $ativo, UsuarioService $usuarioService): void
    {
        $usuario = User::findOrFail($usuarioId);

        Gate::authorize($ativo ? 'update' : 'delete', $usuario);

        try {
            $usuarioService->alternarAtivo($usuario, $ativo, auth()->user());
            $this->mensagem = $ativo ? 'Usuário ativado' : 'Usuário desativado';
            $this->erro = null;
        } catch (BusinessException $e) {
            $this->erro = $e->getMessage();
        }
    }

    public function render()
    {
        $usuarios = User::query()
            ->when(
                $this->busca,
                fn ($query) => $query->where(fn ($q) => $q
                    ->where('name', 'like', "%{$this->busca}%")
                    ->orWhere('email', 'like', "%{$this->busca}%"))
            )
            ->latest()
            ->get();

        return view('livewire.usuarios.usuarios-index', [
            'usuarios' => $usuarios,
            'temFiltroAtivo' => $this->busca !== '',
            'existemUsuarios' => User::query()->exists(),
        ]);
    }
}
