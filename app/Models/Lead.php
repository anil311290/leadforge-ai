<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use SoftDeletes;

    const STATUS_NEW = 'NEW';
    const STATUS_DISCOVERED = 'DISCOVERED';
    const STATUS_ANALYZED = 'ANALYZED';
    const STATUS_QUALIFIED = 'QUALIFIED';
    const STATUS_CONTACTED = 'CONTACTED';
    const STATUS_REPLIED = 'REPLIED';
    const STATUS_INTERESTED = 'INTERESTED';
    const STATUS_MEETING = 'MEETING';
    const STATUS_PROPOSAL = 'PROPOSAL';
    const STATUS_NEGOTIATION = 'NEGOTIATION';
    const STATUS_WON = 'WON';
    const STATUS_LOST = 'LOST';
    const STATUS_NOT_INTERESTED = 'NOT_INTERESTED';
    const STATUS_DO_NOT_CONTACT = 'DO_NOT_CONTACT';

    public static array $statuses = [
        self::STATUS_NEW,
        self::STATUS_DISCOVERED,
        self::STATUS_ANALYZED,
        self::STATUS_QUALIFIED,
        self::STATUS_CONTACTED,
        self::STATUS_REPLIED,
        self::STATUS_INTERESTED,
        self::STATUS_MEETING,
        self::STATUS_PROPOSAL,
        self::STATUS_NEGOTIATION,
        self::STATUS_WON,
        self::STATUS_LOST,
        self::STATUS_NOT_INTERESTED,
        self::STATUS_DO_NOT_CONTACT,
    ];

    public static array $scoreClasses = ['HOT', 'HIGH', 'MEDIUM', 'LOW', 'IGNORE'];

    protected $fillable = [
        'campaign_id',
        'owner_id',
        'company',
        'normalized_company',
        'website',
        'normalized_domain',
        'industry',
        'sub_industry',
        'business_model',
        'business_type',
        'location',
        'city',
        'state',
        'country',
        'address',
        'phone',
        'email',
        'source',
        'opportunity_score',
        'score_class',
        'confidence',
        'digital_maturity',
        'estimated_min',
        'estimated_max',
        'data_quality',
        'recommended_service',
        'recommended_services',
        'status',
        'analysis',
        'notes',
        'rejection_reason',
        'next_action',
        'next_follow_up_at',
        'analyzed_at',
        'contacted_at',
    ];

    protected function casts(): array
    {
        return [
            'opportunity_score' => 'decimal:2',
            'confidence' => 'decimal:2',
            'digital_maturity' => 'integer',
            'estimated_min' => 'decimal:2',
            'estimated_max' => 'decimal:2',
            'data_quality' => 'decimal:2',
            'recommended_services' => 'array',
            'analysis' => 'array',
            'next_follow_up_at' => 'datetime',
            'analyzed_at' => 'datetime',
            'contacted_at' => 'datetime',
        ];
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function contacts()
    {
        return $this->hasMany(LeadContact::class);
    }

    public function opportunities()
    {
        return $this->hasMany(LeadOpportunity::class);
    }

    public function scans()
    {
        return $this->hasMany(WebsiteScan::class);
    }

    public function analyses()
    {
        return $this->hasMany(AiAnalysis::class);
    }

    public function recommendations()
    {
        return $this->hasMany(AiRecommendation::class);
    }

    public function emails()
    {
        return $this->hasMany(EmailMessage::class);
    }

    public function followUps()
    {
        return $this->hasMany(FollowUp::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    public function latestScan()
    {
        return $this->hasOne(WebsiteScan::class)->latestOfMany();
    }

    public function latestAnalysis()
    {
        return $this->hasOne(AiAnalysis::class)->latestOfMany();
    }

    public function scopeHot($query)
    {
        return $query->whereIn('score_class', ['HOT', 'HIGH']);
    }

    public function scopeOpen($query)
    {
        $open = [
            self::STATUS_NEW,
            self::STATUS_DISCOVERED,
            self::STATUS_ANALYZED,
            self::STATUS_QUALIFIED,
            self::STATUS_CONTACTED,
            self::STATUS_REPLIED,
            self::STATUS_INTERESTED,
            self::STATUS_MEETING,
            self::STATUS_PROPOSAL,
            self::STATUS_NEGOTIATION,
        ];

        return $query->whereIn('status', $open);
    }

    public function markScoreClass(): void
    {
        $score = $this->opportunity_score;

        $this->score_class = match (true) {
            $score >= 90 => 'HOT',
            $score >= 75 => 'HIGH',
            $score >= 60 => 'MEDIUM',
            $score >= 40 => 'LOW',
            default => 'IGNORE',
        };
    }
}