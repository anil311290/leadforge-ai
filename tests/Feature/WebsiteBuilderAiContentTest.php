<?php

namespace Tests\Feature;

use App\Models\BusinessWebsite;
use App\Models\Lead;
use App\Models\WebsiteProduct;
use App\Models\WebsiteService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WebsiteBuilderAiContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_website_builder_user_can_generate_ai_content_for_website(): void
    {
        $user = User::factory()->create(['role' => 'website_builder']);
        $website = BusinessWebsite::create([
            'created_by' => $user->id,
            'business_name' => 'Rama Medical Store',
            'slug' => 'rama-medical-store',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($user)->post('/website-builder/websites/'.$website->id.'/generate-ai-content');

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_website_builder_user_can_create_website_from_lead_with_prefilled_business_data(): void
    {
        $user = User::factory()->create(['role' => 'website_builder']);
        $lead = Lead::create([
            'company' => 'Rama Medical Store',
            'phone' => '9876543210',
            'email' => 'rama@example.com',
            'city' => 'Jaipur',
            'state' => 'Rajasthan',
            'address' => 'Main Market',
            'website' => 'https://ramamedicalstore.com',
            'industry' => 'Healthcare',
            'status' => 'NEW',
        ]);

        $response = $this->actingAs($user)->post('/website-builder/websites', [
            'lead_id' => $lead->id,
            'business_name' => $lead->company,
            'phone' => $lead->phone,
            'email' => $lead->email,
            'city' => $lead->city,
            'state' => $lead->state,
            'address' => $lead->address,
            'business_description' => 'Local healthcare support',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('business_websites', [
            'lead_id' => $lead->id,
            'business_name' => 'Rama Medical Store',
            'phone' => '9876543210',
            'email' => 'rama@example.com',
        ]);
    }

    public function test_website_builder_user_can_save_branding_and_upload_gallery_media(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'website_builder']);
        $website = BusinessWebsite::create([
            'created_by' => $user->id,
            'business_name' => 'Rama Medical Store',
            'slug' => 'rama-medical-store',
            'status' => 'draft',
        ]);

        $update = $this->actingAs($user)->patch(route('website-builder.websites.update', $website), [
            'business_name' => $website->business_name,
            'primary_color' => '#123456',
            'secondary_color' => '#654321',
            'accent_color' => '#abcdef',
            'logo' => UploadedFile::fake()->image('logo.png'),
        ]);
        $update->assertRedirect();

        $upload = $this->actingAs($user)->post(route('website-builder.media.store', $website), [
            'media' => UploadedFile::fake()->image('gallery.jpg'),
            'label' => 'Storefront',
        ]);
        $upload->assertRedirect();

        $website->refresh();
        $this->assertSame('#123456', $website->primary_color);
        $this->assertNotEmpty($website->logo);
        $this->assertDatabaseHas('website_media', [
            'website_id' => $website->id,
            'label' => 'Storefront',
        ]);

        $product = WebsiteProduct::create(['website_id' => $website->id, 'product_name' => 'Vitamins']);
        $service = WebsiteService::create(['website_id' => $website->id, 'service_name' => 'Prescription support']);

        $this->actingAs($user)->post(route('website-builder.media.store', $website), [
            'media' => UploadedFile::fake()->image('banner.jpg'),
            'asset_type' => 'banner',
        ])->assertRedirect();

        $this->actingAs($user)->post(route('website-builder.media.item-image.store', $website), [
            'media' => UploadedFile::fake()->image('product.jpg'),
            'item_type' => 'product',
            'item_id' => $product->id,
        ])->assertRedirect();

        $this->actingAs($user)->post(route('website-builder.media.item-image.store', $website), [
            'media' => UploadedFile::fake()->image('service.jpg'),
            'item_type' => 'service',
            'item_id' => $service->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('website_media', ['website_id' => $website->id, 'type' => 'banner']);
        $this->assertNotEmpty($product->refresh()->image);
        $this->assertNotEmpty($service->refresh()->image);
    }
}
