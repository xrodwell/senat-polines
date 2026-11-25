<?php

namespace App\Http\Controllers;

use App\Contracts\ArchiveRepositoryInterface;
use App\Contracts\AspirationRepositoryInterface;
use App\Contracts\MeetingRepositoryInterface;
use App\Contracts\PostRepositoryInterface;
use App\Contracts\RegulationRepositoryInterface;
use App\Contracts\VotingRepositoryInterface;
use App\Http\Controllers\Portal\Traits\HasPortalAuth;
use App\Http\Controllers\Portal\Traits\HasSecretariatPortal;
use App\Http\Controllers\Portal\Traits\HasVotingAndAttendance;
use App\Support\PublicNewsApi;
use App\Support\SenatDirectory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PortalController extends Controller
{
    use HasPortalAuth;
    use HasSecretariatPortal;
    use HasVotingAndAttendance;

    public function __construct(
        private MeetingRepositoryInterface $meetings,
        private AspirationRepositoryInterface $aspirations,
        private ArchiveRepositoryInterface $archives,
        private RegulationRepositoryInterface $regulations,
        private PostRepositoryInterface $posts,
        private VotingRepositoryInterface $votings,
    ) {}

    /**
     * Ringkasan statistik sidang & rapat senat
     *
     * @return array<string, mixed>
     */
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

    /**
     * 12A — Dashboard Anggota · Ringkasan
     */
    public function dashboard(): View
    {
        $newsResult = PublicNewsApi::fetchPage(perPage: 12);
        $published = $newsResult['posts'] ?? [];

        return view('portal.dashboard', [
            'stats' => $this->meetingStats(),
            'meetings' => $this->meetings->all(),
            'aspirations' => $this->aspirations->all(),
            'announcements' => $published,
            'allPosts' => $published,
            'newsAvailable' => $newsResult !== null,
        ]);
    }

    /**
     * Tambah & Publikasikan Berita langsung dari Dashboard
     */
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
        $thumbnail = ! empty($validated['thumbnail']) ? $validated['thumbnail'] : null;

        $result = PublicNewsApi::createNews((string) session('auth_token', ''), [
            'id_user' => (int) (auth()->id() ?: session('auth_user_id', 1)),
            'judul' => $validated['title'],
            'isi' => $validated['body'],
            'gambar' => $thumbnail,
            'galeri' => $thumbnail !== null ? [$thumbnail] : [],
            'lampiran' => [],
            'tanggal_publish' => $status === 'Terpublikasi' ? now()->format('Y-m-d H:i:s') : null,
        ]);

        if (! $result['ok']) {
            return redirect()->route('portal.dashboard')->with('error', $result['message']);
        }

        $msg = $status === 'Terpublikasi'
            ? "Berita \"{$validated['title']}\" sukses dipublikasikan dan langsung tampil di dashboard!"
            : "Draf berita \"{$validated['title']}\" berhasil disimpan.";

        return redirect()->route('portal.dashboard')->with('success', $msg);
    }

    /**
     * 12B — Agenda & Presensi
     */
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

    /**
     * 12C — Notulensi & Risalah
     */
    public function minutes(): View
    {
        return view('portal.minutes', [
            'meetings' => $this->meetings->all(),
            'archives' => $this->archives->all(),
            'idUser' => (int) (auth()->id() ?: session('auth_user_id')),
        ]);
    }

    /**
     * 12E — Aspirasi & RUU Kampus
     */
    public function aspirations(): View
    {
        return view('portal.aspirations', [
            'aspirations' => $this->aspirations->all(),
        ]);
    }

    /**
     * 12F — Dokumen & Arsip
     */
    public function documents(): View
    {
        return view('portal.documents', [
            'archives' => $this->archives->all(),
            'regulations' => $this->regulations->all(),
        ]);
    }

    /**
     * 12G — Profil Anggota
     */
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
}
