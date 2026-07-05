<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="font-display text-2xl font-semibold text-ink">Manutenção</h1>
                <p class="text-sm text-ink-muted">Alertas de manutenção e ordens de serviço.</p>
            </div>

            @can('create', \App\Models\OrdemDeServico::class)
                <button
                    type="button"
                    x-data=""
                    x-on:click="$dispatch('open-modal', 'ordem-servico-form'); $dispatch('ordem-servico-form-novo')"
                    class="inline-flex items-center px-4 py-2 bg-brand hover:bg-brand-dark rounded-md font-semibold text-xs text-white uppercase tracking-widest transition"
                >
                    Nova Ordem de Serviço
                </button>
            @endcan
        </div>

        @if ($mensagem)
            <div class="rounded-md bg-status-disponivel/10 text-status-disponivel px-4 py-3 text-sm" wire:key="mensagem-sucesso">
                {{ $mensagem }}
            </div>
        @endif

        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="font-display text-lg font-semibold text-ink">Alertas</h2>
                <label class="inline-flex items-center gap-2 text-sm text-ink-muted">
                    <input type="checkbox" wire:model.live="mostrarAlertasResolvidos" class="rounded border-border text-brand focus:ring-brand">
                    Mostrar resolvidos
                </label>
            </div>

            <div class="bg-surface-card border border-border rounded-lg shadow-sm">
                @if ($alertasPendentes->isEmpty())
                    <div class="p-8 text-center">
                        <p class="text-ink-muted">Nenhum alerta pendente no momento.</p>
                    </div>
                @else
                    <table class="min-w-full divide-y divide-border">
                        <thead>
                            <tr class="text-left text-xs font-medium text-ink-muted uppercase tracking-wide">
                                <th class="px-4 py-3">Ativo</th>
                                <th class="px-4 py-3">Motivo</th>
                                <th class="px-4 py-3">Pendente há</th>
                                <th class="px-4 py-3 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @foreach ($alertasPendentes as $alerta)
                                <tr wire:key="alerta-{{ $alerta->id }}" class="hover:bg-surface">
                                    <td class="px-4 py-3 text-ink">{{ $alerta->ativo->nome }}</td>
                                    <td class="px-4 py-3 text-ink-muted">{{ $alerta->tipo->label() }}</td>
                                    <td class="px-4 py-3 text-signal font-medium">{{ $alerta->data_alerta->diffForHumans() }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-3">
                                            @can('create', \App\Models\OrdemDeServico::class)
                                                <button
                                                    type="button"
                                                    x-data=""
                                                    x-on:click="$dispatch('open-modal', 'ordem-servico-form'); $dispatch('ordem-servico-form-novo', { alertaId: {{ $alerta->id }} })"
                                                    class="text-sm text-brand hover:text-brand-dark"
                                                >
                                                    Abrir Ordem de Serviço
                                                </button>
                                            @endcan

                                            @can('resolver', $alerta)
                                                <button
                                                    type="button"
                                                    wire:click="resolverAlerta({{ $alerta->id }})"
                                                    wire:confirm="Marcar este alerta como resolvido?"
                                                    class="text-sm text-ink-muted hover:text-ink"
                                                >
                                                    Marcar como resolvido
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            @if ($mostrarAlertasResolvidos)
                <div class="bg-surface-card border border-border rounded-lg shadow-sm">
                    @if ($alertasResolvidos->isEmpty())
                        <div class="p-8 text-center">
                            <p class="text-ink-muted">Nenhum alerta resolvido ainda.</p>
                        </div>
                    @else
                        <table class="min-w-full divide-y divide-border">
                            <thead>
                                <tr class="text-left text-xs font-medium text-ink-muted uppercase tracking-wide">
                                    <th class="px-4 py-3">Ativo</th>
                                    <th class="px-4 py-3">Motivo</th>
                                    <th class="px-4 py-3">Gerado em</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                @foreach ($alertasResolvidos as $alerta)
                                    <tr wire:key="alerta-resolvido-{{ $alerta->id }}" class="hover:bg-surface opacity-75">
                                        <td class="px-4 py-3 text-ink">{{ $alerta->ativo->nome }}</td>
                                        <td class="px-4 py-3 text-ink-muted">{{ $alerta->tipo->label() }}</td>
                                        <td class="px-4 py-3 font-mono text-ink-muted">{{ $alerta->data_alerta->format('d/m/Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            @endif
        </div>

        <div class="space-y-4">
            <h2 class="font-display text-lg font-semibold text-ink">Ordens de serviço</h2>

            <div class="bg-surface-card border border-border rounded-lg shadow-sm">
                <div class="p-4 border-b border-border flex flex-col sm:flex-row gap-3">
                    <select wire:model.live="statusOS" class="rounded-md border-border focus:border-brand focus:ring-brand text-sm">
                        <option value="">Todas as situações</option>
                        @foreach ($statusOptions as $opcao)
                            <option value="{{ $opcao->value }}">{{ $opcao->label() }}</option>
                        @endforeach
                    </select>

                    <select wire:model.live="tecnicoId" class="rounded-md border-border focus:border-brand focus:ring-brand text-sm">
                        <option value="">Todos os técnicos</option>
                        @foreach ($tecnicos as $tecnico)
                            <option value="{{ $tecnico->id }}">{{ $tecnico->name }}</option>
                        @endforeach
                    </select>
                </div>

                @if ($ordens->isEmpty())
                    <div class="p-12 text-center">
                        <p class="text-ink-muted">Nenhuma ordem de serviço encontrada.</p>
                    </div>
                @else
                    <table class="min-w-full divide-y divide-border">
                        <thead>
                            <tr class="text-left text-xs font-medium text-ink-muted uppercase tracking-wide">
                                <th class="px-4 py-3">Situação</th>
                                <th class="px-4 py-3">Ativo</th>
                                <th class="px-4 py-3">Tipo</th>
                                <th class="px-4 py-3">Técnico</th>
                                <th class="px-4 py-3">Custo</th>
                                <th class="px-4 py-3 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @foreach ($ordens as $os)
                                <tr wire:key="os-{{ $os->id }}" class="hover:bg-surface">
                                    <td class="px-4 py-3"><x-status-badge :status="$os->status" /></td>
                                    <td class="px-4 py-3 text-ink">{{ $os->ativo->nome }}</td>
                                    <td class="px-4 py-3 text-ink-muted">{{ $os->tipo->label() }}</td>
                                    <td class="px-4 py-3 text-ink-muted">{{ $os->tecnico->name }}</td>
                                    <td class="px-4 py-3 font-mono text-ink-muted">
                                        {{ $os->custo !== null ? 'R$ '.number_format($os->custo, 2, ',', '.') : '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        @can('fechar', $os)
                                            @if ($os->status !== $statusFechada)
                                                <button
                                                    type="button"
                                                    x-data=""
                                                    x-on:click="$dispatch('open-modal', 'ordem-servico-fechar-form'); $dispatch('ordem-servico-fechar-form-novo', { ordemServicoId: {{ $os->id }} })"
                                                    class="text-sm text-brand hover:text-brand-dark"
                                                >
                                                    Fechar Ordem
                                                </button>
                                            @endif
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>

    @can('create', \App\Models\OrdemDeServico::class)
        <x-modal name="ordem-servico-form" maxWidth="lg">
            <livewire:manutencao.ordem-servico-abrir-form />
        </x-modal>

        <x-modal name="ordem-servico-fechar-form" maxWidth="md">
            <livewire:manutencao.ordem-servico-fechar-form />
        </x-modal>
    @endcan
</div>
