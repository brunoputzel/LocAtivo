<?php

namespace Database\Factories;

use App\Enums\StatusContrato;
use App\Models\Ativo;
use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contrato>
 */
class ContratoFactory extends Factory
{
    public function definition(): array
    {
        $dataInicio = fake()->dateTimeBetween('-30 days', 'now');

        return [
            'ativo_id' => Ativo::factory(),
            'cliente_id' => Cliente::factory(),
            'data_inicio' => $dataInicio,
            'data_fim' => fake()->dateTimeBetween($dataInicio, '+30 days'),
            'valor_diaria' => fake()->randomFloat(2, 50, 500),
            'status' => StatusContrato::ATIVO,
        ];
    }
}
