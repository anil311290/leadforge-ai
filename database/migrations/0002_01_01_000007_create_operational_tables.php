<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->foreignId('lead_id')->nullable()->constrained();
            $table->foreignId('campaign_id')->nullable()->constrained();
            $table->string('type'); // lead_discovered | analysis_completed | email_generated | email_sent | reply_received | follow_up_due | status_changed | note_added | meeting | proposal | won | lost
            $table->text('payload')->nullable();
            $table->string('title')->nullable();
            $table->timestamps();

            $table->index('lead_id');
            $table->index('user_id');
            $table->index('created_at');
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->foreignId('lead_id')->nullable()->constrained();
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('pending'); // pending | in_progress | completed | cancelled
            $table->string('task_type')->nullable();
            $table->dateTime('due_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['assigned_to', 'status']);
            $table->index('due_at');
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type')->nullable();
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('link')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_read']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->string('action');
            $table->string('entity');
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->text('before')->nullable();
            $table->text('after')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['entity', 'entity_id']);
            $table->index('user_id');
            $table->index('created_at');
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general');
            $table->boolean('is_encrypted')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('activities');
    }
};