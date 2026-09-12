<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class FreelancerAccount extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'freelancer_username',
        'oauth_token',
        'api_url',
        'is_active',
        'auto_submit_bids',
        'max_bids_per_day',
        'budget_min',
        'budget_min_currency',
        'include_keywords',
        'exclude_keywords',
        'exclude_countries',
        'bid_amount_default',
        'bid_period_days_default',
        'proposal_use_ai',
        'profile_title',
        'profile_summary',
        'experience_years',
        'proposal_style',
        'portfolio_url',
        'portfolio_projects',
        'status',
        'last_error',
        'last_scanned_at',
    ];

    protected $hidden = [
        'oauth_token',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'auto_submit_bids' => 'boolean',
            'proposal_use_ai' => 'boolean',
            'experience_years' => 'integer',
            'include_keywords' => 'array',
            'exclude_keywords' => 'array',
            'exclude_countries' => 'array',
            'portfolio_projects' => 'array',
            'last_scanned_at' => 'datetime',
        ];
    }

    // OAuth token is stored encrypted at rest; never returned in plain text via arrays/JSON.
    protected function oauthToken(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: function (?string $value) {
                if (! $value) {
                    return null;
                }

                try {
                    return Crypt::decryptString($value);
                } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                    // Happens when APP_KEY changed since this token was saved (e.g. moved between environments).
                    throw new \RuntimeException("This account's OAuth token could not be decrypted (the app's encryption key may have changed). Please re-enter the token in Freelancer Accounts.");
                }
            },
            set: fn (?string $value) => $value ? Crypt::encryptString($value) : null,
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bids()
    {
        return $this->hasMany(FreelancerBid::class);
    }

    public function todayBidCount(): int
    {
        return $this->bids()
            ->whereIn('status', ['submitted', 'pending'])
            ->whereDate('created_at', now()->toDateString())
            ->count();
    }

    /**
     * Parse the "Title | URL | tags,comma,separated | Description | Demo admin URL | Demo credentials"
     * textarea format used on the account form into structured portfolio project rows.
     * Demo admin URL/credentials are optional and should only ever be non-production demo logins.
     */
    public static function parsePortfolioProjects(string $text): array
    {
        $projects = [];

        foreach (preg_split('/\r\n|\r|\n/', trim($text)) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $parts = array_map('trim', explode('|', $line));
            $title = $parts[0] ?? '';
            $url = $parts[1] ?? '';
            if ($title === '' || $url === '') {
                continue;
            }

            $tags = isset($parts[2]) ? array_values(array_filter(array_map('trim', explode(',', $parts[2])))) : [];

            $projects[] = [
                'title' => $title,
                'url' => $url,
                'tags' => $tags,
                'description' => $parts[3] ?? '',
                'demo_admin_url' => $parts[4] ?? '',
                'demo_credentials' => $parts[5] ?? '',
            ];
        }

        return $projects;
    }

    /**
     * Render structured portfolio project rows back into the editable textarea format.
     */
    public static function formatPortfolioProjects(?array $projects): string
    {
        return collect($projects ?? [])->map(fn (array $p) => sprintf(
            '%s | %s | %s | %s | %s | %s',
            $p['title'] ?? '',
            $p['url'] ?? '',
            implode(',', $p['tags'] ?? []),
            $p['description'] ?? '',
            $p['demo_admin_url'] ?? '',
            $p['demo_credentials'] ?? '',
        ))->implode("\n");
    }
}
