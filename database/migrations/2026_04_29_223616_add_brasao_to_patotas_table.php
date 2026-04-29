<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('patotas', function (Blueprint $table) {
            $table->string('brasao', 60)->nullable()->after('logo_url');
        });
    }

    public function down(): void
    {
        Schema::table('patotas', function (Blueprint $table) {
            $table->dropColumn('brasao');
        });
    }
};
