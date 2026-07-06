<?php

namespace Database\Seeders;

use App\Enums\PerfilUsuario;
use App\Enums\StatusAtivo;
use App\Enums\TipoOrdemServico;
use App\Models\Ativo;
use App\Models\User;
use App\Services\OrdemDeServicoService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OrdemDeServicoSeeder extends Seeder
{
    private const DESCRICOES = [
        'Troca de óleo e filtros de rotina.',
        'Vazamento identificado na mangueira de combustível.',
        'Manutenção preventiva programada pelo horímetro.',
        'Ruído anormal reportado pelo operador durante o uso.',
        'Revisão elétrica e teste de disjuntores.',
    ];

    public function run(): void
    {
        $tecnico = User::where('perfil', PerfilUsuario::TECNICO_MANUTENCAO)->first();

        if (! $tecnico) {
            // sem técnico cadastrado não dá pra cumprir a regra de responsável -
            // cria um usuário demo em vez de deixar a tabela vazia num ambiente novo
            $tecnico = User::factory()->perfil(PerfilUsuario::TECNICO_MANUTENCAO)->create([
                'name' => 'Técnico de Manutenção Demo',
                'email' => 'tecnico.demo@locativo.com',
                'password' => Hash::make('locativo123'),
            ]);

            $this->command?->info('Nenhum técnico de manutenção encontrado - usuário demo criado.');
        }

        $ordemDeServicoService = app(OrdemDeServicoService::class);

        $this->criarFechadas($ordemDeServicoService, $tecnico, 6);
        $this->criarAbertas($ordemDeServicoService, $tecnico, 4);
    }

    private function criarFechadas(OrdemDeServicoService $service, User $tecnico, int $quantidade): void
    {
        for ($i = 0; $i < $quantidade; $i++) {
            $ativo = $this->proximoAtivoDisponivel();

            if (! $ativo) {
                return;
            }

            $dataAbertura = now()->subDays(fake()->numberBetween(15, 60));

            $os = $service->abrir([
                'ativo_id' => $ativo->id,
                'tecnico_id' => $tecnico->id,
                'tipo' => fake()->randomElement(TipoOrdemServico::cases()),
                'descricao' => fake()->randomElement(self::DESCRICOES),
                'data_abertura' => $dataAbertura->toDateString(),
            ]);

            $service->fechar(
                $os,
                fake()->randomFloat(2, 100, 2500),
                $dataAbertura->copy()->addDays(fake()->numberBetween(1, 10))->toDateString()
            );
        }
    }

    private function criarAbertas(OrdemDeServicoService $service, User $tecnico, int $quantidade): void
    {
        for ($i = 0; $i < $quantidade; $i++) {
            $ativo = $this->proximoAtivoDisponivel();

            if (! $ativo) {
                return;
            }

            $service->abrir([
                'ativo_id' => $ativo->id,
                'tecnico_id' => $tecnico->id,
                'tipo' => fake()->randomElement(TipoOrdemServico::cases()),
                'descricao' => fake()->randomElement(self::DESCRICOES),
                'data_abertura' => now()->subDays(fake()->numberBetween(1, 10))->toDateString(),
            ]);
        }
    }

    private function proximoAtivoDisponivel(): ?Ativo
    {
        $ativo = Ativo::where('status', StatusAtivo::DISPONIVEL)->inRandomOrder()->first();

        if (! $ativo) {
            $this->command?->warn('Sem ativos disponíveis sobrando - pulando o restante das ordens de serviço.');
        }

        return $ativo;
    }
}
