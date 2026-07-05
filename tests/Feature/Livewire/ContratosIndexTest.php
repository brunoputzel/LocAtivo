<?php

namespace Tests\Feature\Livewire;

use App\Enums\PerfilUsuario;
use App\Enums\StatusAtivo;
use App\Enums\StatusContrato;
use App\Livewire\Contratos\ContratosIndex;
use App\Models\Ativo;
use App\Models\Cliente;
use App\Models\Contrato;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ContratosIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_cliente_ve_apenas_os_proprios_contratos_na_tela(): void
    {
        $cliente = Cliente::factory()->create();
        $outroCliente = Cliente::factory()->create();
        $usuario = User::factory()->perfil(PerfilUsuario::CLIENTE)->create(['cliente_id' => $cliente->id]);

        $ativoMeu = Ativo::factory()->create(['nome' => 'Ativo do meu contrato']);
        $ativoOutro = Ativo::factory()->create(['nome' => 'Ativo de outro cliente']);
        Contrato::factory()->create(['cliente_id' => $cliente->id, 'ativo_id' => $ativoMeu->id]);
        Contrato::factory()->create(['cliente_id' => $outroCliente->id, 'ativo_id' => $ativoOutro->id]);

        Livewire::actingAs($usuario)
            ->test(ContratosIndex::class)
            ->assertSee('Ativo do meu contrato')
            ->assertDontSee('Ativo de outro cliente');
    }

    public function test_contrato_vencendo_em_3_dias_aparece_destacado(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        $ativo = Ativo::factory()->create(['nome' => 'Ativo Vencendo']);
        Contrato::factory()->create([
            'ativo_id' => $ativo->id,
            'status' => StatusContrato::ATIVO,
            'data_fim' => now()->addDays(2),
        ]);

        Livewire::actingAs($gestor)
            ->test(ContratosIndex::class)
            ->assertSee('Vence em breve');
    }

    public function test_encerrar_contrato_pela_tela_gera_cobranca(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        $ativo = Ativo::factory()->create(['status' => StatusAtivo::EM_LOCACAO]);
        $contrato = Contrato::factory()->create([
            'ativo_id' => $ativo->id,
            'status' => StatusContrato::ATIVO,
            'data_inicio' => now()->subDays(5),
            'valor_diaria' => 150,
        ]);

        Livewire::actingAs($gestor)
            ->test(ContratosIndex::class)
            ->call('encerrar', $contrato->id)
            ->assertSet('mensagem', 'Contrato encerrado — cobrança de R$ 750,00 gerada.');

        $this->assertDatabaseHas('contratos', ['id' => $contrato->id, 'status' => StatusContrato::ENCERRADO->value]);
        $this->assertDatabaseHas('cobrancas', ['contrato_id' => $contrato->id, 'valor' => 750]);
    }

    public function test_financeiro_nao_pode_encerrar_contrato_pela_tela(): void
    {
        $financeiro = User::factory()->perfil(PerfilUsuario::FINANCEIRO)->create();
        $contrato = Contrato::factory()->create(['status' => StatusContrato::ATIVO]);

        $this->withoutExceptionHandling();
        $this->expectException(AuthorizationException::class);

        Livewire::actingAs($financeiro)
            ->test(ContratosIndex::class)
            ->call('encerrar', $contrato->id);
    }
}
