<?php

namespace Database\Factories;

use App\Enums\StatusAtivo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ativo>
 */
class AtivoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nome' => fake()->randomElement(['Gerador', 'Compressor', 'Andaime']).' '.fake()->word(),
            'tipo' => fake()->randomElement(['gerador', 'compressor', 'andaime']),
            'modelo' => fake()->bothify('Modelo-####'),
            'numero_serie' => fake()->unique()->bothify('SN-########'),
            'status' => StatusAtivo::DISPONIVEL,
            'horimetro' => fake()->randomFloat(2, 0, 500),
        ];
    }
}
