<?php

namespace Tests\Feature\Livewire;

use App\Enums\PerfilUsuario;
use App\Livewire\Usuarios\UsuariosIndex;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UsuariosIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_operador_logistico_nao_acessa_a_pagina_de_usuarios(): void
    {
        $operador = User::factory()->perfil(PerfilUsuario::OPERADOR_LOGISTICO)->create();

        $this->actingAs($operador)->get('/usuarios')->assertForbidden();
    }

    public function test_gestor_acessa_a_pagina_de_usuarios(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();

        $this->actingAs($gestor)->get('/usuarios')->assertOk();
    }

    public function test_busca_filtra_usuarios_por_nome(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        User::factory()->create(['name' => 'João da Silva']);
        User::factory()->create(['name' => 'Maria Souza']);

        Livewire::actingAs($gestor)
            ->test(UsuariosIndex::class)
            ->set('busca', 'João')
            ->assertSee('João da Silva')
            ->assertDontSee('Maria Souza');
    }

    public function test_desativar_usuario_nao_remove_do_banco(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        $usuario = User::factory()->create(['ativo' => true]);

        Livewire::actingAs($gestor)
            ->test(UsuariosIndex::class)
            ->call('alternarAtivo', $usuario->id, false);

        $this->assertDatabaseHas('users', ['id' => $usuario->id, 'ativo' => false]);
    }

    public function test_gestor_nao_pode_desativar_a_propria_conta(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create(['ativo' => true]);

        Livewire::actingAs($gestor)
            ->test(UsuariosIndex::class)
            ->call('alternarAtivo', $gestor->id, false)
            ->assertSet('erro', 'Você não pode desativar sua própria conta.');

        $this->assertDatabaseHas('users', ['id' => $gestor->id, 'ativo' => true]);
    }
}
