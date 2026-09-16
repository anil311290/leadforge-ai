<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('freelancer_accounts', function (Blueprint $table) {
            $table->unsignedInteger('max_project_bids')->default(20)->after('max_bids_per_day');
            $table->unsignedInteger('max_project_age_hours')->default(24)->after('max_project_bids');
        });
    }

    public function down(): void
    {
        Schema::table('freelancer_accounts', function (Blueprint $table) {
            $table->dropColumn(['max_project_bids', 'max_project_age_hours']);
        });
    }
};