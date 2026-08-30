<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscoveryBatch extends Model
{
    protected $fillable = [
        'campaign_id',
        'source_id',
        'status',
        'total',
        'processed',
        'succeeded',
        'failed',
        'error',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'integer',
            'processed' => 'integer',
            'succeeded' => 'integer',
            'failed' => 'integer',
        ];
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function source()
    {
        return $this->belongsTo(CampaignSource::class);
    }
}