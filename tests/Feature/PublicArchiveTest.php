<?php

namespace Tests\Feature;

use App\Contracts\ArchiveRepositoryInterface;
use App\Repositories\Api\ApiArchiveRepository;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PublicArchiveTest extends TestCase
{
    protected function tearDown(): void
    {
        Http::swap(new \Illuminate\Http\Client\Factory());
        parent::tearDown();
    }

    public function test_archive_repository_is_bound_to_api_archive_repository(): void
    {
        $this->assertInstanceOf(ApiArchiveRepository::class, app(ArchiveRepositoryInterface::class));
    }

    public function test_archives_page_displays_notulensi_and_reports_from_backend_without_mock(): void
    {
        config(['services.backend_api.url' => 'http://example.test/api']);

        Http::fake([
            '*notulensi*' => Http::response([[
                'id_notulensi' => 11,
                'isi_notulensi' => 'Keputusan pleno penyelarasan kurikulum OBE.',
                'status' => 'ditetapkan',
                'berkas_pdf' => 'uploads/notulen/1/notulen-11.pdf',
                'tanggal_dibuat' => '2026-10-15 10:00:00',
                'rapat' => [
                    'nama_agenda' => 'Sidang Pleno Kurikulum 2026',
                    'tanggal' => '2026-10-15',
                    'jenis_rapat' => ['nama_jenis' => 'Rapat Pleno'],
                ],
            ]]),
            '*peraturan*' => Http::response([
                [
                    'id_peraturan' => 21,
                    'nomor_sk' => 'LPJ/SA/2026/01',
                    'judul' => 'Laporan Tahunan Kinerja Senat Akademik 2026',
                    'kategori' => 'Laporan Tahunan',
                    'tahap_pembentukan' => 'Disahkan',
                    'file_dokumen' => 'peraturan/lpj-2026.pdf',
                    'tanggal_terbit' => '2026-11-20',
                ],
                [
                    'id_peraturan' => 22,
                    'nomor_sk' => 'PER/SA/2026/02',
                    'judul' => 'Peraturan Tata Tertib Sidang',
                    'kategori' => 'Peraturan Senat',
                    'tahap_pembentukan' => 'Disahkan',
                    'file_dokumen' => 'peraturan/tatib.pdf',
                    'tanggal_terbit' => '2026-11-21',
                ],
            ]),
            '*' => Http::response(['data' => []]),
        ]);

        // 1. /arsip menampilkan notulensi + laporan tahunan dari database (tanpa mock Draft OTK)
        $this->get('/arsip')
            ->assertOk()
            ->assertSee('Risalah — Sidang Pleno Kurikulum 2026')
            ->assertSee('http://example.test/api/notulensi/11/pdf')
            ->assertSee('Laporan Tahunan Kinerja Senat Akademik 2026')
            ->assertSee('http://example.test/api/peraturan/21/download')
            ->assertDontSee('Draft OTK Senat 2026');

        // 2. /arsip/laporan-tahunan hanya menampilkan Laporan Tahunan / LPJ (bukan notulensi)
        $this->get('/arsip/laporan-tahunan')
            ->assertOk()
            ->assertSee('Laporan Tahunan Kinerja Senat Akademik 2026')
            ->assertSee('http://example.test/api/peraturan/21/download')
            ->assertDontSee('Risalah — Sidang Pleno Kurikulum 2026')
            ->assertDontSee('Draft OTK Senat 2026');

        // 3. /regulasi tetap bersih dari kategori laporan tahunan
        $this->get('/regulasi')
            ->assertOk()
            ->assertSee('Peraturan Tata Tertib Sidang')
            ->assertDontSee('Laporan Tahunan Kinerja Senat Akademik 2026');
    }

    public function test_reports_page_shows_empty_state_when_no_reports_in_database(): void
    {
        config(['services.backend_api.url' => 'http://example.test/api']);

        Http::fake([
            '*notulensi*' => Http::response([[
                'id_notulensi' => 11,
                'isi_notulensi' => 'Isi rapat.',
                'tanggal_dibuat' => '2026-10-15 10:00:00',
                'rapat' => ['nama_agenda' => 'Sidang Pleno', 'jenis_rapat' => ['nama_jenis' => 'Rapat Pleno']],
            ]]),
            '*peraturan*' => Http::response([]),
            '*' => Http::response(['data' => []]),
        ]);

        $this->get('/arsip/laporan-tahunan')
            ->assertOk()
            ->assertSee('Belum ada laporan tahunan')
            ->assertDontSee('Sidang Pleno')
            ->assertDontSee('Draft OTK Senat 2026');
    }

    public function test_archives_pages_handle_backend_outage_without_mock(): void
    {
        config(['services.backend_api.url' => 'http://example.test/api']);
        Http::fake(['*' => Http::response([], 503)]);

        $this->get('/arsip')
            ->assertOk()
            ->assertSee('Belum ada arsip dokumen')
            ->assertDontSee('Draft OTK Senat 2026');

        $this->get('/arsip/laporan-tahunan')
            ->assertOk()
            ->assertSee('Belum ada laporan tahunan')
            ->assertDontSee('Draft OTK Senat 2026');
    }
}
