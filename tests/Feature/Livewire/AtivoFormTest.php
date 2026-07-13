<?php

namespace Tests\Feature\Livewire;

use App\Enums\PerfilUsuario;
use App\Livewire\Ativos\AtivoForm;
use App\Models\Ativo;
use App\Models\TipoAtivo;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AtivoFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_nome_e_obrigatorio_para_salvar(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        $tipo = TipoAtivo::factory()->create();

        Livewire::actingAs($gestor)
            ->test(AtivoForm::class)
            ->set('tipoAtivoId', $tipo->id)
            ->set('modelo', 'G20')
            ->set('numeroSerie', 'SN-0001')
            ->call('salvar')
            ->assertHasErrors(['nome' => 'required']);
    }

    public function test_tipo_de_ativo_e_obrigatorio_para_salvar(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();

        Livewire::actingAs($gestor)
            ->test(AtivoForm::class)
            ->set('nome', 'Gerador 20kva')
            ->set('modelo', 'G20')
            ->set('numeroSerie', 'SN-0001')
            ->call('salvar')
            ->assertHasErrors(['tipoAtivoId' => 'required']);
    }

    public function test_gestor_cadastra_ativo_com_upload_de_foto(): void
    {
        Storage::fake('s3');
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        $tipo = TipoAtivo::factory()->create();

        Livewire::actingAs($gestor)
            ->test(AtivoForm::class)
            ->set('nome', 'Gerador 20kva')
            ->set('tipoAtivoId', $tipo->id)
            ->set('modelo', 'G20')
            ->set('numeroSerie', 'SN-0001')
            ->set('novaFoto', UploadedFile::fake()->image('foto.jpg'))
            ->call('salvar')
            ->assertHasNoErrors();

        $ativo = Ativo::where('numero_serie', 'SN-0001')->firstOrFail();
        $this->assertNotNull($ativo->foto_url);
        Storage::disk('s3')->assertExists($ativo->foto_url);
    }

    public function test_rejeita_upload_de_arquivo_que_nao_e_imagem(): void
    {
        Storage::fake('s3');
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        $tipo = TipoAtivo::factory()->create();

        Livewire::actingAs($gestor)
            ->test(AtivoForm::class)
            ->set('nome', 'Gerador 20kva')
            ->set('tipoAtivoId', $tipo->id)
            ->set('modelo', 'G20')
            ->set('numeroSerie', 'SN-0001')
            ->set('novaFoto', UploadedFile::fake()->create('documento.pdf', 100))
            ->call('salvar')
            ->assertHasErrors(['novaFoto']);
    }

    public function test_rejeita_upload_de_foto_maior_que_o_limite(): void
    {
        Storage::fake('s3');
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        $tipo = TipoAtivo::factory()->create();

        Livewire::actingAs($gestor)
            ->test(AtivoForm::class)
            ->set('nome', 'Gerador 20kva')
            ->set('tipoAtivoId', $tipo->id)
            ->set('modelo', 'G20')
            ->set('numeroSerie', 'SN-0001')
            ->set('novaFoto', UploadedFile::fake()->image('foto.jpg')->size(3000))
            ->call('salvar')
            ->assertHasErrors(['novaFoto']);
    }

    public function test_editar_ativo_substituindo_a_foto_apaga_a_antiga(): void
    {
        Storage::fake('s3');
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        $fotoAntiga = UploadedFile::fake()->image('antiga.jpg')->store('ativos', 's3');
        $ativo = Ativo::factory()->create(['foto_url' => $fotoAntiga]);

        Livewire::actingAs($gestor)
            ->test(AtivoForm::class)
            ->call('editar', $ativo->id)
            ->set('novaFoto', UploadedFile::fake()->image('nova.jpg'))
            ->call('salvar')
            ->assertHasNoErrors();

        Storage::disk('s3')->assertMissing($fotoAntiga);
        $this->assertNotEquals($fotoAntiga, $ativo->fresh()->foto_url);
    }

    public function test_editar_ativo_removendo_a_foto_existente(): void
    {
        Storage::fake('s3');
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        $foto = UploadedFile::fake()->image('foto.jpg')->store('ativos', 's3');
        $ativo = Ativo::factory()->create(['foto_url' => $foto]);

        Livewire::actingAs($gestor)
            ->test(AtivoForm::class)
            ->call('editar', $ativo->id)
            ->call('removerFotoAtual')
            ->call('salvar')
            ->assertHasNoErrors();

        Storage::disk('s3')->assertMissing($foto);
        $this->assertNull($ativo->fresh()->foto_url);
    }

    public function test_busca_tipo_ativo_existente_filtra_a_lista(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        TipoAtivo::factory()->create(['nome' => 'Gerador']);
        TipoAtivo::factory()->create(['nome' => 'Compressor']);

        Livewire::actingAs($gestor)
            ->test(AtivoForm::class)
            ->call('abrirPainelTipoAtivo')
            ->set('buscaTipoAtivo', 'Gera')
            ->assertSee('Gerador')
            ->assertDontSee('Compressor');
    }

    public function test_cadastra_novo_tipo_de_ativo_pelo_combobox(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();

        Livewire::actingAs($gestor)
            ->test(AtivoForm::class)
            ->set('buscaTipoAtivo', 'Retroescavadeira')
            ->call('cadastrarTipoAtivo')
            ->assertSet('buscaTipoAtivo', '');

        $tipo = TipoAtivo::where('nome', 'Retroescavadeira')->first();
        $this->assertNotNull($tipo);
    }

    public function test_nao_duplica_tipo_de_ativo_ao_cadastrar_com_nome_repetido_case_insensitive(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        TipoAtivo::factory()->create(['nome' => 'Retroescavadeira']);

        Livewire::actingAs($gestor)
            ->test(AtivoForm::class)
            ->set('buscaTipoAtivo', '  retroescavadeira  ')
            ->call('cadastrarTipoAtivo');

        $this->assertDatabaseCount('tipo_ativos', 1);
    }

    public function test_gestor_cadastra_ativo_com_valor_diaria_de_referencia(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        $tipo = TipoAtivo::factory()->create();

        Livewire::actingAs($gestor)
            ->test(AtivoForm::class)
            ->set('nome', 'Gerador 20kva')
            ->set('tipoAtivoId', $tipo->id)
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
        $tipo = TipoAtivo::factory()->create();

        Livewire::actingAs($gestor)
            ->test(AtivoForm::class)
            ->set('nome', 'Gerador 20kva')
            ->set('tipoAtivoId', $tipo->id)
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
        $tipo = TipoAtivo::factory()->create();

        // Livewire converte a exceção não tratada numa resposta de teste em vez de
        // deixá-la borbulhar - precisa desligar o exception handling pra capturá-la
        $this->withoutExceptionHandling();
        $this->expectException(AuthorizationException::class);

        Livewire::actingAs($operador)
            ->test(AtivoForm::class)
            ->set('nome', 'Gerador 20kva')
            ->set('tipoAtivoId', $tipo->id)
            ->set('modelo', 'G20')
            ->set('numeroSerie', 'SN-0001')
            ->call('salvar');
    }
}
