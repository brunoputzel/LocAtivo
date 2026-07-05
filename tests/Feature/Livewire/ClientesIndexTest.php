<?php

namespace Tests\Feature\Livewire;

use App\Enums\PerfilUsuario;
use App\Livewire\Clientes\ClientesIndex;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ClientesIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_tecnico_manutencao_nao_acessa_a_pagina_de_clientes(): void
    {
        $tecnico = User::factory()->perfil(PerfilUsuario::TECNICO_MANUTENCAO)->create();

        $this->actingAs($tecnico)->get('/clientes')->assertForbidden();
    }

    public function test_gestor_acessa_a_pagina_de_clientes(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();

        $this->actingAs($gestor)->get('/clientes')->assertOk();
    }

    public function test_busca_filtra_clientes_por_nome(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        Cliente::factory()->create(['nome' => 'João da Silva']);
        Cliente::factory()->create(['nome' => 'Empresa LTDA']);

        Livewire::actingAs($gestor)
            ->test(ClientesIndex::class)
            ->set('busca', 'João')
            ->assertSee('João da Silva')
            ->assertDontSee('Empresa LTDA');
    }

    public function test_lista_esconde_inativos_por_padrao_e_mostra_com_o_toggle(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        Cliente::factory()->create(['nome' => 'Cliente Ativo', 'ativo' => true]);
        Cliente::factory()->create(['nome' => 'Cliente Inativo', 'ativo' => false]);

        Livewire::actingAs($gestor)
            ->test(ClientesIndex::class)
            ->assertSee('Cliente Ativo')
            ->assertDontSee('Cliente Inativo')
            ->set('mostrarInativos', true)
            ->assertSee('Cliente Ativo')
            ->assertSee('Cliente Inativo');
    }

    public function test_inativar_cliente_nao_remove_do_banco(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        $cliente = Cliente::factory()->create(['ativo' => true]);

        Livewire::actingAs($gestor)
            ->test(ClientesIndex::class)
            ->call('inativar', $cliente->id);

        $this->assertDatabaseHas('clientes', ['id' => $cliente->id, 'ativo' => false]);
    }
}
