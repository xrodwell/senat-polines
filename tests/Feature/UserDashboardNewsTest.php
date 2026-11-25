<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UserDashboardNewsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        session([
            'auth_token'            => 'test-token-anggota',
            'auth_token_checked_at' => time(),
            'auth_user'             => ['id_user' => 2, 'nama' => 'Anggota Senat', 'role' => 'anggota'],
            'auth_user_id'          => 2,
            'auth_user_role'        => 'anggota',
        ]);
    }

    protected function tearDown(): void
    {
        Http::swap(new \Illuminate\Http\Client\Factory());
        parent::tearDown();
    }

    public function test_user_dashboard_displays_published_news_from_backend_without_mock(): void
    {
        Http::fake([
            '*public-news*' => Http::response([
                'data' => [[
                    'id_berita' => 101,
                    'judul' => 'Sidang Pleno Database Terintegrasi',
                    'isi' => 'Rilis resmi langsung dari database backend.',
                    'tanggal_publish' => '2026-11-25 10:00:00',
                    'gambar' => null,
                ]],
                'total' => 1,
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 12,
            ]),
            '*' => Http::response([], 503),
        ]);

        $response = $this->get(route('portal.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Warta &amp; Pengumuman', false);
        $response->assertSee('Up-to-date', false);
        $response->assertSee('+ Tulis Berita', false);
        $response->assertSee('Sidang Pleno Database Terintegrasi', false);
        $response->assertDontSee('Berita Utama Terkini 6af400a770ba9', false);
    }

    public function test_user_dashboard_shows_empty_when_database_has_no_news(): void
    {
        Http::fake([
            '*public-news*' => Http::response([
                'data' => [],
                'total' => 0,
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 12,
            ]),
            '*' => Http::response([], 503),
        ]);

        $response = $this->get(route('portal.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Belum ada warta terpublikasi', false);
        $response->assertDontSee('Berita Utama Terkini 6af400a770ba9', false);
        $response->assertDontSee('wisudawan', false);
    }

    public function test_user_dashboard_shows_unavailable_when_backend_outage(): void
    {
        Http::fake([
            '*' => Http::response(['error' => 'Service Unavailable'], 503),
        ]);

        $response = $this->get(route('portal.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Warta sementara tidak tersedia', false);
        $response->assertDontSee('Berita Utama Terkini 6af400a770ba9', false);
    }

    public function test_user_can_create_and_publish_news_from_dashboard(): void
    {
        $uniqueTitle = 'Berita Utama Terkini ' . uniqid();

        Http::fake([
            '*berita' => Http::response([
                'id_berita' => 555,
                'judul' => $uniqueTitle,
            ], 201),
            '*public-news*' => Http::response([
                'data' => [[
                    'id_berita' => 555,
                    'judul' => $uniqueTitle,
                    'isi' => 'Naskah rilis pers terverifikasi untuk dashboard anggota.',
                    'tanggal_publish' => '2026-11-25 12:00:00',
                    'gambar' => null,
                ]],
                'total' => 1,
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 12,
            ]),
            '*' => Http::response([], 503),
        ]);

        $response = $this->post(route('portal.news.store'), [
            'title' => $uniqueTitle,
            'category' => 'Warta Senat',
            'body' => 'Naskah rilis pers terverifikasi untuk dashboard anggota.',
            'status' => 'Terpublikasi',
        ]);

        $response->assertRedirect(route('portal.dashboard'));
        $response->assertSessionHas('success');

        Http::assertSent(fn ($req) => str_ends_with($req->url(), '/api/berita')
            && $req->hasHeader('Authorization', 'Bearer test-token-anggota')
            && $req['judul'] === $uniqueTitle);

        $dashboardResponse = $this->get(route('portal.dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee($uniqueTitle, false);
    }
}
