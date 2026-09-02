<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WebsiteScan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'lead_id',
        'url',
        'domain',
        'http_status',
        'https_enabled',
        'title',
        'meta_description',
        'canonical',
        'robots_txt',
        'sitemap',
        'response_time',
        'page_size_kb',
        'page_count',
        'cms',
        'ecommerce_platform',
        'business_data',
        'data_quality',
        'status',
        'error',
        'statistics',
        'scanned_at',
    ];

    protected function casts(): array
    {
        return [
            'http_status' => 'integer',
            'https_enabled' => 'boolean',
            'response_time' => 'decimal:2',
            'page_size_kb' => 'integer',
            'page_count' => 'integer',
            'data_quality' => 'integer',
            'business_data' => 'array',
            'statistics' => 'array',
            'scanned_at' => 'datetime',
        ];
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function pages()
    {
        return $this->hasMany(WebsitePage::class, 'scan_id');
    }

    public function technologies()
    {
        return $this->hasMany(WebsiteTechnology::class, 'scan_id');
    }

    public function signals()
    {
        return $this->hasMany(WebsiteScanSignal::class, 'scan_id');
    }

    public function analyses()
    {
        return $this->hasMany(AiAnalysis::class, 'scan_id');
    }
}