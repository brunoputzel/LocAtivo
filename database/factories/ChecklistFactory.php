<?php

namespace Database\Factories;

use App\Enums\TipoChecklist;
use App\Models\Contrato;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Checklist>
 */
class ChecklistFactory extends Factory
{
    public function definition(): array
    {
        return [
            'contrato_id' => Contrato::factory(),
            'usuario_id' => User::factory(),
            'tipo' => TipoChecklist::SAIDA,
            'fotos_json' => [],
            'observacoes' => fake()->optional()->sentence(),
        ];
    }
}
