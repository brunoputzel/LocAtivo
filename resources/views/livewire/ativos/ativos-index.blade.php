<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="font-display text-2xl font-semibold text-ink">Ativos</h1>
                <p class="text-sm text-ink-muted">Equipamentos do portfólio disponíveis pra locação.</p>
            </div>

            @can('create', \App\Models\Ativo::class)
                <button
                    type="button"
                    x-data=""
                    x-on:click="$dispatch('open-modal', 'ativo-form'); $dispatch('ativo-form-novo')"
                    class="inline-flex items-center px-4 py-2 bg-brand hover:bg-brand-dark rounded-md font-semibold text-xs text-white uppercase tracking-widest transition"
                >
                    Novo Ativo
                </button>
            @endcan
        </div>

        @if ($mensagem)
            <div class="rounded-md bg-status-disponivel/10 text-status-disponivel px-4 py-3 text-sm" wire:key="mensagem-sucesso">
                {{ $mensagem }}
            </div>
        @endif

        @if ($erro)
            <div class="rounded-md bg-status-cancelado/10 text-status-cancelado px-4 py-3 text-sm" wire:key="mensagem-erro">
                {{ $erro }}
            </div>
        @endif

        <div class="bg-surface-card border border-border rounded-lg shadow-sm">
            <div class="p-4 border-b border-border flex flex-col sm:flex-row gap-3 sm:items-center">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="busca"
                    placeholder="Buscar por nome..."
                    class="flex-1 rounded-md border-border focus:border-brand focus:ring-brand text-sm"
                >

                <select wire:model.live="status" class="rounded-md border-border focus:border-brand focus:ring-brand text-sm">
                    <option value="">Todas as situações</option>
                    @foreach ($statusOptions as $opcao)
                        <option value="{{ $opcao->value }}">{{ $opcao->label() }}</option>
                    @endforeach
                </select>
            </div>

            @if ($ativos->isEmpty() && ! $existemAtivos)
                <div class="p-12 text-center space-y-3">
                    <p class="text-ink-muted">Nenhum ativo cadastrado ainda — cadastre o primeiro.</p>
                    @can('create', \App\Models\Ativo::class)
                        <button
                            type="button"
                            x-data=""
                            x-on:click="$dispatch('open-modal', 'ativo-form'); $dispatch('ativo-form-novo')"
                            class="text-sm font-medium text-brand hover:text-brand-dark"
                        >
                            Cadastrar ativo
                        </button>
                    @endcan
                </div>
            @elseif ($ativos->isEmpty())
                <div class="p-12 text-center space-y-3">
                    <p class="text-ink-muted">Nenhum ativo encontrado com esse filtro.</p>
                    <button type="button" wire:click="limparFiltros" class="text-sm font-medium text-brand hover:text-brand-dark">
                        Limpar filtros
                    </button>
                </div>
            @else
                <table class="min-w-full divide-y divide-border">
                    <thead>
                        <tr class="text-left text-xs font-medium text-ink-muted uppercase tracking-wide">
                            <th class="px-4 py-3">Situação</th>
                            <th class="px-4 py-3">Nome</th>
                            <th class="px-4 py-3">Tipo</th>
                            <th class="px-4 py-3">Modelo</th>
                            <th class="px-4 py-3">Nº Série</th>
                            <th class="px-4 py-3">Horímetro</th>
                            <th class="px-4 py-3 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach ($ativos as $ativo)
                            <tr wire:key="ativo-{{ $ativo->id }}" class="hover:bg-surface">
                                <td class="px-4 py-3"><x-status-badge :status="$ativo->status" /></td>
                                <td class="px-4 py-3 text-ink">{{ $ativo->nome }}</td>
                                <td class="px-4 py-3 text-ink-muted">{{ $ativo->tipo }}</td>
                                <td class="px-4 py-3 text-ink-muted">{{ $ativo->modelo }}</td>
                                <td class="px-4 py-3 font-mono text-ink-muted">{{ $ativo->numero_serie }}</td>
                                <td class="px-4 py-3 font-mono text-ink-muted">{{ number_format($ativo->horimetro, 2) }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-3" x-data="{ confirmando: false }">
                                        @can('update', $ativo)
                                            <button
                                                type="button"
                                                x-show="!confirmando"
                                                x-data=""
                                                x-on:click="$dispatch('open-modal', 'ativo-form'); $dispatch('ativo-form-editar', { ativoId: {{ $ativo->id }} })"
                                                class="text-sm text-brand hover:text-brand-dark"
                                            >
                                                Editar
                                            </button>
                                        @endcan

                                        @can('delete', $ativo)
                                            <button type="button" x-show="!confirmando" x-on:click="confirmando = true" class="text-sm text-status-cancelado hover:opacity-75">
                                                Excluir
                                            </button>

                                            <div x-show="confirmando" x-cloak class="flex items-center gap-2 text-sm">
                                                @if ($ativo->status === \App\Enums\StatusAtivo::EM_LOCACAO)
                                                    <span class="text-ink-muted">Está em locação, não pode ser excluído.</span>
                                                    <button type="button" x-on:click="confirmando = false" class="text-brand hover:text-brand-dark">Fechar</button>
                                                @else
                                                    <span class="text-ink-muted">Excluir este ativo?</span>
                                                    <button type="button" wire:click="excluir({{ $ativo->id }})" x-on:click="confirmando = false" class="text-status-cancelado font-medium">Confirmar</button>
                                                    <button type="button" x-on:click="confirmando = false" class="text-ink-muted">Cancelar</button>
                                                @endif
                                            </div>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    @can('create', \App\Models\Ativo::class)
        <x-modal name="ativo-form" maxWidth="lg">
            <livewire:ativos.ativo-form />
        </x-modal>
    @endcan
</div>
