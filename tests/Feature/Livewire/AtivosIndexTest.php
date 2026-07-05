<?php

namespace Tests\Feature\Livewire;

use App\Enums\PerfilUsuario;
use App\Enums\StatusAtivo;
use App\Livewire\Ativos\AtivosIndex;
use App\Models\Ativo;
use App\Models\Contrato;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AtivosIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitante_nao_autenticado_e_redirecionado_para_login(): void
    {
        $this->get('/ativos')->assertRedirect('/login');
    }

    public function test_tecnico_manutencao_acessa_a_pagina_de_ativos(): void
    {
        $tecnico = User::factory()->perfil(PerfilUsuario::TECNICO_MANUTENCAO)->create();

        $this->actingAs($tecnico)->get('/ativos')->assertOk();
    }

    public function test_busca_filtra_lista_de_ativos_por_nome(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        Ativo::factory()->create(['nome' => 'Gerador Diesel 20kva']);
        Ativo::factory()->create(['nome' => 'Compressor de Ar']);

        Livewire::actingAs($gestor)
            ->test(AtivosIndex::class)
            ->set('busca', 'Gerador')
            ->assertSee('Gerador Diesel 20kva')
            ->assertDontSee('Compressor de Ar');
    }

    public function test_filtro_por_status_mostra_apenas_ativos_combinantes(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        Ativo::factory()->create(['nome' => 'Ativo Disponível', 'status' => StatusAtivo::DISPONIVEL]);
        Ativo::factory()->create(['nome' => 'Ativo em Manutenção', 'status' => StatusAtivo::EM_MANUTENCAO]);

        Livewire::actingAs($gestor)
            ->test(AtivosIndex::class)
            ->set('status', StatusAtivo::DISPONIVEL->value)
            ->assertSee('Ativo Disponível')
            ->assertDontSee('Ativo em Manutenção');
    }

    public function test_nao_permite_excluir_ativo_em_locacao(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        $ativo = Ativo::factory()->create(['status' => StatusAtivo::EM_LOCACAO]);
        Contrato::factory()->create(['ativo_id' => $ativo->id]);

        Livewire::actingAs($gestor)
            ->test(AtivosIndex::class)
            ->call('excluir', $ativo->id)
            ->assertSet('erro', 'Este ativo está em locação e não pode ser excluído.');

        $this->assertDatabaseHas('ativos', ['id' => $ativo->id]);
    }

    public function test_estado_vazio_quando_nao_ha_ativos_cadastrados(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();

        Livewire::actingAs($gestor)
            ->test(AtivosIndex::class)
            ->assertSee('Nenhum ativo cadastrado ainda');
    }
}
