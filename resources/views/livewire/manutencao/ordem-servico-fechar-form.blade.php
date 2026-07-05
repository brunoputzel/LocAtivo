<div>
    <form wire:submit="salvar" class="p-6">
        <h2 class="font-display text-lg font-semibold text-ink">Fechar ordem de serviço</h2>

        @if ($ordemServico)
            <p class="mt-1 text-sm text-ink-muted">{{ $ordemServico->ativo->nome }}</p>
        @endif

        <div class="mt-6 space-y-4">
            <div>
                <x-input-label for="custo" value="Custo final (R$)" />
                <x-text-input wire:model.blur="custo" id="custo" type="number" step="0.01" min="0" class="block mt-1 w-full font-mono" />
                <x-input-error :messages="$errors->get('custo')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="dataFechamento" value="Data de fechamento" />
                <input type="date" wire:model.blur="dataFechamento" id="dataFechamento" class="mt-1 block w-full rounded-md border-border focus:border-brand focus:ring-brand text-sm font-mono">
                <x-input-error :messages="$errors->get('dataFechamento')" class="mt-2" />
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-3 border-t border-border pt-4">
            <x-secondary-button type="button" x-on:click="$dispatch('close')">
                Cancelar
            </x-secondary-button>

            <x-primary-button class="bg-brand hover:bg-brand-dark focus:bg-brand-dark active:bg-brand-dark">
                Fechar ordem
            </x-primary-button>
        </div>
    </form>
</div>
