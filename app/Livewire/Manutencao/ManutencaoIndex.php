<?php

namespace App\Livewire\Manutencao;

use App\Enums\PerfilUsuario;
use App\Enums\StatusOrdemServico;
use App\Models\AlertaManutencao;
use App\Models\OrdemDeServico;
use App\Models\User;
use App\Services\AlertaManutencaoService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Manutenção')]
class ManutencaoIndex extends Component
{
    public string $statusOS = '';

    public string $tecnicoId = '';

    public bool $mostrarAlertasResolvidos = false;

    public ?string $mensagem = null;

    #[On('ordem-servico-salva')]
    public function ordemServicoSalva(string $mensagem): void
    {
        $this->mensagem = $mensagem;
    }

    public function resolverAlerta(int $alertaId, AlertaManutencaoService $alertaManutencaoService): void
    {
        $alerta = AlertaManutencao::findOrFail($alertaId);

        Gate::authorize('resolver', $alerta);

        $alertaManutencaoService->resolver($alerta);

        $this->mensagem = 'Alerta marcado como resolvido.';
    }

    public function render()
    {
        $alertasPendentes = AlertaManutencao::query()
            ->where('resolvido', false)
            ->with('ativo')
            ->orderBy('data_alerta')
            ->get();

        $alertasResolvidos = AlertaManutencao::query()
            ->where('resolvido', true)
            ->with('ativo')
            ->latest('updated_at')
            ->get();

        $ordens = OrdemDeServico::query()
            ->when($this->statusOS, fn ($query) => $query->where('status', $this->statusOS))
            ->when($this->tecnicoId, fn ($query) => $query->where('tecnico_id', $this->tecnicoId))
            ->with(['ativo', 'tecnico'])
            ->latest()
            ->get();

        return view('livewire.manutencao.manutencao-index', [
            'alertasPendentes' => $alertasPendentes,
            'alertasResolvidos' => $alertasResolvidos,
            'ordens' => $ordens,
            'tecnicos' => User::query()->where('perfil', PerfilUsuario::TECNICO_MANUTENCAO)->where('ativo', true)->orderBy('name')->get(),
            'statusOptions' => StatusOrdemServico::cases(),
            'statusFechada' => StatusOrdemServico::FECHADA,
        ]);
    }
}
