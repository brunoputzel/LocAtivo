<div>
    <form wire:submit="salvar" class="p-6">
        <h2 class="font-display text-lg font-semibold text-ink">
            {{ $ativoId ? 'Editar equipamento' : 'Novo equipamento' }}
        </h2>

        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <x-input-label for="nome" value="Nome" />
                <x-text-input wire:model.blur="nome" id="nome" class="block mt-1 w-full" />
                <x-input-error :messages="$errors->get('nome')" class="mt-2" />
            </div>

            <div wire:key="tipo-ativo-busca-wrapper">
                <x-input-label value="Tipo" />

                @if ($tipoAtivoSelecionado)
                    <div class="mt-1 flex items-center justify-between rounded-md border border-border px-3 py-2 text-sm">
                        <span class="text-ink">{{ $tipoAtivoSelecionado->nome }}</span>
                        <button type="button" wire:click="limparTipoAtivoSelecionado" class="text-xs font-medium text-brand hover:text-brand-dark">
                            Trocar
                        </button>
                    </div>
                @else
                    <div class="relative mt-1" x-data x-on:click.outside="$wire.fecharPainelTipoAtivo()">
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="buscaTipoAtivo"
                            wire:focus="abrirPainelTipoAtivo"
                            placeholder="Buscar tipo de ativo..."
                            class="block w-full rounded-md border-border focus:border-brand focus:ring-brand text-sm"
                        >

                        @if ($painelTipoAtivoAberto)
                            <div class="absolute z-10 mt-1 w-full bg-surface-card border border-border rounded-md shadow-lg max-h-60 overflow-auto">
                                @forelse ($tiposAtivoFiltrados as $tipoAtivo)
                                    <button
                                        type="button"
                                        wire:key="tipo-ativo-opcao-{{ $tipoAtivo->id }}"
                                        wire:click="selecionarTipoAtivo({{ $tipoAtivo->id }})"
                                        class="block w-full text-left px-3 py-2 text-sm hover:bg-surface"
                                    >
                                        {{ $tipoAtivo->nome }}
                                    </button>
                                @empty
                                    <p wire:key="tipo-ativo-vazio" class="px-3 py-2 text-sm text-ink-muted">Nenhum tipo encontrado.</p>
                                @endforelse

                                @if ($podeCadastrarNovoTipoAtivo)
                                    <button
                                        type="button"
                                        wire:key="tipo-ativo-cadastrar-novo"
                                        wire:click="cadastrarTipoAtivo"
                                        class="block w-full text-left px-3 py-2 text-sm font-medium text-brand hover:bg-surface border-t border-border"
                                    >
                                        + Cadastrar novo tipo: "{{ trim($buscaTipoAtivo) }}"
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif
                <x-input-error :messages="$errors->get('tipoAtivoId')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="modelo" value="Modelo" />
                <x-text-input wire:model.blur="modelo" id="modelo" class="block mt-1 w-full" />
                <x-input-error :messages="$errors->get('modelo')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="numeroSerie" value="Número de série" />
                <x-text-input wire:model.blur="numeroSerie" id="numeroSerie" class="block mt-1 w-full font-mono" />
                <x-input-error :messages="$errors->get('numeroSerie')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="horimetro" value="Horímetro" />
                <x-text-input wire:model.blur="horimetro" id="horimetro" type="number" step="0.01" min="0" class="block mt-1 w-full font-mono" />
                <p class="mt-1 text-xs text-ink-muted">Usado apenas para controle interno, sem impacto automático no sistema por enquanto.</p>
                <x-input-error :messages="$errors->get('horimetro')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="valorDiariaReferencia" value="Valor diária de referência (R$)" />
                <x-text-input wire:model.blur="valorDiariaReferencia" id="valorDiariaReferencia" type="number" step="0.01" min="0" class="block mt-1 w-full font-mono" />
                <p class="mt-1 text-xs text-ink-muted">Opcional. Preenche automaticamente o valor diário sugerido ao criar um contrato para este equipamento.</p>
                <x-input-error :messages="$errors->get('valorDiariaReferencia')" class="mt-2" />
            </div>

            <div class="sm:col-span-2" wire:loading.class="opacity-50" wire:target="novaFoto">
                <x-input-label value="Foto do equipamento (opcional)" />

                @if ($novaFoto && $novaFoto->isPreviewable())
                    <div class="mt-3">
                        <img src="{{ $novaFoto->temporaryUrl() }}" alt="Prévia da nova foto" class="h-32 w-32 object-cover rounded-md border border-border">
                        <button type="button" wire:click="$set('novaFoto', null)" class="mt-2 block text-sm text-ink-muted hover:text-ink">
                            Cancelar
                        </button>
                    </div>
                @elseif ($novaFoto)
                    <div class="mt-3">
                        <p class="text-sm text-ink-muted">Arquivo selecionado não é uma imagem válida.</p>
                        <button type="button" wire:click="$set('novaFoto', null)" class="mt-2 block text-sm text-ink-muted hover:text-ink">
                            Cancelar
                        </button>
                    </div>
                @elseif ($fotoAtualUrl)
                    <div class="mt-3">
                        <img src="{{ $fotoAtualUrl }}" alt="Foto atual do equipamento" class="h-32 w-32 object-cover rounded-md border border-border">
                        <div class="mt-2 flex gap-4">
                            <label class="text-sm font-medium text-brand hover:text-brand-dark cursor-pointer">
                                Substituir
                                <input type="file" wire:model="novaFoto" accept="image/jpeg,image/png,image/webp" class="hidden">
                            </label>
                            <button type="button" wire:click="removerFotoAtual" class="text-sm text-status-cancelado hover:opacity-75">
                                Remover
                            </button>
                        </div>
                    </div>
                @else
                    <label class="mt-1 inline-flex items-center px-4 py-2 bg-brand hover:bg-brand-dark rounded-md font-semibold text-xs text-white uppercase tracking-widest transition cursor-pointer">
                        Selecionar foto
                        <input type="file" wire:model="novaFoto" accept="image/jpeg,image/png,image/webp" class="hidden">
                    </label>
                @endif

                <p class="mt-1 text-xs text-ink-muted">JPG, PNG ou WEBP, até 2MB.</p>
                <x-input-error :messages="$errors->get('novaFoto')" class="mt-2" />
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-3 border-t border-border pt-4">
            <x-secondary-button type="button" x-on:click="$dispatch('close')">
                Cancelar
            </x-secondary-button>

            <x-primary-button class="bg-brand hover:bg-brand-dark focus:bg-brand-dark active:bg-brand-dark">
                Salvar
            </x-primary-button>
        </div>
    </form>
</div>
