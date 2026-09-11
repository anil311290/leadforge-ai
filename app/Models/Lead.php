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

    public function whatsappOutreachMessage(): string
    {
        $industry = $this->industry ?: 'local business';
        $area = $this->city ?: $this->location ?: 'your area';
        $service = $this->recommended_service ?: $this->defaultOutreachService();
        $opening = $this->website
            ? "I came across {$this->company} online and noticed your work in the {$industry} space around {$area}."
            : "I found {$this->company} on Google for {$area} and noticed a website is not listed yet.";

        return implode("\n\n", [
            "Hi {$this->company},",
            $opening,
            $this->industryOutreachAngle(),
            "At APARK IT SOLUTIONS, we can help you with {$service} so customers can find you online, understand your services, and contact you easily.",
            'Would you be open to a quick 10-minute discussion?',
            "Best regards,\nAPARK IT SOLUTIONS",
        ]);
    }

    public function quotationMessage(?string $prompt = null): string
    {
        $service = $this->recommended_service ?: $this->defaultOutreachService();
        $industry = $this->industry ?: 'business';
        $area = $this->city ?: $this->location ?: 'your area';
        $min = $this->estimated_min ? '₹'.number_format((float) $this->estimated_min) : '₹25,000';
        $max = $this->estimated_max ? '₹'.number_format((float) $this->estimated_max) : '₹75,000';
        $customRequirement = trim((string) $prompt);

        $scope = "Scope includes:\n- Requirement discussion and planning\n- Professional UI/design setup\n- Website/software/app development as per selected scope\n- Contact/enquiry or WhatsApp integration\n- Basic testing and deployment support";
        if ($customRequirement !== '') {
            $scope .= "\n- Custom requirement: {$customRequirement}";
        }

        return implode("\n\n", [
            "Hi {$this->company},",
            "As discussed/reviewed, we are sharing an initial quotation from APARK IT SOLUTIONS for {$service} for your {$industry} business in {$area}.",
            $scope,
            "Estimated budget: {$min} - {$max}\nTimeline: 2-6 weeks, depending on final scope and content readiness.",
            'This is an initial estimate. Final quotation can be adjusted after understanding your exact requirements.',
            'Would you like us to schedule a quick call and finalize the scope?',
            "Best regards,\nAPARK IT SOLUTIONS",
        ]);
    }

    protected function defaultOutreachService(): string
    {
        $industry = strtolower((string) $this->industry);

        return match (true) {
            str_contains($industry, 'restaurant'), str_contains($industry, 'cafe'), str_contains($industry, 'hospitality') => 'a menu website, WhatsApp ordering flow, and customer enquiry system',
            str_contains($industry, 'clinic'), str_contains($industry, 'health') => 'a clinic website, appointment enquiry form, and patient follow-up system',
            str_contains($industry, 'real estate') => 'a property listing website, lead capture forms, and enquiry management system',
            str_contains($industry, 'education'), str_contains($industry, 'school'), str_contains($industry, 'training') => 'an admission enquiry website, course pages, and follow-up automation',
            str_contains($industry, 'fitness'), str_contains($industry, 'gym') => 'a fitness website, membership enquiry flow, and WhatsApp follow-up system',
            str_contains($industry, 'manufacturing') => 'a business website, product catalogue, and B2B enquiry system',
            str_contains($industry, 'travel') => 'a travel package website, booking enquiry flow, and lead follow-up system',
            str_contains($industry, 'salon'), str_contains($industry, 'beauty') => 'a salon website, appointment booking flow, and offer promotion system',
            default => 'a professional website, enquiry form, WhatsApp integration, and simple customer follow-up system',
        };
    }

    protected function industryOutreachAngle(): string
    {
        $industry = strtolower((string) $this->industry);

        return match (true) {
            str_contains($industry, 'retail') => 'For retail shops, a simple online catalogue with location, offers, photos, and WhatsApp enquiry can help customers check products before visiting.',
            str_contains($industry, 'restaurant'), str_contains($industry, 'cafe'), str_contains($industry, 'hospitality') => 'For food businesses, customers often search for menu, photos, timings, location, and ordering options before deciding where to visit or call.',
            str_contains($industry, 'clinic'), str_contains($industry, 'health') => 'For clinics, a clear website with doctor/service details, timings, appointment enquiry, and Google profile support can make patient enquiries easier.',
            str_contains($industry, 'real estate') => 'For real estate businesses, property pages, enquiry forms, WhatsApp buttons, and lead tracking can make follow-ups much easier.',
            str_contains($industry, 'education'), str_contains($industry, 'school'), str_contains($industry, 'training') => 'For education businesses, course/admission pages and enquiry follow-ups can help convert parents or students who search online.',
            str_contains($industry, 'fitness'), str_contains($industry, 'gym') => 'For gyms and fitness centres, a website with plans, photos, trial enquiry, and WhatsApp follow-up can help bring more local enquiries.',
            str_contains($industry, 'manufacturing') => 'For manufacturing businesses, a product catalogue website and enquiry system can help buyers understand capability before calling.',
            str_contains($industry, 'travel') => 'For travel businesses, package pages, enquiry forms, and WhatsApp follow-ups can help manage interested customers faster.',
            str_contains($industry, 'salon'), str_contains($industry, 'beauty') => 'For salons and beauty businesses, service pages, offers, gallery, and booking enquiries can help customers choose and contact quickly.',
            default => 'Many customers check Google first, so a clean website with services, location, photos, enquiry form, and WhatsApp contact can improve trust and follow-ups.',
        };
    }
}