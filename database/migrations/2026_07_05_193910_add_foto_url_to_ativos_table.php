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
            $table->string('foto_url')->nullable()->after('numero_serie');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ativos', function (Blueprint $table) {
            $table->dropColumn('foto_url');
        });
    }
};
