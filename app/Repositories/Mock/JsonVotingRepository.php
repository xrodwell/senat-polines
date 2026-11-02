<?php

namespace App\Repositories\Mock;

use App\Contracts\VotingRepositoryInterface;

class JsonVotingRepository implements VotingRepositoryInterface
{
    public function all(): array
    {
        $mockFile = storage_path('mock/votings.json');
        if (file_exists($mockFile)) {
            $data = json_decode(file_get_contents($mockFile), true);
            if (is_array($data)) {
                return $data;
            }
        }

        return $this->defaultVotings();
    }

    public function getActive(): ?array
    {
        foreach ($this->all() as $voting) {
            if (in_array(strtolower($voting['status'] ?? ''), ['dibuka', 'aktif', 'active', 'open'], true)) {
                return $voting;
            }
        }

        $all = $this->all();
        return $all[0] ?? null;
    }

    public function castVote(int $votingId, int $optionId, ?int $userId = null): array
    {
        return [
            'success' => true,
            'message' => 'Suara berhasil dikirim (mode offline/mock).',
            'id_voting' => $votingId,
            'id_opsi' => $optionId,
            'id_user' => $userId ?? 1,
        ];
    }

    public function updateStatus(int $votingId, string $status): array
    {
        return [
            'success' => true,
            'message' => "Status voting berhasil diubah menjadi {$status} (mode offline/mock).",
            'id_voting' => $votingId,
            'status' => $status,
        ];
    }

    public function rekap(int $votingId): ?array
    {
        foreach ($this->all() as $voting) {
            if (($voting['id_voting'] ?? $voting['id'] ?? null) == $votingId) {
                return [
                    'id_voting' => $votingId,
                    'total_suara' => $voting['total_suara'] ?? 0,
                    'opsi' => $voting['opsi'] ?? [],
                    'hasil_rekap' => $voting['hasil_rekap'] ?? [],
                ];
            }
        }

        return [
            'id_voting' => $votingId,
            'total_suara' => 28,
            'opsi' => [
                ['id_opsi' => 1, 'nama_opsi' => 'Setuju', 'total' => 24, 'persentase' => 85.7],
                ['id_opsi' => 2, 'nama_opsi' => 'Tidak Setuju', 'total' => 2, 'persentase' => 7.1],
                ['id_opsi' => 3, 'nama_opsi' => 'Abstain', 'total' => 2, 'persentase' => 7.1],
            ],
            'hasil_rekap' => ['Setuju' => 24, 'Tidak Setuju' => 2, 'Abstain' => 2],
        ];
    }

    private function defaultVotings(): array
    {
        return [
            [
                'id' => 1,
                'id_voting' => 1,
                'judul' => 'Persetujuan Pengesahan Perubahan Kurikulum MBKM Vokasi 2026/2027',
                'judul_voting' => 'Persetujuan Pengesahan Perubahan Kurikulum MBKM Vokasi 2026/2027',
                'deskripsi' => 'Pengambilan keputusan persetujuan atas revisi kurikulum vokasi berbasis MBKM tahun akademik 2026/2027 untuk seluruh program studi Diploma dan Sarjana Terapan di lingkungan Polines.',
                'status' => 'dibuka',
                'waktu_mulai' => '2026-10-27 09:00:00',
                'waktu_selesai' => null,
                'rapat' => [
                    'id_rapat' => 1,
                    'nama_rapat' => 'Sidang Pleno Kurikulum MBKM',
                    'title' => 'Sidang Pleno Kurikulum MBKM',
                    'type' => 'Sidang Pleno',
                    'kode' => 'PLN-892',
                ],
                'opsi' => [
                    ['id' => 1, 'id_opsi' => 1, 'label' => 'Setuju', 'nama_opsi' => 'Setuju', 'total' => 24, 'persentase' => 85.7],
                    ['id' => 2, 'id_opsi' => 2, 'label' => 'Tidak Setuju', 'nama_opsi' => 'Tidak Setuju', 'total' => 2, 'persentase' => 7.1],
                    ['id' => 3, 'id_opsi' => 3, 'label' => 'Abstain', 'nama_opsi' => 'Abstain', 'total' => 2, 'persentase' => 7.1],
                ],
                'total_suara' => 28,
                'kuorum' => [
                    'hadir' => 28,
                    'total' => 34,
                    'tercapai' => true,
                ],
                'hasil_rekap' => [
                    'Setuju' => 24,
                    'Tidak Setuju' => 2,
                    'Abstain' => 2,
                ],
            ],
        ];
    }
}
