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
            ->assertSee('Bagian Rapat')
            ->assertSee('Bagian Urus Website')
            ->assertSee('Preview visual')
            ->assertSee('Segera dihubungkan')
            ->assertDontSee('Super Admin');
    }

    public function test_sidebar_has_exactly_two_groups_and_all_screens(): void
    {
        $groups = AdminPreview::GROUPS;

        $this->assertSame(['Bagian Rapat', 'Bagian Urus Website'], array_keys($groups));
        $this->assertCount(7, $groups['Bagian Rapat']);
        $this->assertCount(9, $groups['Bagian Urus Website']);

        foreach (array_merge(...array_values($groups)) as $screen) {
            Livewire::test(AdminPreview::class)->assertSee($screen);
        }
    }

    public function test_temporary_active_state_switches_screen(): void
    {
        Livewire::test(AdminPreview::class)
            ->assertSet('active', 'Dashboard')
            ->call('select', 'Kuorum')
            ->assertSet('active', 'Kuorum')
            ->assertSee('Kuorum');
    }

    public function test_unknown_screen_is_ignored(): void
    {
        Livewire::test(AdminPreview::class)
            ->call('select', 'Super Admin')
            ->assertSet('active', 'Dashboard');
    }

    public function test_public_home_renders_admin_link_and_livewire_scripts(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Panel Admin')
            ->assertSee('livewire.js')
            ->assertSee('senatChatWidget()')
            ->assertSee('style="opacity:0;visibility:hidden;pointer-events:none;"', false);
    }
}
