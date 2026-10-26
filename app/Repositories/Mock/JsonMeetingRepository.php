<?php

namespace App\Repositories\Mock;

use App\Contracts\MeetingRepositoryInterface;

class JsonMeetingRepository implements MeetingRepositoryInterface
{
    public function all(): array
    {
        return json_decode(file_get_contents(storage_path('mock/meetings.json')), true) ?: [];
    }

    public function getActiveSession(): ?array
    {
        foreach ($this->all() as $meeting) {
            if ($meeting['status'] === 'Sedang Berlangsung') {
                return $meeting;
            }
        }

        return null;
    }

    public function getUpcoming(): array
    {
        return array_values(array_filter(
            $this->all(),
            fn (array $meeting): bool => $meeting['status'] === 'Terjadwal'
        ));
    }

    public function submitPresensi(int $userId, ?string $tokenQr = null, ?string $sessionCode = null, ?int $meetingId = null): array
    {
        return [
            'success' => true,
            'message' => 'Presensi berhasil dicatat (Mode Fallback Mock)!',
            'data' => [
                'user_nama' => 'Anggota Senat',
                'status_kehadiran' => 'hadir',
                'metode_presensi' => $tokenQr ? 'qr_scan' : 'token_input',
                'waktu_presensi' => date('H:i:s d/m/Y'),
            ],
        ];
    }

    public function getLiveAttendance(int $meetingId): ?array
    {
        $active = $this->getActiveSession();
        return [
            'id_rapat' => $meetingId,
            'nama_agenda' => $active['title'] ?? 'Sidang Senat',
            'status' => 'Sedang Berlangsung',
            'is_presensi_open' => true,
            'is_kuorum_locked' => false,
            'token_qr' => $active['qr_token'] ?? 'token-polines',
            'kode_sesi' => $active['session_code'] ?? 'PLN-892',
            'kuorum' => [
                'total_invited' => $active['total_invited'] ?? 34,
                'present_count' => $active['present_count'] ?? 23,
                'izin_count' => 3,
                'tidak_hadir_count' => 0,
                'menunggu_count' => 8,
                'ambang_kuorum' => 23,
                'present_percentage' => 67.6,
                'is_kuorum' => true,
                'status_kuorum_label' => 'Kuorum Terpenuhi',
            ],
            'anggota' => [],
        ];
    }

    public function generateQr(int $meetingId): ?array
    {
        return [
            'id_rapat' => $meetingId,
            'token_qr' => 'token-polines-mock-' . time(),
            'kode_sesi' => 'PLN-892',
            'is_presensi_open' => true,
        ];
    }

    public function freezePresensi(int $meetingId): ?array
    {
        return [
            'id_rapat' => $meetingId,
            'is_presensi_open' => false,
            'is_kuorum_locked' => true,
        ];
    }
}
