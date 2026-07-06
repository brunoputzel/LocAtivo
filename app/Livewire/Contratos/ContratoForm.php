<?php

namespace App\Livewire\Contratos;

use App\Enums\StatusAtivo;
use App\Exceptions\BusinessException;
use App\Models\Ativo;
use App\Models\Cliente;
use App\Models\Contrato;
use App\Services\ContratoService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\On;
use Livewire\Component;

class ContratoForm extends Component
{
    public ?int $ativoId = null;

    public string $buscaAtivo = '';

    public bool $painelAtivoAberto = false;

    public ?int $clienteId = null;

    public string $buscaCliente = '';

    public bool $painelClienteAberto = false;

    public string $dataInicio = '';

    public string $dataFim = '';

    public string $valorDiaria = '';

    public string $observacoes = '';

    protected function rules(): array
    {
        return [
            'ativoId' => ['required', 'integer', 'exists:ativos,id'],
            'clienteId' => ['required', 'integer', 'exists:clientes,id'],
            'dataInicio' => ['required', 'date'],
            'dataFim' => ['required', 'date', 'after_or_equal:dataInicio'],
            'valorDiaria' => ['required', 'numeric', 'min:0.01'],
            'observacoes' => ['nullable', 'string'],
        ];
    }

    #[On('contrato-form-novo')]
    public function novo(): void
    {
        $this->reset([
            'ativoId', 'buscaAtivo', 'painelAtivoAberto',
            'clienteId', 'buscaCliente', 'painelClienteAberto',
            'dataInicio', 'dataFim', 'valorDiaria', 'observacoes',
        ]);
        $this->resetErrorBag();
    }

    public function abrirPainelAtivo(): void
    {
        $this->painelAtivoAberto = true;
    }

    public function fecharPainelAtivo(): void
    {
        $this->painelAtivoAberto = false;
    }

    public function selecionarAtivo(int $ativoId): void
    {
        $this->ativoId = $ativoId;
        $this->buscaAtivo = '';
        $this->painelAtivoAberto = false;

        // só sugestão pro campo - o usuário ainda pode editar antes de salvar
        $valorReferencia = Ativo::find($ativoId)?->valor_diaria_referencia;

        if ($valorReferencia !== null) {
            $this->valorDiaria = (string) $valorReferencia;
        }
    }

    public function limparAtivoSelecionado(): void
    {
        $this->ativoId = null;
    }

    public function abrirPainelCliente(): void
    {
        $this->painelClienteAberto = true;
    }

    public function fecharPainelCliente(): void
    {
        $this->painelClienteAberto = false;
    }

    public function selecionarCliente(int $clienteId): void
    {
        $this->clienteId = $clienteId;
        $this->buscaCliente = '';
        $this->painelClienteAberto = false;
    }

    public function limparClienteSelecionado(): void
    {
        $this->clienteId = null;
    }

    public function salvar(ContratoService $contratoService): void
    {
        Gate::authorize('create', Contrato::class);

        $this->validate();

        $dados = [
            'ativo_id' => $this->ativoId,
            'cliente_id' => $this->clienteId,
            'data_inicio' => $this->dataInicio,
            'data_fim' => $this->dataFim,
            'valor_diaria' => $this->valorDiaria,
            'observacoes' => $this->observacoes !== '' ? $this->observacoes : null,
        ];

        try {
            $contratoService->criar($dados);
        } catch (BusinessException $e) {
            $this->addError('ativoId', $e->getMessage());

            return;
        }

        $this->dispatch('close-modal', 'contrato-form');
        $this->dispatch('contrato-salvo', mensagem: 'Contrato criado');
        $this->novo();
    }

    public function render()
    {
        $ativosDisponiveis = Ativo::query()
            ->where('status', StatusAtivo::DISPONIVEL)
            ->when($this->buscaAtivo, fn ($query) => $query->where('nome', 'like', "%{$this->buscaAtivo}%"))
            ->orderBy('nome')
            ->get();

        $clientesFiltrados = Cliente::query()
            ->where('ativo', true)
            ->when($this->buscaCliente, fn ($query) => $query->where('nome', 'like', "%{$this->buscaCliente}%"))
            ->orderBy('nome')
            ->get();

        return view('livewire.contratos.contrato-form', [
            'ativosDisponiveis' => $ativosDisponiveis,
            'ativoSelecionado' => $this->ativoId ? Ativo::find($this->ativoId) : null,
            'clientesFiltrados' => $clientesFiltrados,
            'clienteSelecionado' => $this->clienteId ? Cliente::find($this->clienteId) : null,
        ]);
    }
}
