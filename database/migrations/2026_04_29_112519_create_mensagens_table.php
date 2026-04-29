<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mensagens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patota_id')->constrained('patotas')->cascadeOnDelete();
            $table->foreignId('partida_id')->nullable()->constrained('partidas')->cascadeOnDelete();
            $table->foreignId('autor_id')->constrained('users')->cascadeOnDelete();
            $table->text('conteudo');
            $table->string('tipo', 20)->default('texto');
            $table->boolean('fixada')->default(false);
            $table->timestamps();

            $table->index('created_at');
            $table->index('fixada');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mensagens');
    }
};
