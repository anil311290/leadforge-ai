<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsitePage extends Model
{
    protected $fillable = [
        'scan_id',
        'url',
        'path',
        'http_status',
        'title',
        'meta_description',
        'text_content',
        'links',
        'page_type',
        'page_size_kb',
    ];

    protected function casts(): array
    {
        return [
            'http_status' => 'integer',
            'links' => 'array',
            'page_size_kb' => 'integer',
        ];
    }

    public function scan()
    {
        return $this->belongsTo(WebsiteScan::class);
    }
}