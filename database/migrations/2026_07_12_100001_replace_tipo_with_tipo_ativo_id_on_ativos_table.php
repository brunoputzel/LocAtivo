<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ativos', function (Blueprint $table) {
            $table->foreignId('tipo_ativo_id')->nullable()->after('tipo')->constrained('tipo_ativos')->restrictOnDelete();
        });

        // migra os valores de texto livre existentes pra registros TipoAtivo antes de tirar a coluna antiga
        DB::table('ativos')->select('tipo')->whereNotNull('tipo')->distinct()->get()->each(function ($linha) {
            $nome = trim($linha->tipo);

            if ($nome === '') {
                return;
            }

            $tipoAtivoId = DB::table('tipo_ativos')->where('nome', $nome)->value('id')
                ?? DB::table('tipo_ativos')->insertGetId(['nome' => $nome, 'created_at' => now(), 'updated_at' => now()]);

            DB::table('ativos')->where('tipo', $linha->tipo)->update(['tipo_ativo_id' => $tipoAtivoId]);
        });

        Schema::table('ativos', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });
    }

    public function down(): void
    {
        Schema::table('ativos', function (Blueprint $table) {
            $table->string('tipo')->nullable()->after('numero_serie');
        });

        DB::table('ativos')->whereNotNull('tipo_ativo_id')->get()->each(function ($ativo) {
            $nome = DB::table('tipo_ativos')->where('id', $ativo->tipo_ativo_id)->value('nome');
            DB::table('ativos')->where('id', $ativo->id)->update(['tipo' => $nome]);
        });

        Schema::table('ativos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tipo_ativo_id');
        });
    }
};
