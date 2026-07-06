<?php

namespace Database\Factories;

use App\Enums\TipoCliente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cliente>
 */
class ClienteFactory extends Factory
{
    /**
     * Ruas e bairros reais de Chapecó e da região oeste de SC.
     */
    private const ENDERECOS = [
        ['rua' => 'Rua Marechal Bormann', 'bairro' => 'Centro', 'cidade' => 'Chapecó'],
        ['rua' => 'Avenida Getúlio Vargas', 'bairro' => 'Centro', 'cidade' => 'Chapecó'],
        ['rua' => 'Rua Rio Branco', 'bairro' => 'Presidente Médici', 'cidade' => 'Chapecó'],
        ['rua' => 'Rua Roraima', 'bairro' => 'Efapi', 'cidade' => 'Chapecó'],
        ['rua' => 'Avenida Willy Barth', 'bairro' => 'São Cristóvão', 'cidade' => 'Chapecó'],
        ['rua' => 'Rua XV de Novembro', 'bairro' => 'Centro', 'cidade' => 'Xanxerê'],
        ['rua' => 'Rua Santos Dumont', 'bairro' => 'Centro', 'cidade' => 'Concórdia'],
        ['rua' => 'Rua Amazonas', 'bairro' => 'Centro', 'cidade' => 'São Miguel do Oeste'],
    ];

    public function definition(): array
    {
        $tipo = fake()->randomElement(TipoCliente::cases());
        $endereco = fake()->randomElement(self::ENDERECOS);

        return [
            'nome' => $tipo === TipoCliente::PJ ? fake()->company() : fake()->name(),
            'tipo' => $tipo,
            // faker no locale pt_BR já gera CPF/CNPJ com dígito verificador válido
            'cpf_cnpj' => $tipo === TipoCliente::PJ ? fake()->unique()->cnpj(false) : fake()->unique()->cpf(false),
            'email' => fake()->unique()->safeEmail(),
            'telefone' => '(49) '.fake()->numerify('9####-####'),
            'endereco' => "{$endereco['rua']}, ".fake()->numberBetween(50, 2500)." - {$endereco['bairro']}, {$endereco['cidade']} - SC",
            'ativo' => true,
        ];
    }
}
