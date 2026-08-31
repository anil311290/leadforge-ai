<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'location',
        'radius_km',
        'min_score',
        'max_businesses',
        'email_outreach_enabled',
        'auto_analysis_enabled',
        'status',
        'progress',
        'progress_message',
        'parameters',
        'error',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'radius_km' => 'decimal:2',
            'min_score' => 'decimal:2',
            'email_outreach_enabled' => 'boolean',
            'auto_analysis_enabled' => 'boolean',
            'parameters' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sources()
    {
        return $this->hasMany(CampaignSource::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['running', 'paused']);
    }
}