<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->nullable()->constrained();
            $table->foreignId('owner_id')->nullable()->constrained('users');
            $table->string('company');
            $table->string('normalized_company')->nullable();
            $table->string('website')->nullable();
            $table->string('normalized_domain')->nullable();
            $table->string('industry')->nullable();
            $table->string('sub_industry')->nullable();
            $table->string('business_model')->nullable();
            $table->string('business_type')->nullable();
            $table->string('location')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('source')->nullable();
            $table->decimal('opportunity_score', 5, 2)->default(0)->nullable();
            $table->string('score_class')->nullable();
            $table->decimal('confidence', 5, 2)->default(0)->nullable();
            $table->integer('digital_maturity')->default(0)->nullable();
            $table->decimal('estimated_min', 12, 2)->default(0)->nullable();
            $table->decimal('estimated_max', 12, 2)->default(0)->nullable();
            $table->decimal('data_quality', 5, 2)->default(0)->nullable();
            $table->string('recommended_service')->nullable();
            $table->text('recommended_services')->nullable();
            $table->string('status')->default('NEW');
            $table->text('analysis')->nullable();
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->string('next_action')->nullable();
            $table->timestamp('next_follow_up_at')->nullable();
            $table->timestamp('analyzed_at')->nullable();
            $table->timestamp('contacted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('owner_id');
            $table->index('score_class');
            $table->index('campaign_id');
            $table->index('city');
            $table->index('industry');
            $table->index('normalized_domain');
            $table->index('normalized_company');
        });

        Schema::create('lead_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('role')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        Schema::create('lead_duplicates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('duplicate_of_lead_id')->constrained('leads')->nullable();
            $table->foreignId('merged_into_lead_id')->constrained('leads')->nullable();
            $table->string('matched_on');
            $table->decimal('similarity', 5, 2)->default(0)->nullable();
            $table->string('status')->default('open');
            $table->timestamps();
        });

        Schema::create('lead_opportunities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('service_name')->nullable();
            $table->decimal('score', 5, 2)->default(0);
            $table->decimal('confidence', 5, 2)->default(0)->nullable();
            $table->text('evidence')->nullable();
            $table->text('inference')->nullable();
            $table->text('recommendation')->nullable();
            $table->decimal('estimated_min', 12, 2)->default(0)->nullable();
            $table->decimal('estimated_max', 12, 2)->default(0)->nullable();
            $table->timestamps();

            $table->index(['lead_id', 'score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_opportunities');
        Schema::dropIfExists('lead_duplicates');
        Schema::dropIfExists('lead_contacts');
        Schema::dropIfExists('leads');
    }
};