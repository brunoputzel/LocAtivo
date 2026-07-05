<div>
    <form wire:submit="salvar" class="p-6">
        <h2 class="font-display text-lg font-semibold text-ink">
            {{ $clienteId ? 'Editar cliente' : 'Novo cliente' }}
        </h2>

        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <x-input-label for="nome" value="Nome" />
                <x-text-input wire:model.blur="nome" id="nome" class="block mt-1 w-full" />
                <x-input-error :messages="$errors->get('nome')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="tipo" value="Tipo" />
                <select wire:model.live="tipo" id="tipo" class="mt-1 block w-full rounded-md border-border focus:border-brand focus:ring-brand text-sm">
                    <option value="PF">Pessoa física</option>
                    <option value="PJ">Pessoa jurídica</option>
                </select>
                <x-input-error :messages="$errors->get('tipo')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="cpfCnpj" :value="$tipo === 'PJ' ? 'CNPJ' : 'CPF'" />
                <x-text-input
                    wire:model.blur="cpfCnpj"
                    id="cpfCnpj"
                    class="block mt-1 w-full font-mono"
                    x-data=""
                    x-mask:dynamic="$wire.tipo === 'PJ' ? '99.999.999/9999-99' : '999.999.999-99'"
                />
                <x-input-error :messages="$errors->get('cpfCnpj')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="email" value="E-mail (opcional)" />
                <x-text-input wire:model.blur="email" id="email" type="email" class="block mt-1 w-full" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="telefone" value="Telefone (opcional)" />
                <x-text-input
                    wire:model.blur="telefone"
                    id="telefone"
                    class="block mt-1 w-full"
                    x-data=""
                    x-mask:dynamic="$input.replace(/\D/g, '').length > 10 ? '(99) 99999-9999' : '(99) 9999-9999'"
                />
                <x-input-error :messages="$errors->get('telefone')" class="mt-2" />
            </div>

            <div class="sm:col-span-2">
                <x-input-label for="endereco" value="Endereço (opcional)" />
                <x-text-input wire:model.blur="endereco" id="endereco" class="block mt-1 w-full" />
                <x-input-error :messages="$errors->get('endereco')" class="mt-2" />
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
