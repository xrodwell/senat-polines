<?php

namespace App\Repositories\Api;

use App\Contracts\VotingRepositoryInterface;
use App\Repositories\Mock\JsonVotingRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiVotingRepository implements VotingRepositoryInterface
{
    public function __construct(
        private JsonVotingRepository $fallback = new JsonVotingRepository()
    ) {}

    private bool $available = true;

    private ?string $lastError = null;

    private function http()
    {
        $token = session('auth_token');
        $client = Http::acceptJson();
        return $token ? $client->withToken($token) : $client;
    }

    private function getBaseUrl(): string
    {
        $configured = config('services.voting.url');

        if (is_string($configured) && trim($configured) !== '') {
            return rtrim(trim($configured), '/');
        }

        $backend = config('services.backend_api.url') ?: config('services.backend.url');

        if (is_string($backend) && trim($backend) !== '') {
            return rtrim(trim($backend), '/') . '/api';
        }

        return 'http://127.0.0.1:8001/api';
    }

    public function myBallot(int $votingId): ?array
    {
        $token = session('auth_token');
        if (! $token) {
            return null;
        }
        try {
            $response = $this->http()->withToken($token)->timeout(3)
                ->get("{$this->getBaseUrl()}/voting/{$votingId}/my-ballot");
            if ($response->successful()) {
                $this->available = true;
                return $response->json();
            }
            $this->markUnavailable('HTTP ' . $response->status());
        } catch (\Throwable $e) {
            $this->markUnavailable($e->getMessage());
            Log::warning("Gagal memuat my-ballot {$votingId}: " . $e->getMessage());
        }
        return null;
    }

    public function isAvailable(): bool
    {
        return $this->available;
    }

    public function lastError(): ?string
    {
        return $this->lastError;
    }

    private function markAvailable(): void
    {
        $this->available = true;
        $this->lastError = null;
    }

    private function markUnavailable(string $message): void
    {
        $this->available = false;
        $this->lastError = $message;
    }

    /**
     * Fetch the whole voting list from the backend in a single bounded request.
     *
     * Returns null when the payload cannot be trusted as a complete list
     * (non-success status, malformed body, non-list data, null entries or
     * detected pagination/truncation). Availability and lastError are updated.
     *
     * @return array<int, array>|null
     */
    private function fetchList(): ?array
    {
        $apiUrl = $this->getBaseUrl();

        try {
            $response = Http::timeout(3)->acceptJson()->get("{$apiUrl}/voting?all=1");
        } catch (\Throwable $e) {
            // Exception text may contain the URL, a bearer token or credentials.
            Log::warning('API Voting list request failed.');
            $this->markUnavailable('Backend voting tidak dapat dihubungi.');

            return null;
        }

        if (! $response->successful()) {
            Log::warning('API Voting list returned HTTP ' . $response->status() . '.');
            $this->markUnavailable('Backend voting mengembalikan status ' . $response->status() . '.');

            return null;
        }

        $payload = $response->json();

        if (! is_array($payload)) {
            $this->markUnavailable('Respons backend voting tidak valid.');

            return null;
        }

        if ($this->looksPaginated($payload)) {
            $this->markUnavailable('Daftar voting dari backend tidak lengkap (terindikasi terpotong).');

            return null;
        }

        $items = $payload;

        if (array_key_exists('data', $payload)) {
            if (! is_array($payload['data']) || ! array_is_list($payload['data'])) {
                $this->markUnavailable('Data daftar voting dari backend tidak valid.');

                return null;
            }

            $items = $payload['data'];
        } elseif (! array_is_list($payload)) {
            $this->markUnavailable('Data daftar voting dari backend tidak valid.');

            return null;
        }

        foreach ($items as $item) {
            if (! is_array($item)) {
                $this->markUnavailable('Daftar voting dari backend memuat entri tidak valid.');

                return null;
            }
        }

        $this->markAvailable();

        return $items;
    }

    private function looksPaginated(array $payload): bool
    {
        $paginatedKeys = [
            'next_page_url', 'prev_page_url', 'first_page_url', 'last_page_url',
            'last_page', 'current_page', 'per_page', 'links', 'next_cursor', 'nextCursor',
        ];

        foreach ($paginatedKeys as $key) {
            if (array_key_exists($key, $payload) && $payload[$key] !== null && $payload[$key] !== []) {
                return true;
            }
        }

        if (isset($payload['meta']) && is_array($payload['meta'])) {
            $meta = $payload['meta'];

            if (isset($meta['last_page']) && (int) $meta['last_page'] > 1) {
                return true;
            }

            if (isset($meta['current_page']) && (int) $meta['current_page'] < (int) ($meta['last_page'] ?? $meta['current_page'])) {
                return true;
            }

            foreach (['next_page_url', 'next_cursor', 'total', 'links'] as $key) {
                if (array_key_exists($key, $meta) && $meta[$key] !== null && $meta[$key] !== []) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getActive(): ?array
    {
        $items = $this->fetchList();

        if ($items === null) {
            return null;
        }

        $openStatuses = ['dibuka', 'aktif', 'active', 'open'];

        foreach ($items as $item) {
            if (in_array(strtolower((string) ($item['status'] ?? '')), $openStatuses, true)) {
                return $this->formatVoting($item);
            }
        }

        return null;
    }

    public function all(): array
    {
        $items = $this->fetchList();

        if ($items === null) {
            return [];
        }

        return array_map(fn ($item) => $this->formatVoting($item), $items);
    }

    public function castVote(int $votingId, int $optionId, ?int $userId = null): array
    {
        $apiUrl = $this->getBaseUrl();

        // Identity travels in the bearer token; without one we refuse rather
        // than inventing a user. The body never carries an id_user.
        if (! session('auth_token')) {
            return [
                'success' => false,
                'message' => 'Sesi tidak valid. Silakan login ulang.',
            ];
        }

        try {
            $response = $this->http()->timeout(3)->post("{$apiUrl}/suara-voting", [
                'id_voting' => $votingId,
                'id_opsi' => $optionId,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Suara berhasil dicatat.',
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => $response->json('message') ?? 'Gagal mencatat suara.',
            ];
        } catch (\Throwable $e) {
            Log::warning('API Voting castVote tidak dapat menjangkau backend.');

            return [
                'success' => false,
                'message' => 'Backend tidak terjangkau',
            ];
        }
    }

    public function updateStatus(int $votingId, string $status): array
    {
        $apiUrl = $this->getBaseUrl();
        $statusNormalized = strtolower(trim($status));

        // Only the explicit open/close endpoints are valid; a raw status PUT
        // is forbidden so unsupported values are rejected outright.
        $action = match ($statusNormalized) {
            'dibuka' => 'buka',
            'ditutup' => 'tutup',
            default => null,
        };

        if ($action === null) {
            return [
                'success' => false,
                'message' => "Status '{$status}' tidak didukung.",
            ];
        }

        try {
            $res = $this->http()->timeout(3)->post("{$apiUrl}/voting/{$votingId}/{$action}");

            if ($res->successful()) {
                return ['success' => true, 'status' => $statusNormalized, 'data' => $res->json()];
            }

            return [
                'success' => false,
                'message' => $res->json('message') ?? 'Gagal mengubah status voting.',
            ];
        } catch (\Throwable $e) {
            Log::warning('API Voting updateStatus tidak dapat menjangkau backend.');

            return ['success' => false, 'message' => 'Backend tidak terjangkau'];
        }
    }

    public function rekap(int $votingId): ?array
    {
        $apiUrl = $this->getBaseUrl();

        try {
            $response = $this->http()->timeout(3)->get("{$apiUrl}/voting/{$votingId}/rekap");

            if ($response->successful()) {
                return $response->json('data') ?? $response->json();
            }
        } catch (\Throwable $e) {
            Log::warning('Interoperabilitas API Voting rekap fallback: ' . $e->getMessage());
        }

        return $this->fallback->rekap($votingId);
    }

    private function formatVoting(array $v): array
    {
        $id = $v['id_voting'] ?? $v['id'] ?? null;
        $judul = $v['judul_voting'] ?? $v['judul'] ?? $v['title'] ?? 'Voting Senat';
        $deskripsi = $v['deskripsi'] ?? $v['description'] ?? '';
        $status = strtolower($v['status'] ?? 'draft');
        $waktuMulai = $v['waktu_mulai'] ?? null;
        $waktuSelesai = $v['waktu_selesai'] ?? null;

        $rapatRaw = $v['rapat'] ?? [];
        $anggotaRapats = is_array($rapatRaw['anggota_rapats'] ?? $rapatRaw['anggotaRapats'] ?? null)
            ? ($rapatRaw['anggota_rapats'] ?? $rapatRaw['anggotaRapats'])
            : [];
        $rapatId = $rapatRaw['id_rapat'] ?? $v['id_rapat'] ?? null;
        $rapat = array_merge([
            'id_rapat' => $rapatId,
            'title' => $rapatRaw['nama_agenda'] ?? $rapatRaw['title'] ?? '',
            'nama_rapat' => $rapatRaw['nama_agenda'] ?? $rapatRaw['nama_rapat'] ?? '',
            'type' => $rapatRaw['type'] ?? '',
            'kode' => $rapatRaw['kode_sesi'] ?? $rapatRaw['kode'] ?? '',
            'tanggal' => isset($rapatRaw['tanggal']) ? substr((string) $rapatRaw['tanggal'], 0, 10) : null,
        ], is_array($rapatRaw) ? $rapatRaw : []);

        // Kuorum hanya dari payload backend; tanpa data jangan mengarang angka/angka 2/3 lokal.
        // Normalisasi kunci kanonik backend (total_invited/present_count/is_kuorum)
        // ke kontrak view (total/hadir/tercapai).
        $kuorumData = null;
        $k = $v['kuorum'] ?? null;
        if (is_array($k)) {
            $kuorumData = [
                'total' => (int) ($k['total_invited'] ?? $k['total_undangan'] ?? $k['total'] ?? 0),
                'hadir' => (int) ($k['present_count'] ?? $k['total_hadir'] ?? $k['hadir'] ?? 0),
                'tercapai' => (bool) ($k['is_kuorum'] ?? $k['kuorum_terpenuhi'] ?? $k['tercapai'] ?? false),
                'persentase' => $k['present_percentage'] ?? $k['persentase_kehadiran'] ?? null,
                'ambang' => $k['ambang_kuorum'] ?? null,
            ];
        }

        $suaraVotings = $v['suara_votings'] ?? $v['suaraVotings'] ?? [];

        $opsiRaw = $v['opsi_votings'] ?? $v['opsiVotings'] ?? $v['opsi'] ?? [];
        $totalSuara = (int) ($v['total_suara'] ?? 0);
        $opsiFormatted = [];
        $hasilRekap = $v['hasil_rekap'] ?? [];

        if (!empty($opsiRaw) && is_array($opsiRaw)) {
            $sumSuara = 0;
            foreach ($opsiRaw as $opt) {
                $suaraCount = (int) ($opt['jumlah_suara'] ?? $opt['total'] ?? (isset($opt['suara_votings']) && is_array($opt['suara_votings']) ? count($opt['suara_votings']) : 0));
                $sumSuara += $suaraCount;
            }
            if ($totalSuara === 0 && $sumSuara > 0) {
                $totalSuara = $sumSuara;
            }

            foreach ($opsiRaw as $opt) {
                $optId = $opt['id_opsi'] ?? $opt['id'] ?? null;
                $namaOpsi = $opt['nama_opsi'] ?? $opt['label'] ?? '';
                $suara = (int) ($opt['jumlah_suara'] ?? $opt['total'] ?? (isset($opt['suara_votings']) && is_array($opt['suara_votings']) ? count($opt['suara_votings']) : 0));
                $persen = $totalSuara > 0 ? round(($suara / $totalSuara) * 100, 1) : 0;
                if (isset($opt['persentase'])) {
                    $persen = (float) $opt['persentase'];
                }

                $opsiFormatted[] = [
                    'id' => $optId,
                    'id_opsi' => $optId,
                    'label' => $namaOpsi,
                    'nama_opsi' => $namaOpsi,
                    'total' => $suara,
                    'suara' => $suara,
                    'jumlah_suara' => $suara,
                    'persentase' => $persen,
                ];

                if ($namaOpsi !== '') {
                    $hasilRekap[$namaOpsi] = $suara;
                }
            }
        } else {
            // Tanpa opsi dari backend, tampilkan kosong; bukan angka demo.
            $opsiFormatted = [];
            $hasilRekap = [];
        }

        return [
            'id' => $id,
            'id_voting' => $id,
            'judul' => $judul,
            'judul_voting' => $judul,
            'title' => $judul,
            'deskripsi' => $deskripsi,
            'status' => $status,
            'waktu_mulai' => $waktuMulai,
            'waktu_selesai' => $waktuSelesai,
            'rapat' => $rapat,
            'kuorum' => $kuorumData,
            'opsi' => $opsiFormatted,
            'total_suara' => $totalSuara,
            'hasil_rekap' => $hasilRekap,
            'suara_votings' => $suaraVotings,
        ];
    }
}
