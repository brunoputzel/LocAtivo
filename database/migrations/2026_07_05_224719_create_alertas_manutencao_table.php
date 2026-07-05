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
        Schema::create('alertas_manutencao', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ativo_id')->constrained('ativos');
            $table->string('tipo');
            $table->text('descricao')->nullable();
            $table->date('data_alerta');
            $table->boolean('resolvido')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alertas_manutencao');
    }
};
