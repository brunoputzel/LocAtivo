<div>
    <form wire:submit="salvar" class="p-6">
        <h2 class="font-display text-lg font-semibold text-ink">Novo contrato</h2>

        <div class="mt-6 space-y-4">
            <div wire:key="ativo-busca-wrapper">
                <x-input-label value="Ativo" />

                @if ($ativoSelecionado)
                    <div class="mt-1 flex items-center justify-between rounded-md border border-border px-3 py-2 text-sm bg-surface">
                        <span>
                            {{ $ativoSelecionado->nome }} — {{ $ativoSelecionado->tipoAtivo?->nome }} —
                            <span class="font-mono">{{ $ativoSelecionado->numero_serie }}</span>
                        </span>
                        <button type="button" wire:click="limparAtivoSelecionado" class="text-xs text-brand hover:text-brand-dark">
                            Trocar
                        </button>
                    </div>
                @else
                    <div class="relative mt-1" x-data x-on:click.outside="$wire.fecharPainelAtivo()">
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="buscaAtivo"
                            wire:focus="abrirPainelAtivo"
                            placeholder="Buscar ativo disponível por nome..."
                            class="block w-full rounded-md border-border focus:border-brand focus:ring-brand text-sm"
                        >

                        @if ($painelAtivoAberto)
                            <div class="absolute z-10 mt-1 w-full bg-surface-card border border-border rounded-md shadow-lg max-h-60 overflow-auto">
                                @forelse ($ativosDisponiveis as $a)
                                    <button
                                        type="button"
                                        wire:key="ativo-opcao-{{ $a->id }}"
                                        wire:click="selecionarAtivo({{ $a->id }})"
                                        class="block w-full text-left px-3 py-2 text-sm hover:bg-surface"
                                    >
                                        {{ $a->nome }} — {{ $a->tipoAtivo?->nome }} —
                                        <span class="font-mono">{{ $a->numero_serie }}</span>
                                    </button>
                                @empty
                                    <p wire:key="ativo-vazio" class="px-3 py-2 text-sm text-ink-muted">Nenhum ativo disponível encontrado.</p>
                                @endforelse
                            </div>
                        @endif
                    </div>
                @endif

                <x-input-error :messages="$errors->get('ativoId')" class="mt-2" />
            </div>

            <div wire:key="cliente-busca-wrapper">
                <x-input-label value="Cliente" />

                @if ($clienteSelecionado)
                    <div class="mt-1 flex items-center justify-between rounded-md border border-border px-3 py-2 text-sm bg-surface">
                        <span>
                            {{ $clienteSelecionado->nome }} —
                            <span class="font-mono">{{ $clienteSelecionado->cpf_cnpj }}</span>
                        </span>
                        <button type="button" wire:click="limparClienteSelecionado" class="text-xs text-brand hover:text-brand-dark">
                            Trocar
                        </button>
                    </div>
                @else
                    <div class="relative mt-1" x-data x-on:click.outside="$wire.fecharPainelCliente()">
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="buscaCliente"
                            wire:focus="abrirPainelCliente"
                            placeholder="Buscar cliente por nome..."
                            class="block w-full rounded-md border-border focus:border-brand focus:ring-brand text-sm"
                        >

                        @if ($painelClienteAberto)
                            <div class="absolute z-10 mt-1 w-full bg-surface-card border border-border rounded-md shadow-lg max-h-60 overflow-auto">
                                @forelse ($clientesFiltrados as $c)
                                    <button
                                        type="button"
                                        wire:key="cliente-opcao-{{ $c->id }}"
                                        wire:click="selecionarCliente({{ $c->id }})"
                                        class="block w-full text-left px-3 py-2 text-sm hover:bg-surface"
                                    >
                                        {{ $c->nome }} —
                                        <span class="font-mono">{{ $c->cpf_cnpj }}</span>
                                    </button>
                                @empty
                                    <p wire:key="cliente-vazio" class="px-3 py-2 text-sm text-ink-muted">Nenhum cliente encontrado.</p>
                                @endforelse
                            </div>
                        @endif
                    </div>
                @endif

                <x-input-error :messages="$errors->get('clienteId')" class="mt-2" />
            </div>

            <div
                x-data="{
                    dataInicio: @entangle('dataInicio'),
                    dataFim: @entangle('dataFim'),
                    valorDiaria: @entangle('valorDiaria'),
                    get diasEstimados() {
                        if (! this.dataInicio || ! this.dataFim) return 0;
                        const dias = Math.round((new Date(this.dataFim) - new Date(this.dataInicio)) / 86400000);
                        return dias > 0 ? dias : 0;
                    },
                    get valorTotalEstimado() {
                        const valor = parseFloat(this.valorDiaria);
                        if (! this.diasEstimados || isNaN(valor)) return null;
                        return (this.diasEstimados * valor).toFixed(2);
                    },
                }"
            >
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <x-input-label for="dataInicio" value="Data de início" />
                        <input type="date" id="dataInicio" x-model="dataInicio" class="mt-1 block w-full rounded-md border-border focus:border-brand focus:ring-brand text-sm font-mono">
                        <x-input-error :messages="$errors->get('dataInicio')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="dataFim" value="Data de fim" />
                        <input type="date" id="dataFim" x-model="dataFim" class="mt-1 block w-full rounded-md border-border focus:border-brand focus:ring-brand text-sm font-mono">
                        <x-input-error :messages="$errors->get('dataFim')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="valorDiaria" value="Valor diário (R$)" />
                        <input type="number" step="0.01" min="0" id="valorDiaria" x-model="valorDiaria" class="mt-1 block w-full rounded-md border-border focus:border-brand focus:ring-brand text-sm font-mono">
                        <x-input-error :messages="$errors->get('valorDiaria')" class="mt-2" />
                    </div>
                </div>

                <template x-if="valorTotalEstimado">
                    <p class="mt-3 text-sm text-ink-muted">
                        Valor total estimado: <span class="font-mono text-ink font-medium" x-text="'R$ ' + valorTotalEstimado"></span>
                        (<span x-text="diasEstimados"></span> dias — recalculado no servidor ao encerrar)
                    </p>
                </template>
            </div>

            <div>
                <x-input-label for="observacoes" value="Observações (opcional)" />
                <textarea
                    wire:model.blur="observacoes"
                    id="observacoes"
                    rows="3"
                    class="mt-1 block w-full rounded-md border-border focus:border-brand focus:ring-brand text-sm"
                ></textarea>
                <x-input-error :messages="$errors->get('observacoes')" class="mt-2" />
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-3 border-t border-border pt-4">
            <x-secondary-button type="button" x-on:click="$dispatch('close')">
                Cancelar
            </x-secondary-button>

            <x-primary-button class="bg-brand hover:bg-brand-dark focus:bg-brand-dark active:bg-brand-dark">
                Criar contrato
            </x-primary-button>
        </div>
    </form>
</div>
