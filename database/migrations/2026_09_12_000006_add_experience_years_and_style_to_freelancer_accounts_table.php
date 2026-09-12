<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('freelancer_accounts', function (Blueprint $table) {
            $table->unsignedInteger('experience_years')->default(5)->after('profile_summary');
            $table->string('proposal_style', 50)->default('direct')->after('experience_years');
        });
    }

    public function down(): void
    {
        Schema::table('freelancer_accounts', function (Blueprint $table) {
            $table->dropColumn(['experience_years', 'proposal_style']);
        });
    }
};
