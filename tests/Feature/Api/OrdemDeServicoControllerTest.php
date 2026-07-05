<?php

namespace Tests\Feature\Api;

use App\Enums\PerfilUsuario;
use App\Enums\StatusAtivo;
use App\Enums\StatusOrdemServico;
use App\Enums\TipoOrdemServico;
use App\Models\AlertaManutencao;
use App\Models\Ativo;
use App\Models\OrdemDeServico;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrdemDeServicoControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_tecnico_abre_ordem_de_servico_a_partir_de_um_alerta(): void
    {
        $tecnico = User::factory()->perfil(PerfilUsuario::TECNICO_MANUTENCAO)->create();
        $ativo = Ativo::factory()->create(['status' => StatusAtivo::EM_MANUTENCAO]);
        $alerta = AlertaManutencao::factory()->create(['ativo_id' => $ativo->id, 'resolvido' => false]);

        $response = $this->actingAs($tecnico, 'sanctum')->postJson('/api/ordens-servico', [
            'ativo_id' => $ativo->id,
            'tecnico_id' => $tecnico->id,
            'alerta_id' => $alerta->id,
            'tipo' => 'preventiva',
        ]);

        $response->assertCreated()->assertJsonPath('tipo', 'preventiva');

        $this->assertDatabaseHas('ordens_servico', [
            'ativo_id' => $ativo->id,
            'tecnico_id' => $tecnico->id,
            'alerta_id' => $alerta->id,
            'status' => 'aberta',
        ]);
    }

    public function test_abre_ordem_corretiva_sem_alerta_previo(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        $tecnico = User::factory()->perfil(PerfilUsuario::TECNICO_MANUTENCAO)->create();
        $ativo = Ativo::factory()->create(['status' => StatusAtivo::DISPONIVEL]);

        $response = $this->actingAs($gestor, 'sanctum')->postJson('/api/ordens-servico', [
            'ativo_id' => $ativo->id,
            'tecnico_id' => $tecnico->id,
            'tipo' => 'corretiva',
            'descricao' => 'Vazamento de óleo identificado.',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('ativos', ['id' => $ativo->id, 'status' => StatusAtivo::EM_MANUTENCAO->value]);
    }

    public function test_fechar_ordem_de_servico_libera_o_ativo_e_resolve_o_alerta(): void
    {
        $tecnico = User::factory()->perfil(PerfilUsuario::TECNICO_MANUTENCAO)->create();
        $ativo = Ativo::factory()->create(['status' => StatusAtivo::EM_MANUTENCAO]);
        $alerta = AlertaManutencao::factory()->create(['ativo_id' => $ativo->id, 'resolvido' => false]);
        $os = OrdemDeServico::factory()->create([
            'ativo_id' => $ativo->id,
            'tecnico_id' => $tecnico->id,
            'alerta_id' => $alerta->id,
            'status' => StatusOrdemServico::ABERTA,
        ]);

        $response = $this->actingAs($tecnico, 'sanctum')->patchJson("/api/ordens-servico/{$os->id}/fechar", [
            'custo' => 350.00,
        ]);

        $response->assertOk()->assertJsonPath('status', 'fechada');

        $this->assertDatabaseHas('ordens_servico', ['id' => $os->id, 'status' => 'fechada', 'custo' => 350.00]);
        $this->assertDatabaseHas('ativos', ['id' => $ativo->id, 'status' => StatusAtivo::DISPONIVEL->value]);
        $this->assertDatabaseHas('alertas_manutencao', ['id' => $alerta->id, 'resolvido' => true]);
    }

    public function test_operador_logistico_nao_pode_fechar_ordem_de_servico(): void
    {
        $operador = User::factory()->perfil(PerfilUsuario::OPERADOR_LOGISTICO)->create();
        $os = OrdemDeServico::factory()->create(['status' => StatusOrdemServico::ABERTA]);

        $response = $this->actingAs($operador, 'sanctum')->patchJson("/api/ordens-servico/{$os->id}/fechar", [
            'custo' => 100,
        ]);

        $response->assertForbidden();
    }

    public function test_tecnico_nao_atribuido_nao_pode_fechar_ordem_de_outro_tecnico(): void
    {
        $tecnicoResponsavel = User::factory()->perfil(PerfilUsuario::TECNICO_MANUTENCAO)->create();
        $outroTecnico = User::factory()->perfil(PerfilUsuario::TECNICO_MANUTENCAO)->create();
        $os = OrdemDeServico::factory()->create([
            'tecnico_id' => $tecnicoResponsavel->id,
            'status' => StatusOrdemServico::ABERTA,
        ]);

        $response = $this->actingAs($outroTecnico, 'sanctum')->patchJson("/api/ordens-servico/{$os->id}/fechar", [
            'custo' => 100,
        ]);

        $response->assertForbidden();
    }

    public function test_nao_permite_fechar_ordem_ja_fechada(): void
    {
        $tecnico = User::factory()->perfil(PerfilUsuario::TECNICO_MANUTENCAO)->create();
        $os = OrdemDeServico::factory()->create([
            'tecnico_id' => $tecnico->id,
            'status' => StatusOrdemServico::FECHADA,
        ]);

        $response = $this->actingAs($tecnico, 'sanctum')->patchJson("/api/ordens-servico/{$os->id}/fechar", [
            'custo' => 100,
        ]);

        $response->assertStatus(400);
    }
}
