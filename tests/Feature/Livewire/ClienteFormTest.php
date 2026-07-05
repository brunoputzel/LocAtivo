<?php

namespace Tests\Feature\Livewire;

use App\Enums\PerfilUsuario;
use App\Livewire\Clientes\ClienteForm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ClienteFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_tipo_pf_exige_documento_com_11_digitos(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();

        Livewire::actingAs($gestor)
            ->test(ClienteForm::class)
            ->set('nome', 'João da Silva')
            ->set('tipo', 'PF')
            ->set('cpfCnpj', '123')
            ->call('salvar')
            ->assertHasErrors(['cpfCnpj']);
    }

    public function test_cadastra_cliente_com_endereco(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();

        Livewire::actingAs($gestor)
            ->test(ClienteForm::class)
            ->set('nome', 'João da Silva')
            ->set('tipo', 'PF')
            ->set('cpfCnpj', '11144477735')
            ->set('endereco', 'Rua das Flores, 123')
            ->call('salvar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('clientes', [
            'nome' => 'João da Silva',
            'endereco' => 'Rua das Flores, 123',
        ]);
    }

    public function test_cpf_com_digito_verificador_invalido_e_rejeitado(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();

        Livewire::actingAs($gestor)
            ->test(ClienteForm::class)
            ->set('nome', 'João da Silva')
            ->set('tipo', 'PF')
            ->set('cpfCnpj', '12345678901')
            ->call('salvar')
            ->assertHasErrors(['cpfCnpj']);
    }

    public function test_cadastra_cliente_pessoa_juridica_com_cnpj_valido(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();

        Livewire::actingAs($gestor)
            ->test(ClienteForm::class)
            ->set('nome', 'Empresa LTDA')
            ->set('tipo', 'PJ')
            ->set('cpfCnpj', '11222333000181')
            ->call('salvar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('clientes', [
            'nome' => 'Empresa LTDA',
            'cpf_cnpj' => '11222333000181',
        ]);
    }

    public function test_cpf_com_pontuacao_e_normalizado_antes_de_salvar(): void
    {
        $gestor = User::factory()->perfil(PerfilUsuario::GESTOR)->create();

        Livewire::actingAs($gestor)
            ->test(ClienteForm::class)
            ->set('nome', 'João da Silva')
            ->set('tipo', 'PF')
            ->set('cpfCnpj', '111.444.777-35')
            ->call('salvar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('clientes', [
            'nome' => 'João da Silva',
            'cpf_cnpj' => '11144477735',
        ]);
    }
}
