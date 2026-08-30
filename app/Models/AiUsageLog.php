<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiUsageLog extends Model
{
    protected $fillable = [
        'user_id',
        'lead_id',
        'campaign_id',
        'provider',
        'model',
        'prompt_name',
        'prompt_version',
        'operation',
        'input_tokens',
        'output_tokens',
        'cost',
        'duration_ms',
        'status',
        'cached',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'input_tokens' => 'integer',
            'output_tokens' => 'integer',
            'cost' => 'decimal:6',
            'duration_ms' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
}