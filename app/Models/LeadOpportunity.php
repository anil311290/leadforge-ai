<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadOpportunity extends Model
{
    protected $fillable = [
        'lead_id',
        'service_id',
        'service_name',
        'score',
        'confidence',
        'evidence',
        'inference',
        'recommendation',
        'estimated_min',
        'estimated_max',
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

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}