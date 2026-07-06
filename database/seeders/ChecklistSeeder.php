<?php

namespace Database\Seeders;

use App\Enums\PerfilUsuario;
use App\Enums\StatusContrato;
use App\Enums\TipoChecklist;
use App\Models\Contrato;
use App\Models\User;
use App\Services\ChecklistService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ChecklistSeeder extends Seeder
{
    private const OBSERVACOES_SAIDA = [
        'Equipamento em bom estado, sem avarias visíveis.',
        'Pequeno desgaste na pintura, sem impacto no funcionamento.',
        'Revisado antes da entrega, nível de óleo conferido.',
        'Cliente conferiu o equipamento e assinou o checklist.',
    ];

    private const OBSERVACOES_RETORNO = [
        'Equipamento devolvido em bom estado.',
        'Pequeno desgaste na lona, dentro do esperado pelo uso.',
        'Sujeira acumulada, necessário higienizar antes do próximo contrato.',
        'Sem avarias, pronto para nova locação.',
    ];

    public function run(): void
    {
        $operador = User::firstOrCreate(
            ['email' => 'operador.demo@locativo.com'],
            [
                'name' => 'Operador Logístico Demo',
                'password' => Hash::make('locativo123'),
                'perfil' => PerfilUsuario::OPERADOR_LOGISTICO,
                'ativo' => true,
                'email_verified_at' => now(),
            ]
        );

        $checklistService = app(ChecklistService::class);

        // saída pra todos os contratos, sejam eles ainda ativos ou pra encerrar
        Contrato::all()->each(
            fn (Contrato $contrato) => $checklistService->registrar(
                $contrato,
                $operador,
                TipoChecklist::SAIDA,
                fake()->randomElement(self::OBSERVACOES_SAIDA),
                []
            )
        );

        // retorno só nos contratos cujo prazo já passou - registrar o retorno
        // encerra o contrato automaticamente (ver ChecklistService::registrar)
        Contrato::where('status', StatusContrato::ATIVO)
            ->whereDate('data_fim', '<', now())
            ->get()
            ->each(
                fn (Contrato $contrato) => $checklistService->registrar(
                    $contrato,
                    $operador,
                    TipoChecklist::RETORNO,
                    fake()->randomElement(self::OBSERVACOES_RETORNO),
                    []
                )
            );
    }
}
