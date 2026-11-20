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

    private function http()
    {
        $token = session('auth_token');
        $client = Http::acceptJson();
        return $token ? $client->withToken($token) : $client;
    }

    private function getBaseUrl(): string
    {
        return rtrim(env('SENAT_BACKEND_API_URL', 'http://127.0.0.1:8001/api'), '/');
    }

    public function getActive(): ?array
    {
        $apiUrl = $this->getBaseUrl();

        try {
            $response = Http::timeout(3)->acceptJson()->get("{$apiUrl}/voting?active=1");

            if ($response->successful()) {
                $data = $response->json('data') ?? $response->json();
                $items = isset($data['data']) && is_array($data['data']) ? $data['data'] : $data;

                if (is_array($items) && !empty($items)) {
                    // Jika mengembalikan 1 object langsung
                    if (isset($items['id_voting']) || isset($items['id'])) {
                        if (in_array(strtolower($items['status'] ?? ''), ['dibuka', 'aktif', 'active', 'open'], true)) {
                            return $this->formatVoting($items);
                        }
                        return null;
                    }

                    // Jika array of items
                    foreach ($items as $item) {
                        if (is_array($item) && in_array(strtolower($item['status'] ?? ''), ['dibuka', 'aktif', 'active', 'open'], true)) {
                            return $this->formatVoting($item);
                        }
                    }
                }
                return null;
            }
        } catch (\Throwable $e) {
            Log::warning('Interoperabilitas API Voting getActive fallback: ' . $e->getMessage());
        }

        return $this->fallback->getActive();
    }

    public function all(): array
    {
        $apiUrl = $this->getBaseUrl();

        try {
            $response = Http::timeout(3)->acceptJson()->get("{$apiUrl}/voting");

            if ($response->successful()) {
                $data = $response->json('data') ?? $response->json();
                $items = isset($data['data']) && is_array($data['data']) ? $data['data'] : $data;

                if (is_array($items) && !empty($items)) {
                    return array_map(fn ($item) => $this->formatVoting($item), $items);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Interoperabilitas API Voting all fallback: ' . $e->getMessage());
        }

        return $this->fallback->all();
    }

    public function castVote(int $votingId, int $optionId, ?int $userId = null): array
    {
        $apiUrl = $this->getBaseUrl();
        $userId = $userId ?? auth()->id() ?? 2;

        try {
            $response = $this->http()->timeout(3)->post("{$apiUrl}/suara-voting", [
                'id_voting' => $votingId,
                'id_opsi' => $optionId,
                'id_user' => $userId,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Suara berhasil dicatat.',
                    'data' => $response->json(),
                ];
            }

            $message = $response->json('message') ?? 'Gagal mencatat suara.';
            return [
                'success' => false,
                'message' => $message,
            ];
        } catch (\Throwable $e) {
            Log::warning('Interoperabilitas API Voting castVote fallback: ' . $e->getMessage());
        }

        return $this->fallback->castVote($votingId, $optionId, $userId);
    }

    public function updateStatus(int $votingId, string $status): array
    {
        $apiUrl = $this->getBaseUrl();
        $statusNormalized = strtolower(trim($status));

        try {
            if ($statusNormalized === 'dibuka') {
                $res = $this->http()->timeout(3)->post("{$apiUrl}/voting/{$votingId}/buka");
                if ($res->successful()) {
                    return ['success' => true, 'status' => 'dibuka', 'data' => $res->json()];
                }
            } elseif ($statusNormalized === 'ditutup') {
                $res = $this->http()->timeout(3)->post("{$apiUrl}/voting/{$votingId}/tutup");
                if ($res->successful()) {
                    return ['success' => true, 'status' => 'ditutup', 'data' => $res->json()];
                }
            }

            $putRes = $this->http()->timeout(3)->put("{$apiUrl}/voting/{$votingId}", [
                'status' => $status,
            ]);

            if ($putRes->successful()) {
                return ['success' => true, 'status' => $status, 'data' => $putRes->json()];
            }
        } catch (\Throwable $e) {
            Log::warning('Interoperabilitas API Voting updateStatus fallback: ' . $e->getMessage());
        }

        return $this->fallback->updateStatus($votingId, $status);
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
        $rapatId = $rapatRaw['id_rapat'] ?? $v['id_rapat'] ?? 1;
        $rapat = array_merge([
            'id_rapat' => $rapatId,
            'title' => $rapatRaw['nama_agenda'] ?? $rapatRaw['title'] ?? 'Sidang Pleno Senat Akademik',
            'nama_rapat' => $rapatRaw['nama_agenda'] ?? $rapatRaw['nama_rapat'] ?? 'Sidang Pleno Senat Akademik',
            'type' => 'Sidang Pleno',
            'kode' => $rapatRaw['kode_sesi'] ?? $rapatRaw['kode'] ?? ('PLN-' . str_pad((string) $rapatId, 3, '0', STR_PAD_LEFT)),
            'tanggal' => isset($rapatRaw['tanggal']) ? substr((string) $rapatRaw['tanggal'], 0, 10) : '2026-10-27',
        ], is_array($rapatRaw) ? $rapatRaw : []);

        $totalInvited = count($anggotaRapats);
        $totalHadir = collect($anggotaRapats)->filter(
            fn ($a) => strtolower((string) ($a['status_kehadiran'] ?? '')) === 'hadir' || !empty($a['waktu_presensi'])
        )->count();
        $kuorumData = $v['kuorum'] ?? [
            'hadir' => $totalHadir,
            'total' => $totalInvited,
            'terpenuhi' => $totalInvited > 0 && $totalHadir >= (int) ceil(($totalInvited * 2) / 3),
        ];

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
            $opsiFormatted = [
                ['id' => 1, 'id_opsi' => 1, 'label' => 'Setuju', 'nama_opsi' => 'Setuju', 'total' => 24, 'jumlah_suara' => 24, 'persentase' => 85.7],
                ['id' => 2, 'id_opsi' => 2, 'label' => 'Tidak Setuju', 'nama_opsi' => 'Tidak Setuju', 'total' => 2, 'jumlah_suara' => 2, 'persentase' => 7.1],
                ['id' => 3, 'id_opsi' => 3, 'label' => 'Abstain', 'nama_opsi' => 'Abstain', 'total' => 2, 'jumlah_suara' => 2, 'persentase' => 7.1],
            ];
            $totalSuara = 28;
            $hasilRekap = ['Setuju' => 24, 'Tidak Setuju' => 2, 'Abstain' => 2];
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
