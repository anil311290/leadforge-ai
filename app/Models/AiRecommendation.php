<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiRecommendation extends Model
{
    protected $fillable = [
        'lead_id',
        'ai_analysis_id',
        'service_id',
        'service_name',
        'score',
        'confidence',
        'evidence',
        'inference',
        'recommendation',
        'estimated_min',
        'estimated_max',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'confidence' => 'decimal:2',
            'evidence' => 'array',
            'inference' => 'array',
            'estimated_min' => 'decimal:2',
            'estimated_max' => 'decimal:2',
        ];
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function analysis()
    {
        return $this->belongsTo(AiAnalysis::class, 'ai_analysis_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}