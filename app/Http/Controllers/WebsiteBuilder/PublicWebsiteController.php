<?php

namespace App\Http\Controllers\WebsiteBuilder;

use App\Http\Controllers\Controller;
use App\Models\BusinessWebsite;
use App\Models\Lead;
use App\Services\WebsiteBuilder\WebsiteBrandingService;
use Illuminate\Http\Request;

class PublicWebsiteController extends Controller
{
    public function preview(BusinessWebsite $businessWebsite, WebsiteBrandingService $brandingService)
    {
        $businessWebsite->load(['services', 'products', 'media', 'sections', 'template']);

        return view('website-builder.public.modern', ['website' => $businessWebsite, 'isPreview' => true, 'templateSlug' => $businessWebsite->template?->slug ?: 'modern-business', 'brandPalette' => $brandingService->forWebsite($businessWebsite)]);
    }

    public function show(Request $request, string $slug, WebsiteBrandingService $brandingService)
    {
        $website = BusinessWebsite::with(['services', 'products', 'media', 'sections', 'template'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        return view('website-builder.public.modern', ['website' => $website, 'isPreview' => false, 'templateSlug' => $website->template?->slug ?: 'modern-business', 'brandPalette' => $brandingService->forWebsite($website)]);
    }

    public function storeEnquiry(Request $request, string $slug)
    {
        $website = BusinessWebsite::where('slug', $slug)->where('status', 'published')->firstOrFail();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:50'],
            'message' => ['required', 'string', 'max:2000'],
            'enquiry_type' => ['required', 'in:product,service,contact'],
            'subject' => ['nullable', 'string', 'max:255', 'required_if:enquiry_type,product,service'],
        ]);

        $source = $data['enquiry_type'] === 'contact' ? 'website_contact' : 'website_enquiry';
        $subject = $data['subject'] ?? 'General contact enquiry';

        Lead::create([
            'company' => $website->business_name,
            'website' => $website->public_url,
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'source' => $source,
            'status' => Lead::STATUS_NEW,
            'notes' => ucfirst($data['enquiry_type'])." enquiry from {$data['name']} about {$subject}: {$data['message']}",
        ]);

        return back()->with('enquiry_success', 'Thanks. We received your enquiry and will contact you soon.');
    }
}
