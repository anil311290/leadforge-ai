<?php

namespace App\Http\Controllers\WebsiteBuilder;

use App\Http\Controllers\Controller;
use App\Models\BusinessWebsite;
use App\Models\WebsiteProduct;
use App\Models\WebsiteService;
use App\Services\WebsiteBuilder\AiImageGenerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function index(BusinessWebsite $businessWebsite)
    {
        return view('website-builder.media', compact('businessWebsite'));
    }

    public function store(Request $request, BusinessWebsite $businessWebsite)
    {
        $data = $request->validate([
            'media' => ['required', 'image', 'max:5120'],
            'label' => ['nullable', 'string', 'max:255'],
            'asset_type' => ['nullable', 'in:banner,gallery'],
        ]);

        $businessWebsite->media()->create([
            'type' => $data['asset_type'] ?? 'gallery',
            'path' => $request->file('media')->store('website-builder/'.$businessWebsite->id.'/'.($data['asset_type'] ?? 'gallery'), 'public'),
            'label' => $data['label'] ?? null,
        ]);

        return back()->with('success', ucfirst($data['asset_type'] ?? 'gallery').' image uploaded.');
    }

    public function storeItemImage(Request $request, BusinessWebsite $businessWebsite)
    {
        $data = $request->validate([
            'media' => ['required', 'image', 'max:5120'],
            'item_type' => ['required', 'in:product,service'],
            'item_id' => ['required', 'integer'],
        ]);

        $model = $data['item_type'] === 'product' ? WebsiteProduct::class : WebsiteService::class;
        $item = $model::where('website_id', $businessWebsite->id)->findOrFail($data['item_id']);

        if ($item->image) {
            Storage::disk('public')->delete($item->image);
        }

        $item->image = $request->file('media')->store('website-builder/'.$businessWebsite->id.'/'.$data['item_type'].'s', 'public');
        $item->save();

        return back()->with('success', ucfirst($data['item_type']).' image uploaded.');
    }

    public function generate(Request $request, BusinessWebsite $businessWebsite, AiImageGenerationService $imageService)
    {
        $data = $request->validate([
            'asset_type' => ['required', 'in:banner,product,service'],
            'item_id' => ['nullable', 'integer'],
        ]);

        try {
            if ($data['asset_type'] === 'banner') {
                $path = $imageService->generate($imageService->businessPrompt($businessWebsite, 'banner'), 'website-builder/'.$businessWebsite->id.'/banner');
                $existing = $businessWebsite->media()->where('type', 'banner')->first();
                if ($existing) {
                    Storage::disk('public')->delete($existing->path);
                    $existing->update(['path' => $path]);
                } else {
                    $businessWebsite->media()->create(['type' => 'banner', 'path' => $path, 'label' => 'AI generated hero banner']);
                }
            } else {
                $model = $data['asset_type'] === 'product' ? WebsiteProduct::class : WebsiteService::class;
                $item = $model::where('website_id', $businessWebsite->id)->findOrFail($data['item_id']);
                $subject = $data['asset_type'] === 'product' ? $item->product_name : $item->service_name;
                $path = $imageService->generate($imageService->businessPrompt($businessWebsite, $data['asset_type'], $subject), 'website-builder/'.$businessWebsite->id.'/'.$data['asset_type'].'s');
                if ($item->image) {
                    Storage::disk('public')->delete($item->image);
                }
                $item->update(['image' => $path]);
            }
        } catch (\Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'AI image generated successfully.');
    }

    public function destroy(BusinessWebsite $businessWebsite, $media)
    {
        $websiteMedia = $businessWebsite->media()->findOrFail($media);
        Storage::disk('public')->delete($websiteMedia->path);
        $websiteMedia->delete();

        return back()->with('success', 'Gallery image removed.');
    }
}
