<?php

namespace Tests\Feature\Api;

use App\Enums\PerfilUsuario;
use App\Models\Cliente;
use App\Models\Contrato;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClienteControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_gestor_cadastra_cliente_pessoa_fisica(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();

        $response = $this->actingAs($gestor, 'sanctum')->postJson('/api/clientes', [
            'nome' => 'João da Silva',
            'tipo' => 'PF',
            'cpf_cnpj' => '11144477735',
        ]);

        $response->assertCreated();
    }

    public function test_cadastra_cliente_com_endereco(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();

        $response = $this->actingAs($gestor, 'sanctum')->postJson('/api/clientes', [
            'nome' => 'João da Silva',
            'tipo' => 'PF',
            'cpf_cnpj' => '11144477735',
            'endereco' => 'Rua das Flores, 123',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('endereco', 'Rua das Flores, 123');
    }

    public function test_operador_logistico_cadastra_cliente_pessoa_juridica(): void
    {
        $operador = User::factory()->perfil(PerfilUsuario::OPERADOR_LOGISTICO)->create();

        $response = $this->actingAs($operador, 'sanctum')->postJson('/api/clientes', [
            'nome' => 'Empresa LTDA',
            'tipo' => 'PJ',
            'cpf_cnpj' => '11222333000181',
        ]);

        $response->assertCreated();
    }

    public function test_tipo_invalido_e_rejeitado(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();

        $response = $this->actingAs($gestor, 'sanctum')->postJson('/api/clientes', [
            'nome' => 'Empresa LTDA',
            'tipo' => 'EMPRESA',
            'cpf_cnpj' => '11222333000181',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['tipo']);
    }

    public function test_email_invalido_e_rejeitado(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();

        $response = $this->actingAs($gestor, 'sanctum')->postJson('/api/clientes', [
            'nome' => 'João da Silva',
            'tipo' => 'PF',
            'cpf_cnpj' => '11144477735',
            'email' => 'nao-e-um-email',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['email']);
    }

    public function test_cpf_com_tamanho_invalido_para_pessoa_fisica_e_rejeitado(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();

        $response = $this->actingAs($gestor, 'sanctum')->postJson('/api/clientes', [
            'nome' => 'João da Silva',
            'tipo' => 'PF',
            'cpf_cnpj' => '123',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['cpf_cnpj']);
    }

    public function test_cpf_com_digito_verificador_invalido_e_rejeitado(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();

        $response = $this->actingAs($gestor, 'sanctum')->postJson('/api/clientes', [
            'nome' => 'João da Silva',
            'tipo' => 'PF',
            'cpf_cnpj' => '12345678901',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['cpf_cnpj']);
    }

    public function test_cpf_com_todos_digitos_iguais_e_rejeitado(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();

        $response = $this->actingAs($gestor, 'sanctum')->postJson('/api/clientes', [
            'nome' => 'João da Silva',
            'tipo' => 'PF',
            'cpf_cnpj' => '11111111111',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['cpf_cnpj']);
    }

    public function test_cnpj_com_digito_verificador_invalido_e_rejeitado(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();

        $response = $this->actingAs($gestor, 'sanctum')->postJson('/api/clientes', [
            'nome' => 'Empresa LTDA',
            'tipo' => 'PJ',
            'cpf_cnpj' => '12345678000199',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['cpf_cnpj']);
    }

    public function test_tecnico_manutencao_nao_pode_cadastrar_cliente(): void
    {
        $tecnico = User::factory()->perfil(PerfilUsuario::TECNICO_MANUTENCAO)->create();

        $response = $this->actingAs($tecnico, 'sanctum')->postJson('/api/clientes', [
            'nome' => 'João da Silva',
            'tipo' => 'PF',
            'cpf_cnpj' => '11144477735',
        ]);

        $response->assertForbidden();
    }

    public function test_excluir_cliente_com_contratos_faz_soft_delete(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        $cliente = Cliente::factory()->create();
        Contrato::factory()->create(['cliente_id' => $cliente->id]);

        $response = $this->actingAs($gestor, 'sanctum')->deleteJson("/api/clientes/{$cliente->id}");

        $response->assertNoContent();
        $this->assertDatabaseHas('clientes', ['id' => $cliente->id, 'ativo' => false]);
    }

    public function test_buscar_cliente_inexistente_retorna_404(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();

        $response = $this->actingAs($gestor, 'sanctum')->getJson('/api/clientes/999');

        $response->assertNotFound();
    }
}
