<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteTemplate extends Model
{
    protected $table = 'website_templates';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'preview_image',
        'is_active',
        'category',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function websites()
    {
        return $this->hasMany(BusinessWebsite::class, 'template_id');
    }
}
