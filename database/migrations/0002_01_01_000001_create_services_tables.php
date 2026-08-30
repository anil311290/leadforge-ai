<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->decimal('min_value', 12, 2)->default(0);
            $table->decimal('max_value', 12, 2)->default(0);
            $table->string('currency', 3)->default('INR');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('service_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // positive_signal | negative_signal | required_signal
            $table->string('signal')->nullable();
            $table->string('keyword')->nullable();
            $table->string('weight', 10)->nullable();
            $table->timestamps();
        });

        Schema::create('service_case_studies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('summary')->nullable();
            $table->string('client')->nullable();
            $table->string('industry')->nullable();
            $table->string('outcome')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_case_studies');
        Schema::dropIfExists('service_rules');
        Schema::dropIfExists('services');
    }
};