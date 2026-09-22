<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessWebsite extends Model
{
    use SoftDeletes;

    protected $table = 'business_websites';

    protected $fillable = [
        'lead_id',
        'created_by',
        'business_category_id',
        'template_id',
        'business_name',
        'slug',
        'business_description',
        'owner_name',
        'phone',
        'alternate_phone',
        'whatsapp',
        'email',
        'address',
        'city',
        'state',
        'country',
        'pincode',
        'google_maps_url',
        'latitude',
        'longitude',
        'business_hours',
        'facebook_url',
        'instagram_url',
        'linkedin_url',
        'youtube_url',
        'logo',
        'favicon',
        'primary_color',
        'secondary_color',
        'accent_color',
        'status',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function template()
    {
        return $this->belongsTo(WebsiteTemplate::class, 'template_id');
    }

    public function services()
    {
        return $this->hasMany(WebsiteService::class, 'website_id')->orderBy('sort_order');
    }

    public function products()
    {
        return $this->hasMany(WebsiteProduct::class, 'website_id')->orderBy('sort_order');
    }

    public function media()
    {
        return $this->hasMany(WebsiteMedia::class, 'website_id')->orderBy('sort_order');
    }

    public function sections()
    {
        return $this->hasMany(WebsiteSection::class, 'website_id');
    }

    public function getPublicUrlAttribute(): string
    {
        $baseUrl = trim((string) config('app.website_base_url', env('APP_URL', 'http://localhost')));

        return rtrim($baseUrl, '/').'/'.$this->slug;
    }

    public static function generateUniqueSlug(string $businessName): string
    {
        $base = strtolower(trim((string) $businessName));
        $base = preg_replace('/[^a-z0-9\s-]+/', '', $base) ?? '';
        $base = preg_replace('/\s+/', '-', trim($base));
        $base = preg_replace('/-+/', '-', $base);
        $base = trim($base, '-');

        $slug = $base !== '' ? $base : 'business';
        $original = $slug;
        $counter = 2;

        while (static::query()->where('slug', $slug)->exists()) {
            $slug = $original.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
