<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('freelancer_accounts', function (Blueprint $table) {
            // Null = fall back to the global Freelancer Settings portfolio URL.
            $table->string('portfolio_url')->nullable()->after('profile_summary');
        });
    }

    public function down(): void
    {
        Schema::table('freelancer_accounts', function (Blueprint $table) {
            $table->dropColumn('portfolio_url');
        });
    }
};
