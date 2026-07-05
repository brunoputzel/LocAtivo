<?php

namespace Tests\Feature\Livewire;

use App\Enums\PerfilUsuario;
use App\Enums\StatusAtivo;
use App\Enums\StatusOrdemServico;
use App\Livewire\Manutencao\OrdemServicoFecharForm;
use App\Models\Ativo;
use App\Models\OrdemDeServico;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrdemServicoFecharFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_tecnico_atribuido_fecha_a_ordem_e_libera_o_ativo(): void
    {
        $tecnico = User::factory()->perfil(PerfilUsuario::TECNICO_MANUTENCAO)->create();
        $ativo = Ativo::factory()->create(['status' => StatusAtivo::EM_MANUTENCAO]);
        $os = OrdemDeServico::factory()->create([
            'ativo_id' => $ativo->id,
            'tecnico_id' => $tecnico->id,
            'status' => StatusOrdemServico::ABERTA,
        ]);

        Livewire::actingAs($tecnico)
            ->test(OrdemServicoFecharForm::class)
            ->call('novo', $os->id)
            ->set('custo', '420.00')
            ->call('salvar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('ordens_servico', ['id' => $os->id, 'status' => 'fechada', 'custo' => 420.00]);
        $this->assertDatabaseHas('ativos', ['id' => $ativo->id, 'status' => StatusAtivo::DISPONIVEL->value]);
    }

    public function test_tecnico_nao_atribuido_nao_pode_fechar(): void
    {
        $tecnicoResponsavel = User::factory()->perfil(PerfilUsuario::TECNICO_MANUTENCAO)->create();
        $outroTecnico = User::factory()->perfil(PerfilUsuario::TECNICO_MANUTENCAO)->create();
        $os = OrdemDeServico::factory()->create([
            'tecnico_id' => $tecnicoResponsavel->id,
            'status' => StatusOrdemServico::ABERTA,
        ]);

        $this->withoutExceptionHandling();
        $this->expectException(AuthorizationException::class);

        Livewire::actingAs($outroTecnico)
            ->test(OrdemServicoFecharForm::class)
            ->call('novo', $os->id)
            ->set('custo', '100')
            ->call('salvar');
    }
}
