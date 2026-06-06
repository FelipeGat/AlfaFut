<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dono_id')->constrained('users')->cascadeOnDelete();
            $table->string('nome', 120);
            $table->string('endereco', 200)->nullable();
            $table->string('cidade', 80)->nullable();
            $table->char('estado', 2)->nullable();
            $table->string('cep', 9)->nullable();
            $table->string('tipo_piso', 20)->nullable(); // grama_natural | grama_sintetica | saibro | quadra_coberta | futsal
            $table->boolean('coberto')->default(false);
            $table->boolean('possui_vestiario')->default(false);
            $table->boolean('possui_estacionamento')->default(false);
            $table->boolean('acessivel_cadeirante')->default(false);
            $table->decimal('valor_hora', 8, 2)->nullable();
            $table->string('contato_whatsapp', 20)->nullable();
            $table->text('descricao')->nullable();
            $table->string('foto_url', 255)->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->index(['cidade', 'estado']);
            $table->index('ativo');
        });

        // Partidas podem opcionalmente apontar para um campo do catalogo publico
        Schema::table('partidas', function (Blueprint $table) {
            $table->foreignId('campo_id')->nullable()->after('local_id')->constrained('campos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('partidas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('campo_id');
        });
        Schema::dropIfExists('campos');
    }
};
