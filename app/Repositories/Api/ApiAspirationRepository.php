<?php

namespace App\Repositories\Api;

use App\Contracts\AspirationRepositoryInterface;
use App\Repositories\Mock\JsonAspirationRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ApiAspirationRepository implements AspirationRepositoryInterface
{
    public function __construct(
        private JsonAspirationRepository $fallback = new JsonAspirationRepository()
    ) {}

    private function getBackendUrl(): string
    {
        return rtrim(env('SENAT_BACKEND_API_URL', env('BACKEND_API_URL', 'http://127.0.0.1:8001/api')), '/');
    }

    public function all(): array
    {
        $base = $this->getBackendUrl();

        try {
            $response = Http::timeout(3)->get("{$base}/aspirasi");

            if ($response->successful()) {
                $items = $response->json('data', []);
                if (!empty($items)) {
                    return array_map([$this, 'transformItem'], $items);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Interoperabilitas API Aspirasi fallback ke local mock: ' . $e->getMessage());
        }

        return $this->fallback->all();
    }

    public function findByTicket(string $ticket): ?array
    {
        $ticket = trim($ticket);
        if ($ticket === '') {
            return null;
        }

        // 1. Coba cari di semua data hasil transform dari API
        $all = $this->all();
        foreach ($all as $item) {
            if (strcasecmp($item['ticket_code'], $ticket) === 0) {
                return $item;
            }
        }

        // 2. Parse ID numerik dari format kode tiket (misal: "ASP-0001", "ASP-POLINES-2026-0001", "1")
        $numericId = null;
        if (preg_match('/(\d+)/', $ticket, $matches)) {
            $numericId = (int) $matches[1];
        }

        if ($numericId) {
            foreach ($all as $item) {
                if ((int) $item['id'] === $numericId) {
                    return $item;
                }
            }

            // Coba tembak langsung endpoint single /aspirasi/{id}
            try {
                $base = $this->getBackendUrl();
                $response = Http::timeout(3)->get("{$base}/aspirasi/{$numericId}");
                if ($response->successful()) {
                    $item = $response->json('data') ?? $response->json();
                    if (!empty($item) && isset($item['id_aspirasi'])) {
                        return $this->transformItem($item);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("Gagal fetch single aspirasi ID {$numericId}: " . $e->getMessage());
            }
        }

        // 3. Fallback ke mock data lokal jika di API tidak ditemukan
        return $this->fallback->findByTicket($ticket);
    }

    private function transformItem(array $item): array
    {
        $id = $item['id_aspirasi'] ?? 1;
        $tanggapans = $item['tanggapans'] ?? [];
        $lastTanggapan = !empty($tanggapans) ? end($tanggapans)['pesan'] : 'Belum ada tanggapan resmi dari komisi.';

        $statusRaw = strtolower($item['status'] ?? 'pending');
        $statusFormatted = match ($statusRaw) {
            'selesai' => 'Ditindaklanjuti Komisi',
            'diproses' => 'Sedang Ditelaah',
            default => 'Menunggu Verifikasi',
        };

        $kategoriNama = $item['kategori']['nama_kategori'] ?? 'Umum';
        $committeeTarget = match ($kategoriNama) {
            'Akademik' => 'Komisi I – Bidang Pendidikan & Pengajaran',
            'Fasilitas' => 'Komisi II – Bidang Sarana & Prasarana',
            'Kemahasiswaan' => 'Komisi III – Bidang Kemahasiswaan & Kerjasama',
            default => 'Komisi I – Bidang Pendidikan & Pengajaran',
        };

        $subject = Str::limit($item['isi_aspirasi'] ?? '', 75);
        if (preg_match('/^\[(.*?)\]\s*(.*)$/s', $item['isi_aspirasi'] ?? '', $matches)) {
            $subject = $matches[1];
        }

        return [
            'id' => $id,
            'ticket_code' => 'ASP-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT),
            'sender_category' => !empty($item['anonim']) ? 'Mahasiswa (Anonim)' : ($item['user']['nama'] ?? 'Mahasiswa'),
            'jurusan' => $kategoriNama,
            'committee_target' => $committeeTarget,
            'subject' => $subject,
            'description' => $item['isi_aspirasi'] ?? '',
            'status' => $statusFormatted,
            'raw_status' => $statusRaw,
            'submitted_at' => substr($item['tanggal_dibuat'] ?? $item['created_at'] ?? date('Y-m-d H:i'), 0, 16),
            'response' => $lastTanggapan,
        ];
    }
}
