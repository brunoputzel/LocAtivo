<?php

namespace Database\Seeders;

use App\Enums\StatusAtivo;
use App\Models\Ativo;
use App\Models\Cliente;
use App\Services\ContratoService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class ContratoSeeder extends Seeder
{
    private const OBSERVACOES = [
        'Entrega agendada para o período da manhã.',
        'Cliente solicitou nota fiscal antecipada.',
        'Retirada no próprio depósito.',
        'Equipamento com acessórios extras incluídos.',
    ];

    public function run(): void
    {
        $contratoService = app(ContratoService::class);
        $clientes = Cliente::all();

        if ($clientes->isEmpty()) {
            $this->command?->warn('Nenhum cliente encontrado - rode o ClienteSeeder antes.');

            return;
        }

        // cada cenário consome um ativo DISPONIVEL diferente, igual a regra real
        // de negócio exige (ContratoService::criar só aceita ativo disponível)
        $this->criarLote($contratoService, $clientes, 6, 'ativo');
        $this->criarLote($contratoService, $clientes, 2, 'vence_em_breve');
        $this->criarLote($contratoService, $clientes, 5, 'encerrar');
    }

    private function criarLote(ContratoService $contratoService, Collection $clientes, int $quantidade, string $cenario): void
    {
        for ($i = 0; $i < $quantidade; $i++) {
            $ativo = Ativo::where('status', StatusAtivo::DISPONIVEL)->inRandomOrder()->first();

            if (! $ativo) {
                $this->command?->warn('Sem ativos disponíveis sobrando - pulando o restante dos contratos.');

                return;
            }

            [$dataInicio, $dataFim] = $this->datasPara($cenario);

            $contratoService->criar([
                'ativo_id' => $ativo->id,
                'cliente_id' => $clientes->random()->id,
                'data_inicio' => $dataInicio,
                'data_fim' => $dataFim,
                'valor_diaria' => $ativo->valor_diaria_referencia ?? fake()->randomFloat(2, 80, 500),
                'observacoes' => fake()->optional(0.5)->randomElement(self::OBSERVACOES),
            ]);
        }
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function datasPara(string $cenario): array
    {
        // 'encerrar' fica com status ATIVO até o ChecklistSeeder registrar o
        // retorno - é o retorno que encerra o contrato de verdade (ver ChecklistService)
        return match ($cenario) {
            'ativo' => [
                now()->subDays(fake()->numberBetween(5, 20))->toDateString(),
                now()->addDays(fake()->numberBetween(10, 45))->toDateString(),
            ],
            'vence_em_breve' => [
                now()->subDays(fake()->numberBetween(10, 25))->toDateString(),
                now()->addDays(fake()->numberBetween(1, 3))->toDateString(),
            ],
            'encerrar' => [
                now()->subDays(fake()->numberBetween(30, 90))->toDateString(),
                now()->subDays(fake()->numberBetween(1, 25))->toDateString(),
            ],
        };
    }
}
