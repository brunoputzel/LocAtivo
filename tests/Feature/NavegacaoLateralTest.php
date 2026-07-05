<?php

namespace Tests\Feature;

use App\Enums\PerfilUsuario;
use App\Livewire\Layout\NavegacaoLateral;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NavegacaoLateralTest extends TestCase
{
    use RefreshDatabase;

    public function test_tecnico_manutencao_ve_apenas_ativos_e_manutencao(): void
    {
        $usuario = User::factory()->perfil(PerfilUsuario::TECNICO_MANUTENCAO)->create();

        $this->actingAs($usuario);

        Livewire::test(NavegacaoLateral::class)
            ->assertDontSee('Painel')
            ->assertSee('Ativos')
            ->assertSee('Manutenção')
            ->assertDontSee('Financeiro')
            ->assertDontSee('Usuários')
            ->assertDontSee('Clientes')
            ->assertDontSee('Contratos')
            ->assertDontSee('Checklists');
    }

    public function test_gestor_ve_todos_os_itens_do_menu(): void
    {
        $usuario = User::factory()->perfil(PerfilUsuario::GESTOR)->create();

        $this->actingAs($usuario);

        Livewire::test(NavegacaoLateral::class)
            ->assertDontSee('Painel')
            ->assertSee('Ativos')
            ->assertSee('Clientes')
            ->assertSee('Contratos')
            ->assertSee('Checklists')
            ->assertSee('Manutenção')
            ->assertDontSee('Financeiro')
            ->assertSee('Usuários');
    }

    public function test_cliente_ve_apenas_contratos(): void
    {
        $cliente = Cliente::factory()->create();
        $usuario = User::factory()->perfil(PerfilUsuario::CLIENTE)->create(['cliente_id' => $cliente->id]);

        $this->actingAs($usuario);

        Livewire::test(NavegacaoLateral::class)
            ->assertDontSee('Painel')
            ->assertSee('Contratos')
            ->assertDontSee('Ativos')
            ->assertDontSee('Usuários')
            ->assertDontSee('Financeiro');
    }
}
