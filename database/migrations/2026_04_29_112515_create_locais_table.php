<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('locais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patota_id')->nullable()->constrained('patotas')->nullOnDelete();
            $table->string('nome');
            $table->string('endereco')->nullable();
            $table->string('cidade')->nullable();
            $table->string('estado', 2)->nullable();
            $table->string('cep', 10)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('tipo_piso', 30)->nullable();
            $table->boolean('coberto')->default(false);
            $table->boolean('possui_vestiario')->default(false);
            $table->boolean('possui_estacionamento')->default(false);
            $table->boolean('acessivel_cadeirante')->default(false);
            $table->decimal('valor_locacao', 10, 2)->nullable();
            $table->string('contato')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locais');
    }
};
