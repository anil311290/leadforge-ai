<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('freelancer_accounts', function (Blueprint $table) {
            // Structured list of past-work projects: [{title, url, tags[], description}, ...]
            // Only ever store public URLs here — never admin links or credentials.
            $table->json('portfolio_projects')->nullable()->after('portfolio_url');
        });
    }

    public function down(): void
    {
        Schema::table('freelancer_accounts', function (Blueprint $table) {
            $table->dropColumn('portfolio_projects');
        });
    }
};
