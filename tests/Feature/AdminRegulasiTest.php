<?php

namespace Tests\Feature;

use App\Livewire\AdminPreview;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class AdminRegulasiTest extends TestCase
{
    private function fakeBackend(): void
    {
        Http::fake([
            '*/periode-senat*' => Http::response([
                ['id_periode_senat' => 1, 'nama_periode' => '2025/2026'],
                ['id_periode_senat' => 2, 'nama_periode' => '2026-2029'],
            ], 200),
            '*/notulensi*' => Http::response([], 200),
            '*/rapat*' => Http::response([], 200),
            '*/user*' => Http::response([], 200),
            '*/peraturan*' => Http::response([
                [
                    'id_peraturan' => 1,
                    'id_periode_senat' => 2,
                    'nomor_sk' => 'PER-SA/01/POLINES/2026',
                    'judul' => 'Pedoman Penyelenggaraan Sidang Pleno Senat',
                    'kategori' => 'Peraturan Senat',
                    'tahap_pembentukan' => 'Disahkan',
                    'file_dokumen' => 'pedoman-sidang.pdf',
                    'tanggal_terbit' => '2026-01-10',
                    'periode_senat' => ['nama_periode' => '2026-2029'],
                ],
                [
                    'id_peraturan' => 2,
                    'id_periode_senat' => 2,
                    'nomor_sk' => 'DRAF-RPS/04/2026',
                    'judul' => 'Penyelarasan Tata Tertib Pemilihan Direktur',
                    'kategori' => 'Rancangan Peraturan',
                    'tahap_pembentukan' => 'Draf',
                    'file_dokumen' => null,
                    'tanggal_terbit' => '2026-02-01',
                    'periode_senat' => ['nama_periode' => '2026-2029'],
                ],
            ], 200),
            '*' => Http::response([], 200),
        ]);
    }

    public function test_regulasi_tab_renders_with_backend_data(): void
    {
        $this->fakeBackend();

        Livewire::withQueryParams(['tab' => 'Katalog Regulasi & Produk Hukum'])
            ->test(AdminPreview::class)
            ->assertSet('active', 'Katalog Regulasi & Produk Hukum')
            ->assertSee('Katalog Regulasi &amp; Produk Hukum Senat', false)
            ->assertSee('Sinkron Database Backend')
            ->assertSee('PER-SA/01/POLINES/2026')
            ->assertSee('DRAF-RPS/04/2026')
            ->assertSee('+ Tambah Produk Hukum');
    }

    public function test_form_open_close_and_reset(): void
    {
        $this->fakeBackend();

        Livewire::withQueryParams(['tab' => 'Katalog Regulasi & Produk Hukum'])
            ->test(AdminPreview::class)
            ->assertSet('isRegulasiFormOpen', false)
            ->call('openRegulasiForm')
            ->assertSet('isRegulasiFormOpen', true)
            ->assertSee('Tambah Produk Hukum Baru')
            ->call('closeRegulasiForm')
            ->assertSet('isRegulasiFormOpen', false)
            ->assertSet('regulasiNomor', '');
    }

    public function test_save_regulasi_posts_to_backend(): void
    {
        $this->fakeBackend();

        Livewire::withQueryParams(['tab' => 'Katalog Regulasi & Produk Hukum'])
            ->test(AdminPreview::class)
            ->call('openRegulasiForm')
            ->set('regulasiNomor', 'PER-SA/99/POLINES/2026')
            ->set('regulasiJudul', 'Peraturan Uji Coba Katalog')
            ->set('regulasiKategori', 'Peraturan Senat')
            ->set('regulasiTahap', 'Draf')
            ->set('regulasiPeriodeId', 2)
            ->set('regulasiTanggal', '2026-10-26')
            ->call('saveRegulasi')
            ->assertSet('isRegulasiFormOpen', false)
            ->assertSee('Berhasil! Produk hukum baru sukses ditambahkan', false);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/peraturan')
                && $request->method() === 'POST'
                && $request['nomor_sk'] === 'PER-SA/99/POLINES/2026';
        });
    }

    public function test_regulasi_filter_and_search(): void
    {
        $this->fakeBackend();

        Livewire::withQueryParams(['tab' => 'Katalog Regulasi & Produk Hukum'])
            ->test(AdminPreview::class)
            ->set('regulasiFilterTahap', 'Disahkan')
            ->assertSee('PER-SA/01/POLINES/2026')
            ->assertDontSee('DRAF-RPS/04/2026')
            ->set('regulasiFilterTahap', 'all')
            ->set('regulasiSearch', 'Pemilihan Direktur')
            ->assertSee('DRAF-RPS/04/2026')
            ->assertDontSee('PER-SA/01/POLINES/2026');
    }

    public function test_delete_regulasi_calls_backend(): void
    {
        $this->fakeBackend();

        Livewire::withQueryParams(['tab' => 'Katalog Regulasi & Produk Hukum'])
            ->test(AdminPreview::class)
            ->call('deleteRegulasi', 2)
            ->assertSee('berhasil dihapus dari database', false);

        Http::assertSent(fn ($request) => $request->method() === 'DELETE'
            && str_contains($request->url(), '/peraturan/2'));
    }
}
