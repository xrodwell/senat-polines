<?php

namespace Tests\Feature;

use App\Livewire\AdminPreview;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class AdminAspirationManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        session([
            'auth_token' => 'test-token',
            'auth_user' => ['id_user' => 1, 'role' => 'admin', 'nama' => 'Admin Senat'],
            'auth_user_id' => 1,
            'auth_user_role' => 'admin',
        ]);

        Http::preventStrayRequests();

        // Mock backend API endpoint /aspirasi
        Http::fake([
            '*/api/aspirasi?all=1' => Http::response([
                'status' => 'success',
                'data' => [
                    [
                        'id_aspirasi' => 12,
                        'id_user' => 1,
                        'id_kategori' => 4,
                        'isi_aspirasi' => "[Jalan Berlubang]\njalan di laphit berlubang\n\nPengirim: Ulfan Nayaka Dipta (ulfan.43325223@mhs.polines.ac.id) - Jurusan: Teknik Elektro",
                        'status' => 'pending',
                        'anonim' => false,
                        'tanggal_dibuat' => '2026-11-04 22:39:20',
                        'tanggapans' => [],
                    ],
                    [
                        'id_aspirasi' => 10,
                        'id_user' => 1,
                        'id_kategori' => 1,
                        'isi_aspirasi' => "[Usulan Stop Kontak]\nBanyak meja belajar kekurangan colokan.\n\nPengirim: Fadhil Pratama (fadhil@polines.ac.id) - Jurusan: Teknik Elektro",
                        'status' => 'diproses',
                        'anonim' => false,
                        'tanggal_dibuat' => '2026-11-04 21:24:14',
                        'tanggapans' => [
                            ['pesan' => 'Sudah masuk pembahasan Komisi I.'],
                        ],
                    ],
                ],
            ], 200),
            '*/api/aspirasi/12' => Http::response(['status' => 'success'], 200),
            '*/api/tanggapan-aspirasi' => Http::response(['status' => 'success'], 201),
            '*/api/komisi*' => Http::response(['data' => []], 200),
            '*/api/user*' => Http::response(['data' => []], 200),
            '*/api/*' => Http::response(['data' => []], 200),
        ]);
    }

    public function test_pusat_aspirasi_renders_and_parses_real_sender_info(): void
    {
        Livewire::withQueryParams(['tab' => 'Pusat Aspirasi & Tindak Lanjut'])
            ->test(AdminPreview::class)
            ->assertSee('Pusat Aspirasi & Disposisi Komisi', false)
            ->assertSee('ASP-POLINES-2026-0012')
            ->assertSee('Jalan Berlubang')
            ->assertSee('Ulfan Nayaka Dipta')
            ->assertSee('Teknik Elektro')
            ->assertSee('Komisi IV')
            ->assertSee('ASP-POLINES-2026-0010')
            ->assertSee('Usulan Stop Kontak')
            ->assertSee('Fadhil Pratama')
            ->assertSee('Komisi I');
    }

    public function test_filter_by_commission_and_status(): void
    {
        $test = Livewire::withQueryParams(['tab' => 'Pusat Aspirasi & Tindak Lanjut'])
            ->test(AdminPreview::class);

        // Filter Komisi I -> hanya menampilkan tiket #10
        $test->call('setFilterKomisi', '1')
            ->assertSee('ASP-POLINES-2026-0010')
            ->assertDontSee('ASP-POLINES-2026-0012');

        // Filter Komisi IV -> hanya menampilkan tiket #12
        $test->call('setFilterKomisi', '4')
            ->assertSee('ASP-POLINES-2026-0012')
            ->assertDontSee('ASP-POLINES-2026-0010');

        // Reset komisi ke 'all', filter status 'pending' -> hanya tiket #12
        $test->call('setFilterKomisi', 'all')
            ->call('setFilterStatus', 'pending')
            ->assertSee('ASP-POLINES-2026-0012')
            ->assertDontSee('ASP-POLINES-2026-0010');
    }

    public function test_admin_can_reassign_disposisi_to_another_commission(): void
    {
        Livewire::withQueryParams(['tab' => 'Pusat Aspirasi & Tindak Lanjut'])
            ->test(AdminPreview::class)
            ->call('setAspirasiDisposisi', 12, 2)
            ->assertSee('Komisi II')
            ->assertSee('Disposisi tiket #12 berhasil dialihkan ke: Komisi II');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/api/aspirasi/12') &&
                $request->method() === 'PUT' &&
                $request['id_kategori'] === 2;
        });
    }

    public function test_admin_can_submit_official_response_and_update_status(): void
    {
        Livewire::withQueryParams(['tab' => 'Pusat Aspirasi & Tindak Lanjut'])
            ->test(AdminPreview::class)
            ->call('openTanggapanForm', 12)
            ->set('tanggapanText', 'Usulan perbaikan jalan laphit telah diagendakan ke bagian sarpras.')
            ->set('tanggapanStatusChoice', 'selesai')
            ->call('submitTanggapan', 12)
            ->assertSee('Usulan perbaikan jalan laphit telah diagendakan ke bagian sarpras.')
            ->assertSee('Berhasil! Tanggapan resmi komisi telah tersimpan ke database');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/api/tanggapan-aspirasi') &&
                $request['id_aspirasi'] === 12 &&
                $request['pesan'] === 'Usulan perbaikan jalan laphit telah diagendakan ke bagian sarpras.';
        });

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/api/aspirasi/12') &&
                $request->method() === 'PUT' &&
                $request['status'] === 'selesai';
        });
    }
}
