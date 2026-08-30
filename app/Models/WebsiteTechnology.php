<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteTechnology extends Model
{
    protected $fillable = [
        'scan_id',
        'name',
        'category',
        'version',
        'confidence',
        'evidence',
    ];

    public function scan()
    {
        return $this->belongsTo(WebsiteScan::class);
    }
}