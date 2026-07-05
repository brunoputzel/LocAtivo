<?php

namespace Tests\Feature\Livewire;

use App\Enums\PerfilUsuario;
use App\Enums\StatusAtivo;
use App\Livewire\Manutencao\OrdemServicoAbrirForm;
use App\Models\AlertaManutencao;
use App\Models\Ativo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrdemServicoAbrirFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_selecionar_alerta_pre_preenche_ativo_e_tipo_preventiva(): void
    {
        $tecnico = User::factory()->perfil(PerfilUsuario::TECNICO_MANUTENCAO)->create();
        $ativo = Ativo::factory()->create(['status' => StatusAtivo::EM_MANUTENCAO]);
        $alerta = AlertaManutencao::factory()->create(['ativo_id' => $ativo->id]);

        Livewire::actingAs($tecnico)
            ->test(OrdemServicoAbrirForm::class)
            ->call('novo', $alerta->id)
            ->assertSet('ativoId', $ativo->id)
            ->assertSet('tipo', 'preventiva');
    }

    public function test_abre_ordem_corretiva_sem_alerta(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        $tecnico = User::factory()->perfil(PerfilUsuario::TECNICO_MANUTENCAO)->create();
        $ativo = Ativo::factory()->create(['status' => StatusAtivo::DISPONIVEL]);

        Livewire::actingAs($gestor)
            ->test(OrdemServicoAbrirForm::class)
            ->set('ativoId', $ativo->id)
            ->set('tecnicoId', $tecnico->id)
            ->set('tipo', 'corretiva')
            ->set('descricao', 'Defeito não previsto.')
            ->call('salvar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('ordens_servico', [
            'ativo_id' => $ativo->id,
            'tecnico_id' => $tecnico->id,
            'tipo' => 'corretiva',
            'alerta_id' => null,
        ]);
    }
}
