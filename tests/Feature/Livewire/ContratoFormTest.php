<?php

namespace Tests\Feature\Livewire;

use App\Enums\PerfilUsuario;
use App\Enums\StatusAtivo;
use App\Livewire\Contratos\ContratoForm;
use App\Models\Ativo;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ContratoFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_seletor_de_ativo_so_lista_ativos_disponiveis(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        Ativo::factory()->create(['nome' => 'Ativo Disponível', 'status' => StatusAtivo::DISPONIVEL]);
        Ativo::factory()->create(['nome' => 'Ativo em Locação', 'status' => StatusAtivo::EM_LOCACAO]);

        Livewire::actingAs($gestor)
            ->test(ContratoForm::class)
            ->call('abrirPainelAtivo')
            ->assertSee('Ativo Disponível')
            ->assertDontSee('Ativo em Locação');
    }

    public function test_painel_de_ativo_fica_fechado_ate_o_campo_ganhar_foco(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        Ativo::factory()->create(['nome' => 'Ativo Disponível', 'status' => StatusAtivo::DISPONIVEL]);

        Livewire::actingAs($gestor)
            ->test(ContratoForm::class)
            ->assertSet('painelAtivoAberto', false)
            ->assertDontSee('Ativo Disponível')
            ->call('abrirPainelAtivo')
            ->assertSet('painelAtivoAberto', true)
            ->assertSee('Ativo Disponível');
    }

    public function test_selecionar_ativo_fecha_o_painel(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        $ativo = Ativo::factory()->create(['status' => StatusAtivo::DISPONIVEL]);

        Livewire::actingAs($gestor)
            ->test(ContratoForm::class)
            ->call('abrirPainelAtivo')
            ->assertSet('painelAtivoAberto', true)
            ->call('selecionarAtivo', $ativo->id)
            ->assertSet('painelAtivoAberto', false);
    }

    public function test_busca_de_cliente_filtra_por_nome_ao_digitar(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        Cliente::factory()->create(['nome' => 'João da Silva']);
        Cliente::factory()->create(['nome' => 'Maria Souza']);

        Livewire::actingAs($gestor)
            ->test(ContratoForm::class)
            ->call('abrirPainelCliente')
            ->set('buscaCliente', 'João')
            ->assertSee('João da Silva')
            ->assertDontSee('Maria Souza');
    }

    public function test_selecionar_cliente_mostra_cartao_com_opcao_de_trocar(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        $cliente = Cliente::factory()->create(['nome' => 'João da Silva']);

        Livewire::actingAs($gestor)
            ->test(ContratoForm::class)
            ->call('selecionarCliente', $cliente->id)
            ->assertSet('clienteId', $cliente->id)
            ->assertSee('Trocar');
    }

    public function test_cria_contrato_com_observacoes(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        $ativo = Ativo::factory()->create(['status' => StatusAtivo::DISPONIVEL]);
        $cliente = Cliente::factory()->create();

        Livewire::actingAs($gestor)
            ->test(ContratoForm::class)
            ->call('selecionarAtivo', $ativo->id)
            ->set('clienteId', $cliente->id)
            ->set('dataInicio', now()->toDateString())
            ->set('dataFim', now()->addDays(10)->toDateString())
            ->set('valorDiaria', '150')
            ->set('observacoes', 'Entregar no período da manhã.')
            ->call('salvar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('contratos', [
            'ativo_id' => $ativo->id,
            'cliente_id' => $cliente->id,
            'observacoes' => 'Entregar no período da manhã.',
        ]);
    }

    public function test_selecionar_ativo_pre_preenche_valor_diario_de_referencia(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        $ativo = Ativo::factory()->create([
            'status' => StatusAtivo::DISPONIVEL,
            'valor_diaria_referencia' => 180.50,
        ]);

        Livewire::actingAs($gestor)
            ->test(ContratoForm::class)
            ->call('selecionarAtivo', $ativo->id)
            ->assertSet('valorDiaria', '180.50');
    }

    public function test_valor_diario_pre_preenchido_ainda_pode_ser_editado(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        $ativo = Ativo::factory()->create([
            'status' => StatusAtivo::DISPONIVEL,
            'valor_diaria_referencia' => 180.50,
        ]);
        $cliente = Cliente::factory()->create();

        Livewire::actingAs($gestor)
            ->test(ContratoForm::class)
            ->call('selecionarAtivo', $ativo->id)
            ->assertSet('valorDiaria', '180.50')
            ->set('valorDiaria', '150')
            ->set('clienteId', $cliente->id)
            ->set('dataInicio', now()->toDateString())
            ->set('dataFim', now()->addDays(10)->toDateString())
            ->call('salvar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('contratos', [
            'ativo_id' => $ativo->id,
            'valor_diaria' => 150,
        ]);
    }

    public function test_ativo_sem_valor_de_referencia_nao_preenche_valor_diario(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        $ativo = Ativo::factory()->create([
            'status' => StatusAtivo::DISPONIVEL,
            'valor_diaria_referencia' => null,
        ]);

        Livewire::actingAs($gestor)
            ->test(ContratoForm::class)
            ->call('selecionarAtivo', $ativo->id)
            ->assertSet('valorDiaria', '');
    }

    public function test_nao_permite_criar_contrato_para_ativo_que_deixou_de_estar_disponivel(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        $ativo = Ativo::factory()->create(['status' => StatusAtivo::DISPONIVEL]);
        $cliente = Cliente::factory()->create();

        $component = Livewire::actingAs($gestor)
            ->test(ContratoForm::class)
            ->call('selecionarAtivo', $ativo->id)
            ->set('clienteId', $cliente->id)
            ->set('dataInicio', now()->toDateString())
            ->set('dataFim', now()->addDays(10)->toDateString())
            ->set('valorDiaria', '150');

        // simula outra requisição colocando o ativo em manutenção entre a seleção e o envio
        $ativo->update(['status' => StatusAtivo::EM_MANUTENCAO]);

        $component->call('salvar')->assertHasErrors(['ativoId']);

        $this->assertDatabaseCount('contratos', 0);
    }
}
