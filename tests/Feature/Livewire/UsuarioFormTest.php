<?php

namespace Tests\Feature\Livewire;

use App\Enums\PerfilUsuario;
use App\Livewire\Usuarios\UsuarioForm;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UsuarioFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_gestor_cadastra_novo_usuario(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();

        Livewire::actingAs($gestor)
            ->test(UsuarioForm::class)
            ->set('name', 'Novo Técnico')
            ->set('email', 'tecnico@locativo.com')
            ->set('perfil', PerfilUsuario::TECNICO_MANUTENCAO->value)
            ->set('password', 'senha1234')
            ->call('salvar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'email' => 'tecnico@locativo.com',
            'perfil' => PerfilUsuario::TECNICO_MANUTENCAO->value,
        ]);
    }

    public function test_perfil_cliente_exige_cliente_vinculado(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();

        Livewire::actingAs($gestor)
            ->test(UsuarioForm::class)
            ->set('name', 'Novo Cliente')
            ->set('email', 'cliente@locativo.com')
            ->set('perfil', PerfilUsuario::CLIENTE->value)
            ->set('password', 'senha1234')
            ->call('salvar')
            ->assertHasErrors(['clienteId']);
    }

    public function test_edita_nome_perfil_e_email_de_usuario_existente(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        $usuario = User::factory()->perfil(PerfilUsuario::OPERADOR_LOGISTICO)->create();

        Livewire::actingAs($gestor)
            ->test(UsuarioForm::class)
            ->call('editar', $usuario->id)
            ->assertSet('name', $usuario->name)
            ->set('name', 'Nome Editado')
            ->set('perfil', PerfilUsuario::GESTOR->value)
            ->call('salvar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $usuario->id,
            'name' => 'Nome Editado',
            'perfil' => PerfilUsuario::GESTOR->value,
        ]);
    }

    public function test_editar_usuario_nao_exige_senha(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();
        $usuario = User::factory()->create();

        Livewire::actingAs($gestor)
            ->test(UsuarioForm::class)
            ->call('editar', $usuario->id)
            ->set('name', 'Outro Nome')
            ->call('salvar')
            ->assertHasNoErrors();
    }

    public function test_operador_logistico_nao_pode_salvar_novo_usuario(): void
    {
        $operador = User::factory()->perfil(PerfilUsuario::OPERADOR_LOGISTICO)->create();

        $this->withoutExceptionHandling();
        $this->expectException(AuthorizationException::class);

        Livewire::actingAs($operador)
            ->test(UsuarioForm::class)
            ->set('name', 'Alguém')
            ->set('email', 'alguem@locativo.com')
            ->set('password', 'senha1234')
            ->call('salvar');
    }
}
