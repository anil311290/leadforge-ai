<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteSection extends Model
{
    protected $table = 'website_sections';

    protected $fillable = [
        'website_id',
        'section_type',
        'title',
        'content',
        'is_enabled',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    public function website()
    {
        return $this->belongsTo(BusinessWebsite::class, 'website_id');
    }
}
