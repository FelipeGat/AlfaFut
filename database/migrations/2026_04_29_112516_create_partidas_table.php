<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('partidas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patota_id')->constrained('patotas')->cascadeOnDelete();
            $table->foreignId('local_id')->nullable()->constrained('locais')->nullOnDelete();
            $table->foreignId('organizador_id')->constrained('users');
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->dateTime('data_hora');
            $table->unsignedSmallInteger('duracao_minutos')->default(90);
            $table->unsignedSmallInteger('vagas_total');
            $table->decimal('valor_individual', 10, 2)->default(0);
            $table->string('status', 20)->default('agendada');
            $table->dateTime('confirmacao_ate')->nullable();
            $table->boolean('lista_espera_habilitada')->default(true);
            $table->timestamps();

            $table->index('data_hora');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partidas');
    }
};
