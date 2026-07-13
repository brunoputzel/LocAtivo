<?php

namespace App\Livewire\Ativos;

use App\Models\Ativo;
use App\Models\TipoAtivo;
use App\Services\AtivoService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class AtivoForm extends Component
{
    use WithFileUploads;

    public ?int $ativoId = null;

    public string $nome = '';

    public ?int $tipoAtivoId = null;

    public string $buscaTipoAtivo = '';

    public bool $painelTipoAtivoAberto = false;

    public string $modelo = '';

    public string $numeroSerie = '';

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $novaFoto = null;

    public ?string $fotoAtualPath = null;

    public bool $fotoRemovida = false;

    public string $horimetro = '';

    public string $valorDiariaReferencia = '';

    protected function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'tipoAtivoId' => ['required', 'integer', 'exists:tipo_ativos,id'],
            'modelo' => ['required', 'string', 'max:255'],
            'numeroSerie' => [
                'required', 'string', 'max:255',
                Rule::unique('ativos', 'numero_serie')->ignore($this->ativoId),
            ],
            'novaFoto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'horimetro' => ['nullable', 'numeric', 'min:0'],
            'valorDiariaReferencia' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    #[On('ativo-form-novo')]
    public function novo(): void
    {
        $this->reset([
            'ativoId', 'nome', 'tipoAtivoId', 'buscaTipoAtivo', 'painelTipoAtivoAberto',
            'modelo', 'numeroSerie', 'novaFoto', 'fotoAtualPath', 'fotoRemovida',
            'horimetro', 'valorDiariaReferencia',
        ]);
        $this->resetErrorBag();
    }

    #[On('ativo-form-editar')]
    public function editar(int $ativoId): void
    {
        $ativo = Ativo::findOrFail($ativoId);

        $this->ativoId = $ativo->id;
        $this->nome = $ativo->nome;
        $this->tipoAtivoId = $ativo->tipo_ativo_id;
        $this->buscaTipoAtivo = '';
        $this->painelTipoAtivoAberto = false;
        $this->modelo = $ativo->modelo;
        $this->numeroSerie = $ativo->numero_serie;
        $this->novaFoto = null;
        $this->fotoAtualPath = $ativo->foto_url;
        $this->fotoRemovida = false;
        $this->horimetro = (string) $ativo->horimetro;
        $this->valorDiariaReferencia = $ativo->valor_diaria_referencia !== null ? (string) $ativo->valor_diaria_referencia : '';
        $this->resetErrorBag();
    }

    public function updatedNovaFoto(): void
    {
        $this->fotoRemovida = false;
    }

    public function removerFotoAtual(): void
    {
        $this->novaFoto = null;
        $this->fotoRemovida = true;
    }

    public function abrirPainelTipoAtivo(): void
    {
        $this->painelTipoAtivoAberto = true;
    }

    public function fecharPainelTipoAtivo(): void
    {
        $this->painelTipoAtivoAberto = false;
    }

    public function selecionarTipoAtivo(int $tipoAtivoId): void
    {
        $this->tipoAtivoId = $tipoAtivoId;
        $this->buscaTipoAtivo = '';
        $this->painelTipoAtivoAberto = false;
    }

    public function limparTipoAtivoSelecionado(): void
    {
        $this->tipoAtivoId = null;
    }

    public function cadastrarTipoAtivo(): void
    {
        Gate::authorize($this->ativoId ? 'update' : 'create', $this->ativoId ? Ativo::findOrFail($this->ativoId) : Ativo::class);

        $nome = trim($this->buscaTipoAtivo);

        if ($nome === '') {
            return;
        }

        // comparação sem case-sensitive pra não duplicar "Gerador" e "gerador"
        $tipoAtivo = TipoAtivo::whereRaw('LOWER(nome) = ?', [mb_strtolower($nome)])->first()
            ?? TipoAtivo::create(['nome' => $nome]);

        $this->selecionarTipoAtivo($tipoAtivo->id);
    }

    public function salvar(AtivoService $ativoService): void
    {
        $this->validate();

        $dados = [
            'nome' => $this->nome,
            'tipo_ativo_id' => $this->tipoAtivoId,
            'modelo' => $this->modelo,
            'numero_serie' => $this->numeroSerie,
        ];

        if ($this->novaFoto) {
            $dados['foto_url'] = $this->novaFoto->store('ativos', 's3');
        } elseif ($this->fotoRemovida || ! $this->ativoId) {
            $dados['foto_url'] = null;
        }

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
        $tiposAtivoFiltrados = TipoAtivo::query()
            ->when($this->buscaTipoAtivo, fn ($query) => $query->where('nome', 'like', "%{$this->buscaTipoAtivo}%"))
            ->orderBy('nome')
            ->get();

        $buscaNormalizada = mb_strtolower(trim($this->buscaTipoAtivo));
        $existeTipoAtivoExato = $buscaNormalizada !== '' && TipoAtivo::whereRaw('LOWER(nome) = ?', [$buscaNormalizada])->exists();

        return view('livewire.ativos.ativo-form', [
            'tipoAtivoSelecionado' => $this->tipoAtivoId ? TipoAtivo::find($this->tipoAtivoId) : null,
            'tiposAtivoFiltrados' => $tiposAtivoFiltrados,
            'podeCadastrarNovoTipoAtivo' => $buscaNormalizada !== '' && ! $existeTipoAtivoExato,
            'fotoAtualUrl' => $this->resolverUrlFotoAtual(),
        ]);
    }

    private function resolverUrlFotoAtual(): ?string
    {
        if (! $this->fotoAtualPath) {
            return null;
        }

        // registros antigos podem ter uma URL absoluta salva (cadastrados via API,
        // antes do upload de arquivo) - nesse caso não passa pelo resolver do disco
        if (str_starts_with($this->fotoAtualPath, 'http://') || str_starts_with($this->fotoAtualPath, 'https://')) {
            return $this->fotoAtualPath;
        }

        return Storage::disk('s3')->url($this->fotoAtualPath);
    }
}
