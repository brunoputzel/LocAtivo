<?php

namespace Database\Factories;

use App\Enums\TipoAlerta;
use App\Models\Ativo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AlertaManutencao>
 */
class AlertaManutencaoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'ativo_id' => Ativo::factory(),
            'tipo' => fake()->randomElement(TipoAlerta::cases()),
            'descricao' => fake()->sentence(),
            'data_alerta' => fake()->dateTimeBetween('-30 days', 'now'),
            'resolvido' => false,
        ];
    }
}
