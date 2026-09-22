<?php

namespace App\Services\WebsiteBuilder;

use App\Models\BusinessWebsite;
use App\Services\Ai\AiClient;

class AiWebsiteContentService
{
    public function __construct(protected AiClient $aiClient)
    {
    }

    public function generateForWebsite(BusinessWebsite $website): array
    {
        $businessName = trim((string) ($website->business_name ?: 'Business Website'));
        $city = trim((string) ($website->city ?: 'your city'));
        $context = trim(implode(' ', array_filter([
            $website->business_name,
            $website->business_description,
            $website->city,
            $website->state,
        ])));
        $isMedicalShop = preg_match('/medical|pharmacy|chemist|medicine|drug store|health store/i', $context) === 1;

        $defaultProducts = $isMedicalShop ? [
            ['product_name' => 'Prescription Medicines', 'description' => 'Common prescription medicines sourced with care and availability support.'],
            ['product_name' => 'Over-the-Counter Medicines', 'description' => 'Everyday healthcare essentials for common needs.'],
            ['product_name' => 'Vitamins and Wellness', 'description' => 'Vitamins, supplements and wellness products for your routine.'],
            ['product_name' => 'Personal and Baby Care', 'description' => 'Practical personal care and baby care essentials.'],
        ] : [];

        $defaultServices = $isMedicalShop ? [
            ['service_name' => 'Medicine Availability Help', 'description' => 'Ask our team about product availability and alternatives.'],
            ['service_name' => 'Prescription Support', 'description' => 'Bring or share your prescription for assistance finding the right items.'],
            ['service_name' => 'Local Enquiry Support', 'description' => 'Contact us for product guidance and store information.'],
        ] : [
            ['service_name' => 'Business Consultation', 'description' => 'Talk to our team about your requirements.'],
            ['service_name' => 'Product Guidance', 'description' => 'Get practical help choosing the right option for your needs.'],
        ];

        $defaultContent = [
            'business_description' => $isMedicalShop
                ? 'A local medical shop helping customers find everyday healthcare products and medicine support in '.$city.'.'
                : 'A local business serving customers in '.$city.' with dependable products and practical support.',
            'hero_headline' => $isMedicalShop ? 'Your local healthcare partner' : 'Dependable support for '.$businessName,
            'hero_subtitle' => $isMedicalShop ? 'Find everyday medicines, wellness products and personal care essentials with helpful local support.' : 'Discover dependable products and thoughtful support from a local team.',
            'cta' => 'Send an Enquiry',
            'slug' => BusinessWebsite::generateUniqueSlug($businessName),
            'seo_title' => $businessName.' | '.$city,
            'seo_description' => $isMedicalShop ? $businessName.' in '.$city.' for medicines, wellness and personal care enquiries.' : $businessName.' in '.$city.' for products and local business enquiries.',
            'products' => $defaultProducts,
            'services' => $defaultServices,
        ];

        if (! $this->aiClient->isConfigured()) {
            return $defaultContent;
        }

        try {
            $response = $this->aiClient->complete(
                'You are a website strategist and copy specialist. Classify the business before writing. Do not invent certifications, guarantees, prices, doctors, brands, delivery promises, or customer testimonials. For shops, prioritise products/categories; for service businesses, prioritise services. Return concise factual JSON.',
                "Create structured JSON with keys: business_description, hero_headline, hero_subtitle, cta, slug, seo_title, seo_description, products, services. Products and services must be arrays of objects with name and description. Business context: {$context}. City: {$city}. If this is a medical shop, pharmacy or chemist, return product categories such as prescription medicines, OTC medicines, vitamins/wellness and personal care before any support services. Keep all claims grounded in the provided context."
            );

            $decoded = json_decode($response, true);

            if (is_array($decoded)) {
                return array_merge($defaultContent, $decoded);
            }
        } catch (\Throwable $e) {
            // intentional fallback when provider is unavailable
        }

        return $defaultContent;
    }
}
