<?php

namespace App\Livewire\Clientes;

use App\Models\Cliente;
use App\Services\ClienteService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Clientes')]
class ClientesIndex extends Component
{
    public string $busca = '';

    public bool $mostrarInativos = false;

    public ?string $mensagem = null;

    #[On('cliente-salvo')]
    public function clienteSalvo(string $mensagem): void
    {
        $this->mensagem = $mensagem;
    }

    public function inativar(int $clienteId, ClienteService $clienteService): void
    {
        $cliente = Cliente::findOrFail($clienteId);

        Gate::authorize('delete', $cliente);

        $clienteService->inativar($cliente);
        $this->mensagem = 'Cliente inativado';
    }

    public function limparFiltros(): void
    {
        $this->reset(['busca']);
    }

    public function render()
    {
        $clientes = Cliente::query()
            ->when(
                $this->busca,
                fn ($query) => $query->where(fn ($q) => $q
                    ->where('nome', 'like', "%{$this->busca}%")
                    ->orWhere('cpf_cnpj', 'like', "%{$this->busca}%"))
            )
            ->when(! $this->mostrarInativos, fn ($query) => $query->where('ativo', true))
            ->latest()
            ->get();

        return view('livewire.clientes.clientes-index', [
            'clientes' => $clientes,
            'temFiltroAtivo' => $this->busca !== '',
            'existemClientes' => Cliente::query()->exists(),
        ]);
    }
}
