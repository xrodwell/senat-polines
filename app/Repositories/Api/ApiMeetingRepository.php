<?php

namespace App\Repositories\Api;

use App\Contracts\MeetingRepositoryInterface;
use App\Repositories\Mock\JsonMeetingRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiMeetingRepository implements MeetingRepositoryInterface
{
    private static ?array $cachedAll = null;
    private static int $cachedAllTime = 0;
    private static ?array $cachedActive = null;
    private static int $cachedActiveTime = 0;
    private static array $cachedLive = [];
    private static array $cachedLiveTime = [];

    public function __construct(
        private JsonMeetingRepository $fallback = new JsonMeetingRepository()
    ) {}

    public static function clearCache(): void
    {
        self::$cachedAll = null;
        self::$cachedAllTime = 0;
        self::$cachedActive = null;
        self::$cachedActiveTime = 0;
        self::$cachedLive = [];
        self::$cachedLiveTime = [];
    }

    private function getBaseUrl(): string
    {
        return rtrim(env('SENAT_BACKEND_API_URL', env('BACKEND_API_URL', 'http://127.0.0.1:3000/api')), '/');
    }

    public function all(): array
    {
        if (self::$cachedAll !== null && (time() - self::$cachedAllTime) < 10) {
            return self::$cachedAll;
        }

        $apiUrl = $this->getBaseUrl();

        try {
            $response = Http::timeout(3)->get("{$apiUrl}/rapat");

            if ($response->successful()) {
                $apiData = $response->json('data', []);
                if (!empty($apiData)) {
                    $result = array_map(function ($r) {
                        return [
                            'id' => $r['id_rapat'],
                            'title' => $r['nama_agenda'],
                            'type' => $r['jenis_rapat']['nama_jenis'] ?? 'Sidang Senat',
                            'date' => $r['tanggal'] ?? date('Y-m-d'),
                            'time' => $r['waktu'] ? substr($r['waktu'], 0, 5) . ' WIB' : '09:00 WIB',
                            'room' => $r['tempat'] ?? 'Ruang Sidang Senat',
                            'status' => $r['status'] ?? 'Terjadwal',
                            'session_code' => $r['kode_sesi'] ?? 'PLN-892',
                            'qr_token' => $r['token_qr'] ?? 'token-polines',
                            'total_invited' => 34,
                            'present_count' => 0,
                            'is_presensi_open' => (bool) ($r['is_presensi_open'] ?? false),
                            'is_kuorum_locked' => (bool) ($r['is_kuorum_locked'] ?? false),
                        ];
                    }, $apiData);

                    self::$cachedAll = $result;
                    self::$cachedAllTime = time();
                    return $result;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Interoperabilitas API Rapat fallback ke local mock: ' . $e->getMessage());
        }

        return $this->fallback->all();
    }

    public function getActiveSession(bool $forceRefresh = false): ?array
    {
        if (!$forceRefresh && self::$cachedActive !== null && (time() - self::$cachedActiveTime) < 5) {
            return self::$cachedActive;
        }

        $apiUrl = $this->getBaseUrl();

        try {
            $response = Http::timeout(3)->get("{$apiUrl}/rapat/active");

            if ($response->successful()) {
                $r = $response->json('data');
                if ($r) {
                    $result = [
                        'id' => $r['id_rapat'],
                        'title' => $r['nama_agenda'],
                        'type' => $r['jenis_rapat'] ?? 'Sidang Pleno',
                        'date' => $r['tanggal'] ?? date('Y-m-d'),
                        'time' => $r['waktu'] ? substr($r['waktu'], 0, 5) . ' WIB' : '09:00 WIB',
                        'room' => $r['tempat'] ?? 'Ruang Sidang Senat',
                        'status' => $r['status'] ?? 'Sedang Berlangsung',
                        'session_code' => $r['kode_sesi'] ?? 'PLN-892',
                        'qr_token' => $r['token_qr'] ?? 'token-polines-pln-892-live',
                        'total_invited' => $r['kuorum']['total_invited'] ?? 34,
                        'present_count' => $r['kuorum']['present_count'] ?? 0,
                        'is_presensi_open' => (bool) ($r['is_presensi_open'] ?? false),
                        'is_kuorum_locked' => (bool) ($r['is_kuorum_locked'] ?? false),
                        'qr_expires_at' => $r['qr_expires_at'] ?? null,
                        'kuorum' => $r['kuorum'] ?? null,
                    ];

                    self::$cachedActive = $result;
                    self::$cachedActiveTime = time();
                    return $result;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Interoperabilitas API Active Rapat fallback ke local mock: ' . $e->getMessage());
        }

        return $this->fallback->getActiveSession();
    }

    public function getUpcoming(): array
    {
        return array_values(array_filter(
            $this->all(),
            fn (array $meeting): bool => ($meeting['status'] ?? '') === 'Terjadwal'
        ));
    }

    /**
     * Generate QR Presensi & Kode Sesi di Backend
     */
    public function generateQr(int $id): ?array
    {
        self::clearCache();
        $apiUrl = $this->getBaseUrl();
        try {
            $response = Http::timeout(5)->post("{$apiUrl}/rapat/{$id}/generate-qr");
            if ($response->successful()) {
                return $response->json('data');
            }
        } catch (\Throwable $e) {
            Log::warning("Gagal generate QR API rapat {$id}: " . $e->getMessage());
        }
        return null;
    }

    /**
     * Bekukan sesi presensi dan kunci status kuorum
     */
    public function freezePresensi(int $id): ?array
    {
        self::clearCache();
        $apiUrl = $this->getBaseUrl();
        try {
            $response = Http::timeout(5)->post("{$apiUrl}/rapat/{$id}/freeze-presensi");
            if ($response->successful()) {
                return $response->json('data');
            }
        } catch (\Throwable $e) {
            Log::warning("Gagal freeze presensi API rapat {$id}: " . $e->getMessage());
        }
        return null;
    }

    /**
     * Submit Presensi Anggota (QR Scan atau Kode Sesi)
     */
    public function submitPresensi(int $userId, ?string $tokenQr = null, ?string $sessionCode = null, ?int $meetingId = null): array
    {
        self::clearCache();
        $apiUrl = $this->getBaseUrl();
        try {
            $response = Http::timeout(5)->post("{$apiUrl}/rapat/presensi", [
                'id_user' => $userId,
                'token_qr' => $tokenQr,
                'kode_sesi' => $sessionCode,
                'id_rapat' => $meetingId,
            ]);

            return $response->json() ?: [
                'success' => false,
                'message' => 'Gagal memproses respon dari Backend API.',
            ];
        } catch (\Throwable $e) {
            Log::warning('Gagal submit presensi ke Backend: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Koneksi ke Backend API gagal: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Dapatkan daftar live attendance dan kalkulasi kuorum
     */
    public function getLiveAttendance(int $id, bool $forceRefresh = false): ?array
    {
        if (!$forceRefresh && isset(self::$cachedLive[$id]) && (time() - (self::$cachedLiveTime[$id] ?? 0)) < 4) {
            return self::$cachedLive[$id];
        }

        $apiUrl = $this->getBaseUrl();
        try {
            $response = Http::timeout(3)->get("{$apiUrl}/rapat/{$id}/live-attendance");
            if ($response->successful()) {
                $data = $response->json('data');
                if ($data) {
                    self::$cachedLive[$id] = $data;
                    self::$cachedLiveTime[$id] = time();
                    return $data;
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Gagal ambil live attendance API rapat {$id}: " . $e->getMessage());
        }
        return null;
    }

    /**
     * Validasi manual kehadiran anggota oleh admin dengan cap admin
     */
    public function validateMemberAttendance(int $id, int $userId, string $status, ?string $notes = null): ?array
    {
        if (isset(self::$cachedLive[$id])) {
            unset(self::$cachedLive[$id], self::$cachedLiveTime[$id]);
        }
        $apiUrl = $this->getBaseUrl();
        try {
            $response = Http::timeout(5)->put("{$apiUrl}/rapat/{$id}/anggota/{$userId}/validasi", [
                'status_kehadiran' => $status,
                'catatan' => $notes,
            ]);
            if ($response->successful()) {
                return $response->json('data');
            }
        } catch (\Throwable $e) {
            Log::warning("Gagal validasi manual kehadiran rapat {$id} user {$userId}: " . $e->getMessage());
        }
        return null;
    }
}
