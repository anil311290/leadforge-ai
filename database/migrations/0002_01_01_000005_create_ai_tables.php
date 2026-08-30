<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prompt_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('prompt_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prompt_template_id')->constrained()->cascadeOnDelete();
            $table->integer('version')->default(1);
            $table->text('content');
            $table->boolean('is_active')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('prompt_template_id');
        });

        Schema::create('ai_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('scan_id')->nullable()->constrained('website_scans');
            $table->foreignId('prompt_version_id')->nullable()->constrained('prompt_versions');
            $table->string('model')->nullable();
            $table->string('provider')->nullable();
            $table->string('content_hash', 64)->nullable();
            $table->text('input')->nullable();
            $table->text('output')->nullable();
            $table->decimal('score', 5, 2)->default(0)->nullable();
            $table->decimal('confidence', 5, 2)->default(0)->nullable();
            $table->integer('input_tokens')->default(0);
            $table->integer('output_tokens')->default(0);
            $table->decimal('cost', 10, 4)->default(0)->nullable();
            $table->integer('duration_ms')->nullable();
            $table->string('status')->default('pending'); // pending | completed | failed
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index(['scan_id', 'content_hash']);
        });

        Schema::create('ai_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ai_analysis_id')->nullable()->constrained('ai_analyses');
            $table->foreignId('service_id')->nullable()->constrained();
            $table->string('service_name')->nullable();
            $table->decimal('score', 5, 2)->default(0);
            $table->decimal('confidence', 5, 2)->default(0)->nullable();
            $table->text('evidence')->nullable();
            $table->text('inference')->nullable();
            $table->text('recommendation')->nullable();
            $table->decimal('estimated_min', 12, 2)->default(0)->nullable();
            $table->decimal('estimated_max', 12, 2)->default(0)->nullable();
            $table->string('status')->default('pending'); // pending | approved | rejected
            $table->timestamps();
        });

        Schema::create('ai_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->foreignId('lead_id')->nullable()->constrained();
            $table->foreignId('campaign_id')->nullable()->constrained();
            $table->string('provider')->nullable();
            $table->string('model');
            $table->string('prompt_name')->nullable();
            $table->integer('prompt_version')->nullable();
            $table->string('operation')->nullable();
            $table->integer('input_tokens')->default(0);
            $table->integer('output_tokens')->default(0);
            $table->decimal('cost', 12, 6)->default(0)->nullable();
            $table->integer('duration_ms')->nullable();
            $table->string('status')->default('completed');
            $table->string('cached')->default('no'); // yes | no
            $table->timestamps();

            $table->index('user_id');
            $table->index('created_at');
            $table->index(['provider', 'model']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_usage_logs');
        Schema::dropIfExists('ai_recommendations');
        Schema::dropIfExists('ai_analyses');
        Schema::dropIfExists('prompt_versions');
        Schema::dropIfExists('prompt_templates');
    }
};