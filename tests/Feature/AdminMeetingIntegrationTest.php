<?php

namespace Tests\Feature;

use App\Contracts\MeetingRepositoryInterface;
use App\Livewire\AdminPreview;
use App\Repositories\Api\ApiMeetingRepository;
use Illuminate\Support\Facades\Http;
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
        Http::fake([
            '*/rapat' => Http::response(['data' => ['id_rapat' => 10]], 201),
            '*/rapat/*' => Http::response(['data' => ['id_rapat' => 10]], 200),
            '*' => Http::response([], 200),
        ]);

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

    public function test_can_schedule_meeting_with_custom_organizer_type_and_time_format(): void
    {
        Http::fake([
            '*/rapat' => Http::response(['data' => ['id_rapat' => 77]], 201),
            '*/rapat/*' => Http::response(['data' => ['id_rapat' => 77]], 200),
            '*' => Http::response([], 200),
        ]);

        $component = Livewire::test(AdminPreview::class)
            ->call('select', 'Jadwal & Agenda Sidang')
            ->assertSee('Penyelenggara Sidang')
            ->assertSee('Komisi 1 (Komisi Pendidikan dan Pengajaran)')
            ->assertSee('Komisi 5 (Komisi Etik)')
            ->assertSee('Badan Pekerja')
            ->assertSee('Panitia kerja, badan pekerja')
            ->set('newMeetingTitle', 'Rapat Koordinasi Kurikulum MBKM')
            ->set('newMeetingOrganizer', 'Komisi 1 (Komisi Pendidikan dan Pengajaran)')
            ->set('newMeetingType', 'Komisi')
            ->set('newMeetingDate', '2026-12-01')
            ->set('newMeetingStartTime', '08:30')
            ->set('newMeetingEndTime', '11:45')
            ->set('newMeetingUntilFinished', false)
            ->call('createNewMeeting');

        $this->assertStringContainsString('berhasil dijadwalkan', $component->get('flashMessage'));
        $this->assertSame('08:30 - 11:45 WIB', $component->instance()->formatMeetingTime('08:30', '11:45', false));
        $this->assertSame('08:30 WIB - Selesai', $component->instance()->formatMeetingTime('08:30', '11:45', true));
    }
}
