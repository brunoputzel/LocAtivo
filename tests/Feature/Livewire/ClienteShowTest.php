<?php

namespace Tests\Feature\Livewire;

use App\Enums\PerfilUsuario;
use App\Models\Ativo;
use App\Models\Cliente;
use App\Models\Contrato;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClienteShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_mostra_historico_de_contratos_do_cliente(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        $cliente = Cliente::factory()->create();
        $ativo = Ativo::factory()->create(['nome' => 'Gerador Diesel']);
        Contrato::factory()->create(['cliente_id' => $cliente->id, 'ativo_id' => $ativo->id]);

        $this->actingAs($gestor)
            ->get(route('clientes.show', $cliente))
            ->assertOk()
            ->assertSee('Gerador Diesel');
    }

    public function test_tecnico_manutencao_nao_acessa_detalhe_do_cliente(): void
    {
        $tecnico = User::factory()->perfil(PerfilUsuario::TECNICO_MANUTENCAO)->create();
        $cliente = Cliente::factory()->create();

        $this->actingAs($tecnico)
            ->get(route('clientes.show', $cliente))
            ->assertForbidden();
    }
}
