<div>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <div>
            <a href="{{ route('contratos.index') }}" wire:navigate class="text-sm text-brand hover:text-brand-dark">&larr; Contratos</a>

            <h1 class="mt-2 font-display text-2xl font-semibold text-ink">
                Checklist de {{ $tipoChecklist->label() }}
            </h1>
            <p class="text-sm text-ink-muted">
                Contrato #{{ $contrato->id }} — {{ $contrato->ativo->nome }} — {{ $contrato->cliente->nome }}
            </p>
        </div>

        @if ($tipoChecklist === \App\Enums\TipoChecklist::RETORNO && ! $checklistSaida)
            <div class="bg-surface-card border border-border rounded-lg shadow-sm p-8 text-center">
                <p class="text-ink-muted">Este contrato ainda não tem checklist de saída registrado — registre a saída antes do retorno.</p>
            </div>
        @else
            <div class="grid grid-cols-1 {{ $checklistSaida ? 'lg:grid-cols-2' : '' }} gap-6">
                @if ($checklistSaida)
                    <div class="bg-surface-card border border-border rounded-lg shadow-sm p-6">
                        <h2 class="font-display text-sm font-semibold text-ink-muted uppercase tracking-wide mb-4">
                            Fotos da saída (comparação)
                        </h2>

                        @if (empty($checklistSaida->fotos_json))
                            <p class="text-sm text-ink-muted">Nenhuma foto anexada na saída.</p>
                        @else
                            <div class="grid grid-cols-3 gap-2">
                                @foreach ($checklistSaida->fotos_json as $url)
                                    <img src="{{ $url }}" class="h-24 w-full object-cover rounded-md border border-border">
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                <form wire:submit="salvar" class="bg-surface-card border border-border rounded-lg shadow-sm p-6 space-y-4">
                    <div
                        x-data="{ enviando: false, progresso: 0 }"
                        x-on:livewire-upload-start.window="enviando = true"
                        x-on:livewire-upload-finish.window="enviando = false"
                        x-on:livewire-upload-error.window="enviando = false"
                        x-on:livewire-upload-progress.window="progresso = $event.detail.progress"
                    >
                        <x-input-label for="fotos" value="Fotos do equipamento (até 10, opcional)" />
                        <label class="mt-1 inline-flex items-center px-4 py-2 bg-brand hover:bg-brand-dark rounded-md font-semibold text-xs text-white uppercase tracking-widest transition cursor-pointer">
                            Selecionar fotos
                            <input
                                type="file"
                                id="fotos"
                                wire:model="fotos"
                                multiple
                                accept="image/jpeg,image/png,image/webp"
                                class="hidden"
                            >
                        </label>
                        <x-input-error :messages="$errors->get('fotos')" class="mt-2" />
                        <x-input-error :messages="$errors->get('fotos.*')" class="mt-2" />

                        <div x-show="enviando" x-cloak class="mt-3">
                            <div class="h-2 bg-border rounded-full overflow-hidden">
                                <div class="h-full bg-brand transition-all" :style="`width: ${progresso}%`"></div>
                            </div>
                            <p class="mt-1 text-xs text-ink-muted" x-text="`Enviando... ${progresso}%`"></p>
                        </div>

                        @if ($fotos)
                            <div class="mt-4 grid grid-cols-3 gap-2">
                                @foreach ($fotos as $indice => $foto)
                                    <div class="relative" wire:key="foto-{{ $indice }}">
                                        <img src="{{ $foto->temporaryUrl() }}" class="h-24 w-full object-cover rounded-md border border-border">
                                        <button
                                            type="button"
                                            wire:click="removerFoto({{ $indice }})"
                                            class="absolute -top-2 -right-2 h-6 w-6 rounded-full bg-status-cancelado text-white text-xs leading-none flex items-center justify-center shadow"
                                            aria-label="Remover foto"
                                        >
                                            &times;
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div>
                        <x-input-label for="observacoes" value="Observações (opcional)" />
                        <textarea
                            wire:model.blur="observacoes"
                            id="observacoes"
                            rows="4"
                            class="mt-1 block w-full rounded-md border-border focus:border-brand focus:ring-brand text-sm"
                        ></textarea>
                        <x-input-error :messages="$errors->get('observacoes')" class="mt-2" />
                    </div>

                    <div class="flex justify-end gap-3 border-t border-border pt-4">
                        <a href="{{ route('contratos.index') }}" wire:navigate>
                            <x-secondary-button type="button">Cancelar</x-secondary-button>
                        </a>

                        <x-primary-button
                            wire:loading.attr="disabled"
                            wire:target="salvar"
                            class="bg-brand hover:bg-brand-dark focus:bg-brand-dark active:bg-brand-dark"
                        >
                            Registrar {{ strtolower($tipoChecklist->label()) }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        @endif
    </div>
</div>
