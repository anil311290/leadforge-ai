<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FreelancerBid extends Model
{
    protected $fillable = [
        'freelancer_account_id',
        'project_id',
        'project_title',
        'project_url',
        'budget_min',
        'budget_max',
        'currency_code',
        'currency_sign',
        'client_country',
        'bid_amount',
        'bid_period_days',
        'proposal_text',
        'status',
        'error_message',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
        ];
    }

    public function account()
    {
        return $this->belongsTo(FreelancerAccount::class, 'freelancer_account_id');
    }
}
