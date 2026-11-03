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
            if (is_array($data) && !empty($data)) {
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

        return null;
    }

    public function castVote(int $votingId, int $optionId, ?int $userId = null): array
    {
        return [
            'success' => true,
            'message' => 'Suara berhasil dikirim (mode offline/mock).',
            'id_voting' => $votingId,
            'id_opsi' => $optionId,
            'id_user' => $userId ?? 2,
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

        return null;
    }

    public function defaultVotings(): array
    {
        return [
            [
                'id' => 1,
                'id_voting' => 1,
                'judul' => 'Persetujuan Pengesahan Perubahan Kurikulum MBKM Vokasi 2026/2027',
                'judul_voting' => 'Persetujuan Pengesahan Perubahan Kurikulum MBKM Vokasi 2026/2027',
                'deskripsi' => 'Pengambilan keputusan persetujuan atas revisi kurikulum vokasi berbasis MBKM tahun akademik 2026/2027 untuk seluruh program studi Diploma dan Sarjana Terapan di lingkungan Polines.',
                'status' => 'ditutup',
                'waktu_mulai' => '2026-10-27 09:00:00',
                'waktu_selesai' => '2026-10-27 10:30:00',
                'rapat' => [
                    'id_rapat' => 1,
                    'nama_rapat' => 'Sidang Pleno Kurikulum MBKM',
                    'title' => 'Sidang Pleno Kurikulum MBKM',
                    'type' => 'Sidang Pleno',
                    'kode' => 'PLN-892',
                    'tanggal' => '2026-10-27',
                    'anggota_rapats' => [
                        ['id_user' => 2, 'status_kehadiran' => 'hadir', 'waktu_presensi' => '2026-10-27 08:45:00'],
                    ],
                ],
                'opsi' => [
                    ['id' => 1, 'id_opsi' => 1, 'label' => 'Setuju', 'nama_opsi' => 'Setuju', 'total' => 24, 'persentase' => 85.7],
                    ['id' => 2, 'id_opsi' => 2, 'label' => 'Tidak Setuju', 'nama_opsi' => 'Tidak Setuju', 'total' => 2, 'persentase' => 7.1],
                    ['id' => 3, 'id_opsi' => 3, 'label' => 'Abstain', 'nama_opsi' => 'Abstain', 'total' => 2, 'persentase' => 7.1],
                ],
                'suara_votings' => [
                    ['id_user' => 2, 'id_opsi' => 1, 'waktu_vote' => '2026-10-27 09:15:20'],
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
            [
                'id' => 2,
                'id_voting' => 2,
                'judul' => 'Pertimbangan Pemberian Gelar Profesor Kehormatan Bidang Mekatronika Cerdas',
                'judul_voting' => 'Pertimbangan Pemberian Gelar Profesor Kehormatan Bidang Mekatronika Cerdas',
                'deskripsi' => 'Pemberian pertimbangan dan rekomendasi Senat Akademik atas berkas usulan Guru Besar Kehormatan pada Jurusan Teknik Mesin dan Elektro.',
                'status' => 'ditutup',
                'waktu_mulai' => '2026-10-15 13:30:00',
                'waktu_selesai' => '2026-10-15 15:00:00',
                'rapat' => [
                    'id_rapat' => 2,
                    'nama_rapat' => 'Sidang Khusus Komisi II Ketenagaan & Guru Besar',
                    'title' => 'Sidang Khusus Komisi II Ketenagaan & Guru Besar',
                    'type' => 'Sidang Komisi',
                    'kode' => 'KMS-042',
                    'tanggal' => '2026-10-15',
                    'anggota_rapats' => [
                        ['id_user' => 99, 'status_kehadiran' => 'hadir'],
                    ],
                ],
                'opsi' => [
                    ['id' => 1, 'id_opsi' => 1, 'label' => 'Setuju', 'nama_opsi' => 'Setuju', 'total' => 14, 'persentase' => 93.3],
                    ['id' => 2, 'id_opsi' => 2, 'label' => 'Tidak Setuju', 'nama_opsi' => 'Tidak Setuju', 'total' => 1, 'persentase' => 6.7],
                    ['id' => 3, 'id_opsi' => 3, 'label' => 'Abstain', 'nama_opsi' => 'Abstain', 'total' => 0, 'persentase' => 0.0],
                ],
                'suara_votings' => [],
                'total_suara' => 15,
                'kuorum' => [
                    'hadir' => 15,
                    'total' => 16,
                    'tercapai' => true,
                ],
                'hasil_rekap' => [
                    'Setuju' => 14,
                    'Tidak Setuju' => 1,
                    'Abstain' => 0,
                ],
            ],
            [
                'id' => 3,
                'id_voting' => 3,
                'judul' => 'Persetujuan Usulan Pembukaan Program Studi Magister Terapan Rekayasa Keamanan Siber',
                'judul_voting' => 'Persetujuan Usulan Pembukaan Program Studi Magister Terapan Rekayasa Keamanan Siber',
                'deskripsi' => 'Pengambilan keputusan kelayakan akademis, sarana prasarana, dan persetujuan pengusulan prodi baru S2 Terapan ke kementerian.',
                'status' => 'ditutup',
                'waktu_mulai' => '2026-09-28 10:00:00',
                'waktu_selesai' => '2026-09-28 11:45:00',
                'rapat' => [
                    'id_rapat' => 3,
                    'nama_rapat' => 'Sidang Pleno Pembukaan Prodi Baru',
                    'title' => 'Sidang Pleno Pembukaan Prodi Baru',
                    'type' => 'Sidang Pleno',
                    'kode' => 'PLN-875',
                    'tanggal' => '2026-09-28',
                    'anggota_rapats' => [
                        ['id_user' => 2, 'status_kehadiran' => 'hadir', 'waktu_presensi' => '2026-09-28 09:50:00'],
                    ],
                ],
                'opsi' => [
                    ['id' => 1, 'id_opsi' => 1, 'label' => 'Setuju', 'nama_opsi' => 'Setuju', 'total' => 29, 'persentase' => 90.6],
                    ['id' => 2, 'id_opsi' => 2, 'label' => 'Tidak Setuju', 'nama_opsi' => 'Tidak Setuju', 'total' => 2, 'persentase' => 6.3],
                    ['id' => 3, 'id_opsi' => 3, 'label' => 'Abstain', 'nama_opsi' => 'Abstain', 'total' => 1, 'persentase' => 3.1],
                ],
                'suara_votings' => [],
                'total_suara' => 32,
                'kuorum' => [
                    'hadir' => 32,
                    'total' => 34,
                    'tercapai' => true,
                ],
                'hasil_rekap' => [
                    'Setuju' => 29,
                    'Tidak Setuju' => 2,
                    'Abstain' => 1,
                ],
            ],
        ];
    }
}
