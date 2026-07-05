<?php

namespace Tests\Feature\Livewire;

use App\Enums\PerfilUsuario;
use App\Livewire\Checklists\ChecklistsIndex;
use App\Models\Checklist;
use App\Models\Contrato;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ChecklistsIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_tecnico_manutencao_nao_acessa_a_pagina_de_checklists(): void
    {
        $tecnico = User::factory()->perfil(PerfilUsuario::TECNICO_MANUTENCAO)->create();

        $this->actingAs($tecnico)->get('/checklists')->assertForbidden();
    }

    public function test_filtra_checklists_por_contrato(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();

        $contratoA = Contrato::factory()->create();
        $contratoB = Contrato::factory()->create();

        // "registrado por" só aparece na linha da tabela, não no <select> de
        // filtro (que só lista dados do contrato) - é o que dá pra distinguir
        // qual checklist sumiu da lista depois do filtro
        $usuarioA = User::factory()->create(['name' => 'Fulano da Silva']);
        $usuarioB = User::factory()->create(['name' => 'Beltrano Souza']);

        Checklist::factory()->create(['contrato_id' => $contratoA->id, 'usuario_id' => $usuarioA->id]);
        Checklist::factory()->create(['contrato_id' => $contratoB->id, 'usuario_id' => $usuarioB->id]);

        Livewire::actingAs($gestor)
            ->test(ChecklistsIndex::class)
            ->assertSee('Fulano da Silva')
            ->assertSee('Beltrano Souza')
            ->set('contratoId', $contratoA->id)
            ->assertSee('Fulano da Silva')
            ->assertDontSee('Beltrano Souza');
    }

    public function test_estado_vazio_quando_nao_ha_checklists(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();

        Livewire::actingAs($gestor)
            ->test(ChecklistsIndex::class)
            ->assertSee('Nenhum checklist registrado ainda');
    }
}
