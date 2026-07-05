<?php

namespace App\Livewire\Clientes;

use App\Models\Cliente;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ClienteShow extends Component
{
    public Cliente $cliente;

    public function mount(Cliente $cliente): void
    {
        $this->cliente = $cliente;
    }

    public function render()
    {
        $contratos = $this->cliente->contratos()->with('ativo')->latest()->get();

        return view('livewire.clientes.cliente-show', [
            'contratos' => $contratos,
        ])->title($this->cliente->nome);
    }
}
