<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('patotas', function (Blueprint $table) {
            $table->foreignId('responsavel_id')->nullable()->after('criador_id')->constrained('users')->nullOnDelete();
        });

        // Backfill: responsavel = criador para turmas existentes
        DB::statement('UPDATE patotas SET responsavel_id = criador_id WHERE responsavel_id IS NULL');
    }

    public function down(): void
    {
        Schema::table('patotas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('responsavel_id');
        });
    }
};
