<?php

namespace Tests\Feature;

use App\Contracts\PostRepositoryInterface;
use App\Repositories\Api\ApiPostRepository;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PublicNewsTest extends TestCase
{
    protected function tearDown(): void
    {
        Http::swap(new \Illuminate\Http\Client\Factory());
        parent::tearDown();
    }

    public function test_home_link_navigates_to_news_detail(): void
    {
        $item = [
            'id_berita' => 42,
            'judul' => 'Rapat Koordinasi Evaluasi MBKM Mandiri',
            'isi' => "Senat Akademik menyelenggarakan rapat koordinasi evaluasi program MBKM.\n\nEvaluasi mencakup konversi SKS dan kemitraan industri.",
            'gambar' => 'https://example.test/mbkm.jpg',
            'galeri' => ['https://example.test/mbkm1.jpg', 'https://example.test/mbkm2.jpg'],
            'lampiran' => [['name' => 'Panduan MBKM PDF', 'url' => 'https://example.test/panduan-mbkm.pdf']],
            'tanggal_publish' => '2026-05-10 09:00:00',
            'author' => 'Sekretaris Senat',
            'user' => ['nama' => 'Sekretaris Senat'],
        ];

        Http::fake([
            '*landing-news*' => Http::response(['data' => [$item]]),
            '*public-news/rapat-koordinasi-evaluasi-mbkm-mandiri*' => Http::response(['data' => $item]),
            '*public-news*' => Http::response(['data' => [$item], 'total' => 1, 'current_page' => 1, 'last_page' => 1, 'per_page' => 12]),
            '*' => Http::response([], 503),
        ]);

        // 1. Link on homepage points to /berita/rapat-koordinasi-evaluasi-mbkm-mandiri
        $homeRes = $this->get('/')->assertOk();
        $homeRes->assertSee('/berita/rapat-koordinasi-evaluasi-mbkm-mandiri');
        $homeRes->assertSee('Rapat Koordinasi Evaluasi MBKM Mandiri');

        // 2. Detail page loads successfully with real data
        $detailRes = $this->get('/berita/rapat-koordinasi-evaluasi-mbkm-mandiri')->assertOk();
        $detailRes->assertSee('Rapat Koordinasi Evaluasi MBKM Mandiri');
        $detailRes->assertSee('Sekretaris Senat');
        $detailRes->assertSee('https://example.test/mbkm.jpg');
        $detailRes->assertSee('https://example.test/mbkm1.jpg');
        $detailRes->assertSee('Panduan MBKM PDF');
        $detailRes->assertSee('https://example.test/panduan-mbkm.pdf');
        $detailRes->assertSee('Senat Akademik menyelenggarakan rapat koordinasi evaluasi program MBKM.');
        $detailRes->assertSee('Evaluasi mencakup konversi SKS dan kemitraan industri.');
    }

    public function test_news_index_pagination_beyond_fifteen(): void
    {
        $allPosts = [];
        foreach (range(1, 20) as $i) {
            $allPosts[] = [
                'id_berita' => $i,
                'judul' => sprintf('Warta Senat Nomor %02d', $i),
                'isi' => "Isi warta ke-$i tentang kebijakan akademik.",
                'gambar' => "https://example.test/pic$i.jpg",
                'tanggal_publish' => sprintf('2026-05-%02d 10:00:00', min(28, $i)),
                'author' => 'Humas Polines',
            ];
        }
        $reversed = array_reverse($allPosts); // Newest first

        Http::fake([
            '*public-news*page=2*' => Http::response([
                'data' => array_slice($reversed, 12, 8),
                'total' => 20,
                'current_page' => 2,
                'last_page' => 2,
                'per_page' => 12,
                'from' => 13,
                'to' => 20,
            ]),
            '*public-news*' => Http::response([
                'data' => array_slice($reversed, 0, 12),
                'total' => 20,
                'current_page' => 1,
                'last_page' => 2,
                'per_page' => 12,
                'from' => 1,
                'to' => 12,
            ]),
            '*' => Http::response([], 503),
        ]);

        // Page 1
        $resPage1 = $this->get('/berita')->assertOk();
        $resPage1->assertSee('Menampilkan 1 – 12 dari 20 warta dan siaran pers');
        $resPage1->assertSee('Warta Senat Nomor 20');
        $resPage1->assertSee('page=2');

        // Page 2 (> 12 items, accessing rest of > 15 dataset)
        $resPage2 = $this->get('/berita?page=2')->assertOk();
        $resPage2->assertSee('Menampilkan 13 – 20 dari 20 warta dan siaran pers');
        $resPage2->assertSee('Warta Senat Nomor 08');
    }

    public function test_news_index_search_queries_backend(): void
    {
        $item = [
            'id_berita' => 99,
            'judul' => 'Sosialisasi Kurikulum Vokasi 2026',
            'isi' => 'Pembahasan kurikulum baru berbasis industri.',
            'gambar' => null,
            'tanggal_publish' => '2026-04-01 08:00:00',
            'author' => 'Tim Kurikulum',
        ];

        Http::fake([
            '*public-news*search=Vokasi*' => Http::response([
                'data' => [$item],
                'total' => 1,
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 12,
                'from' => 1,
                'to' => 1,
            ]),
            '*public-news*' => Http::response([
                'data' => [],
                'total' => 0,
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 12,
                'from' => 0,
                'to' => 0,
            ]),
            '*' => Http::response([], 503),
        ]);

        $res = $this->get('/berita?search=Vokasi')->assertOk();
        $res->assertSee('Sosialisasi Kurikulum Vokasi 2026');
        $res->assertSee('Pembahasan kurikulum baru');
        Http::assertSent(fn ($req) => str_contains($req->url(), 'search=Vokasi'));
    }

    public function test_empty_database_shows_empty_message_and_no_mock(): void
    {
        Http::fake([
            '*public-news*' => Http::response([
                'data' => [],
                'total' => 0,
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 12,
                'from' => 0,
                'to' => 0,
            ]),
            '*' => Http::response([], 503),
        ]);

        $res = $this->get('/berita')->assertOk();
        $res->assertSee('Belum ada warta yang diterbitkan.');
        $res->assertDontSee('Warta sementara tidak tersedia');
        // Ensure local mock items from storage/mock/posts.json are NOT shown
        $res->assertDontSee('Uji Publik Rencana Peraturan Senat Mengenai Kode Etik');
        $res->assertDontSee('Sidang Pleno Terbuka');
    }

    public function test_backend_outage_shows_outage_message_and_no_mock(): void
    {
        Http::fake([
            '*' => Http::response(['error' => 'Server Error'], 503),
        ]);

        $res = $this->get('/berita')->assertOk();
        $res->assertSee('Warta sementara tidak tersedia');
        $res->assertDontSee('Belum ada warta yang diterbitkan.');
        $res->assertDontSee('Uji Publik Rencana Peraturan Senat Mengenai Kode Etik');

        // Detail on outage returns 503
        $detailRes = $this->get('/berita/uji-publik-peraturan');
        $detailRes->assertStatus(503);
    }

    public function test_draft_and_future_news_are_not_exposed(): void
    {
        Http::fake([
            '*public-news/draf-rahasia*' => Http::response(['message' => 'Not Found'], 404),
            '*public-news/jadwal-masa-depan*' => Http::response(['message' => 'Not Found'], 404),
            '*landing-news*' => Http::response(['data' => []], 200),
            '*' => Http::response([], 503),
        ]);

        $this->get('/berita/draf-rahasia')->assertNotFound();
        $this->get('/berita/jadwal-masa-depan')->assertNotFound();
    }

    public function test_admin_repository_and_mock_are_preserved(): void
    {
        // Admin repository binding must remain intact
        $repo = app(PostRepositoryInterface::class);
        $this->assertInstanceOf(ApiPostRepository::class, $repo);
    }
}
