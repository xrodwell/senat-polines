<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LandingStatsTest extends TestCase
{
    public function test_home_shows_aggregate_from_configured_backend(): void
    {
        config(['services.backend_api.url' => 'http://example.test/api/']);
        Http::fake(['*landing-stats' => Http::response(['members' => 71, 'committees' => 8, 'published_news' => 45]), '*' => Http::response([], 503)]);
        $response = $this->get('/');
        $response->assertOk()->assertSee('71')->assertSee('45')->assertViewHas('landingStats', ['members' => 71, 'committees' => 8, 'published_news' => 45]);
        Http::assertSent(fn ($request) => $request->url() === 'http://example.test/api/landing-stats');
    }

    public function test_home_shows_unavailable_on_backend_failure(): void
    {
        Http::fake(['*' => Http::response([], 503)]);
        $this->get('/')->assertOk()->assertViewHas('landingStats', null)->assertSee('Data tidak tersedia');
    }

    public function test_home_rejects_invalid_success_payload(): void
    {
        Http::fake(['*landing-stats' => Http::response(['members' => '55', 'committees' => 1, 'published_news' => 2]), '*' => Http::response([], 503)]);
        $this->get('/')->assertOk()->assertViewHas('landingStats', null)->assertSee('Data tidak tersedia');
    }
}
