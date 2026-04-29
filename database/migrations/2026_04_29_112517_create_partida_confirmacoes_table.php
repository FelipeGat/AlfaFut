<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('partida_confirmacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partida_id')->constrained('partidas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 20)->default('confirmado');
            $table->boolean('em_lista_espera')->default(false);
            $table->unsignedSmallInteger('posicao_lista_espera')->nullable();
            $table->text('observacao')->nullable();
            $table->timestamp('confirmado_em')->useCurrent();
            $table->timestamps();

            $table->unique(['partida_id', 'user_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partida_confirmacoes');
    }
};
