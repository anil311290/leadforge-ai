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
        'internal_cost',
        'internal_timeline_days',
        'proposal_text',
        'status',
        'error_message',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'internal_cost' => 'float',
        ];
    }

    public function account()
    {
        return $this->belongsTo(FreelancerAccount::class, 'freelancer_account_id');
    }

    public function getProposalTextAttribute($value): string
    {
        if (! $value) {
            return '';
        }

        return html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public function getProjectTitleAttribute($value): ?string
    {
        if (! $value) {
            return null;
        }

        return html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public function getErrorMessageAttribute($value): ?string
    {
        if (! $value) {
            return null;
        }

        return html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public function getInternalCostAttribute($value): ?float
    {
        if ($value !== null) {
            return (float) $value;
        }

        $ceiling = max((float) ($this->budget_max ?? 0), (float) ($this->budget_min ?? 0), (float) ($this->bid_amount ?? 0));

        return $ceiling > 0 ? (float) (max(1, (int) ceil(max($ceiling * 1.2, ((float) $this->bid_amount) * 1.25) / 5)) * 5) : null;
    }

    public function getInternalTimelineDaysAttribute($value): ?int
    {
        if ($value !== null) {
            return (int) $value;
        }

        return $this->bid_period_days ? max(10, (int) $this->bid_period_days + 5) : null;
    }
}
