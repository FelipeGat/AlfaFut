<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('despesas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patota_id')->constrained('patotas')->cascadeOnDelete();
            $table->foreignId('partida_id')->nullable()->constrained('partidas')->nullOnDelete();
            $table->foreignId('criada_por_id')->constrained('users');
            $table->string('descricao');
            $table->string('categoria', 30)->default('locacao');
            $table->decimal('valor_total', 10, 2);
            $table->date('data_despesa');
            $table->boolean('rateada')->default(true);
            $table->string('status', 20)->default('aberta');
            $table->timestamps();

            $table->index('data_despesa');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('despesas');
    }
};
