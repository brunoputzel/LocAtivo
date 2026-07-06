<?php

namespace Database\Seeders;

use App\Models\Ativo;
use Illuminate\Database\Seeder;

class AtivoSeeder extends Seeder
{
    public function run(): void
    {
        // maioria disponível, só uma minoria em manutenção - sobra ativo livre
        // pro ContratoSeeder alocar depois
        Ativo::factory()->count(15)->create();
        Ativo::factory()->count(3)->emManutencao()->create();
    }
}
