<?php

namespace App\Livewire\Manutencao;

use App\Exceptions\BusinessException;
use App\Models\OrdemDeServico;
use App\Services\OrdemDeServicoService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\On;
use Livewire\Component;

class OrdemServicoFecharForm extends Component
{
    public ?int $ordemServicoId = null;

    public string $custo = '';

    public string $dataFechamento = '';

    protected function rules(): array
    {
        return [
            'custo' => ['required', 'numeric', 'min:0'],
            'dataFechamento' => ['nullable', 'date'],
        ];
    }

    #[On('ordem-servico-fechar-form-novo')]
    public function novo(int $ordemServicoId): void
    {
        $this->ordemServicoId = $ordemServicoId;
        $this->custo = '';
        $this->dataFechamento = now()->toDateString();
        $this->resetErrorBag();
    }

    public function salvar(OrdemDeServicoService $ordemDeServicoService): void
    {
        $os = OrdemDeServico::findOrFail($this->ordemServicoId);

        Gate::authorize('fechar', $os);

        $this->validate();

        try {
            $ordemDeServicoService->fechar(
                $os,
                (float) $this->custo,
                $this->dataFechamento !== '' ? $this->dataFechamento : null
            );
        } catch (BusinessException $e) {
            $this->addError('custo', $e->getMessage());

            return;
        }

        $this->dispatch('close-modal', 'ordem-servico-fechar-form');
        $this->dispatch('ordem-servico-salva', mensagem: 'Ordem de serviço fechada — ativo disponível novamente.');
    }

    public function render()
    {
        return view('livewire.manutencao.ordem-servico-fechar-form', [
            'ordemServico' => $this->ordemServicoId ? OrdemDeServico::with('ativo')->find($this->ordemServicoId) : null,
        ]);
    }
}
