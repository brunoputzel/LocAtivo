<?php

namespace App\Livewire\Ativos;

use App\Models\Ativo;
use App\Services\AtivoService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class AtivoForm extends Component
{
    public ?int $ativoId = null;

    public string $nome = '';

    public string $tipo = '';

    public string $modelo = '';

    public string $numeroSerie = '';

    public string $fotoUrl = '';

    public string $horimetro = '';

    public string $valorDiariaReferencia = '';

    protected function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'tipo' => ['required', 'string', 'max:255'],
            'modelo' => ['required', 'string', 'max:255'],
            'numeroSerie' => [
                'required', 'string', 'max:255',
                Rule::unique('ativos', 'numero_serie')->ignore($this->ativoId),
            ],
            'fotoUrl' => ['nullable', 'url', 'max:2048'],
            'horimetro' => ['nullable', 'numeric', 'min:0'],
            'valorDiariaReferencia' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    #[On('ativo-form-novo')]
    public function novo(): void
    {
        $this->reset(['ativoId', 'nome', 'tipo', 'modelo', 'numeroSerie', 'fotoUrl', 'horimetro', 'valorDiariaReferencia']);
        $this->resetErrorBag();
    }

    #[On('ativo-form-editar')]
    public function editar(int $ativoId): void
    {
        $ativo = Ativo::findOrFail($ativoId);

        $this->ativoId = $ativo->id;
        $this->nome = $ativo->nome;
        $this->tipo = $ativo->tipo;
        $this->modelo = $ativo->modelo;
        $this->numeroSerie = $ativo->numero_serie;
        $this->fotoUrl = $ativo->foto_url ?? '';
        $this->horimetro = (string) $ativo->horimetro;
        $this->valorDiariaReferencia = $ativo->valor_diaria_referencia !== null ? (string) $ativo->valor_diaria_referencia : '';
        $this->resetErrorBag();
    }

    public function salvar(AtivoService $ativoService): void
    {
        $this->validate();

        $dados = [
            'nome' => $this->nome,
            'tipo' => $this->tipo,
            'modelo' => $this->modelo,
            'numero_serie' => $this->numeroSerie,
            'foto_url' => $this->fotoUrl !== '' ? $this->fotoUrl : null,
        ];

        // omitido (não null) quando vazio - a coluna tem default 0 no banco
        if ($this->horimetro !== '') {
            $dados['horimetro'] = $this->horimetro;
        }

        $dados['valor_diaria_referencia'] = $this->valorDiariaReferencia !== '' ? $this->valorDiariaReferencia : null;

        if ($this->ativoId) {
            $ativo = Ativo::findOrFail($this->ativoId);
            Gate::authorize('update', $ativo);
            $ativoService->atualizar($ativo, $dados);
            $mensagem = 'Ativo atualizado';
        } else {
            Gate::authorize('create', Ativo::class);
            $ativoService->criar($dados);
            $mensagem = 'Ativo cadastrado';
        }

        $this->dispatch('close-modal', 'ativo-form');
        $this->dispatch('ativo-salvo', mensagem: $mensagem);
        $this->novo();
    }

    public function render()
    {
        return view('livewire.ativos.ativo-form');
    }
}
