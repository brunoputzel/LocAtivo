<?php

namespace Database\Factories;

use App\Enums\TipoCliente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cliente>
 */
class ClienteFactory extends Factory
{
    public function definition(): array
    {
        $tipo = fake()->randomElement(TipoCliente::cases());

        return [
            'nome' => fake()->name(),
            'tipo' => $tipo,
            'cpf_cnpj' => fake()->unique()->numerify(str_repeat('#', $tipo->tamanhoDocumento())),
            'email' => fake()->unique()->safeEmail(),
            'telefone' => fake()->numerify('###########'),
            'ativo' => true,
        ];
    }
}
