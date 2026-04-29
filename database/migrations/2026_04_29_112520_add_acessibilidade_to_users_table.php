<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('apelido')->nullable()->after('name');
            $table->string('telefone', 20)->nullable()->after('email');
            $table->string('avatar_url')->nullable()->after('telefone');
            $table->date('data_nascimento')->nullable()->after('avatar_url');
            $table->string('posicao_preferida', 30)->nullable();
            $table->string('nivel_habilidade', 20)->default('intermediario');
            $table->boolean('alto_contraste')->default(false);
            $table->string('tamanho_fonte', 20)->default('media');
            $table->boolean('reduzir_movimento')->default(false);
            $table->boolean('leitor_tela_otimizado')->default(false);
            $table->json('necessidades_acessibilidade')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'apelido', 'telefone', 'avatar_url', 'data_nascimento',
                'posicao_preferida', 'nivel_habilidade',
                'alto_contraste', 'tamanho_fonte', 'reduzir_movimento',
                'leitor_tela_otimizado', 'necessidades_acessibilidade',
            ]);
        });
    }
};
