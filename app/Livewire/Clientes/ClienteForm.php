<?php

namespace App\Livewire\Clientes;

use App\Enums\TipoCliente;
use App\Models\Cliente;
use App\Rules\CpfCnpjValido;
use App\Services\ClienteService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class ClienteForm extends Component
{
    public ?int $clienteId = null;

    public string $nome = '';

    public string $tipo = 'PF';

    public string $cpfCnpj = '';

    public string $email = '';

    public string $telefone = '';

    public string $endereco = '';

    protected function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'tipo' => ['required', Rule::enum(TipoCliente::class)],
            'cpfCnpj' => [
                'required', 'string',
                Rule::unique('clientes', 'cpf_cnpj')->ignore($this->clienteId),
                new CpfCnpjValido(TipoCliente::tryFrom($this->tipo)),
            ],
            'email' => ['nullable', 'email', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'endereco' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function updatedCpfCnpj(): void
    {
        // a máscara x-mask deixa pontuação no valor sincronizado - normaliza
        // aqui pra unique/checksum e a gravação sempre verem só dígitos
        $this->cpfCnpj = preg_replace('/\D/', '', (string) $this->cpfCnpj);
    }

    #[On('cliente-form-novo')]
    public function novo(): void
    {
        $this->reset(['clienteId', 'nome', 'tipo', 'cpfCnpj', 'email', 'telefone', 'endereco']);
        $this->tipo = 'PF';
        $this->resetErrorBag();
    }

    #[On('cliente-form-editar')]
    public function editar(int $clienteId): void
    {
        $cliente = Cliente::findOrFail($clienteId);

        $this->clienteId = $cliente->id;
        $this->nome = $cliente->nome;
        $this->tipo = $cliente->tipo->value;
        $this->cpfCnpj = $cliente->cpf_cnpj;
        $this->email = $cliente->email ?? '';
        $this->telefone = $cliente->telefone ?? '';
        $this->endereco = $cliente->endereco ?? '';
        $this->resetErrorBag();
    }

    public function salvar(ClienteService $clienteService): void
    {
        $this->validate();

        $dados = [
            'nome' => $this->nome,
            'tipo' => $this->tipo,
            'cpf_cnpj' => preg_replace('/\D/', '', $this->cpfCnpj),
            'email' => $this->email !== '' ? $this->email : null,
            'telefone' => $this->telefone !== '' ? $this->telefone : null,
            'endereco' => $this->endereco !== '' ? $this->endereco : null,
        ];

        if ($this->clienteId) {
            $cliente = Cliente::findOrFail($this->clienteId);
            Gate::authorize('update', $cliente);
            $clienteService->atualizar($cliente, $dados);
            $mensagem = 'Cliente atualizado';
        } else {
            Gate::authorize('create', Cliente::class);
            $clienteService->criar($dados);
            $mensagem = 'Cliente cadastrado';
        }

        $this->dispatch('close-modal', 'cliente-form');
        $this->dispatch('cliente-salvo', mensagem: $mensagem);
        $this->novo();
    }

    public function render()
    {
        return view('livewire.clientes.cliente-form');
    }
}
