<?php

namespace Database\Seeders;

use App\Enums\TipoAlerta;
use App\Models\AlertaManutencao;
use App\Models\Ativo;
use Illuminate\Database\Seeder;

class AlertaManutencaoSeeder extends Seeder
{
    private const DESCRICOES = [
        'Horímetro se aproximando do limite de manutenção preventiva.',
        'Prazo de revisão programada vencendo em breve.',
        'Alerta gerado automaticamente pelo acompanhamento de uso.',
        'Necessário agendar inspeção de rotina.',
    ];

    public function run(): void
    {
        $ativos = Ativo::inRandomOrder()->limit(4)->get();

        if ($ativos->isEmpty()) {
            $this->command?->warn('Nenhum ativo encontrado - rode o AtivoSeeder antes.');

            return;
        }

        $cenarios = [
            ['resolvido' => true, 'data_alerta' => now()->subDays(20)],
            ['resolvido' => true, 'data_alerta' => now()->subDays(10)],
            // não resolvido e próximo de hoje - é o caso que testa a notificação
            ['resolvido' => false, 'data_alerta' => now()->addDay()],
            ['resolvido' => false, 'data_alerta' => now()->subDays(5)],
        ];

        foreach ($cenarios as $indice => $cenario) {
            $ativo = $ativos[$indice % $ativos->count()];

            AlertaManutencao::factory()->create([
                'ativo_id' => $ativo->id,
                'tipo' => fake()->randomElement(TipoAlerta::cases()),
                'descricao' => fake()->randomElement(self::DESCRICOES),
                'data_alerta' => $cenario['data_alerta']->toDateString(),
                'resolvido' => $cenario['resolvido'],
            ]);
        }
    }
}
