<?php

namespace Tests\Feature;

use App\Models\BusinessWebsite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebsiteBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_website_builder_user_can_access_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'website_builder',
            'email' => 'builder@example.com',
        ]);

        $response = $this->actingAs($user)->get('/website-builder/dashboard');

        $response->assertOk();
        $response->assertSee('Website Builder');
        $response->assertSee('Websites');
        $response->assertDontSee('Find Projects');
    }

    public function test_website_builder_user_can_preview_a_draft_website(): void
    {
        $user = User::factory()->create(['role' => 'website_builder']);
        $website = \App\Models\BusinessWebsite::create([
            'created_by' => $user->id,
            'business_name' => 'Draft Business',
            'slug' => 'draft-business',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($user)->get(route('website-builder.websites.preview', $website));

        $response->assertOk();
        $response->assertSee('Preview mode');
    }

    public function test_admin_can_access_website_builder_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/website-builder/dashboard');

        $response->assertOk();
        $response->assertSee('Website Builder');
    }

    public function test_website_builder_user_cannot_access_main_application_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'website_builder']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertForbidden();
    }

    public function test_website_builder_user_is_sent_to_website_builder_from_root(): void
    {
        $user = User::factory()->create(['role' => 'website_builder']);

        $response = $this->actingAs($user)->get('/');

        $response->assertRedirect('/website-builder/dashboard');
    }

    public function test_public_website_enquiry_creates_a_new_lead(): void
    {
        $website = BusinessWebsite::create([
            'business_name' => 'Published Business',
            'slug' => 'published-business',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->post(route('public.website.enquiry', $website->slug), [
            'name' => 'Anil Customer',
            'email' => 'customer@example.com',
            'phone' => '9999999999',
            'message' => 'Please share more details.',
            'enquiry_type' => 'product',
            'subject' => 'Prescription Medicines',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('enquiry_success');
        $this->assertDatabaseHas('leads', [
            'company' => 'Published Business',
            'email' => 'customer@example.com',
            'source' => 'website_enquiry',
            'status' => 'NEW',
        ]);
    }

    public function test_website_builder_user_is_redirected_to_website_builder_dashboard_after_login(): void
    {
        $user = User::factory()->create([
            'email' => 'builder2@example.com',
            'password' => bcrypt('password123'),
            'role' => 'website_builder',
        ]);

        $response = $this->from('/login')->post('/website-builder/login', [
            'email' => 'builder2@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/website-builder/dashboard');
    }
}
