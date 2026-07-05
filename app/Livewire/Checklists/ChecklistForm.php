<?php

namespace App\Livewire\Checklists;

use App\Enums\TipoChecklist;
use App\Exceptions\BusinessException;
use App\Http\Requests\Api\StoreChecklistRequest;
use App\Models\Checklist;
use App\Models\Contrato;
use App\Services\ChecklistService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Registrar checklist')]
class ChecklistForm extends Component
{
    use WithFileUploads;

    public Contrato $contrato;

    // nome diferente do parâmetro $tipo do mount() de propósito - se os nomes
    // colidirem, o Livewire tenta atribuir a string crua da rota direto na
    // propriedade tipada como enum antes do mount() rodar, e quebra
    public TipoChecklist $tipoChecklist;

    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $fotos = [];

    public string $observacoes = '';

    public function mount(Contrato $contrato, string $tipo): void
    {
        Gate::authorize('create', Checklist::class);

        $this->contrato = $contrato;
        $this->tipoChecklist = TipoChecklist::from($tipo);
    }

    protected function rules(): array
    {
        // mesma fonte de validação do backend (StoreChecklistRequest) - só tira
        // 'tipo' porque aqui ele vem fixo da rota, não é campo do formulário
        $regras = StoreChecklistRequest::regras();
        unset($regras['tipo']);

        return $regras;
    }

    public function removerFoto(int $indice): void
    {
        unset($this->fotos[$indice]);
        $this->fotos = array_values($this->fotos);
    }

    public function salvar(ChecklistService $checklistService): void
    {
        $this->validate();

        try {
            $checklistService->registrar(
                $this->contrato,
                auth()->user(),
                $this->tipoChecklist,
                $this->observacoes !== '' ? $this->observacoes : null,
                $this->fotos
            );
        } catch (BusinessException $e) {
            $this->addError('fotos', $e->getMessage());

            return;
        }

        session()->flash('mensagem', $this->tipoChecklist === TipoChecklist::RETORNO
            ? 'Checklist de retorno registrado — contrato encerrado.'
            : 'Checklist de saída registrado.');

        $this->redirect(route('contratos.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.checklists.checklist-form', [
            'checklistSaida' => $this->tipoChecklist === TipoChecklist::RETORNO ? $this->contrato->checklistDeSaida() : null,
        ]);
    }
}
