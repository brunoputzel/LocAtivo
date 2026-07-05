<?php

namespace App\Livewire\Contratos;

use App\Enums\StatusContrato;
use App\Exceptions\BusinessException;
use App\Models\Contrato;
use App\Services\ContratoService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Contratos')]
class ContratosIndex extends Component
{
    public ?string $mensagem = null;

    public ?string $erro = null;

    public function mount(): void
    {
        // pega a mensagem de sucesso deixada pelo ChecklistForm antes do redirect
        // (evento Livewire não atravessa navegação de página, sessão sim)
        $this->mensagem = session('mensagem');
    }

    #[On('contrato-salvo')]
    public function contratoSalvo(string $mensagem): void
    {
        $this->mensagem = $mensagem;
        $this->erro = null;
    }

    public function encerrar(int $contratoId, ContratoService $contratoService): void
    {
        $contrato = Contrato::findOrFail($contratoId);

        Gate::authorize('encerrar', $contrato);

        try {
            $contrato = $contratoService->encerrar($contrato);
            $this->mensagem = 'Contrato encerrado — cobrança de R$ '.number_format((float) $contrato->cobranca->valor, 2, ',', '.').' gerada.';
            $this->erro = null;
        } catch (BusinessException $e) {
            $this->erro = $e->getMessage();
        }
    }

    public function render()
    {
        $contratos = app(ContratoService::class)
            ->listarPara(auth()->user())
            ->load(['cobranca', 'checklists']);

        return view('livewire.contratos.contratos-index', [
            'contratos' => $contratos,
            'statusAtivo' => StatusContrato::ATIVO,
        ]);
    }
}
