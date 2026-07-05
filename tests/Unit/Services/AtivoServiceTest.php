<?php

namespace Tests\Unit\Services;

use App\Enums\StatusAtivo;
use App\Enums\TipoAlerta;
use App\Models\Ativo;
use App\Services\AtivoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AtivoServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_horimetro_cruzando_limiar_de_500h_gera_alerta_e_coloca_ativo_em_manutencao(): void
    {
        $ativo = Ativo::factory()->create(['status' => StatusAtivo::DISPONIVEL, 'horimetro' => 498]);

        (new AtivoService())->atualizarHorimetro($ativo, 502);

        $this->assertDatabaseHas('alertas_manutencao', [
            'ativo_id' => $ativo->id,
            'tipo' => TipoAlerta::HORIMETRO->value,
            'resolvido' => false,
        ]);
        $this->assertDatabaseHas('ativos', ['id' => $ativo->id, 'status' => StatusAtivo::EM_MANUTENCAO->value]);
    }

    public function test_horimetro_que_nao_cruza_limiar_nao_gera_alerta(): void
    {
        $ativo = Ativo::factory()->create(['status' => StatusAtivo::DISPONIVEL, 'horimetro' => 100]);

        (new AtivoService())->atualizarHorimetro($ativo, 145.50);

        $this->assertDatabaseCount('alertas_manutencao', 0);
        $this->assertDatabaseHas('ativos', [
            'id' => $ativo->id,
            'status' => StatusAtivo::DISPONIVEL->value,
            'horimetro' => 145.50,
        ]);
    }

    public function test_atualizar_ativo_sem_mudar_horimetro_nao_gera_alerta(): void
    {
        $ativo = Ativo::factory()->create(['horimetro' => 498, 'nome' => 'Nome Antigo']);

        (new AtivoService())->atualizar($ativo, ['nome' => 'Nome Novo']);

        $this->assertDatabaseCount('alertas_manutencao', 0);
        $this->assertDatabaseHas('ativos', ['id' => $ativo->id, 'nome' => 'Nome Novo']);
    }
}
