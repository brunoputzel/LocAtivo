<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="font-display text-2xl font-semibold text-ink">Clientes</h1>
                <p class="text-sm text-ink-muted">Pessoas físicas e jurídicas cadastradas pra locação.</p>
            </div>

            @can('create', \App\Models\Cliente::class)
                <button
                    type="button"
                    x-data=""
                    x-on:click="$dispatch('open-modal', 'cliente-form'); $dispatch('cliente-form-novo')"
                    class="inline-flex items-center px-4 py-2 bg-brand hover:bg-brand-dark rounded-md font-semibold text-xs text-white uppercase tracking-widest transition"
                >
                    Novo Cliente
                </button>
            @endcan
        </div>

        @if ($mensagem)
            <div class="rounded-md bg-status-disponivel/10 text-status-disponivel px-4 py-3 text-sm" wire:key="mensagem-sucesso">
                {{ $mensagem }}
            </div>
        @endif

        <div class="bg-surface-card border border-border rounded-lg shadow-sm">
            <div class="p-4 border-b border-border flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="busca"
                    placeholder="Buscar por nome ou CPF/CNPJ..."
                    class="flex-1 rounded-md border-border focus:border-brand focus:ring-brand text-sm"
                >

                <label class="inline-flex items-center gap-2 text-sm text-ink-muted whitespace-nowrap">
                    <input type="checkbox" wire:model.live="mostrarInativos" class="rounded border-border text-brand focus:ring-brand">
                    Mostrar inativos
                </label>
            </div>

            @if ($clientes->isEmpty() && ! $existemClientes)
                <div class="p-12 text-center space-y-3">
                    <p class="text-ink-muted">Nenhum cliente cadastrado ainda — cadastre o primeiro.</p>
                    @can('create', \App\Models\Cliente::class)
                        <button
                            type="button"
                            x-data=""
                            x-on:click="$dispatch('open-modal', 'cliente-form'); $dispatch('cliente-form-novo')"
                            class="text-sm font-medium text-brand hover:text-brand-dark"
                        >
                            Cadastrar cliente
                        </button>
                    @endcan
                </div>
            @elseif ($clientes->isEmpty())
                <div class="p-12 text-center space-y-3">
                    <p class="text-ink-muted">Nenhum cliente encontrado com esse filtro.</p>
                    <button type="button" wire:click="limparFiltros" class="text-sm font-medium text-brand hover:text-brand-dark">
                        Limpar filtros
                    </button>
                </div>
            @else
                <table class="min-w-full divide-y divide-border">
                    <thead>
                        <tr class="text-left text-xs font-medium text-ink-muted uppercase tracking-wide">
                            <th class="px-4 py-3">Nome</th>
                            <th class="px-4 py-3">Tipo</th>
                            <th class="px-4 py-3">CPF/CNPJ</th>
                            <th class="px-4 py-3">Contato</th>
                            <th class="px-4 py-3 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach ($clientes as $cliente)
                            <tr wire:key="cliente-{{ $cliente->id }}" class="hover:bg-surface {{ ! $cliente->ativo ? 'opacity-50' : '' }}">
                                <td class="px-4 py-3">
                                    <a href="{{ route('clientes.show', $cliente) }}" wire:navigate class="text-ink hover:text-brand font-medium">
                                        {{ $cliente->nome }}
                                    </a>
                                    @unless ($cliente->ativo)
                                        <span class="ml-2 text-xs text-ink-muted">(inativo)</span>
                                    @endunless
                                </td>
                                <td class="px-4 py-3 text-ink-muted">{{ $cliente->tipo->value }}</td>
                                <td class="px-4 py-3 font-mono text-ink-muted">{{ $cliente->cpf_cnpj }}</td>
                                <td class="px-4 py-3 text-ink-muted">{{ $cliente->email ?? $cliente->telefone ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-3">
                                        @can('update', $cliente)
                                            <button
                                                type="button"
                                                x-data=""
                                                x-on:click="$dispatch('open-modal', 'cliente-form'); $dispatch('cliente-form-editar', { clienteId: {{ $cliente->id }} })"
                                                class="text-sm text-brand hover:text-brand-dark"
                                            >
                                                Editar
                                            </button>
                                        @endcan

                                        @can('delete', $cliente)
                                            @if ($cliente->ativo)
                                                <button type="button" wire:click="inativar({{ $cliente->id }})" wire:confirm="Inativar este cliente?" class="text-sm text-status-cancelado hover:opacity-75">
                                                    Inativar
                                                </button>
                                            @endif
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

    @can('create', \App\Models\Cliente::class)
        <x-modal name="cliente-form" maxWidth="lg">
            <livewire:clientes.cliente-form />
        </x-modal>
    @endcan
</div>
