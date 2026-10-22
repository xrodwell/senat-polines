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
            ->call('select', 'Kuorum & Kehadiran Anggota')
            ->assertSet('active', 'Kuorum & Kehadiran Anggota')
            ->assertSee('Status Validasi Kuorum Sidang');
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
