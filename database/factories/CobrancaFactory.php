<?php

namespace Database\Factories;

use App\Models\Contrato;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cobranca>
 */
class CobrancaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'contrato_id' => Contrato::factory(),
            'valor' => fake()->randomFloat(2, 100, 5000),
            'status' => 'pendente',
        ];
    }
}
