<div>
    <form wire:submit="salvar" class="p-6">
        <h2 class="font-display text-lg font-semibold text-ink">Nova ordem de serviço</h2>

        @if ($alertaSelecionado)
            <p class="mt-1 text-sm text-ink-muted">A partir do alerta de {{ $alertaSelecionado->tipo->label() }} em {{ $alertaSelecionado->ativo->nome }}.</p>
        @endif

        <div class="mt-6 space-y-4">
            <div>
                <x-input-label for="ativoId" value="Ativo" />
                <select wire:model.blur="ativoId" id="ativoId" class="mt-1 block w-full rounded-md border-border focus:border-brand focus:ring-brand text-sm" @disabled($alertaSelecionado)>
                    <option value="">Selecione...</option>
                    @foreach ($ativos as $a)
                        <option value="{{ $a->id }}">{{ $a->nome }} — {{ $a->numero_serie }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('ativoId')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="tecnicoId" value="Técnico responsável" />
                <select wire:model.blur="tecnicoId" id="tecnicoId" class="mt-1 block w-full rounded-md border-border focus:border-brand focus:ring-brand text-sm">
                    <option value="">Selecione...</option>
                    @foreach ($tecnicos as $tecnico)
                        <option value="{{ $tecnico->id }}">{{ $tecnico->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('tecnicoId')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="tipo" value="Tipo" />
                <select wire:model.blur="tipo" id="tipo" class="mt-1 block w-full rounded-md border-border focus:border-brand focus:ring-brand text-sm">
                    <option value="preventiva">Preventiva</option>
                    <option value="corretiva">Corretiva</option>
                </select>
                <x-input-error :messages="$errors->get('tipo')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="descricao" value="Descrição (opcional)" />
                <textarea
                    wire:model.blur="descricao"
                    id="descricao"
                    rows="3"
                    class="mt-1 block w-full rounded-md border-border focus:border-brand focus:ring-brand text-sm"
                ></textarea>
                <x-input-error :messages="$errors->get('descricao')" class="mt-2" />
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-3 border-t border-border pt-4">
            <x-secondary-button type="button" x-on:click="$dispatch('close')">
                Cancelar
            </x-secondary-button>

            <x-primary-button class="bg-brand hover:bg-brand-dark focus:bg-brand-dark active:bg-brand-dark">
                Abrir ordem
            </x-primary-button>
        </div>
    </form>
</div>
