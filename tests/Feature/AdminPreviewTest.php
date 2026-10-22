<?php

namespace Tests\Feature;

use App\Livewire\AdminPreview;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPreviewTest extends TestCase
{
    public function test_admin_route_renders_preview(): void
    {
        $this->get('/admin')
            ->assertOk()
            ->assertSee('Admin Sekretariat')
            ->assertSee('Manajemen Persidangan &amp; Rapat', false)
            ->assertSee('Manajemen Publikasi &amp; Portal Web', false)
            ->assertDontSee('Preview visual')
            ->assertDontSee('Segera dihubungkan')
            ->assertDontSee('Super Admin');
    }

    public function test_secretariat_portal_redirects_to_admin_preview(): void
    {
        $this->get('/portal/sekretariat/dashboard')->assertRedirect('/admin?tab=Dashboard%20Rapat');
        $this->get('/portal/sekretariat/sidang')->assertRedirect('/admin?tab=Jadwal%20%26%20Agenda%20Sidang');
        $this->get('/portal/sekretariat/presensi')->assertRedirect('/admin?tab=Presensi%20%26%20Verifikasi%20QR');
        $this->get('/portal/sekretariat/notulensi')->assertRedirect('/admin?tab=Risalah%20%26%20Notulensi%20Sidang');
        $this->get('/portal/sekretariat/e-voting')->assertRedirect('/admin?tab=E-Voting%20%26%20Pengambilan%20Keputusan');
        $this->get('/portal/sekretariat/aspirasi')->assertRedirect('/admin?tab=Pusat%20Aspirasi%20%26%20Tindak%20Lanjut');
        $this->get('/portal/sekretariat/publikasi')->assertRedirect('/admin?tab=Manajemen%20Berita%20%26%20Siaran%20Pers');
    }

    public function test_admin_mount_respects_query_tab(): void
    {
        Livewire::withQueryParams(['tab' => 'E-Voting & Pengambilan Keputusan'])
            ->test(AdminPreview::class)
            ->assertSet('active', 'E-Voting & Pengambilan Keputusan')
            ->assertSee('Pemungutan Suara · Sidang Pleno Akreditasi Program Studi');
    }

    public function test_sidebar_has_exactly_two_groups_and_all_screens(): void
    {
        $groups = AdminPreview::GROUPS;

        $this->assertSame(['Manajemen Persidangan & Rapat', 'Manajemen Publikasi & Portal Web'], array_keys($groups));
        $this->assertCount(6, $groups['Manajemen Persidangan & Rapat']);
        $this->assertCount(7, $groups['Manajemen Publikasi & Portal Web']);

        foreach (array_merge(...array_values($groups)) as $screen) {
            Livewire::test(AdminPreview::class)->assertSee($screen);
        }
    }

    public function test_temporary_active_state_switches_screen(): void
    {
        Livewire::test(AdminPreview::class)
            ->assertSet('active', 'Dashboard Rapat')
            ->call('select', 'Jadwal & Agenda Sidang')
            ->assertSet('active', 'Jadwal & Agenda Sidang')
            ->call('select', 'Presensi & Verifikasi QR')
            ->assertSet('active', 'Presensi & Verifikasi QR')
            ->call('select', 'Kuorum & Kehadiran Anggota')
            ->assertSet('active', 'Kuorum & Kehadiran Anggota')
            ->assertSee('Status Kuorum Sidang Pleno PLN-892')
            ->call('select', 'Risalah & Notulensi Sidang')
            ->assertSet('active', 'Risalah & Notulensi Sidang')
            ->call('select', 'E-Voting & Pengambilan Keputusan')
            ->assertSet('active', 'E-Voting & Pengambilan Keputusan')
            ->call('select', 'Dashboard Rapat')
            ->assertSet('active', 'Dashboard Rapat');
    }

    public function test_unknown_screen_is_ignored(): void
    {
        Livewire::test(AdminPreview::class)
            ->call('select', 'Super Admin')
            ->assertSet('active', 'Dashboard Rapat');
    }

    public function test_public_home_renders_livewire_scripts(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('Panel Admin')
            ->assertDontSee('Peta Produk')
            ->assertDontSee('Lima Fitur Unggulan SENAT')
            ->assertSee('livewire.js')
            ->assertSee('senatChatWidget()')
            ->assertSee('style="opacity:0;visibility:hidden;pointer-events:none;"', false);
    }
}
