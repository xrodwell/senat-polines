<?php

namespace App\Http\Controllers\Portal\Traits;

use App\Repositories\Mock\JsonVotingRepository;
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
        $userId = $request->input('id_user') ?: (auth()->id() ?: session('auth_user_id', 6));
        $tokenQr = $request->input('token_qr');
        $sessionCode = $request->input('kode_sesi');
        $meetingId = $request->input('id_rapat');

        $result = $this->meetings->submitPresensi((int) $userId, $tokenQr, $sessionCode, $meetingId ? (int) $meetingId : null);

        $status = ($result['success'] ?? false) ? 200 : 422;
        return response()->json($result, $status);
    }

    /**
     * 12D — E-Voting
     */
    public function voting(): View
    {
        $userId = (int) (auth()->id() ?: session('auth_user_id', 2));
        $activeVoting = $this->votings->getActive();
        $allVotings = $this->votings->all();

        // Periksa partisipasi anggota pada sesi voting aktif
        if ($activeVoting) {
            $userVotedActive = false;
            $userChoiceActive = null;
            $suaraList = $activeVoting['suara_votings'] ?? $activeVoting['suaraVotings'] ?? [];
            foreach ($suaraList as $s) {
                if ((int) ($s['id_user'] ?? 0) === $userId) {
                    $userVotedActive = true;
                    $optId = $s['id_opsi'] ?? null;
                    foreach (($activeVoting['opsi'] ?? []) as $o) {
                        if (($o['id_opsi'] ?? $o['id'] ?? null) == $optId) {
                            $userChoiceActive = $o;
                            break;
                        }
                    }
                    break;
                }
            }
            $activeVoting['has_voted'] = $userVotedActive;
            $activeVoting['my_choice'] = $userChoiceActive;
        }

        // Siapkan riwayat voting (semua voting terdahulu yang sudah ditutup/selesai)
        $closedVotings = collect($allVotings)
            ->filter(function ($v) {
                $status = strtolower($v['status'] ?? '');
                return in_array($status, ['ditutup', 'selesai', 'closed'], true);
            });

        // Jika di database belum ada sesi voting yang berstatus ditutup, ambil arsip riwayat voting senat
        if ($closedVotings->isEmpty()) {
            $fallbackRepo = new JsonVotingRepository();
            $closedVotings = collect($fallbackRepo->defaultVotings())
                ->filter(fn ($v) => in_array(strtolower($v['status'] ?? ''), ['ditutup', 'selesai', 'closed'], true));
        }

        $historyVotings = $closedVotings
            ->map(function ($v) use ($userId) {
                // Periksa partisipasi anggota pada sesi voting ini
                $userVoted = false;
                $userPilihanNama = null;
                $waktuVote = null;

                $suaraList = $v['suara_votings'] ?? $v['suaraVotings'] ?? [];
                foreach ($suaraList as $s) {
                    if ((int) ($s['id_user'] ?? 0) === $userId) {
                        $userVoted = true;
                        $waktuVote = $s['waktu_vote'] ?? null;
                        $optId = $s['id_opsi'] ?? null;
                        foreach (($v['opsi'] ?? []) as $o) {
                            if (($o['id_opsi'] ?? $o['id'] ?? null) == $optId) {
                                $userPilihanNama = $o['nama_opsi'] ?? $o['label'] ?? 'Suara Tercatat';
                                break;
                            }
                        }
                        break;
                    }
                }

                // Cek presensi / undangan rapat
                $rapat = $v['rapat'] ?? [];
                $anggotaRapats = $rapat['anggota_rapats'] ?? $rapat['anggotaRapats'] ?? [];
                $isInvited = empty($anggotaRapats);
                $isPresent = false;

                if (!empty($anggotaRapats)) {
                    foreach ($anggotaRapats as $ar) {
                        if ((int) ($ar['id_user'] ?? 0) === $userId) {
                            $isInvited = true;
                            $statusHadir = strtolower((string) ($ar['status_kehadiran'] ?? ''));
                            $isPresent = ($statusHadir === 'hadir' || !empty($ar['waktu_presensi']));
                            break;
                        }
                    }
                }

                // Tentukan status partisipasi
                if ($userVoted) {
                    $statusPartisipasi = 'voted';
                } elseif ($isPresent) {
                    $statusPartisipasi = 'present_not_voted';
                } elseif ($isInvited) {
                    $statusPartisipasi = 'absent';
                } else {
                    $statusPartisipasi = 'not_invited';
                }

                $v['user_voted'] = $userVoted;
                $v['user_pilihan_nama'] = $userPilihanNama;
                $v['user_waktu_vote'] = $waktuVote;
                $v['status_partisipasi'] = $statusPartisipasi;

                // Tentukan status hasil keputusan sidang
                $opsiList = $v['opsi'] ?? [];
                $sortedOpsi = collect($opsiList)->sortByDesc(fn ($o) => (int) ($o['total'] ?? $o['jumlah_suara'] ?? 0));
                $topOpsi = $sortedOpsi->first();
                $topNama = strtolower($topOpsi['nama_opsi'] ?? $topOpsi['label'] ?? '');

                $hasilKeputusan = 'Disetujui';
                $badgeState = 'success';
                if (str_contains($topNama, 'tidak') || str_contains($topNama, 'tolak')) {
                    $hasilKeputusan = 'Ditolak';
                    $badgeState = 'danger';
                } elseif (str_contains($topNama, 'abstain')) {
                    $hasilKeputusan = 'Musyawarah Lanjutan';
                    $badgeState = 'warning';
                }

                $v['hasil_keputusan'] = $hasilKeputusan;
                $v['badge_state'] = $badgeState;

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
        ]);
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
            $userId = (int) (auth()->id() ?: session('auth_user_id', 2));
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
            return back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    /**
     * Presensi cepat (QR) — layar operasional lama, dipertahankan
     */
    public function memberAttendance(): View
    {
        return view('portal.member-attendance', [
            'activeSession' => $this->meetings->getActiveSession(),
            'upcomingMeetings' => $this->meetings->getUpcoming(),
            'meetings' => $this->meetings->all(),
            'idUser' => (int) (auth()->id() ?: session('auth_user_id', (int) env('PORTAL_DEMO_USER_ID', 2))),
        ]);
    }

    /**
     * Konsol sidang (operator) — layar operasional lama, dipertahankan
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

