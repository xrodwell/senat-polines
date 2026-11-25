<?php

namespace Tests\Feature;

use App\Livewire\AdminPreview;
use App\Services\MeetingBroadcastService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class MeetingBroadcastTest extends TestCase
{
    public function test_broadcast_service_generates_exact_format(): void
    {
        $service = app(MeetingBroadcastService::class);

        $meeting = [
            'title' => 'Rapat Badan Pekerja',
            'organizer' => 'Badan Pekerja',
            'date' => '2026-11-11',
            'time' => '19.30 WIB s.d selesai',
            'room' => 'Ruang Senat Akademik',
        ];

        $recipient = [
            'nama' => 'Dr. SUKAMTO , S.Kom., M.T.',
            'nim_nip' => '197101172003121001',
        ];

        $text = $service->generateMessageText($meeting, $recipient, false);

        $this->assertStringContainsString('POLINES: Yth. *Dr. SUKAMTO , S.Kom., M.T.*', $text);
        $this->assertStringContainsString('Politeknik Negeri Semarang', $text);
        $this->assertStringContainsString('Mengharap kehadiran Bapak/Ibu/Saudara pada :', $text);
        $this->assertStringContainsString('Hari, Tanggal : Rabu, 11 November 2026', $text);
        $this->assertStringContainsString('Pukul : 19.30 WIB s.d selesai', $text);
        $this->assertStringContainsString('Tempat : Ruang Senat Akademik', $text);
        $this->assertStringContainsString('Agenda : Rapat Badan Pekerja', $text);
        $this->assertStringContainsString('Atas perhatian dan kehadirannya, kami sampaikan terima kasih.', $text);
        $this->assertStringContainsString('Ketua Badan Pekerja,', $text);
        $this->assertStringContainsString('*ANDI SETIAWAN , S.E., M.M.*', $text);
    }

    public function test_creating_meeting_sends_broadcast_email(): void
    {
        Mail::fake();
        Http::fake([
            '*/rapat' => Http::response(['data' => ['id_rapat' => 88]], 201),
            '*/rapat/*' => Http::response(['data' => ['id_rapat' => 88]], 200),
            '*' => Http::response([], 200),
        ]);

        $component = Livewire::test(AdminPreview::class)
            ->call('select', 'Jadwal & Agenda Sidang')
            ->set('newMeetingTitle', 'Sidang Penetapan Visi Misi')
            ->set('newMeetingOrganizer', 'Badan Pekerja')
            ->set('newMeetingType', 'Panitia kerja, badan pekerja')
            ->set('newMeetingDate', '2026-11-20')
            ->set('newMeetingStartTime', '09:00')
            ->set('newMeetingEndTime', '12:00')
            ->set('newMeetingUntilFinished', false)
            ->call('createNewMeeting');

        $this->assertStringContainsString('broadcast email undangan telah dikirim', $component->get('flashMessage'));

        Mail::assertSentCount(count($component->get('selectedLecturerIds')));
    }

    public function test_preview_and_manual_broadcast_email(): void
    {
        Mail::fake();
        Http::fake([
            '*' => Http::response([], 200),
        ]);

        $component = Livewire::test(AdminPreview::class)
            ->call('select', 'Jadwal & Agenda Sidang');

        $meetings = $component->get('availableMeetings');
        $this->assertNotEmpty($meetings);
        $targetId = $meetings[0]['id'] ?? 1;

        $component->call('previewBroadcast', $targetId)
            ->assertSet('broadcastModalMeetingId', (int) $targetId)
            ->assertSee('Pratinjau Broadcast Email Undangan')
            ->assertSee('POLINES: Yth.')
            ->call('sendBroadcastEmail', $targetId)
            ->assertSet('broadcastModalMeetingId', null);

        $this->assertStringContainsString('berhasil dikirim', $component->get('flashMessage'));
    }
}
