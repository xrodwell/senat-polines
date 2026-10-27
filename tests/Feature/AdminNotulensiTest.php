<?php

namespace Tests\Feature;

use App\Livewire\AdminPreview;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class AdminNotulensiTest extends TestCase
{
    public function test_risalah_notulensi_tab_renders_properly(): void
    {
        Livewire::withQueryParams(['tab' => 'Risalah & Notulensi Sidang'])
            ->test(AdminPreview::class)
            ->assertSet('active', 'Risalah & Notulensi Sidang')
            ->assertSee('Risalah &amp; Notulensi Sidang Senat Akademik', false)
            ->assertSee('API Backend Aktif')
            ->assertSee('+ Catat Notulensi Baru');
    }

    public function test_can_open_and_close_notulensi_form(): void
    {
        Livewire::withQueryParams(['tab' => 'Risalah & Notulensi Sidang'])
            ->test(AdminPreview::class)
            ->assertSet('isFormOpen', false)
            ->call('openNotulensiForm')
            ->assertSet('isFormOpen', true)
            ->assertSee('Pencatatan Notulensi Sidang Baru')
            ->call('closeNotulensiForm')
            ->assertSet('isFormOpen', false);
    }

    public function test_can_save_notulensi_via_livewire(): void
    {
        Http::fake([
            '*/notulensi' => Http::response([
                'id_notulensi' => 99,
                'id_rapat' => 1,
                'id_user' => 1,
                'isi_notulensi' => 'Testing catatan pembahasan sidang',
                'file_risalah' => 'test-file.pdf',
                'status' => 'draf',
            ], 201),
            '*/notulensi?all=1' => Http::response([
                [
                    'id_notulensi' => 99,
                    'id_rapat' => 1,
                    'id_user' => 1,
                    'isi_notulensi' => 'Testing catatan pembahasan sidang',
                    'file_risalah' => 'test-file.pdf',
                    'status' => 'draf',
                    'rapat' => ['nama_agenda' => 'Sidang Pleno Uji Coba', 'tempat' => 'Ruang 101'],
                    'user' => ['nama' => 'Tester User'],
                ],
            ], 200),
            '*' => Http::response([], 200),
        ]);

        Livewire::withQueryParams(['tab' => 'Risalah & Notulensi Sidang'])
            ->test(AdminPreview::class)
            ->call('openNotulensiForm')
            ->set('notulensiRapatId', 1)
            ->set('notulensiUserId', 1)
            ->set('notulensiIsi', 'Testing catatan pembahasan sidang')
            ->set('notulensiFile', 'test-file.pdf')
            ->set('notulensiStatus', 'draf')
            ->call('saveNotulensi')
            ->assertSet('isFormOpen', false)
            ->assertSee('Berhasil! Notulensi &amp; Risalah sidang baru sukses disimpan', false);
    }

    public function test_filtering_and_searching_notulensi(): void
    {
        Livewire::withQueryParams(['tab' => 'Risalah & Notulensi Sidang'])
            ->test(AdminPreview::class)
            ->set('notulensiList', [
                [
                    'id_notulensi' => 1,
                    'status' => 'disahkan',
                    'isi_notulensi' => 'Catatan sidang anggaran disahkan',
                    'rapat' => ['nama_agenda' => 'Sidang Anggaran 2026', 'tempat' => 'Ruang A'],
                    'user' => ['nama' => 'Budi'],
                ],
                [
                    'id_notulensi' => 2,
                    'status' => 'review',
                    'isi_notulensi' => 'Catatan sidang kurikulum dalam telaah',
                    'rapat' => ['nama_agenda' => 'Sidang Kurikulum MBKM', 'tempat' => 'Ruang B'],
                    'user' => ['nama' => 'Siti'],
                ],
            ])
            ->set('notulensiFilterStatus', 'disahkan')
            ->assertSee('Sidang Anggaran 2026')
            ->assertDontSee('Sidang Kurikulum MBKM')
            ->set('notulensiFilterStatus', 'all')
            ->set('notulensiSearch', 'Kurikulum')
            ->assertSee('Sidang Kurikulum MBKM')
            ->assertDontSee('Sidang Anggaran 2026');
    }
}
