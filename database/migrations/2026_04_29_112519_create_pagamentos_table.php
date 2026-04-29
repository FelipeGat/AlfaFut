<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pagamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('despesa_id')->constrained('despesas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('valor_devido', 10, 2);
            $table->decimal('valor_pago', 10, 2)->default(0);
            $table->date('data_vencimento')->nullable();
            $table->date('data_pagamento')->nullable();
            $table->string('forma_pagamento', 30)->nullable();
            $table->string('status', 20)->default('pendente');
            $table->text('observacao')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('data_vencimento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagamentos');
    }
};
