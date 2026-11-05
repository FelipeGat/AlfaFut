<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimentos_financeiros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patota_id')->constrained('patotas')->cascadeOnDelete();
            $table->foreignId('despesa_id')->nullable()->constrained('despesas')->nullOnDelete();
            $table->foreignId('pagamento_id')->nullable()->constrained('pagamentos')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('acao', 30);
            $table->string('descricao');
            $table->unsignedBigInteger('valor_centavos')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->index(['despesa_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimentos_financeiros');
    }
};
