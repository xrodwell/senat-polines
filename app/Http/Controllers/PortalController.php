<?php

namespace App\Http\Controllers;

use App\Contracts\ArchiveRepositoryInterface;
use App\Contracts\AspirationRepositoryInterface;
use App\Contracts\MeetingRepositoryInterface;
use App\Contracts\PostRepositoryInterface;
use App\Contracts\RegulationRepositoryInterface;
use App\Support\SenatDirectory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PortalController extends Controller
{
    public function __construct(
        private MeetingRepositoryInterface $meetings,
        private AspirationRepositoryInterface $aspirations,
        private ArchiveRepositoryInterface $archives,
        private RegulationRepositoryInterface $regulations,
        private PostRepositoryInterface $posts,
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
        $allPosts = $this->posts->all();
        $published = array_values(array_filter($allPosts, fn($p) => ($p['status'] ?? 'Terpublikasi') === 'Terpublikasi'));

        return view('portal.dashboard', [
            'stats' => $this->meetingStats(),
            'meetings' => $this->meetings->all(),
            'aspirations' => $this->aspirations->all(),
            'announcements' => $published,
            'allPosts' => $allPosts,
        ]);
    }

    /** Tambah & Publikasikan Berita langsung dari Dashboard */
    public function storePost(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|min:3|max:255',
            'category' => 'required|string',
            'body' => 'required|string',
            'thumbnail' => 'nullable|string',
            'status' => 'nullable|string|in:Terpublikasi,Draf',
        ], [
            'title.required' => 'Judul berita wajib diisi.',
            'title.min' => 'Judul berita minimal 3 karakter.',
            'category.required' => 'Kategori berita wajib dipilih.',
            'body.required' => 'Isi berita lengkap wajib diisi.',
        ]);

        $status = $validated['status'] ?? 'Terpublikasi';
        $excerpt = Str::limit(strip_tags($validated['body']), 180);
        $thumbnail = !empty($validated['thumbnail'])
            ? $validated['thumbnail']
            : 'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?auto=format&fit=crop&w=800&q=80';

        $payload = [
            'title' => $validated['title'],
            'category' => $validated['category'],
            'date' => date('Y-m-d'),
            'status' => $status,
            'excerpt' => $excerpt,
            'body_text' => $validated['body'],
            'body' => explode("\n\n", $validated['body']),
            'thumbnail' => $thumbnail,
            'author' => 'Sekretariat Senat Polines',
        ];

        $this->posts->create($payload);

        $msg = $status === 'Terpublikasi'
            ? "Berita \"{$validated['title']}\" sukses dipublikasikan dan langsung tampil di dashboard!"
            : "Draf berita \"{$validated['title']}\" berhasil disimpan.";

        return redirect()->route('portal.dashboard')->with('success', $msg);
    }

    /** 12B — Agenda & Presensi */
    public function agenda(): View
    {
        return view('portal.agenda', [
            'stats' => $this->meetingStats(),
            'meetings' => $this->meetings->all(),
        ]);
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
        ]);
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
