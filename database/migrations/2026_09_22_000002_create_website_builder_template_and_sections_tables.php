<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('preview_image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('category')->nullable();
            $table->timestamps();
        });

        Schema::create('website_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained('business_websites')->cascadeOnDelete();
            $table->string('service_name');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('image')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index(['website_id', 'sort_order']);
        });

        Schema::create('website_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained('business_websites')->cascadeOnDelete();
            $table->string('product_name');
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->string('category')->nullable();
            $table->string('image')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index(['website_id', 'sort_order']);
        });

        Schema::create('website_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained('business_websites')->cascadeOnDelete();
            $table->string('type')->default('gallery');
            $table->string('path');
            $table->string('label')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index(['website_id', 'type']);
        });

        Schema::create('website_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained('business_websites')->cascadeOnDelete();
            $table->string('section_type');
            $table->string('title')->nullable();
            $table->text('content')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            $table->index(['website_id', 'section_type']);
        });

        Schema::create('website_seo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained('business_websites')->cascadeOnDelete();
            $table->string('seo_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('canonical_url')->nullable();
            $table->text('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->text('twitter_title')->nullable();
            $table->text('twitter_description')->nullable();
            $table->string('schema_type')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_seo');
        Schema::dropIfExists('website_sections');
        Schema::dropIfExists('website_media');
        Schema::dropIfExists('website_products');
        Schema::dropIfExists('website_services');
        Schema::dropIfExists('website_templates');
    }
};
