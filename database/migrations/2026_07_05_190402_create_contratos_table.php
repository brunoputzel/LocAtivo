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
        Schema::create('contratos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ativo_id')->constrained('ativos');
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->date('data_inicio');
            $table->date('data_fim');
            $table->decimal('valor_diaria', 10, 2);
            $table->string('status')->default('ATIVO');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contratos');
    }
};
