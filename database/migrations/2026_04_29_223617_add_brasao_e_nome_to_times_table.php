<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('times', function (Blueprint $table) {
            $table->string('brasao', 60)->nullable()->after('cor');
            $table->string('clube_codigo', 30)->nullable()->after('brasao');
        });
    }

    public function down(): void
    {
        Schema::table('times', function (Blueprint $table) {
            $table->dropColumn(['brasao', 'clube_codigo']);
        });
    }
};
