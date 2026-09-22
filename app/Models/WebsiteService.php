<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteService extends Model
{
    protected $table = 'website_services';

    protected $fillable = [
        'website_id',
        'service_name',
        'description',
        'icon',
        'image',
        'sort_order',
        'status',
    ];

    public function website()
    {
        return $this->belongsTo(BusinessWebsite::class, 'website_id');
    }
}
