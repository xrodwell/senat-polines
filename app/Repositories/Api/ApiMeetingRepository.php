<?php

namespace App\Repositories\Api;

use App\Contracts\MeetingRepositoryInterface;
use App\Repositories\Mock\JsonMeetingRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiMeetingRepository implements MeetingRepositoryInterface
{
    public function __construct(
        private JsonMeetingRepository $fallback = new JsonMeetingRepository()
    ) {}

    public function all(): array
    {
        $apiUrl = env('SENAT_BACKEND_API_URL') ?: env('BACKEND_API_URL');
        if (empty($apiUrl)) {
            $apiUrl = 'http://127.0.0.1:8001/api';
        }
        $apiUrl = rtrim($apiUrl, '/');

        try {
            $response = Http::timeout(3)->get("{$apiUrl}/rapat");

            if ($response->successful()) {
                $items = $response->json('data', []);
                if (!empty($items)) {
                    return array_map(function ($r) {
                        $id = $r['id_rapat'] ?? 1;
                        $jenis = $r['jenis_rapat']['nama_jenis'] ?? match ((int) ($r['id_jenis_rapat'] ?? 1)) {
                            1 => 'Pleno',
                            2 => 'Terbuka',
                            3 => 'Pimpinan',
                            4 => 'Komisi',
                            5 => 'Panitia Kerja / Badan Pekerja',
                            6 => 'Rapat/Sidang Lainnya',
                            default => 'Pleno',
                        };

                        $waktuRaw = $r['waktu'] ?? '09:00:00';
                        $waktuFmt = substr($waktuRaw, 0, 5) . ' WIB';

                        return [
                            'id' => $id,
                            'title' => $r['nama_agenda'] ?? 'Agenda Sidang Senat',
                            'type' => $jenis,
                            'date' => isset($r['tanggal']) ? substr($r['tanggal'], 0, 10) : date('Y-m-d'),
                            'time' => $waktuFmt,
                            'room' => $r['tempat'] ?? 'Ruang Sidang Senat Polines',
                            'status' => $r['status'] ?? 'Terjadwal',
                            'session_code' => 'PLN-' . str_pad((string) $id, 3, '0', STR_PAD_LEFT),
                            'qr_token' => 'token-polines-session-' . $id,
                            'total_invited' => 34,
                            'present_count' => 0,
                            'id_jenis_rapat' => (int) ($r['id_jenis_rapat'] ?? 1),
                            'id_user' => (int) ($r['id_user'] ?? 1),
                        ];
                    }, $items);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Interoperabilitas API Rapat fallback ke local cache: ' . $e->getMessage());
        }

        return $this->fallback->all();
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
            fn (array $meeting): bool => in_array($meeting['status'], ['Terjadwal', 'dijadwalkan'], true)
        ));
    }
}
