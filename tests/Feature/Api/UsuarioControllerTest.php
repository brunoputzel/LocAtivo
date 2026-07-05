<?php

namespace Tests\Feature\Api;

use App\Enums\PerfilUsuario;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsuarioControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_operador_logistico_nao_pode_criar_usuario(): void
    {
        $operador = User::factory()->perfil(PerfilUsuario::OPERADOR_LOGISTICO)->create();

        $response = $this->actingAs($operador, 'sanctum')->postJson('/api/usuarios', [
            'name' => 'Novo Técnico',
            'email' => 'tecnico@locativo.com',
            'password' => 'senha1234',
            'perfil' => PerfilUsuario::TECNICO_MANUTENCAO->value,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('users', ['email' => 'tecnico@locativo.com']);
    }

    public function test_gestor_pode_criar_usuario_com_o_perfil_informado(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();

        $response = $this->actingAs($gestor, 'sanctum')->postJson('/api/usuarios', [
            'name' => 'Novo Técnico',
            'email' => 'tecnico@locativo.com',
            'password' => 'senha1234',
            'perfil' => PerfilUsuario::TECNICO_MANUTENCAO->value,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('perfil', PerfilUsuario::TECNICO_MANUTENCAO->value);

        $this->assertDatabaseHas('users', [
            'email' => 'tecnico@locativo.com',
            'perfil' => PerfilUsuario::TECNICO_MANUTENCAO->value,
        ]);
    }

    public function test_criar_usuario_cliente_exige_cliente_id(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();

        $response = $this->actingAs($gestor, 'sanctum')->postJson('/api/usuarios', [
            'name' => 'Novo Cliente',
            'email' => 'cliente@locativo.com',
            'password' => 'senha1234',
            'perfil' => PerfilUsuario::CLIENTE->value,
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['cliente_id']);
    }

    public function test_criar_usuario_cliente_com_cliente_id_inexistente_e_rejeitado(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();

        $response = $this->actingAs($gestor, 'sanctum')->postJson('/api/usuarios', [
            'name' => 'Novo Cliente',
            'email' => 'cliente@locativo.com',
            'password' => 'senha1234',
            'perfil' => PerfilUsuario::CLIENTE->value,
            'cliente_id' => 999,
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['cliente_id']);
    }

    public function test_gestor_cria_usuario_cliente_vinculado_ao_cliente_id(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        $cliente = Cliente::factory()->create();

        $response = $this->actingAs($gestor, 'sanctum')->postJson('/api/usuarios', [
            'name' => 'Novo Cliente',
            'email' => 'cliente@locativo.com',
            'password' => 'senha1234',
            'perfil' => PerfilUsuario::CLIENTE->value,
            'cliente_id' => $cliente->id,
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('users', [
            'email' => 'cliente@locativo.com',
            'cliente_id' => $cliente->id,
        ]);
    }

    public function test_gestor_atualiza_nome_perfil_e_email_de_usuario(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        $usuario = User::factory()->perfil(PerfilUsuario::OPERADOR_LOGISTICO)->create();

        $response = $this->actingAs($gestor, 'sanctum')->putJson("/api/usuarios/{$usuario->id}", [
            'name' => 'Nome Atualizado',
            'email' => 'atualizado@locativo.com',
            'perfil' => PerfilUsuario::TECNICO_MANUTENCAO->value,
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $usuario->id,
            'name' => 'Nome Atualizado',
            'email' => 'atualizado@locativo.com',
            'perfil' => PerfilUsuario::TECNICO_MANUTENCAO->value,
        ]);
    }

    public function test_gestor_desativa_usuario_sem_excluir_o_registro(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        $usuario = User::factory()->create(['ativo' => true]);

        $response = $this->actingAs($gestor, 'sanctum')->deleteJson("/api/usuarios/{$usuario->id}");

        $response->assertNoContent();
        $this->assertDatabaseHas('users', ['id' => $usuario->id, 'ativo' => false]);
    }

    public function test_gestor_nao_pode_desativar_a_propria_conta(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create(['ativo' => true]);

        $response = $this->actingAs($gestor, 'sanctum')->deleteJson("/api/usuarios/{$gestor->id}");

        $response->assertStatus(400);
        $this->assertDatabaseHas('users', ['id' => $gestor->id, 'ativo' => true]);
    }

    public function test_gestor_reativa_usuario_desativado(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        $usuario = User::factory()->inativo()->create();

        $response = $this->actingAs($gestor, 'sanctum')->patchJson("/api/usuarios/{$usuario->id}/ativar");

        $response->assertOk();
        $this->assertDatabaseHas('users', ['id' => $usuario->id, 'ativo' => true]);
    }
}
