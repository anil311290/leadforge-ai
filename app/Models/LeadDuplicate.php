<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadDuplicate extends Model
{
    protected $fillable = [
        'lead_id',
        'duplicate_of_lead_id',
        'merged_into_lead_id',
        'matched_on',
        'similarity',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'similarity' => 'decimal:2',
        ];
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function duplicateOf()
    {
        return $this->belongsTo(Lead::class, 'duplicate_of_lead_id');
    }
}