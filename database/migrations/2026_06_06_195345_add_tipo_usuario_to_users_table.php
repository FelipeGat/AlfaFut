<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // jogador (default) | dono_pelada | dono_campo
            $table->string('tipo_usuario', 20)->default('jogador')->after('role');
            $table->index('tipo_usuario');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['tipo_usuario']);
            $table->dropColumn('tipo_usuario');
        });
    }
};
