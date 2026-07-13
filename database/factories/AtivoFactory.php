<?php

namespace Database\Factories;

use App\Enums\StatusAtivo;
use App\Models\TipoAtivo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ativo>
 */
class AtivoFactory extends Factory
{
    /**
     * Marcas e modelos reais do mercado brasileiro de locação, por tipo de equipamento.
     * Cada chave vira (ou reaproveita, via firstOrCreate) um TipoAtivo de verdade.
     */
    private const CATALOGO = [
        'Gerador' => [
            'marcas' => ['Cummins', 'Branco', 'Toyama', 'Motomil', 'Stemac'],
            'modelos' => ['C60D5', 'BD-6500CFE', 'TG3000CX', 'MG6000CVE', 'SS44'],
        ],
        'Compressor' => [
            'marcas' => ['Chiaperini', 'Schulz', 'Pratik'],
            'modelos' => ['CJ 10+ 100L', 'CSA 10/100', 'PSV 10ADI', 'MSV 20/300'],
        ],
        'Andaime' => [
            'marcas' => ['Mills', 'Alfa Andaimes', 'Metaltec'],
            'modelos' => ['Fachadeiro 1,50m', 'Torre H1', 'Multidirecional 2,00m'],
        ],
        'Compactador de solo' => [
            'marcas' => ['Wacker Neuson', 'Dynapac', 'Mitsuda'],
            'modelos' => ['BS 60-4s', 'LG 200', 'PC 65'],
        ],
        'Torre de iluminação' => [
            'marcas' => ['Multiquip', 'Generac', 'VMB'],
            'modelos' => ['TL-4000', 'MLT6SD3', 'TI-4x400'],
        ],
    ];

    public function definition(): array
    {
        $tipo = fake()->randomElement(array_keys(self::CATALOGO));
        $catalogo = self::CATALOGO[$tipo];
        $tipoAtivo = TipoAtivo::firstOrCreate(['nome' => $tipo]);

        return [
            'nome' => $tipo.' '.fake()->randomElement($catalogo['marcas']),
            'tipo_ativo_id' => $tipoAtivo->id,
            'modelo' => fake()->randomElement($catalogo['modelos']),
            'numero_serie' => fake()->unique()->bothify('SN-########'),
            'status' => StatusAtivo::DISPONIVEL,
            // 30% zerado (equipamento novo), o resto com uso variado
            'horimetro' => fake()->boolean(30) ? 0 : fake()->randomFloat(2, 20, 3000),
            'valor_diaria_referencia' => fake()->randomFloat(2, 80, 600),
        ];
    }

    public function emManutencao(): static
    {
        return $this->state(fn () => ['status' => StatusAtivo::EM_MANUTENCAO]);
    }
}
