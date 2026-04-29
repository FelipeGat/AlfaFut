<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('time_jogadores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('time_id')->constrained('times')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('posicao', 30)->nullable();
            $table->unsignedTinyInteger('gols')->default(0);
            $table->unsignedTinyInteger('assistencias')->default(0);
            $table->timestamps();

            $table->unique(['time_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_jogadores');
    }
};
