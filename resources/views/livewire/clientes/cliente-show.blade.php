<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <div>
            <a href="{{ route('clientes.index') }}" wire:navigate class="text-sm text-brand hover:text-brand-dark">&larr; Clientes</a>

            <div class="mt-2 flex items-center gap-3">
                <h1 class="font-display text-2xl font-semibold text-ink">{{ $cliente->nome }}</h1>
                @unless ($cliente->ativo)
                    <span class="rounded-full bg-status-inativo/10 text-status-inativo px-2.5 py-0.5 text-xs font-medium">Inativo</span>
                @endunless
            </div>
        </div>

        <div class="bg-surface-card border border-border rounded-lg shadow-sm p-6 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-ink-muted">Tipo</p>
                <p class="text-ink">{{ $cliente->tipo === \App\Enums\TipoCliente::PF ? 'Pessoa física' : 'Pessoa jurídica' }}</p>
            </div>
            <div>
                <p class="text-ink-muted">{{ $cliente->tipo === \App\Enums\TipoCliente::PF ? 'CPF' : 'CNPJ' }}</p>
                <p class="font-mono text-ink">{{ $cliente->cpf_cnpj }}</p>
            </div>
            <div>
                <p class="text-ink-muted">E-mail</p>
                <p class="text-ink">{{ $cliente->email ?? '—' }}</p>
            </div>
            <div>
                <p class="text-ink-muted">Telefone</p>
                <p class="text-ink">{{ $cliente->telefone ?? '—' }}</p>
            </div>
            <div class="sm:col-span-2">
                <p class="text-ink-muted">Endereço</p>
                <p class="text-ink">{{ $cliente->endereco ?? '—' }}</p>
            </div>
        </div>

        <div class="bg-surface-card border border-border rounded-lg shadow-sm">
            <div class="p-4 border-b border-border">
                <h2 class="font-display text-lg font-semibold text-ink">Histórico de contratos</h2>
            </div>

            @if ($contratos->isEmpty())
                <div class="p-12 text-center">
                    <p class="text-ink-muted">Este cliente ainda não tem contratos.</p>
                </div>
            @else
                <table class="min-w-full divide-y divide-border">
                    <thead>
                        <tr class="text-left text-xs font-medium text-ink-muted uppercase tracking-wide">
                            <th class="px-4 py-3">Período</th>
                            <th class="px-4 py-3">Ativo locado</th>
                            <th class="px-4 py-3">Situação</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach ($contratos as $contrato)
                            <tr wire:key="contrato-{{ $contrato->id }}" class="hover:bg-surface">
                                <td class="px-4 py-3 font-mono text-ink-muted">
                                    {{ $contrato->data_inicio->format('d/m/Y') }} – {{ $contrato->data_fim->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3 text-ink">{{ $contrato->ativo->nome }}</td>
                                <td class="px-4 py-3"><x-status-badge :status="$contrato->status" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
