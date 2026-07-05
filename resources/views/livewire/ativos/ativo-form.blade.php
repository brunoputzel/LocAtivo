<div>
    <form wire:submit="salvar" class="p-6">
        <h2 class="font-display text-lg font-semibold text-ink">
            {{ $ativoId ? 'Editar equipamento' : 'Novo equipamento' }}
        </h2>

        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <x-input-label for="nome" value="Nome" />
                <x-text-input wire:model.blur="nome" id="nome" class="block mt-1 w-full" />
                <x-input-error :messages="$errors->get('nome')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="tipo" value="Tipo" />
                <x-text-input wire:model.blur="tipo" id="tipo" class="block mt-1 w-full" placeholder="gerador, compressor, andaime..." />
                <x-input-error :messages="$errors->get('tipo')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="modelo" value="Modelo" />
                <x-text-input wire:model.blur="modelo" id="modelo" class="block mt-1 w-full" />
                <x-input-error :messages="$errors->get('modelo')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="numeroSerie" value="Número de série" />
                <x-text-input wire:model.blur="numeroSerie" id="numeroSerie" class="block mt-1 w-full font-mono" />
                <x-input-error :messages="$errors->get('numeroSerie')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="horimetro" value="Horímetro" />
                <x-text-input wire:model.blur="horimetro" id="horimetro" type="number" step="0.01" min="0" class="block mt-1 w-full font-mono" />
                <p class="mt-1 text-xs text-ink-muted">Usado apenas para controle interno, sem impacto automático no sistema por enquanto.</p>
                <x-input-error :messages="$errors->get('horimetro')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="valorDiariaReferencia" value="Valor diária de referência (R$)" />
                <x-text-input wire:model.blur="valorDiariaReferencia" id="valorDiariaReferencia" type="number" step="0.01" min="0" class="block mt-1 w-full font-mono" />
                <p class="mt-1 text-xs text-ink-muted">Opcional. Preenche automaticamente o valor diário sugerido ao criar um contrato para este equipamento.</p>
                <x-input-error :messages="$errors->get('valorDiariaReferencia')" class="mt-2" />
            </div>

            <div class="sm:col-span-2" x-data="{ fotoUrl: @entangle('fotoUrl'), erroPreview: false }">
                <x-input-label for="fotoUrl" value="Foto do equipamento (URL, opcional)" />
                <x-text-input
                    type="url"
                    id="fotoUrl"
                    x-model="fotoUrl"
                    x-on:input="erroPreview = false"
                    class="block mt-1 w-full"
                    placeholder="https://..."
                />
                <x-input-error :messages="$errors->get('fotoUrl')" class="mt-2" />

                <template x-if="fotoUrl">
                    <div class="mt-3">
                        <img
                            :src="fotoUrl"
                            x-show="!erroPreview"
                            x-on:error="erroPreview = true"
                            alt="Prévia da foto do equipamento"
                            class="h-32 w-32 object-cover rounded-md border border-border"
                        >
                        <p x-show="erroPreview" class="text-sm text-ink-muted">Não foi possível carregar a prévia dessa URL.</p>
                    </div>
                </template>
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-3 border-t border-border pt-4">
            <x-secondary-button type="button" x-on:click="$dispatch('close')">
                Cancelar
            </x-secondary-button>

            <x-primary-button class="bg-brand hover:bg-brand-dark focus:bg-brand-dark active:bg-brand-dark">
                Salvar
            </x-primary-button>
        </div>
    </form>
</div>
