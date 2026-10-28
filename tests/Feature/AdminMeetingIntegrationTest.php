<?php

namespace Tests\Feature;

use App\Contracts\MeetingRepositoryInterface;
use App\Livewire\AdminPreview;
use App\Repositories\Api\ApiMeetingRepository;
use Livewire\Livewire;
use Tests\TestCase;

class AdminMeetingIntegrationTest extends TestCase
{
    public function test_meeting_repository_is_bound_to_api_repository(): void
    {
        $repo = app(MeetingRepositoryInterface::class);
        $this->assertInstanceOf(ApiMeetingRepository::class, $repo);
    }

    public function test_jadwal_agenda_sidang_tab_renders_with_dynamic_data(): void
    {
        Livewire::withQueryParams(['tab' => 'Jadwal & Agenda Sidang'])
            ->test(AdminPreview::class)
            ->assertSet('active', 'Jadwal & Agenda Sidang')
            ->assertSee('Buat Sidang Baru')
            ->assertSee('Sidang Terjadwal')
            ->assertSee('Data dinamis dari Backend API port 8001');
    }

    public function test_can_save_and_manage_meeting_via_backend(): void
    {
        $component = Livewire::test(AdminPreview::class)
            ->call('select', 'Jadwal & Agenda Sidang')
            ->set('meetingTitle', 'Sidang Validasi Otomatis Test')
            ->set('meetingJenisRapatId', 1)
            ->set('meetingDateTime', date('Y-m-d\T10:00'))
            ->set('meetingRoom', 'Ruang Telekonferensi Senat')
            ->call('saveMeeting', 'Terjadwal');

        $this->assertStringContainsString('Berhasil', $component->get('flashMessage'));
        $this->assertNull($component->get('editingMeetingId'));
    }
}
