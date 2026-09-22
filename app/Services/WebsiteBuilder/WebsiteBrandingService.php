<?php

namespace App\Services\WebsiteBuilder;

use App\Models\BusinessWebsite;

class WebsiteBrandingService
{
    private const PALETTES = [
        ['primary' => '#1769aa', 'secondary' => '#102a43', 'accent' => '#f0a202'],
        ['primary' => '#7c3aed', 'secondary' => '#24143d', 'accent' => '#f59e0b'],
        ['primary' => '#0f766e', 'secondary' => '#123b3a', 'accent' => '#f97316'],
        ['primary' => '#be123c', 'secondary' => '#3f1728', 'accent' => '#f59e0b'],
        ['primary' => '#2563eb', 'secondary' => '#172554', 'accent' => '#14b8a6'],
        ['primary' => '#4d7c0f', 'secondary' => '#1a2e05', 'accent' => '#eab308'],
        ['primary' => '#c2410c', 'secondary' => '#431407', 'accent' => '#06b6d4'],
        ['primary' => '#4338ca', 'secondary' => '#1e1b4b', 'accent' => '#fb7185'],
    ];

    public function forWebsite(BusinessWebsite $website): array
    {
        $palette = self::PALETTES[abs(crc32(strtolower(trim($website->business_name)))) % count(self::PALETTES)];

        return [
            'primary' => $website->primary_color ?: $palette['primary'],
            'secondary' => $website->secondary_color ?: $palette['secondary'],
            'accent' => $website->accent_color ?: $palette['accent'],
        ];
    }

    public function applyDefaults(BusinessWebsite $website): void
    {
        $palette = $this->forWebsite($website);
        $website->fill([
            'primary_color' => $website->primary_color ?: $palette['primary'],
            'secondary_color' => $website->secondary_color ?: $palette['secondary'],
            'accent_color' => $website->accent_color ?: $palette['accent'],
        ]);
    }
}
