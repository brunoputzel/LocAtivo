<?php

namespace Tests\Feature\Api;

use App\Enums\PerfilUsuario;
use App\Enums\StatusAtivo;
use App\Enums\StatusContrato;
use App\Models\Ativo;
use App\Models\Contrato;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ChecklistControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_operador_registra_checklist_de_saida(): void
    {
        Storage::fake('s3');

        $operador = User::factory()->perfil(PerfilUsuario::OPERADOR_LOGISTICO)->create();
        $contrato = Contrato::factory()->create(['status' => StatusContrato::ATIVO]);

        $response = $this->actingAs($operador, 'sanctum')->postJson("/api/contratos/{$contrato->id}/checklists", [
            'tipo' => 'saida',
            'observacoes' => 'Equipamento em bom estado.',
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('checklists', [
            'contrato_id' => $contrato->id,
            'usuario_id' => $operador->id,
            'tipo' => 'saida',
        ]);
    }

    public function test_fotos_sao_armazenadas_no_disco_s3(): void
    {
        Storage::fake('s3');

        $operador = User::factory()->perfil(PerfilUsuario::OPERADOR_LOGISTICO)->create();
        $contrato = Contrato::factory()->create(['status' => StatusContrato::ATIVO]);

        $response = $this->actingAs($operador, 'sanctum')->postJson("/api/contratos/{$contrato->id}/checklists", [
            'tipo' => 'saida',
            'fotos' => [
                UploadedFile::fake()->image('foto1.jpg'),
                UploadedFile::fake()->image('foto2.png'),
            ],
        ]);

        $response->assertCreated();

        $checklist = $contrato->fresh()->checklists()->first();
        $this->assertCount(2, $checklist->fotos_json);

        Storage::disk('s3')->assertExists(str_replace(Storage::disk('s3')->url(''), '', $checklist->fotos_json[0]));
    }

    public function test_rejeita_arquivo_que_nao_e_imagem(): void
    {
        Storage::fake('s3');

        $operador = User::factory()->perfil(PerfilUsuario::OPERADOR_LOGISTICO)->create();
        $contrato = Contrato::factory()->create(['status' => StatusContrato::ATIVO]);

        $response = $this->actingAs($operador, 'sanctum')->postJson("/api/contratos/{$contrato->id}/checklists", [
            'tipo' => 'saida',
            'fotos' => [UploadedFile::fake()->create('documento.pdf', 100)],
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['fotos.0']);
    }

    public function test_nao_permite_checklist_de_retorno_sem_checklist_de_saida(): void
    {
        Storage::fake('s3');

        $operador = User::factory()->perfil(PerfilUsuario::OPERADOR_LOGISTICO)->create();
        $contrato = Contrato::factory()->create(['status' => StatusContrato::ATIVO]);

        $response = $this->actingAs($operador, 'sanctum')->postJson("/api/contratos/{$contrato->id}/checklists", [
            'tipo' => 'retorno',
        ]);

        $response->assertStatus(400);
        $this->assertDatabaseCount('checklists', 0);
    }

    public function test_checklist_de_retorno_encerra_o_contrato_automaticamente(): void
    {
        Storage::fake('s3');

        $operador = User::factory()->perfil(PerfilUsuario::OPERADOR_LOGISTICO)->create();
        $ativo = Ativo::factory()->create(['status' => StatusAtivo::EM_LOCACAO]);
        $contrato = Contrato::factory()->create(['status' => StatusContrato::ATIVO, 'ativo_id' => $ativo->id]);

        $this->actingAs($operador, 'sanctum')->postJson("/api/contratos/{$contrato->id}/checklists", [
            'tipo' => 'saida',
        ])->assertCreated();

        $response = $this->actingAs($operador, 'sanctum')->postJson("/api/contratos/{$contrato->id}/checklists", [
            'tipo' => 'retorno',
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('contratos', ['id' => $contrato->id, 'status' => StatusContrato::ENCERRADO->value]);
        $this->assertDatabaseHas('ativos', ['id' => $ativo->id, 'status' => StatusAtivo::DISPONIVEL->value]);
        $this->assertDatabaseHas('cobrancas', ['contrato_id' => $contrato->id]);
    }

    public function test_tecnico_manutencao_nao_pode_registrar_checklist(): void
    {
        Storage::fake('s3');

        $tecnico = User::factory()->perfil(PerfilUsuario::TECNICO_MANUTENCAO)->create();
        $contrato = Contrato::factory()->create(['status' => StatusContrato::ATIVO]);

        $response = $this->actingAs($tecnico, 'sanctum')->postJson("/api/contratos/{$contrato->id}/checklists", [
            'tipo' => 'saida',
        ]);

        $response->assertForbidden();
    }
}
