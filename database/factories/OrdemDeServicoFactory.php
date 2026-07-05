<?php

namespace Database\Factories;

use App\Enums\PerfilUsuario;
use App\Enums\StatusOrdemServico;
use App\Enums\TipoOrdemServico;
use App\Models\Ativo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrdemDeServico>
 */
class OrdemDeServicoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'ativo_id' => Ativo::factory(),
            'tecnico_id' => User::factory()->perfil(PerfilUsuario::TECNICO_MANUTENCAO),
            'alerta_id' => null,
            'tipo' => fake()->randomElement(TipoOrdemServico::cases()),
            'descricao' => fake()->sentence(),
            'data_abertura' => now()->toDateString(),
            'status' => StatusOrdemServico::ABERTA,
        ];
    }
}
