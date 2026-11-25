<?php

namespace App\Http\Controllers\Portal\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

trait HasVotingAndAttendance
{
    /**
     * Submit Presensi Anggota (QR Scan atau Kode Sesi)
     */
    public function submitPresensi(Request $request): JsonResponse
    {
        // Identitas hanya dari sesi/token terautentikasi; body tidak boleh
        // menentukan siapa yang presensi (tanpa fallback id hardcoded).
        $userId = (int) (auth()->id() ?: session('auth_user_id'));
        if ($userId <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi Anda tidak valid. Silakan login ulang.',
            ], 401);
        }
        $tokenQr = $request->input('token_qr');
        $sessionCode = $request->input('kode_sesi');
        $meetingId = $request->input('id_rapat');

        $result = $this->meetings->submitPresensi($userId, $tokenQr, $sessionCode, $meetingId ? (int) $meetingId : null);

        $status = ($result['success'] ?? false) ? 200 : 422;
        return response()->json($result, $status);
    }

    /**
     * 12D - E-Voting
     */
    public function voting(): View
    {
        $userId = (int) (auth()->id() ?: session('auth_user_id'));
        $activeVoting = $this->votings->getActive();
        $allVotings = $this->votings->all();

        // Partisipasi pada sesi aktif hanya dari endpoint my-ballot milik
        // user terautentikasi; payload publik tidak lagi memuat ballot.
        if ($activeVoting) {
            $activeId = (int) ($activeVoting['id_voting'] ?? $activeVoting['id'] ?? 0);
            $ballot = $activeId > 0 ? $this->votings->myBallot($activeId) : null;
            $activeVoting['has_voted'] = (bool) ($ballot['has_voted'] ?? false);
            $myChoice = null;
            if (!empty($ballot['my_ballot'])) {
                $optId = $ballot['my_ballot']['id_opsi'] ?? null;
                foreach (($activeVoting['opsi'] ?? []) as $o) {
                    if (($o['id_opsi'] ?? $o['id'] ?? null) == $optId) {
                        $myChoice = $o;
                        break;
                    }
                }
                $myChoice ??= [
                    'id_opsi' => $optId,
                    'nama_opsi' => $ballot['my_ballot']['nama_opsi'] ?? 'Suara Sah',
                ];
            }
            $activeVoting['my_choice'] = $myChoice;
        }

        // Riwayat hanya sesi ditutup dari backend; tanpa arsip demo palsu.
        $closedVotings = collect($allVotings)
            ->filter(function ($v) {
                $status = strtolower($v['status'] ?? '');
                return in_array($status, ['ditutup', 'selesai', 'closed'], true);
            });

        $historyVotings = $closedVotings
            ->map(function ($v) {
                $id = (int) ($v['id_voting'] ?? $v['id'] ?? 0);
                $ballot = $id > 0 ? $this->votings->myBallot($id) : null;
                $userVoted = (bool) ($ballot['has_voted'] ?? false);

                $v['user_voted'] = $userVoted;
                $v['user_pilihan_nama'] = $ballot['my_ballot']['nama_opsi'] ?? null;
                $v['user_waktu_vote'] = $ballot['my_ballot']['waktu_vote'] ?? null;

                $isInvited = (bool) ($ballot['is_invited'] ?? false);
                $isPresent = (bool) ($ballot['is_present'] ?? false);
                $v['status_partisipasi'] = $userVoted
                    ? 'voted'
                    : ($isPresent ? 'present_not_voted' : ($isInvited ? 'absent' : 'not_invited'));

                // Keputusan sidang diambil dari rekap backend (kuorum, seri,
                // pemenang), bukan heuristik nama opsi di frontend.
                $rekap = $id > 0 ? $this->votings->rekap($id) : null;
                [$hasil, $badge] = self::mapDecision($rekap);
                $v['hasil_keputusan'] = $hasil;
                $v['badge_state'] = $badge;
                if (is_array($rekap) && isset($rekap['kuorum']) && is_array($rekap['kuorum'])) {
                    $k = $rekap['kuorum'];
                    $v['kuorum'] = [
                        'hadir' => (int) ($k['present_count'] ?? 0),
                        'total' => (int) ($k['total_invited'] ?? 0),
                        'tercapai' => (bool) ($k['is_kuorum'] ?? false),
                        'terpenuhi' => (bool) ($k['is_kuorum'] ?? false),
                    ];
                }

                return $v;
            })
            ->values()
            ->all();

        return view('portal.voting', [
            'stats' => $this->meetingStats(),
            'meetings' => $this->meetings->all(),
            'activeVoting' => $activeVoting,
            'historyVotings' => $historyVotings,
            'userId' => $userId,
            'votingBackendAvailable' => $this->votings->isAvailable(),
            'votingBackendError' => $this->votings->lastError(),
        ]);
    }

    /**
     * Petakan payload rekap backend ke label putusan + warna badge.
     * Tanpa data backend yang sah, jangan mengarang "Disetujui".
     *
     * @return array{0: string, 1: string}
     */
    public static function mapDecision(?array $rekap): array
    {
        if (!is_array($rekap) || !array_key_exists('keputusan_sidang', $rekap)) {
            return ['Tidak Tersedia', 'secondary'];
        }
        if (!empty($rekap['seri'])) {
            return ['Seri - Musyawarah Lanjutan', 'warning'];
        }
        $winner = $rekap['keputusan_sidang'];
        if ($winner === null || $winner === '') {
            return [(string) ($rekap['keputusan_alasan'] ?? 'Belum Ada Keputusan'), 'secondary'];
        }
        $nama = strtolower((string) $winner);
        if (str_contains($nama, 'tidak') || str_contains($nama, 'tolak')) {
            return ['Ditolak', 'danger'];
        }
        if (str_contains($nama, 'abstain')) {
            return ['Musyawarah Lanjutan', 'warning'];
        }
        return ['Disetujui', 'success'];
    }

    /**
     * Submit Suara E-Voting
     */
    public function submitVote(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_voting' => ['required', 'integer'],
            'id_opsi' => ['required', 'integer'],
        ]);

        try {
            $userId = (int) (auth()->id() ?: session('auth_user_id'));

            if ($userId <= 0) {
                return back()->with('error', 'Sesi Anda tidak valid. Silakan login ulang.');
            }

            $result = $this->votings->castVote(
                (int) $validated['id_voting'],
                (int) $validated['id_opsi'],
                $userId
            );

            if (isset($result['success']) && ! $result['success']) {
                return back()->with('error', $result['message'] ?? 'Gagal mengirim suara.');
            }

            return back()->with('success', $result['message'] ?? 'Suara Anda berhasil dicatat.');
        } catch (\Throwable $e) {
            \Log::error('castVote failed: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan sistem saat menyimpan suara. Silakan coba lagi.');
        }
    }

    /**
     * Presensi cepat (QR) - layar operasional lama, dipertahankan
     */
    public function memberAttendance(): View
    {
        return view('portal.member-attendance', [
            'activeSession' => $this->meetings->getActiveSession(),
            'upcomingMeetings' => $this->meetings->getUpcoming(),
            'meetings' => $this->meetings->all(),
            'idUser' => (int) (auth()->id() ?: session('auth_user_id', 0)),
        ]);
    }

    /**
     * Konsol sidang (operator) - layar operasional lama, dipertahankan
     */
    public function adminMeetingSession(): View
    {
        return view('portal.admin-meeting-session', [
            'activeSession' => $this->meetings->getActiveSession(),
            'meetings' => $this->meetings->all(),
            'aspirations' => $this->aspirations->all(),
        ]);
    }
}

