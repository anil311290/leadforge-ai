<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('freelancer_accounts', function (Blueprint $table) {
            // Null = fall back to the global Freelancer Settings defaults.
            $table->boolean('proposal_use_ai')->nullable()->after('bid_period_days_default');
            $table->string('profile_title')->nullable()->after('proposal_use_ai');
            $table->text('profile_summary')->nullable()->after('profile_title');
        });
    }

    public function down(): void
    {
        Schema::table('freelancer_accounts', function (Blueprint $table) {
            $table->dropColumn(['proposal_use_ai', 'profile_title', 'profile_summary']);
        });
    }
};
