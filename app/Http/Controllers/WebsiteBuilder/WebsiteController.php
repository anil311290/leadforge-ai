<?php

namespace App\Http\Controllers\WebsiteBuilder;

use App\Http\Controllers\Controller;
use App\Models\BusinessWebsite;
use App\Models\Lead;
use App\Services\WebsiteBuilder\AiWebsiteContentService;
use App\Services\WebsiteBuilder\WebsiteBrandingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WebsiteController extends Controller
{
    public function index(Request $request)
    {
        $query = BusinessWebsite::query()->with('lead')->latest();

        if ($search = trim((string) $request->input('q'))) {
            $query->where(function ($websiteQuery) use ($search) {
                $websiteQuery->where('business_name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('state', 'like', "%{$search}%");
            });
        }

        if (in_array($request->input('status'), ['draft', 'published', 'unpublished', 'archived'], true)) {
            $query->where('status', $request->input('status'));
        }

        $websites = $query->paginate(12)->withQueryString();

        return view('website-builder.websites.index', compact('websites'));
    }

    public function create()
    {
        $lead = null;

        if ($leadId = request('lead_id')) {
            $lead = Lead::find($leadId);
        }

        return view('website-builder.websites.create', compact('lead'));
    }

    public function store(Request $request, WebsiteBrandingService $brandingService)
    {
        $data = $request->validate([
            'lead_id' => ['nullable', 'integer', 'exists:leads,id'],
            'business_name' => ['required', 'string', 'max:255'],
            'business_description' => ['nullable', 'string'],
            'owner_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'slug' => ['nullable', 'string', 'max:255'],
            'template_id' => ['nullable', 'integer', 'exists:website_templates,id'],
        ]);

        $businessWebsite = new BusinessWebsite([
            'lead_id' => $data['lead_id'] ?? null,
            'created_by' => auth()->id(),
            'business_name' => $data['business_name'],
            'business_description' => $data['business_description'] ?? null,
            'owner_name' => $data['owner_name'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'country' => $data['country'] ?? null,
            'address' => $data['address'] ?? null,
            'slug' => $data['slug'] ?? BusinessWebsite::generateUniqueSlug($data['business_name']),
            'status' => 'draft',
            'template_id' => $data['template_id'] ?? 1,
        ]);
        $brandingService->applyDefaults($businessWebsite);
        $businessWebsite->save();

        return redirect()->route('website-builder.websites.edit', $businessWebsite)
            ->with('success', 'Website draft created.');
    }

    public function edit(BusinessWebsite $businessWebsite, WebsiteBrandingService $brandingService)
    {
        $brandPalette = $brandingService->forWebsite($businessWebsite);

        return view('website-builder.websites.edit', compact('businessWebsite', 'brandPalette'));
    }

    public function update(Request $request, BusinessWebsite $businessWebsite)
    {
        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'business_description' => ['nullable', 'string'],
            'owner_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'slug' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:draft,published,unpublished,archived'],
            'template_id' => ['nullable', 'integer', 'exists:website_templates,id'],
            'primary_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'accent_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'favicon' => ['nullable', 'image', 'mimes:png,ico', 'max:1024'],
        ]);

        if (! empty($data['slug'])) {
            $businessWebsite->slug = $data['slug'];
        }

        foreach (['logo', 'favicon'] as $imageField) {
            if ($request->hasFile($imageField)) {
                if ($businessWebsite->{$imageField}) {
                    Storage::disk('public')->delete($businessWebsite->{$imageField});
                }

                $data[$imageField] = $request->file($imageField)->store('website-builder/'.$businessWebsite->id, 'public');
            }
        }

        $businessWebsite->fill($data);

        if (($data['status'] ?? null) === 'published' && ! $businessWebsite->published_at) {
            $businessWebsite->published_at = now();
        } elseif (($data['status'] ?? null) && $data['status'] !== 'published') {
            $businessWebsite->published_at = null;
        }

        $businessWebsite->save();

        return back()->with('success', 'Website updated.');
    }

    public function generateAiContent(AiWebsiteContentService $aiContentService, BusinessWebsite $businessWebsite)
    {
        $content = $aiContentService->generateForWebsite($businessWebsite);

        $businessWebsite->business_description = $content['business_description'] ?? $businessWebsite->business_description;
        $businessWebsite->save();

        if ($businessWebsite->products()->count() === 0) {
            foreach (array_values(array_filter($content['products'] ?? [], 'is_array')) as $sortOrder => $product) {
                $name = $product['product_name'] ?? $product['name'] ?? null;
                if ($name) {
                    $businessWebsite->products()->create([
                        'product_name' => $name,
                        'description' => $product['description'] ?? null,
                        'sort_order' => $sortOrder,
                    ]);
                }
            }
        }

        if ($businessWebsite->services()->count() === 0) {
            foreach (array_values(array_filter($content['services'] ?? [], 'is_array')) as $sortOrder => $service) {
                $name = $service['service_name'] ?? $service['name'] ?? null;
                if ($name) {
                    $businessWebsite->services()->create([
                        'service_name' => $name,
                        'description' => $service['description'] ?? null,
                        'sort_order' => $sortOrder,
                    ]);
                }
            }
        }

        if (! empty($content['slug'])) {
            $businessWebsite->slug = $content['slug'];
            $businessWebsite->save();
        }

        return back()->with('success', 'AI content generated successfully.');
    }

    public function publish(BusinessWebsite $businessWebsite)
    {
        if (empty($businessWebsite->slug)) {
            $businessWebsite->slug = BusinessWebsite::generateUniqueSlug($businessWebsite->business_name);
        }

        $businessWebsite->status = 'published';
        $businessWebsite->published_at = now();
        $businessWebsite->save();

        return back()->with('success', 'Website published.');
    }

    public function unpublish(BusinessWebsite $businessWebsite)
    {
        $businessWebsite->status = 'unpublished';
        $businessWebsite->published_at = null;
        $businessWebsite->save();

        return back()->with('success', 'Website unpublished.');
    }

    public function destroy(BusinessWebsite $businessWebsite)
    {
        $businessWebsite->delete();

        return redirect()->route('website-builder.websites.index')->with('success', 'Website deleted.');
    }
}
