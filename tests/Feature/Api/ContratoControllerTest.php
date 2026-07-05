<?php

namespace Tests\Feature\Api;

use App\Enums\PerfilUsuario;
use App\Enums\StatusAtivo;
use App\Enums\StatusContrato;
use App\Models\Ativo;
use App\Models\Cliente;
use App\Models\Contrato;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContratoControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_cria_contrato_com_ativo_disponivel_e_move_o_ativo_para_em_locacao(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        $ativo = Ativo::factory()->create(['status' => StatusAtivo::DISPONIVEL]);
        $cliente = Cliente::factory()->create();

        $response = $this->actingAs($gestor, 'sanctum')->postJson('/api/contratos', [
            'ativo_id' => $ativo->id,
            'cliente_id' => $cliente->id,
            'data_inicio' => now()->toDateString(),
            'data_fim' => now()->addDays(10)->toDateString(),
            'valor_diaria' => 150,
        ]);

        $response->assertCreated()->assertJsonPath('status', StatusContrato::ATIVO->value);
        $this->assertSame(StatusAtivo::EM_LOCACAO, $ativo->fresh()->status);
    }

    public function test_cria_contrato_com_observacoes(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        $ativo = Ativo::factory()->create(['status' => StatusAtivo::DISPONIVEL]);
        $cliente = Cliente::factory()->create();

        $response = $this->actingAs($gestor, 'sanctum')->postJson('/api/contratos', [
            'ativo_id' => $ativo->id,
            'cliente_id' => $cliente->id,
            'data_inicio' => now()->toDateString(),
            'data_fim' => now()->addDays(10)->toDateString(),
            'valor_diaria' => 150,
            'observacoes' => 'Cliente pediu entrega no período da manhã.',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('observacoes', 'Cliente pediu entrega no período da manhã.');
    }

    public function test_nao_permite_criar_contrato_para_ativo_indisponivel(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        $ativo = Ativo::factory()->create(['status' => StatusAtivo::EM_MANUTENCAO]);
        $cliente = Cliente::factory()->create();

        $response = $this->actingAs($gestor, 'sanctum')->postJson('/api/contratos', [
            'ativo_id' => $ativo->id,
            'cliente_id' => $cliente->id,
            'data_inicio' => now()->toDateString(),
            'data_fim' => now()->addDays(10)->toDateString(),
            'valor_diaria' => 150,
        ]);

        $response->assertStatus(400)->assertJsonFragment(['message' => 'Ativo está com status EM_MANUTENCAO e não pode ser locado.']);
        $this->assertDatabaseCount('contratos', 0);
    }

    public function test_data_fim_anterior_a_data_inicio_e_rejeitada(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        $ativo = Ativo::factory()->create();
        $cliente = Cliente::factory()->create();

        $response = $this->actingAs($gestor, 'sanctum')->postJson('/api/contratos', [
            'ativo_id' => $ativo->id,
            'cliente_id' => $cliente->id,
            'data_inicio' => now()->toDateString(),
            'data_fim' => now()->subDay()->toDateString(),
            'valor_diaria' => 150,
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['data_fim']);
    }

    public function test_tecnico_manutencao_nao_pode_criar_contrato(): void
    {
        $tecnico = User::factory()->perfil(PerfilUsuario::TECNICO_MANUTENCAO)->create();
        $ativo = Ativo::factory()->create();
        $cliente = Cliente::factory()->create();

        $response = $this->actingAs($tecnico, 'sanctum')->postJson('/api/contratos', [
            'ativo_id' => $ativo->id,
            'cliente_id' => $cliente->id,
            'data_inicio' => now()->toDateString(),
            'data_fim' => now()->addDays(10)->toDateString(),
            'valor_diaria' => 150,
        ]);

        $response->assertForbidden();
    }

    public function test_encerrar_contrato_libera_o_ativo(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        $ativo = Ativo::factory()->create(['status' => StatusAtivo::EM_LOCACAO]);
        $contrato = Contrato::factory()->create([
            'ativo_id' => $ativo->id,
            'status' => StatusContrato::ATIVO,
        ]);

        $response = $this->actingAs($gestor, 'sanctum')->patchJson("/api/contratos/{$contrato->id}/encerrar");

        $response->assertOk()->assertJsonPath('status', StatusContrato::ENCERRADO->value);
        $this->assertSame(StatusAtivo::DISPONIVEL, $ativo->fresh()->status);
    }

    public function test_encerrar_contrato_gera_cobranca_com_valor_dos_dias_efetivos(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        $ativo = Ativo::factory()->create(['status' => StatusAtivo::EM_LOCACAO]);
        $contrato = Contrato::factory()->create([
            'ativo_id' => $ativo->id,
            'data_inicio' => now()->subDays(5),
            'valor_diaria' => 150,
            'status' => StatusContrato::ATIVO,
        ]);

        $this->actingAs($gestor, 'sanctum')->patchJson("/api/contratos/{$contrato->id}/encerrar");

        $this->assertDatabaseHas('cobrancas', [
            'contrato_id' => $contrato->id,
            'valor' => 750.00,
            'status' => 'pendente',
        ]);
    }

    public function test_nao_permite_encerrar_contrato_ja_encerrado(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        $contrato = Contrato::factory()->create(['status' => StatusContrato::ENCERRADO]);

        $response = $this->actingAs($gestor, 'sanctum')->patchJson("/api/contratos/{$contrato->id}/encerrar");

        $response->assertStatus(400);
    }

    public function test_financeiro_nao_pode_encerrar_contrato(): void
    {
        $financeiro = User::factory()->perfil(PerfilUsuario::FINANCEIRO)->create();
        $contrato = Contrato::factory()->create(['status' => StatusContrato::ATIVO]);

        $response = $this->actingAs($financeiro, 'sanctum')->patchJson("/api/contratos/{$contrato->id}/encerrar");

        $response->assertForbidden();
    }

    public function test_cliente_ve_apenas_os_proprios_contratos_na_listagem(): void
    {
        $cliente = Cliente::factory()->create();
        $outroCliente = Cliente::factory()->create();
        $usuario = User::factory()->perfil(PerfilUsuario::CLIENTE)->create(['cliente_id' => $cliente->id]);

        Contrato::factory()->create(['cliente_id' => $cliente->id]);
        Contrato::factory()->create(['cliente_id' => $outroCliente->id]);

        $response = $this->actingAs($usuario, 'sanctum')->getJson('/api/contratos');

        $response->assertOk();
        $this->assertCount(1, $response->json());
    }

    public function test_cliente_nao_acessa_contrato_de_outro_cliente(): void
    {
        $cliente = Cliente::factory()->create();
        $usuario = User::factory()->perfil(PerfilUsuario::CLIENTE)->create(['cliente_id' => $cliente->id]);

        $outroCliente = Cliente::factory()->create();
        $contrato = Contrato::factory()->create(['cliente_id' => $outroCliente->id]);

        $response = $this->actingAs($usuario, 'sanctum')->getJson("/api/contratos/{$contrato->id}");

        $response->assertForbidden();
    }

    public function test_cliente_consulta_seu_proprio_contrato(): void
    {
        $cliente = Cliente::factory()->create();
        $usuario = User::factory()->perfil(PerfilUsuario::CLIENTE)->create(['cliente_id' => $cliente->id]);

        $contrato = Contrato::factory()->create(['cliente_id' => $cliente->id]);

        $response = $this->actingAs($usuario, 'sanctum')->getJson("/api/contratos/{$contrato->id}");

        $response->assertOk();
    }
}
