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
        Schema::table('ativos', function (Blueprint $table) {
            $table->decimal('valor_diaria_referencia', 10, 2)->nullable()->after('horimetro');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ativos', function (Blueprint $table) {
            $table->dropColumn('valor_diaria_referencia');
        });
    }
};
