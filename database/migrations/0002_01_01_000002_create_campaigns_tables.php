<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('location');
            $table->decimal('radius_km', 8, 2)->nullable();
            $table->decimal('min_score', 5, 2)->default(0)->nullable();
            $table->integer('max_businesses')->nullable();
            $table->boolean('email_outreach_enabled')->default(false);
            $table->boolean('auto_analysis_enabled')->default(true);
            $table->string('status')->default('draft'); // draft | running | paused | completed | failed
            $table->text('parameters')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
        });

        Schema::create('campaign_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->string('provider'); // search_api | maps_api | directory | manual_urls | csv
            $table->text('configuration')->nullable();
            $table->integer('items_found')->default(0);
            $table->integer('items_imported')->default(0);
            $table->string('status')->default('pending');
            $table->text('error')->nullable();
            $table->timestamps();
        });

        Schema::create('discovery_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('source_id')->nullable()->constrained('campaign_sources')->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->integer('total')->default(0);
            $table->integer('processed')->default(0);
            $table->integer('succeeded')->default(0);
            $table->integer('failed')->default(0);
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discovery_batches');
        Schema::dropIfExists('campaign_sources');
        Schema::dropIfExists('campaigns');
    }
};