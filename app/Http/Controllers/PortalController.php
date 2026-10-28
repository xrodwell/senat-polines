<?php

namespace App\Http\Controllers;

use App\Contracts\ArchiveRepositoryInterface;
use App\Contracts\AspirationRepositoryInterface;
use App\Contracts\MeetingRepositoryInterface;
use App\Contracts\PostRepositoryInterface;
use App\Contracts\RegulationRepositoryInterface;
use App\Contracts\VotingRepositoryInterface;
use App\Support\SenatDirectory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalController extends Controller
{
    public function __construct(
        private MeetingRepositoryInterface $meetings,
        private AspirationRepositoryInterface $aspirations,
        private ArchiveRepositoryInterface $archives,
        private RegulationRepositoryInterface $regulations,
        private PostRepositoryInterface $posts,
        private VotingRepositoryInterface $votings,
    ) {}

    /** @return array<string, mixed> */
    private function meetingStats(): array
    {
        $meetings = $this->meetings->all();
        $invited = collect($meetings)->sum('total_invited');
        $present = collect($meetings)->sum('present_count');

        return [
            'total' => count($meetings),
            'active' => $this->meetings->getActiveSession(),
            'upcoming' => $this->meetings->getUpcoming(),
            'invited' => $invited,
            'present' => $present,
            'attendance_rate' => $invited > 0 ? (int) round($present / $invited * 100) : 0,
        ];
    }

    /** 11 — Login Portal Senat */
    public function login(): View
    {
        return view('portal.login');
    }

    /** 12A — Dashboard Anggota · Ringkasan */
    public function dashboard(): View
    {
        return view('portal.dashboard', [
            'stats' => $this->meetingStats(),
            'meetings' => $this->meetings->all(),
            'aspirations' => $this->aspirations->all(),
            'announcements' => $this->posts->all(),
        ]);
    }

    /** 12B — Agenda & Presensi */
    public function agenda(): View
    {
        $userId = auth()->id() ?: session('auth_user_id', 6);
        $userNama = session('auth_user_name', 'Dr. Sukamto, S.Kom., M.T.');

        return view('portal.agenda', [
            'activeSession' => $this->meetings->getActiveSession(),
            'currentMember' => [
                'id' => $userId,
                'name' => $userNama,
                'nip' => '19700001',
            ],
            'stats' => $this->meetingStats(),
            'meetings' => $this->meetings->all(),
        ]);
    }

    /** Submit Presensi Anggota (QR Scan atau Kode Sesi) */
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

    /** 12C — Notulensi & Risalah */
    public function minutes(): View
    {
        return view('portal.minutes', [
            'meetings' => $this->meetings->all(),
            'archives' => $this->archives->all(),
        ]);
    }

    /** 12D — E-Voting */
    public function voting(): View
    {
        return view('portal.voting', [
            'stats' => $this->meetingStats(),
            'meetings' => $this->meetings->all(),
            'activeVoting' => $this->votings->getActive(),
        ]);
    }

    /** Submit Suara E-Voting */
    public function submitVote(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_voting' => ['required', 'integer'],
            'id_opsi' => ['required', 'integer'],
        ]);

        try {
            $result = $this->votings->castVote(
                (int) $validated['id_voting'],
                (int) $validated['id_opsi'],
                auth()->id() ?? 1
            );

            if (isset($result['success']) && ! $result['success']) {
                return back()->with('error', $result['message'] ?? 'Gagal mengirim suara.');
            }

            return back()->with('success', $result['message'] ?? 'Suara Anda berhasil dicatat.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    /** 12E — Aspirasi & RUU Kampus */
    public function aspirations(): View
    {
        return view('portal.aspirations', [
            'aspirations' => $this->aspirations->all(),
        ]);
    }

    /** 12F — Dokumen & Arsip */
    public function documents(): View
    {
        return view('portal.documents', [
            'archives' => $this->archives->all(),
            'regulations' => $this->regulations->all(),
        ]);
    }

    /** 12G — Profil Anggota */
    public function profile(): View
    {
        return view('portal.profile', [
            'stats' => $this->meetingStats(),
            'leadership' => [
                'chair' => SenatDirectory::chair(),
                'secretary' => SenatDirectory::secretary(),
            ],
            'committees' => SenatDirectory::committees(),
        ]);
    }

    /** Presensi cepat (QR) — layar operasional lama, dipertahankan */
    public function memberAttendance(): View
    {
        return view('portal.member-attendance', [
            'activeSession' => $this->meetings->getActiveSession(),
            'upcomingMeetings' => $this->meetings->getUpcoming(),
            'meetings' => $this->meetings->all(),
        ]);
    }

    /** Konsol sidang (operator) — layar operasional lama, dipertahankan */
    public function adminMeetingSession(): View
    {
        return view('portal.admin-meeting-session', [
            'activeSession' => $this->meetings->getActiveSession(),
            'meetings' => $this->meetings->all(),
            'aspirations' => $this->aspirations->all(),
        ]);
    }

    // ---------------------------------------------------------------------
    // 13 — Portal Internal · Sekretariat
    // ---------------------------------------------------------------------

    /** 13A — Dashboard Sekretariat · Ringkasan */
    public function secretariatDashboard(): View
    {
        return view('portal.secretariat.dashboard', [
            'stats' => $this->meetingStats(),
            'meetings' => $this->meetings->all(),
            'aspirations' => $this->aspirations->all(),
            'archives' => $this->archives->all(),
            'regulations' => $this->regulations->all(),
        ]);
    }

    /** 13B — Sidang & Agenda */
    public function secretariatSessions(): View
    {
        return view('portal.secretariat.sessions', [
            'stats' => $this->meetingStats(),
            'meetings' => $this->meetings->all(),
        ]);
    }

    /** 13D — Presensi & QR */
    public function secretariatAttendance(): View
    {
        return view('portal.secretariat.attendance', [
            'stats' => $this->meetingStats(),
            'meetings' => $this->meetings->all(),
        ]);
    }

    /** 13C — Notulensi & Tindak Lanjut */
    public function secretariatMinutes(): View
    {
        return view('portal.secretariat.minutes', [
            'meetings' => $this->meetings->all(),
            'archives' => $this->archives->all(),
        ]);
    }

    /** 13E — E-Voting */
    public function secretariatVoting(): View
    {
        return view('portal.secretariat.voting', [
            'stats' => $this->meetingStats(),
            'meetings' => $this->meetings->all(),
        ]);
    }

    /** 13F — Aspirasi & RUU Kampus */
    public function secretariatAspirations(): View
    {
        return view('portal.secretariat.aspirations', [
            'aspirations' => $this->aspirations->all(),
        ]);
    }

    /** 13G — Produk, Arsip & Berita */
    public function secretariatPublications(): View
    {
        return view('portal.secretariat.publications', [
            'regulations' => $this->regulations->all(),
            'archives' => $this->archives->all(),
            'posts' => $this->posts->all(),
        ]);
    }
}
