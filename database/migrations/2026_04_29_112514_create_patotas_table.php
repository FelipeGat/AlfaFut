<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('patotas', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('slug')->unique();
            $table->text('descricao')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('cidade')->nullable();
            $table->string('estado', 2)->nullable();
            $table->foreignId('criador_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedSmallInteger('jogadores_por_time')->default(5);
            $table->unsignedSmallInteger('quantidade_times')->default(2);
            $table->decimal('valor_mensalidade', 10, 2)->default(0);
            $table->boolean('publica')->default(false);
            $table->string('codigo_convite', 10)->unique();
            $table->timestamps();
            $table->softDeletes();

            $table->index('cidade');
            $table->index('publica');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patotas');
    }
};
