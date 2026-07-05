<?php

namespace Database\Seeders;

use App\Enums\PerfilUsuario;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // usuário padrão do sistema - sempre disponível após rodar o seeder,
        // pra permitir login imediato sem precisar cadastrar ninguém antes
        User::query()->updateOrCreate(
            ['email' => 'admin@locativo.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('locativo123'),
                'perfil' => PerfilUsuario::GESTOR,
                'ativo' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
