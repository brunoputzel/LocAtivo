<?php

namespace Tests\Feature\Livewire;

use App\Enums\PerfilUsuario;
use App\Livewire\Ativos\AtivoForm;
use App\Models\Ativo;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AtivoFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_nome_e_obrigatorio_para_salvar(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();

        Livewire::actingAs($gestor)
            ->test(AtivoForm::class)
            ->set('tipo', 'gerador')
            ->set('modelo', 'G20')
            ->set('numeroSerie', 'SN-0001')
            ->call('salvar')
            ->assertHasErrors(['nome' => 'required']);
    }

    public function test_gestor_cadastra_ativo_com_foto_url(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();

        Livewire::actingAs($gestor)
            ->test(AtivoForm::class)
            ->set('nome', 'Gerador 20kva')
            ->set('tipo', 'gerador')
            ->set('modelo', 'G20')
            ->set('numeroSerie', 'SN-0001')
            ->set('fotoUrl', 'https://exemplo.com/foto.jpg')
            ->call('salvar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('ativos', [
            'numero_serie' => 'SN-0001',
            'foto_url' => 'https://exemplo.com/foto.jpg',
        ]);
    }

    public function test_gestor_cadastra_ativo_com_valor_diaria_de_referencia(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();

        Livewire::actingAs($gestor)
            ->test(AtivoForm::class)
            ->set('nome', 'Gerador 20kva')
            ->set('tipo', 'gerador')
            ->set('modelo', 'G20')
            ->set('numeroSerie', 'SN-0001')
            ->set('valorDiariaReferencia', '180.50')
            ->call('salvar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('ativos', [
            'numero_serie' => 'SN-0001',
            'valor_diaria_referencia' => 180.50,
        ]);
    }

    public function test_valor_diaria_de_referencia_e_opcional(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();

        Livewire::actingAs($gestor)
            ->test(AtivoForm::class)
            ->set('nome', 'Gerador 20kva')
            ->set('tipo', 'gerador')
            ->set('modelo', 'G20')
            ->set('numeroSerie', 'SN-0001')
            ->call('salvar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('ativos', [
            'numero_serie' => 'SN-0001',
            'valor_diaria_referencia' => null,
        ]);
    }

    public function test_edita_ativo_existente(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        $ativo = Ativo::factory()->create(['nome' => 'Nome Antigo']);

        Livewire::actingAs($gestor)
            ->test(AtivoForm::class)
            ->call('editar', $ativo->id)
            ->assertSet('nome', 'Nome Antigo')
            ->set('nome', 'Nome Novo')
            ->call('salvar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('ativos', ['id' => $ativo->id, 'nome' => 'Nome Novo']);
    }

    public function test_operador_logistico_nao_pode_salvar_novo_ativo(): void
    {
        $operador = User::factory()->perfil(PerfilUsuario::OPERADOR_LOGISTICO)->create();

        // Livewire converte a exceção não tratada numa resposta de teste em vez de
        // deixá-la borbulhar - precisa desligar o exception handling pra capturá-la
        $this->withoutExceptionHandling();
        $this->expectException(AuthorizationException::class);

        Livewire::actingAs($operador)
            ->test(AtivoForm::class)
            ->set('nome', 'Gerador 20kva')
            ->set('tipo', 'gerador')
            ->set('modelo', 'G20')
            ->set('numeroSerie', 'SN-0001')
            ->call('salvar');
    }
}
