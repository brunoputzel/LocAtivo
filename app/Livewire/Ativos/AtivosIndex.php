<?php

namespace App\Livewire\Ativos;

use App\Enums\StatusAtivo;
use App\Exceptions\BusinessException;
use App\Models\Ativo;
use App\Services\AtivoService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Ativos')]
class AtivosIndex extends Component
{
    public string $busca = '';

    public string $status = '';

    public ?string $mensagem = null;

    public ?string $erro = null;

    #[On('ativo-salvo')]
    public function ativoSalvo(string $mensagem): void
    {
        $this->mensagem = $mensagem;
        $this->erro = null;
    }

    public function excluir(int $ativoId, AtivoService $ativoService): void
    {
        $ativo = Ativo::findOrFail($ativoId);

        Gate::authorize('delete', $ativo);

        try {
            $ativoService->excluir($ativo);
            $this->mensagem = 'Ativo excluído';
            $this->erro = null;
        } catch (BusinessException $e) {
            $this->erro = $e->getMessage();
        }
    }

    public function limparFiltros(): void
    {
        $this->reset(['busca', 'status']);
    }

    public function render()
    {
        $ativos = Ativo::query()
            ->when($this->busca, fn ($query) => $query->where('nome', 'like', "%{$this->busca}%"))
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
            ->latest()
            ->get();

        return view('livewire.ativos.ativos-index', [
            'ativos' => $ativos,
            'statusOptions' => StatusAtivo::cases(),
            'temFiltroAtivo' => $this->busca !== '' || $this->status !== '',
            'existemAtivos' => Ativo::query()->exists(),
        ]);
    }
}
