<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->string('url');
            $table->string('domain')->nullable();
            $table->integer('http_status')->nullable();
            $table->boolean('https_enabled')->nullable();
            $table->string('title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('canonical')->nullable();
            $table->text('robots_txt')->nullable();
            $table->text('sitemap')->nullable();
            $table->decimal('response_time', 6, 2)->nullable();
            $table->integer('page_size_kb')->nullable();
            $table->integer('page_count')->default(0);
            $table->string('cms')->nullable();
            $table->string('ecommerce_platform')->nullable();
            $table->text('business_data')->nullable();
            $table->integer('data_quality')->default(0);
            $table->string('status')->default('pending'); // pending | scanning | completed | failed
            $table->text('error')->nullable();
            $table->text('statistics')->nullable();
            $table->timestamp('scanned_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('lead_id');
        });

        Schema::create('website_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scan_id')->constrained('website_scans')->cascadeOnDelete();
            $table->string('url');
            $table->string('path')->nullable();
            $table->integer('http_status')->nullable();
            $table->string('title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('text_content')->nullable();
            $table->text('links')->nullable();
            $table->string('page_type')->nullable();
            $table->integer('page_size_kb')->nullable();
            $table->timestamps();

            $table->index('scan_id');
            $table->index('path');
        });

        Schema::create('website_technologies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scan_id')->constrained('website_scans')->cascadeOnDelete();
            $table->string('name');
            $table->string('category'); // cms | ecommerce | frontend | analytics | payment | hosting | framework
            $table->string('version')->nullable();
            $table->string('confidence')->nullable();
            $table->text('evidence')->nullable();
            $table->timestamps();

            $table->index('scan_id');
            $table->index('name');
        });

        Schema::create('website_signals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scan_id')->constrained('website_scans')->cascadeOnDelete();
            $table->string('signal');
            $table->string('signal_type'); // evidence | inference | recommendation
            $table->string('category'); // usability | seo | mobile | business | automation | security
            $table->decimal('confidence', 5, 2)->default(0)->nullable();
            $table->text('detail')->nullable();
            $table->timestamps();

            $table->index(['scan_id', 'signal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_signals');
        Schema::dropIfExists('website_technologies');
        Schema::dropIfExists('website_pages');
        Schema::dropIfExists('website_scans');
    }
};