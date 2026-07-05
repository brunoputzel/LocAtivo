<?php

namespace Tests\Feature\Livewire;

use App\Enums\PerfilUsuario;
use App\Enums\StatusOrdemServico;
use App\Livewire\Manutencao\ManutencaoIndex;
use App\Models\AlertaManutencao;
use App\Models\Ativo;
use App\Models\OrdemDeServico;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ManutencaoIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_operador_logistico_nao_acessa_a_pagina_de_manutencao(): void
    {
        $operador = User::factory()->perfil(PerfilUsuario::OPERADOR_LOGISTICO)->create();

        $this->actingAs($operador)->get('/manutencao')->assertForbidden();
    }

    public function test_tecnico_manutencao_acessa_a_pagina_de_manutencao(): void
    {
        $tecnico = User::factory()->perfil(PerfilUsuario::TECNICO_MANUTENCAO)->create();

        $this->actingAs($tecnico)->get('/manutencao')->assertOk();
    }

    public function test_alertas_pendentes_aparecem_do_mais_antigo_pro_mais_novo(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();

        $ativoRecente = Ativo::factory()->create(['nome' => 'Alerta Recente']);
        $ativoAntigo = Ativo::factory()->create(['nome' => 'Alerta Antigo']);

        AlertaManutencao::factory()->create(['ativo_id' => $ativoRecente->id, 'data_alerta' => now()->subDay()]);
        AlertaManutencao::factory()->create(['ativo_id' => $ativoAntigo->id, 'data_alerta' => now()->subDays(10)]);

        $html = Livewire::actingAs($gestor)->test(ManutencaoIndex::class)->html();

        $this->assertTrue(strpos($html, 'Alerta Antigo') < strpos($html, 'Alerta Recente'));
    }

    public function test_alertas_resolvidos_ficam_escondidos_ate_marcar_o_toggle(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();

        // não usa o nome do ativo como marcador - ele também aparece no <select>
        // do modal "Nova Ordem de Serviço", que lista todos os ativos sempre
        AlertaManutencao::factory()->create(['resolvido' => true, 'data_alerta' => '2020-03-15']);

        Livewire::actingAs($gestor)
            ->test(ManutencaoIndex::class)
            ->assertDontSee('15/03/2020')
            ->set('mostrarAlertasResolvidos', true)
            ->assertSee('15/03/2020');
    }

    public function test_resolver_alerta_marca_como_resolvido(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        $alerta = AlertaManutencao::factory()->create(['resolvido' => false]);

        Livewire::actingAs($gestor)
            ->test(ManutencaoIndex::class)
            ->call('resolverAlerta', $alerta->id);

        $this->assertDatabaseHas('alertas_manutencao', ['id' => $alerta->id, 'resolvido' => true]);
    }

    public function test_filtra_ordens_de_servico_por_status(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();

        $ativoAberta = Ativo::factory()->create(['nome' => 'Ativo Aberta']);
        $ativoFechada = Ativo::factory()->create(['nome' => 'Ativo Fechada']);

        OrdemDeServico::factory()->create(['ativo_id' => $ativoAberta->id, 'status' => StatusOrdemServico::ABERTA]);
        OrdemDeServico::factory()->create(['ativo_id' => $ativoFechada->id, 'status' => StatusOrdemServico::FECHADA]);

        Livewire::actingAs($gestor)
            ->test(ManutencaoIndex::class)
            ->assertSee('Ativo Aberta')
            ->assertSee('Ativo Fechada')
            ->set('statusOS', StatusOrdemServico::FECHADA->value)
            ->assertDontSee('Ativo Aberta')
            ->assertSee('Ativo Fechada');
    }
}
