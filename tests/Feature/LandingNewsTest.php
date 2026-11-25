<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LandingNewsTest extends TestCase
{
    public function test_empty_database_does_not_show_local_mock_news(): void
    {
        Http::fake(['*landing-news' => Http::response(['data' => []]), '*' => Http::response([], 503)]);
        $this->get('/')->assertOk()->assertViewHas('posts', [])->assertSee('Belum ada warta yang diterbitkan.');
    }

    public function test_unavailable_news_is_distinct_from_empty(): void
    {
        Http::fake(['*' => Http::response([], 503)]);
        $this->get('/')->assertOk()->assertSee('Warta sementara tidak tersedia.')->assertDontSee('Belum ada warta yang diterbitkan.');
    }

    public function test_database_news_is_rendered_in_api_order(): void
    {
        Http::fake(['*landing-news' => Http::response(['data' => [
            ['id_berita' => 2, 'judul' => 'Unique New Title', 'isi' => 'New body', 'tanggal_publish' => '2026-01-02 12:00:00', 'gambar' => null],
            ['id_berita' => 1, 'judul' => 'Unique Old Title', 'isi' => 'Old body', 'tanggal_publish' => '2026-01-01 12:00:00', 'gambar' => null],
        ]]), '*' => Http::response([], 503)]);
        $this->get('/')->assertOk()->assertSeeInOrder(['Unique New Title', 'Unique Old Title'])->assertDontSee('Belum ada warta yang diterbitkan.');
    }

    public function test_malformed_response_is_unavailable(): void
    {
        Http::fake(['*landing-news' => Http::response(['data' => 'broken']), '*' => Http::response([], 503)]);
        $this->get('/')->assertOk()->assertSee('Warta sementara tidak tersedia.');
    }

    public function test_news_index_preserves_post_repository_behavior(): void
    {
        $this->get('/berita')->assertOk()->assertViewHas('posts');
    }
}
