<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    /** @test */
    public function authenticated_pages_render_ok()
    {
        $user = User::first() ?? User::factory()->create();

        $pages = [
            ['route' => '/dashboard', 'needle' => 'Dashboard'],
            ['route' => '/leads', 'needle' => 'Leads'],
            ['route' => '/followups', 'needle' => 'Due now'],
            ['route' => '/pipeline', 'needle' => 'Pipeline'],
            ['route' => '/opportunities', 'needle' => 'Opportunities'],
            ['route' => '/emails', 'needle' => 'Emails'],
            ['route' => '/campaigns', 'needle' => 'Campaigns'],
            ['route' => '/services', 'needle' => 'Services'],
            ['route' => '/reports', 'needle' => 'Reports'],
            ['route' => '/ai/usage', 'needle' => 'AI'],
        ];

        foreach ($pages as $p) {
            $resp = $this->actingAs($user)->get($p['route']);
            $this->assertEquals(200, $resp->getStatusCode(), $p['route'].' failed');
            $this->assertStringContainsString($p['needle'], $resp->getContent(), $p['route'].' missing content');
            echo "OK {$p['route']}\n";
        }

        echo "ALL SMOKE PAGES PASSED\n";
    }
}