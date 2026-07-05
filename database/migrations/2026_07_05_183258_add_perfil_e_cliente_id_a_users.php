<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // nullable no banco porque perfil é obrigatório na validação de criação,
            // não uma restrição de schema - evita default arbitrário entre os 5 perfis
            $table->string('perfil')->nullable()->after('email');

            // sem FK ainda: a tabela clientes só existe a partir da fase 2 (Cliente).
            // a constraint entra junto com a migration que cria clientes.
            $table->unsignedBigInteger('cliente_id')->nullable()->after('perfil');
            $table->index('cliente_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['cliente_id']);
            $table->dropColumn(['perfil', 'cliente_id']);
        });
    }
};
