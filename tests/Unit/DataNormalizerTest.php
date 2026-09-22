<?php

namespace Tests\Unit;

use App\Services\Discovery\DataNormalizer;
use PHPUnit\Framework\TestCase;

class DataNormalizerTest extends TestCase
{
    public function test_whatsapp_number_uses_lead_country(): void
    {
        $this->assertSame('919876543210', DataNormalizer::normalizeWhatsappPhone('9876543210', 'India'));
        $this->assertSame('14155552671', DataNormalizer::normalizeWhatsappPhone('(415) 555-2671', 'United States'));
        $this->assertSame('447911123456', DataNormalizer::normalizeWhatsappPhone('07911 123456', 'United Kingdom'));
    }

    public function test_existing_international_prefix_is_preserved(): void
    {
        $this->assertSame('447911123456', DataNormalizer::normalizeWhatsappPhone('+44 7911 123456'));
        $this->assertSame('14155552671', DataNormalizer::normalizeWhatsappPhone('001 415 555 2671'));
    }

    public function test_unknown_country_does_not_invent_indian_prefix(): void
    {
        $this->assertSame('1234567890', DataNormalizer::normalizeWhatsappPhone('1234567890', 'Brazil'));
    }
}
