<?php

namespace App\Livewire\Usuarios;

use App\Enums\PerfilUsuario;
use App\Models\Cliente;
use App\Models\User;
use App\Services\UsuarioService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class UsuarioForm extends Component
{
    public ?int $usuarioId = null;

    public string $name = '';

    public string $email = '';

    public string $perfil = 'OPERADOR_LOGISTICO';

    public ?int $clienteId = null;

    public string $password = '';

    protected function rules(): array
    {
        $regras = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->usuarioId)],
            'perfil' => ['required', Rule::enum(PerfilUsuario::class)],
            'clienteId' => [
                'nullable', 'integer', 'exists:clientes,id',
                'required_if:perfil,'.PerfilUsuario::CLIENTE->value,
            ],
        ];

        if (! $this->usuarioId) {
            $regras['password'] = ['required', 'string', 'min:8'];
        }

        return $regras;
    }

    #[On('usuario-form-novo')]
    public function novo(): void
    {
        $this->reset(['usuarioId', 'name', 'email', 'clienteId', 'password']);
        $this->perfil = PerfilUsuario::OPERADOR_LOGISTICO->value;
        $this->resetErrorBag();
    }

    #[On('usuario-form-editar')]
    public function editar(int $usuarioId): void
    {
        $usuario = User::findOrFail($usuarioId);

        $this->usuarioId = $usuario->id;
        $this->name = $usuario->name;
        $this->email = $usuario->email;
        $this->perfil = $usuario->perfil->value;
        $this->clienteId = $usuario->cliente_id;
        $this->password = '';
        $this->resetErrorBag();
    }

    public function salvar(UsuarioService $usuarioService): void
    {
        $this->validate();

        $dados = [
            'name' => $this->name,
            'email' => $this->email,
            'perfil' => $this->perfil,
            'cliente_id' => $this->clienteId,
        ];

        if ($this->usuarioId) {
            $usuario = User::findOrFail($this->usuarioId);
            Gate::authorize('update', $usuario);
            $usuarioService->atualizar($usuario, $dados);
            $mensagem = 'Usuário atualizado';
        } else {
            Gate::authorize('create', User::class);
            $dados['password'] = $this->password;
            $usuarioService->criar($dados);
            $mensagem = 'Usuário cadastrado';
        }

        $this->dispatch('close-modal', 'usuario-form');
        $this->dispatch('usuario-salvo', mensagem: $mensagem);
        $this->novo();
    }

    public function render()
    {
        return view('livewire.usuarios.usuario-form', [
            'perfis' => PerfilUsuario::cases(),
            'clientesAtivos' => Cliente::query()->where('ativo', true)->orderBy('nome')->get(),
        ]);
    }
}
