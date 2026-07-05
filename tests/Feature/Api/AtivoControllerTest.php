<?php

namespace Tests\Feature\Api;

use App\Enums\PerfilUsuario;
use App\Enums\StatusAtivo;
use App\Models\Ativo;
use App\Models\Contrato;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AtivoControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_gestor_cadastra_ativo_com_status_disponivel_por_padrao(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();

        $response = $this->actingAs($gestor, 'sanctum')->postJson('/api/ativos', [
            'nome' => 'Gerador 20kva',
            'tipo' => 'gerador',
            'modelo' => 'G20',
            'numero_serie' => 'SN-0001',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('status', StatusAtivo::DISPONIVEL->value);
    }

    public function test_cadastra_ativo_com_foto_url(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();

        $response = $this->actingAs($gestor, 'sanctum')->postJson('/api/ativos', [
            'nome' => 'Gerador 20kva',
            'tipo' => 'gerador',
            'modelo' => 'G20',
            'numero_serie' => 'SN-0001',
            'foto_url' => 'https://exemplo.com/fotos/gerador.jpg',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('foto_url', 'https://exemplo.com/fotos/gerador.jpg');
    }

    public function test_foto_url_com_formato_invalido_e_rejeitada(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();

        $response = $this->actingAs($gestor, 'sanctum')->postJson('/api/ativos', [
            'nome' => 'Gerador 20kva',
            'tipo' => 'gerador',
            'modelo' => 'G20',
            'numero_serie' => 'SN-0001',
            'foto_url' => 'nao-e-uma-url',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['foto_url']);
    }

    public function test_nome_e_obrigatorio_para_cadastrar_ativo(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();

        $response = $this->actingAs($gestor, 'sanctum')->postJson('/api/ativos', [
            'tipo' => 'gerador',
            'modelo' => 'G20',
            'numero_serie' => 'SN-0001',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['nome']);
    }

    public function test_operador_logistico_nao_pode_cadastrar_ativo(): void
    {
        $operador = User::factory()->perfil(PerfilUsuario::OPERADOR_LOGISTICO)->create();

        $response = $this->actingAs($operador, 'sanctum')->postJson('/api/ativos', [
            'nome' => 'Gerador 20kva',
            'tipo' => 'gerador',
            'modelo' => 'G20',
            'numero_serie' => 'SN-0001',
        ]);

        $response->assertForbidden();
    }

    public function test_lista_ativos_filtrando_por_status(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        Ativo::factory()->create(['status' => StatusAtivo::DISPONIVEL]);
        Ativo::factory()->create(['status' => StatusAtivo::EM_MANUTENCAO]);

        $response = $this->actingAs($gestor, 'sanctum')->getJson('/api/ativos?status=DISPONIVEL');

        $response->assertOk();
        $this->assertCount(1, $response->json());
        $this->assertSame(StatusAtivo::DISPONIVEL->value, $response->json('0.status'));
    }

    public function test_atualiza_horimetro_do_ativo(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        $ativo = Ativo::factory()->create(['horimetro' => 100]);

        $response = $this->actingAs($gestor, 'sanctum')->putJson("/api/ativos/{$ativo->id}", [
            'horimetro' => 145.50,
        ]);

        $response->assertOk()->assertJsonPath('horimetro', '145.50');
    }

    public function test_nao_permite_excluir_ativo_em_locacao(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        $ativo = Ativo::factory()->create(['status' => StatusAtivo::EM_LOCACAO]);
        Contrato::factory()->create(['ativo_id' => $ativo->id]);

        $response = $this->actingAs($gestor, 'sanctum')->deleteJson("/api/ativos/{$ativo->id}");

        $response->assertStatus(400)->assertJsonFragment(['message' => 'Este ativo está em locação e não pode ser excluído.']);
        $this->assertDatabaseHas('ativos', ['id' => $ativo->id]);
    }

    public function test_buscar_ativo_inexistente_retorna_404(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();

        $response = $this->actingAs($gestor, 'sanctum')->getJson('/api/ativos/999');

        $response->assertNotFound();
    }
}
