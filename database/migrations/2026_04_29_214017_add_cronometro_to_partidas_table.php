<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('partidas', function (Blueprint $table) {
            // Cronometro
            $table->timestamp('iniciada_em')->nullable()->after('status');
            $table->timestamp('pausada_em')->nullable()->after('iniciada_em');
            $table->timestamp('finalizada_em')->nullable()->after('pausada_em');
            $table->unsignedInteger('tempo_acumulado_segundos')->default(0)->after('finalizada_em');

            // Placar oficial em tempo real (cache rapido)
            $table->unsignedTinyInteger('placar_a')->default(0)->after('tempo_acumulado_segundos');
            $table->unsignedTinyInteger('placar_b')->default(0)->after('placar_a');
        });
    }

    public function down(): void
    {
        Schema::table('partidas', function (Blueprint $table) {
            $table->dropColumn([
                'iniciada_em', 'pausada_em', 'finalizada_em',
                'tempo_acumulado_segundos', 'placar_a', 'placar_b',
            ]);
        });
    }
};
