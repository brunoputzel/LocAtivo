<?php

namespace App\Livewire\Manutencao;

use App\Enums\PerfilUsuario;
use App\Enums\TipoOrdemServico;
use App\Models\AlertaManutencao;
use App\Models\Ativo;
use App\Models\OrdemDeServico;
use App\Models\User;
use App\Services\OrdemDeServicoService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class OrdemServicoAbrirForm extends Component
{
    public ?int $ativoId = null;

    public ?int $alertaId = null;

    public ?int $tecnicoId = null;

    public string $tipo = 'corretiva';

    public string $descricao = '';

    protected function rules(): array
    {
        return [
            'ativoId' => ['required', 'integer', 'exists:ativos,id'],
            'tecnicoId' => ['required', 'integer', 'exists:users,id'],
            'tipo' => ['required', Rule::enum(TipoOrdemServico::class)],
            'descricao' => ['nullable', 'string'],
        ];
    }

    #[On('ordem-servico-form-novo')]
    public function novo(?int $alertaId = null): void
    {
        $this->reset(['ativoId', 'alertaId', 'tecnicoId', 'descricao']);
        $this->resetErrorBag();

        if ($alertaId) {
            $alerta = AlertaManutencao::find($alertaId);
            $this->alertaId = $alerta?->id;
            $this->ativoId = $alerta?->ativo_id;
            $this->tipo = TipoOrdemServico::PREVENTIVA->value;
        } else {
            $this->tipo = TipoOrdemServico::CORRETIVA->value;
        }
    }

    public function salvar(OrdemDeServicoService $ordemDeServicoService): void
    {
        Gate::authorize('create', OrdemDeServico::class);

        $this->validate();

        $ordemDeServicoService->abrir([
            'ativo_id' => $this->ativoId,
            'tecnico_id' => $this->tecnicoId,
            'alerta_id' => $this->alertaId,
            'tipo' => $this->tipo,
            'descricao' => $this->descricao !== '' ? $this->descricao : null,
        ]);

        $this->dispatch('close-modal', 'ordem-servico-form');
        $this->dispatch('ordem-servico-salva', mensagem: 'Ordem de serviço aberta.');
        $this->novo();
    }

    public function render()
    {
        return view('livewire.manutencao.ordem-servico-abrir-form', [
            'ativos' => Ativo::query()->orderBy('nome')->get(),
            'tecnicos' => User::query()->where('perfil', PerfilUsuario::TECNICO_MANUTENCAO)->where('ativo', true)->orderBy('name')->get(),
            'alertaSelecionado' => $this->alertaId ? AlertaManutencao::find($this->alertaId) : null,
        ]);
    }
}
