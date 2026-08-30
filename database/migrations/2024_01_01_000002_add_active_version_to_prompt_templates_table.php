<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prompt_templates', function (Blueprint $table) {
            if (! Schema::hasColumn('prompt_templates', 'active_version_id')) {
                $table->foreignId('active_version_id')->nullable()->after('description')
                    ->constrained('prompt_versions')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('prompt_templates', function (Blueprint $table) {
            $table->dropConstrainedForeignId('active_version_id');
        });
    }
};