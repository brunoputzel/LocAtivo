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
        Schema::create('ordens_servico', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ativo_id')->constrained('ativos');
            $table->foreignId('tecnico_id')->constrained('users');
            $table->foreignId('alerta_id')->nullable()->constrained('alertas_manutencao');
            $table->string('tipo');
            $table->text('descricao')->nullable();
            $table->date('data_abertura');
            $table->date('data_fechamento')->nullable();
            $table->string('status')->default('aberta');
            $table->decimal('custo', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordens_servico');
    }
};
