<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider'); // gmail | microsoft | smtp
            $table->string('email');
            $table->string('name')->nullable();
            $table->text('oauth_tokens')->nullable();
            $table->text('config')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->string('status')->default('connected');
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index('user_id');
        });

        Schema::create('email_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->nullable()->constrained();
            $table->foreignId('email_account_id')->nullable()->constrained('email_accounts');
            $table->string('name');
            $table->string('status')->default('draft'); // draft | active | paused | completed
            $table->boolean('auto_send')->default(false);
            $table->timestamps();
        });

        Schema::create('email_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained();
            $table->foreignId('email_campaign_id')->nullable()->constrained('email_campaigns');
            $table->foreignId('email_account_id')->nullable()->constrained('email_accounts');
            $table->string('direction')->default('outbound'); // outbound | inbound
            $table->string('subject')->nullable();
            $table->text('body')->nullable();
            $table->string('from_email')->nullable();
            $table->string('to_email')->nullable();
            $table->string('message_id')->nullable();
            $table->string('thread_id')->nullable();
            $table->string('status')->default('draft'); // draft | pending_approval | approved | sent | delivered | failed | replied
            $table->string('delivery_status')->nullable(); // sent | delivered | bounced | opened | replied
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['lead_id', 'direction']);
            $table->index('status');
            $table->index('thread_id');
        });

        Schema::create('follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('email_message_id')->nullable()->constrained();
            $table->integer('sequence_number')->default(1);
            $table->dateTime('scheduled_at')->nullable();
            $table->text('content')->nullable();
            $table->string('status')->default('pending'); // pending | sent | cancelled | failed
            $table->timestamp('sent_at')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index(['lead_id', 'status']);
            $table->index('scheduled_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follow_ups');
        Schema::dropIfExists('email_messages');
        Schema::dropIfExists('email_campaigns');
        Schema::dropIfExists('email_accounts');
    }
};