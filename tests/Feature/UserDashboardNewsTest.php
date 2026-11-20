<?php

namespace Tests\Feature;

use App\Contracts\PostRepositoryInterface;
use Tests\TestCase;

class UserDashboardNewsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        session([
            'auth_token'     => 'test-token-anggota',
            'auth_user'      => ['id_user' => 2, 'nama' => 'Anggota Senat', 'role' => 'anggota'],
            'auth_user_id'   => 2,
            'auth_user_role' => 'anggota',
        ]);
    }

    public function test_user_dashboard_displays_published_news(): void
    {
        $response = $this->get(route('portal.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Warta &amp; Pengumuman', false);
        $response->assertSee('Up-to-date', false);
        $response->assertSee('+ Tulis Berita', false);
    }

    public function test_user_can_create_and_publish_news_from_dashboard(): void
    {
        $uniqueTitle = 'Berita Utama Terkini ' . uniqid();

        $response = $this->post(route('portal.news.store'), [
            'title' => $uniqueTitle,
            'category' => 'Warta Senat',
            'body' => 'Naskah rilis pers terverifikasi untuk dashboard anggota.',
            'status' => 'Terpublikasi',
        ]);

        $response->assertRedirect(route('portal.dashboard'));
        $response->assertSessionHas('success');

        // Pastikan berita yang baru dipublikasikan langsung tampil di dashboard user
        $dashboardResponse = $this->get(route('portal.dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee($uniqueTitle, false);
    }
}
