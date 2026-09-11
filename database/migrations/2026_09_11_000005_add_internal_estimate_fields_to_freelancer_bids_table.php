<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('freelancer_bids', function (Blueprint $table) {
            $table->decimal('internal_cost', 12, 2)->nullable()->after('bid_period_days');
            $table->unsignedInteger('internal_timeline_days')->nullable()->after('internal_cost');
        });
    }

    public function down(): void
    {
        Schema::table('freelancer_bids', function (Blueprint $table) {
            $table->dropColumn(['internal_cost', 'internal_timeline_days']);
        });
    }
};
