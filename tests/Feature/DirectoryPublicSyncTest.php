<?php

namespace Tests\Feature;

use App\Support\SenatDirectory;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Memastikan direktori publik memakai sumber data yang sama dengan panel admin
 * (API backend), sehingga perubahan admin langsung tampil di halaman publik.
 */
class DirectoryPublicSyncTest extends TestCase
{
    private function fakeDirectory(): void
    {
        Http::fake([
            '*/api/committees*' => Http::response([
                'status' => 'success',
                'data' => [[
                    'id' => 1,
                    'code' => 'KOM-I',
                    'name' => 'Komisi Uji',
                    'lead' => 'Ketua Uji',
                    'secretary' => 'Belum ditentukan',
                    'focus' => 'Fokus uji',
                    'tupoksi' => 'Tupoksi uji',
                    'tupoksi_items' => [],
                    'members_count' => 2,
                    'members' => [
                        ['id_enrollment' => 1, 'nama' => 'Ketua Uji', 'nim_nip' => '111', 'jabatan' => 'Ketua', 'foto_profil' => null],
                        ['id_enrollment' => 2, 'nama' => 'Anggota Uji', 'nim_nip' => '222', 'jabatan' => 'Anggota', 'foto_profil' => null],
                    ],
                ]],
            ], 200),
            '*' => Http::response(['data' => []], 200),
        ]);
    }

    public function test_committees_are_loaded_from_backend_api_first(): void
    {
        $this->fakeDirectory();

        $committees = SenatDirectory::committees();

        $this->assertCount(1, $committees);
        $this->assertSame('Komisi Uji', $committees[0]['name']);
        $this->assertSame('KOM-I', $committees[0]['code']);
    }

    public function test_public_members_avoid_duplicates_and_empty_positions(): void
    {
        $this->fakeDirectory();

        $members = SenatDirectory::members();
        $names = array_column($members, 'name');

        $this->assertContains('Ketua Uji', $names);
        $this->assertContains('Anggota Uji', $names);

        // Ketua tidak boleh muncul ganda sebagai anggota biasa.
        $this->assertCount(1, array_keys($names, 'Ketua Uji', true));

        // Jabatan kosong tidak boleh tampil sebagai orang.
        $this->assertNotContains('Belum ditentukan', $names);
    }

    public function test_public_directory_page_renders_backend_data(): void
    {
        $this->fakeDirectory();

        $this->get('/direktori-komisi')
            ->assertOk()
            ->assertSee('Komisi Uji')
            ->assertSee('Anggota Uji');
    }

    public function test_directory_falls_back_to_mock_when_backend_is_unreachable(): void
    {
        Http::fake(['*' => Http::failedConnection()]);

        $committees = SenatDirectory::committees();

        $this->assertNotEmpty($committees);
        $this->assertContains('KOM-I', array_column($committees, 'code'));
        $this->assertContains('BP', array_column($committees, 'code'));
    }
}
