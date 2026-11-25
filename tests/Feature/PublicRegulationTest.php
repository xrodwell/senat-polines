<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PublicRegulationTest extends TestCase
{
    protected function tearDown(): void
    {
        Http::swap(new \Illuminate\Http\Client\Factory());
        parent::tearDown();
    }

    public function test_regulation_pages_filter_categories_cleanly_without_wrong_fallback(): void
    {
        config(['services.backend_api.url' => 'http://example.test/api']);

        Http::fake([
            '*peraturan*' => Http::response([
                [
                    'id_peraturan' => 1,
                    'nomor_sk' => 'PER/SA/2026/01',
                    'judul' => 'Peraturan Akademik Polines 2026',
                    'kategori' => 'Peraturan Senat',
                    'tahap_pembentukan' => 'Disahkan',
                    'file_dokumen' => 'peraturan/per-01.pdf',
                    'tanggal_terbit' => '2026-10-10',
                ],
                [
                    'id_peraturan' => 2,
                    'nomor_sk' => 'SK/SA/2026/02',
                    'judul' => 'SK Pertimbangan Penetapan Kurikulum',
                    'kategori' => 'SK Pertimbangan',
                    'tahap_pembentukan' => 'Disahkan',
                    'file_dokumen' => '',
                    'tanggal_terbit' => '2026-10-12',
                ],
                [
                    'id_peraturan' => 3,
                    'nomor_sk' => 'SOP/SA/2026/03',
                    'judul' => 'SOP Mutu Pelaksanaan Yudisium',
                    'kategori' => 'SOP Akademik',
                    'tahap_pembentukan' => 'Harmonisasi',
                    'file_dokumen' => 'peraturan/sop-03.pdf',
                    'tanggal_terbit' => '2026-10-15',
                ],
            ]),
            '*' => Http::response(['data' => []]),
        ]);

        // 1. /regulasi memuat produk hukum + menyertakan tombol unduh / berkas belum tersedia
        $this->get('/regulasi')
            ->assertOk()
            ->assertSee('Peraturan Akademik Polines 2026')
            ->assertSee('http://example.test/api/peraturan/1/download')
            ->assertSee('Format Berkas: PDF')
            ->assertSee('Unduh Berkas PDF')
            ->assertSee('Berkas belum tersedia')
            ->assertDontSee('· PDF');

        // 2. /regulasi/keputusan-senat hanya memuat SK / Keputusan
        $this->get('/regulasi/keputusan-senat')
            ->assertOk()
            ->assertSee('SK Pertimbangan Penetapan Kurikulum')
            ->assertDontSee('Peraturan Akademik Polines 2026')
            ->assertDontSee('SOP Mutu Pelaksanaan Yudisium');

        // 3. /regulasi/standar-pedoman hanya memuat Standar, Pedoman, SOP
        $this->get('/regulasi/standar-pedoman')
            ->assertOk()
            ->assertSee('SOP Mutu Pelaksanaan Yudisium')
            ->assertDontSee('Peraturan Akademik Polines 2026')
            ->assertDontSee('SK Pertimbangan Penetapan Kurikulum');
    }

    public function test_decrees_and_standards_show_empty_states_when_only_other_categories_exist(): void
    {
        config(['services.backend_api.url' => 'http://example.test/api']);

        Http::fake([
            '*peraturan*' => Http::response([
                [
                    'id_peraturan' => 10,
                    'nomor_sk' => 'PER/SA/2026/10',
                    'judul' => 'Peraturan Tata Tertib',
                    'kategori' => 'Peraturan Senat',
                    'tahap_pembentukan' => 'Disahkan',
                    'file_dokumen' => 'peraturan/tatib.pdf',
                    'tanggal_terbit' => '2026-11-01',
                ],
            ]),
            '*' => Http::response(['data' => []]),
        ]);

        $this->get('/regulasi/keputusan-senat')
            ->assertOk()
            ->assertSee('Belum ada keputusan senat')
            ->assertDontSee('Peraturan Tata Tertib');

        $this->get('/regulasi/standar-pedoman')
            ->assertOk()
            ->assertSee('Belum ada standar &amp; pedoman', false)
            ->assertDontSee('Peraturan Tata Tertib');
    }
}
