<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('eventos_partida', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partida_id')->constrained('partidas')->cascadeOnDelete();
            $table->foreignId('time_id')->nullable()->constrained('times')->nullOnDelete();
            $table->foreignId('jogador_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assistencia_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('tipo', 30); // inicio, gol, gol_contra, falta, cartao, pausa, retomada, fim
            $table->unsignedSmallInteger('minuto')->default(0);
            $table->text('observacao')->nullable();
            $table->foreignId('registrado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['partida_id', 'created_at']);
            $table->index('tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eventos_partida');
    }
};
