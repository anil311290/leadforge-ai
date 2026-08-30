<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignSource extends Model
{
    protected $fillable = [
        'campaign_id',
        'provider',
        'configuration',
        'items_found',
        'items_imported',
        'status',
        'error',
    ];

    protected function casts(): array
    {
        return [
            'configuration' => 'array',
        ];
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function batches()
    {
        return $this->hasMany(DiscoveryBatch::class, 'source_id');
    }
}