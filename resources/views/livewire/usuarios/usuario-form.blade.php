<div>
    <form wire:submit="salvar" class="p-6">
        <h2 class="font-display text-lg font-semibold text-ink">
            {{ $usuarioId ? 'Editar usuário' : 'Novo usuário' }}
        </h2>

        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <x-input-label for="name" value="Nome" />
                <x-text-input wire:model.blur="name" id="name" class="block mt-1 w-full" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="email" value="E-mail" />
                <x-text-input wire:model.blur="email" id="email" type="email" class="block mt-1 w-full" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div x-data>
                <x-input-label for="perfil" value="Perfil" />
                <select wire:model.live="perfil" id="perfil" class="mt-1 block w-full rounded-md border-border focus:border-brand focus:ring-brand text-sm">
                    @foreach ($perfis as $opcao)
                        <option value="{{ $opcao->value }}">{{ $opcao->label() }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('perfil')" class="mt-2" />
            </div>

            @if (! $usuarioId)
                <div class="sm:col-span-2">
                    <x-input-label for="password" value="Senha" />
                    <x-text-input wire:model.blur="password" id="password" type="password" class="block mt-1 w-full" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>
            @endif

            @if ($perfil === \App\Enums\PerfilUsuario::CLIENTE->value)
                <div class="sm:col-span-2">
                    <x-input-label for="clienteId" value="Cliente vinculado" />
                    <select wire:model.blur="clienteId" id="clienteId" class="mt-1 block w-full rounded-md border-border focus:border-brand focus:ring-brand text-sm">
                        <option value="">Selecione...</option>
                        @foreach ($clientesAtivos as $c)
                            <option value="{{ $c->id }}">{{ $c->nome }} ({{ $c->cpf_cnpj }})</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('clienteId')" class="mt-2" />
                </div>
            @endif
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
