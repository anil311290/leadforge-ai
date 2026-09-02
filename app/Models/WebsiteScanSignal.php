<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteScanSignal extends Model
{
    protected $table = 'website_signals';

    protected $fillable = [
        'scan_id',
        'signal',
        'signal_type',
        'category',
        'confidence',
        'detail',
    ];

    protected function casts(): array
    {
        return [
            'confidence' => 'decimal:2',
        ];
    }

    public function scan()
    {
        return $this->belongsTo(WebsiteScan::class);
    }
}