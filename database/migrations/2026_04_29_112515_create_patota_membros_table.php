<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('patota_membros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patota_id')->constrained('patotas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('papel', 20)->default('membro');
            $table->string('status', 20)->default('ativo');
            $table->timestamp('entrou_em')->useCurrent();
            $table->timestamp('saiu_em')->nullable();
            $table->timestamps();

            $table->unique(['patota_id', 'user_id']);
            $table->index('papel');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patota_membros');
    }
};
