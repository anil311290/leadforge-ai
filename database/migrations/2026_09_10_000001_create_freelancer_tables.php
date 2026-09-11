<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('freelancer_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name'); // label to tell accounts apart, e.g. "Main Account"
            $table->string('freelancer_username')->nullable();
            $table->text('oauth_token'); // encrypted Freelancer.com OAuth token (per-account key)
            $table->string('api_url')->default('https://www.freelancer.com');
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_submit_bids')->default(false); // false = draft for manual approval
            $table->unsignedInteger('max_bids_per_day')->default(10);
            $table->unsignedInteger('budget_min')->default(0);
            $table->string('budget_min_currency', 8)->default('USD');
            $table->json('include_keywords')->nullable();
            $table->json('exclude_keywords')->nullable();
            $table->json('exclude_countries')->nullable();
            $table->decimal('bid_amount_default', 10, 2)->default(0);
            $table->unsignedInteger('bid_period_days_default')->default(5);
            $table->string('status')->default('pending'); // pending | connected | error
            $table->text('last_error')->nullable();
            $table->timestamp('last_scanned_at')->nullable();
            $table->timestamps();

            $table->index(['is_active']);
        });

        Schema::create('freelancer_bids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('freelancer_account_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('project_id');
            $table->string('project_title')->nullable();
            $table->string('project_url')->nullable();
            $table->decimal('budget_min', 12, 2)->nullable();
            $table->decimal('budget_max', 12, 2)->nullable();
            $table->string('currency_code', 8)->nullable();
            $table->string('currency_sign', 8)->nullable();
            $table->string('client_country', 8)->nullable();
            $table->decimal('bid_amount', 10, 2)->nullable();
            $table->unsignedInteger('bid_period_days')->nullable();
            $table->text('proposal_text')->nullable();
            $table->string('status')->default('pending'); // pending | submitted | failed | skipped | awarded | rejected
            $table->text('error_message')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['freelancer_account_id', 'project_id']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freelancer_bids');
        Schema::dropIfExists('freelancer_accounts');
    }
};
