<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="font-display text-2xl font-semibold text-ink">Contratos</h1>
                <p class="text-sm text-ink-muted">Locações de equipamentos em andamento e encerradas.</p>
            </div>

            @can('create', \App\Models\Contrato::class)
                <button
                    type="button"
                    x-data=""
                    x-on:click="$dispatch('open-modal', 'contrato-form'); $dispatch('contrato-form-novo')"
                    class="inline-flex items-center px-4 py-2 bg-brand hover:bg-brand-dark rounded-md font-semibold text-xs text-white uppercase tracking-widest transition"
                >
                    Novo Contrato
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
            @if ($contratos->isEmpty())
                <div class="p-12 text-center space-y-3">
                    <p class="text-ink-muted">Nenhum contrato cadastrado ainda — cadastre o primeiro.</p>
                    @can('create', \App\Models\Contrato::class)
                        <button
                            type="button"
                            x-data=""
                            x-on:click="$dispatch('open-modal', 'contrato-form'); $dispatch('contrato-form-novo')"
                            class="text-sm font-medium text-brand hover:text-brand-dark"
                        >
                            Cadastrar contrato
                        </button>
                    @endcan
                </div>
            @else
                <table class="min-w-full divide-y divide-border">
                    <thead>
                        <tr class="text-left text-xs font-medium text-ink-muted uppercase tracking-wide">
                            <th class="px-4 py-3">Situação</th>
                            <th class="px-4 py-3">Ativo</th>
                            <th class="px-4 py-3">Cliente</th>
                            <th class="px-4 py-3">Período</th>
                            <th class="px-4 py-3">Valor diário</th>
                            <th class="px-4 py-3">Cobrança</th>
                            <th class="px-4 py-3 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach ($contratos as $contrato)
                            <tr
                                wire:key="contrato-{{ $contrato->id }}"
                                class="hover:bg-surface {{ $contrato->venceEmBreve() ? 'border-l-4 border-signal bg-signal/5' : '' }}"
                            >
                                <td class="px-4 py-3">
                                    <x-status-badge :status="$contrato->status" />
                                    @if ($contrato->venceEmBreve())
                                        <div class="mt-1 text-xs text-signal font-medium">Vence em breve</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-ink">{{ $contrato->ativo->nome }}</td>
                                <td class="px-4 py-3 text-ink">{{ $contrato->cliente->nome }}</td>
                                <td class="px-4 py-3 font-mono text-ink-muted">
                                    {{ $contrato->data_inicio->format('d/m/Y') }} – {{ $contrato->data_fim->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3 font-mono text-ink-muted">
                                    R$ {{ number_format($contrato->valor_diaria, 2, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 font-mono text-ink-muted">
                                    @if ($contrato->cobranca)
                                        R$ {{ number_format($contrato->cobranca->valor, 2, ',', '.') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        @can('create', \App\Models\Checklist::class)
                                            @if ($contrato->status === $statusAtivo)
                                                @if (! $contrato->checklists->firstWhere('tipo', \App\Enums\TipoChecklist::SAIDA))
                                                    <a href="{{ route('checklists.form', ['contrato' => $contrato->id, 'tipo' => 'saida']) }}" wire:navigate class="text-sm text-brand hover:text-brand-dark">
                                                        Checklist de saída
                                                    </a>
                                                @else
                                                    <a href="{{ route('checklists.form', ['contrato' => $contrato->id, 'tipo' => 'retorno']) }}" wire:navigate class="text-sm text-brand hover:text-brand-dark">
                                                        Checklist de retorno
                                                    </a>
                                                @endif
                                            @endif
                                        @endcan

                                        @can('encerrar', $contrato)
                                            @if ($contrato->status === $statusAtivo)
                                                <button
                                                    type="button"
                                                    wire:click="encerrar({{ $contrato->id }})"
                                                    wire:confirm="Encerrar este contrato? O ativo volta a ficar disponível e uma cobrança é gerada."
                                                    class="text-sm text-status-cancelado hover:opacity-75"
                                                >
                                                    Encerrar contrato
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

    @can('create', \App\Models\Contrato::class)
        <x-modal name="contrato-form" maxWidth="xl">
            <livewire:contratos.contrato-form />
        </x-modal>
    @endcan
</div>
