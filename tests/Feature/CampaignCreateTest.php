<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_campaign_create_form_persists_campaign_and_redirects(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->post(route('campaigns.store'), [
            'location' => 'Jaipur',
            'name' => 'Jaipur Medical Leads',
            'radius_km' => '10',
            'min_score' => '50',
            'max_businesses' => '5',
            'auto_analysis_enabled' => '1',
            'email_outreach_enabled' => '0',
            'sources' => ['manual_urls'],
            'businesses' => "Raju Medical Store\nhttps://example.com",
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('campaigns', [
            'user_id' => $user->id,
            'name' => 'Jaipur Medical Leads',
            'location' => 'Jaipur',
            'status' => 'completed',
        ]);
        $this->assertSame(1, Campaign::where('name', 'Jaipur Medical Leads')->count());
    }

    public function test_campaign_create_form_displays_validation_errors(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->from(route('campaigns.create'))->post(route('campaigns.store'), [
            'location' => '',
            'min_score' => 101,
        ]);

        $response->assertRedirect(route('campaigns.create'));
        $response->assertSessionHasErrors(['location', 'min_score']);
        $response->assertSessionHas('errors');
    }
}