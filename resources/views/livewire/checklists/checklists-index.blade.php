<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <div>
            <h1 class="font-display text-2xl font-semibold text-ink">Checklists</h1>
            <p class="text-sm text-ink-muted">Registros de saída e retorno de equipamentos.</p>
        </div>

        <div class="bg-surface-card border border-border rounded-lg shadow-sm">
            <div class="p-4 border-b border-border">
                <select wire:model.live="contratoId" class="rounded-md border-border focus:border-brand focus:ring-brand text-sm w-full sm:w-96">
                    <option value="">Todos os contratos</option>
                    @foreach ($contratos as $c)
                        <option value="{{ $c->id }}">
                            #{{ $c->id }} — {{ $c->ativo->nome }} — {{ $c->cliente->nome }}
                        </option>
                    @endforeach
                </select>
            </div>

            @if ($checklists->isEmpty() && ! $existemChecklists)
                <div class="p-12 text-center space-y-3">
                    <p class="text-ink-muted">Nenhum checklist registrado ainda — registre o primeiro a partir de um contrato.</p>
                    <a href="{{ route('contratos.index') }}" wire:navigate class="text-sm font-medium text-brand hover:text-brand-dark">
                        Ver contratos
                    </a>
                </div>
            @elseif ($checklists->isEmpty())
                <div class="p-12 text-center space-y-3">
                    <p class="text-ink-muted">Nenhum checklist encontrado pra esse contrato.</p>
                    <button type="button" wire:click="$set('contratoId', '')" class="text-sm font-medium text-brand hover:text-brand-dark">
                        Ver todos
                    </button>
                </div>
            @else
                <table class="min-w-full divide-y divide-border">
                    <thead>
                        <tr class="text-left text-xs font-medium text-ink-muted uppercase tracking-wide">
                            <th class="px-4 py-3">Tipo</th>
                            <th class="px-4 py-3">Contrato</th>
                            <th class="px-4 py-3">Registrado por</th>
                            <th class="px-4 py-3">Fotos</th>
                            <th class="px-4 py-3">Data</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach ($checklists as $checklist)
                            <tr wire:key="checklist-{{ $checklist->id }}" class="hover:bg-surface">
                                <td class="px-4 py-3 text-ink">{{ $checklist->tipo->label() }}</td>
                                <td class="px-4 py-3 text-ink-muted">
                                    #{{ $checklist->contrato->id }} — {{ $checklist->contrato->ativo->nome }} — {{ $checklist->contrato->cliente->nome }}
                                </td>
                                <td class="px-4 py-3 text-ink-muted">{{ $checklist->usuario->name }}</td>
                                <td class="px-4 py-3 text-ink-muted">{{ count($checklist->fotos_json ?? []) }}</td>
                                <td class="px-4 py-3 font-mono text-ink-muted">{{ $checklist->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
