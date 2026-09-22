<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteProduct extends Model
{
    protected $table = 'website_products';

    protected $fillable = [
        'website_id',
        'product_name',
        'description',
        'price',
        'category',
        'image',
        'sort_order',
        'status',
    ];

    public function website()
    {
        return $this->belongsTo(BusinessWebsite::class, 'website_id');
    }
}
