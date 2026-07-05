<?php

namespace Tests\Feature\Api;

use App\Enums\PerfilUsuario;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_com_credenciais_validas_retorna_token_e_perfil(): void
    {
        $usuario = User::factory()->perfil(PerfilUsuario::GESTOR)->create([
            'email' => 'gestor@locativo.com',
            'password' => 'senha123',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'gestor@locativo.com',
            'password' => 'senha123',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('usuario.perfil', PerfilUsuario::GESTOR->value)
            ->assertJsonStructure(['token', 'usuario']);

        $this->assertNotEmpty($response->json('token'));
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_login_com_senha_incorreta_retorna_401_sem_gerar_token(): void
    {
        $usuario = User::factory()->create();

        $response = $this->postJson('/api/login', [
            'email' => $usuario->email,
            'password' => 'senha-errada',
        ]);

        $response->assertUnauthorized();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_acesso_a_rota_protegida_sem_token_retorna_401(): void
    {
        $response = $this->getJson('/api/user');

        $response->assertUnauthorized();
    }

    public function test_usuario_inativo_nao_consegue_autenticar(): void
    {
        $usuario = User::factory()->inativo()->create([
            'email' => 'inativo@locativo.com',
            'password' => 'senha123',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'inativo@locativo.com',
            'password' => 'senha123',
        ]);

        $response->assertUnauthorized();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
