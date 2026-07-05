<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="font-display text-2xl font-semibold text-ink">Usuários</h1>
                <p class="text-sm text-ink-muted">Pessoas com acesso ao sistema e seus perfis.</p>
            </div>

            @can('create', \App\Models\User::class)
                <button
                    type="button"
                    x-data=""
                    x-on:click="$dispatch('open-modal', 'usuario-form'); $dispatch('usuario-form-novo')"
                    class="inline-flex items-center px-4 py-2 bg-brand hover:bg-brand-dark rounded-md font-semibold text-xs text-white uppercase tracking-widest transition"
                >
                    Novo Usuário
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
            <div class="p-4 border-b border-border">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="busca"
                    placeholder="Buscar por nome ou e-mail..."
                    class="w-full sm:w-96 rounded-md border-border focus:border-brand focus:ring-brand text-sm"
                >
            </div>

            @if ($usuarios->isEmpty() && ! $existemUsuarios)
                <div class="p-12 text-center space-y-3">
                    <p class="text-ink-muted">Nenhum usuário cadastrado ainda — cadastre o primeiro.</p>
                </div>
            @elseif ($usuarios->isEmpty())
                <div class="p-12 text-center space-y-3">
                    <p class="text-ink-muted">Nenhum usuário encontrado com esse filtro.</p>
                    <button type="button" wire:click="$set('busca', '')" class="text-sm font-medium text-brand hover:text-brand-dark">
                        Limpar filtro
                    </button>
                </div>
            @else
                <table class="min-w-full divide-y divide-border">
                    <thead>
                        <tr class="text-left text-xs font-medium text-ink-muted uppercase tracking-wide">
                            <th class="px-4 py-3">Nome</th>
                            <th class="px-4 py-3">E-mail</th>
                            <th class="px-4 py-3">Perfil</th>
                            <th class="px-4 py-3">Situação</th>
                            <th class="px-4 py-3 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach ($usuarios as $usuario)
                            <tr wire:key="usuario-{{ $usuario->id }}" class="hover:bg-surface {{ ! $usuario->ativo ? 'opacity-50' : '' }}">
                                <td class="px-4 py-3 text-ink">
                                    {{ $usuario->name }}
                                    @if ($usuario->id === auth()->id())
                                        <span class="ml-1 text-xs text-ink-muted">(você)</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-ink-muted">{{ $usuario->email }}</td>
                                <td class="px-4 py-3 text-ink-muted">{{ $usuario->perfil->label() }}</td>
                                <td class="px-4 py-3">
                                    @if ($usuario->ativo)
                                        <span class="rounded-full bg-status-disponivel/10 text-status-disponivel px-2.5 py-0.5 text-xs font-medium">Ativo</span>
                                    @else
                                        <span class="rounded-full bg-status-inativo/10 text-status-inativo px-2.5 py-0.5 text-xs font-medium">Inativo</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-3">
                                        @can('update', $usuario)
                                            <button
                                                type="button"
                                                x-data=""
                                                x-on:click="$dispatch('open-modal', 'usuario-form'); $dispatch('usuario-form-editar', { usuarioId: {{ $usuario->id }} })"
                                                class="text-sm text-brand hover:text-brand-dark"
                                            >
                                                Editar
                                            </button>
                                        @endcan

                                        @if ($usuario->ativo)
                                            @can('delete', $usuario)
                                                @if ($usuario->id !== auth()->id())
                                                    <button
                                                        type="button"
                                                        wire:click="alternarAtivo({{ $usuario->id }}, false)"
                                                        wire:confirm="Desativar este usuário?"
                                                        class="text-sm text-status-cancelado hover:opacity-75"
                                                    >
                                                        Desativar
                                                    </button>
                                                @endif
                                            @endcan
                                        @else
                                            @can('update', $usuario)
                                                <button
                                                    type="button"
                                                    wire:click="alternarAtivo({{ $usuario->id }}, true)"
                                                    class="text-sm text-brand hover:text-brand-dark"
                                                >
                                                    Ativar
                                                </button>
                                            @endcan
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    @can('create', \App\Models\User::class)
        <x-modal name="usuario-form" maxWidth="lg">
            <livewire:usuarios.usuario-form />
        </x-modal>
    @endcan
</div>
