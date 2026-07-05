<?php

namespace Tests\Feature\Livewire;

use App\Enums\PerfilUsuario;
use App\Enums\StatusAtivo;
use App\Enums\StatusContrato;
use App\Livewire\Checklists\ChecklistForm;
use App\Models\Ativo;
use App\Models\Checklist;
use App\Models\Contrato;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ChecklistFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_registra_checklist_de_saida_com_fotos(): void
    {
        Storage::fake('s3');

        $operador = User::factory()->perfil(PerfilUsuario::OPERADOR_LOGISTICO)->create();
        $contrato = Contrato::factory()->create(['status' => StatusContrato::ATIVO]);

        Livewire::actingAs($operador)
            ->test(ChecklistForm::class, ['contrato' => $contrato, 'tipo' => 'saida'])
            ->set('fotos', [UploadedFile::fake()->image('foto.jpg')])
            ->set('observacoes', 'Tudo certo na saída.')
            ->call('salvar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('checklists', [
            'contrato_id' => $contrato->id,
            'tipo' => 'saida',
            'observacoes' => 'Tudo certo na saída.',
        ]);
    }

    public function test_remover_foto_antes_de_enviar(): void
    {
        Storage::fake('s3');

        $operador = User::factory()->perfil(PerfilUsuario::OPERADOR_LOGISTICO)->create();
        $contrato = Contrato::factory()->create(['status' => StatusContrato::ATIVO]);

        Livewire::actingAs($operador)
            ->test(ChecklistForm::class, ['contrato' => $contrato, 'tipo' => 'saida'])
            ->set('fotos', [
                UploadedFile::fake()->image('foto1.jpg'),
                UploadedFile::fake()->image('foto2.jpg'),
            ])
            ->call('removerFoto', 0)
            ->assertCount('fotos', 1);
    }

    public function test_retorno_sem_checklist_de_saida_nao_mostra_formulario(): void
    {
        $operador = User::factory()->perfil(PerfilUsuario::OPERADOR_LOGISTICO)->create();
        $contrato = Contrato::factory()->create(['status' => StatusContrato::ATIVO]);

        Livewire::actingAs($operador)
            ->test(ChecklistForm::class, ['contrato' => $contrato, 'tipo' => 'retorno'])
            ->assertSee('ainda não tem checklist de saída')
            ->assertDontSee('wire:submit');
    }

    public function test_retorno_encerra_contrato_e_mostra_fotos_da_saida_lado_a_lado(): void
    {
        Storage::fake('s3');

        $operador = User::factory()->perfil(PerfilUsuario::OPERADOR_LOGISTICO)->create();
        $ativo = Ativo::factory()->create(['status' => StatusAtivo::EM_LOCACAO]);
        $contrato = Contrato::factory()->create(['status' => StatusContrato::ATIVO, 'ativo_id' => $ativo->id]);

        Checklist::factory()->create([
            'contrato_id' => $contrato->id,
            'tipo' => \App\Enums\TipoChecklist::SAIDA,
            'fotos_json' => ['https://exemplo.com/foto-saida.jpg'],
        ]);

        Livewire::actingAs($operador)
            ->test(ChecklistForm::class, ['contrato' => $contrato, 'tipo' => 'retorno'])
            ->assertSee('foto-saida.jpg')
            ->call('salvar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('contratos', ['id' => $contrato->id, 'status' => StatusContrato::ENCERRADO->value]);
        $this->assertDatabaseHas('ativos', ['id' => $ativo->id, 'status' => StatusAtivo::DISPONIVEL->value]);
    }

    public function test_tecnico_manutencao_nao_acessa_formulario_de_checklist(): void
    {
        $tecnico = User::factory()->perfil(PerfilUsuario::TECNICO_MANUTENCAO)->create();
        $contrato = Contrato::factory()->create(['status' => StatusContrato::ATIVO]);

        $this->actingAs($tecnico)
            ->get(route('checklists.form', ['contrato' => $contrato->id, 'tipo' => 'saida']))
            ->assertForbidden();
    }
}
