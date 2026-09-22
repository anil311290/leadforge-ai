<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteMedia extends Model
{
    protected $table = 'website_media';

    protected $fillable = [
        'website_id',
        'type',
        'path',
        'label',
        'sort_order',
        'status',
    ];

    public function website()
    {
        return $this->belongsTo(BusinessWebsite::class, 'website_id');
    }
}
