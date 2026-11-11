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

    /** Impersonate Member User (Admin Only) */
    public function impersonate(Request $request, $id): RedirectResponse
    {
        $isAdmin = (session('auth_user_role') === 'admin')
            || (session()->has('impersonator') && (session('impersonator.role') === 'admin'));

        if (! $isAdmin) {
            return redirect()->route('portal.dashboard')
                ->with('error', 'Aksi ditolak. Hanya administrator yang dapat menggunakan fitur impersonasi.');
        }

        // Backup sesi admin jika belum dalam mode impersonasi
        if (! session()->has('impersonator')) {
            session([
                'impersonator' => [
                    'token' => session('auth_token'),
                    'user' => session('auth_user'),
                    'id' => session('auth_user_id'),
                    'name' => session('auth_user_name'),
                    'role' => session('auth_user_role'),
                    'nip' => session('auth_user_nip'),
                    'email' => session('auth_user_email'),
                ],
            ]);
        }

        $adminToken = session('impersonator.token') ?: session('auth_token');

        try {
            $backendUrl = rtrim((string) config('services.backend_api.url', env('BACKEND_API_URL', 'http://127.0.0.1:8001/api')), '/');
            $response = \Illuminate\Support\Facades\Http::timeout(5)
                ->withToken($adminToken)
                ->post("{$backendUrl}/admin/impersonate/{$id}");

            if ($response->successful()) {
                $data = $response->json();
                $targetUser = $data['user'] ?? [];
                $targetToken = $data['token'] ?? null;

                session([
                    'auth_token' => $targetToken,
                    'auth_user' => $targetUser,
                    'auth_user_id' => $targetUser['id_user'] ?? $id,
                    'auth_user_name' => $targetUser['nama'] ?? 'Anggota Senat',
                    'auth_user_role' => $targetUser['role'] ?? 'anggota',
                    'auth_user_nip' => $targetUser['nim_nip'] ?? '',
                    'auth_user_email' => $targetUser['email'] ?? '',
                ]);

                return redirect()->route('portal.dashboard')
                    ->with('success', 'Berhasil beralih akun ke ' . ($targetUser['nama'] ?? 'Anggota Senat') . '.');
            }

            $errorMessage = $response->json('message') ?? 'Gagal melakukan impersonasi user.';
            return back()->with('error', $errorMessage);
        } catch (\Throwable $e) {
            \Log::error('Portal impersonation failed: ' . $e->getMessage());
            return back()->with('error', 'Gagal terhubung ke backend API (' . $e->getMessage() . ').');
        }
    }

    /** Kembali dari Mode Impersonasi ke Akun Admin */
    public function leaveImpersonate(Request $request): RedirectResponse
    {
        if (! session()->has('impersonator')) {
            return redirect()->route('portal.dashboard');
        }

        $impersonator = session('impersonator');
        $currentToken = session('auth_token');

        if ($currentToken) {
            try {
                $backendUrl = rtrim((string) config('services.backend_api.url', env('BACKEND_API_URL', 'http://127.0.0.1:8001/api')), '/');
                \Illuminate\Support\Facades\Http::timeout(3)
                    ->withToken($currentToken)
                    ->post("{$backendUrl}/logout");
            } catch (\Throwable) {
                // Abaikan kegagalan backend logout
            }
        }

        // Kembalikan sesi admin awal
        session([
            'auth_token' => $impersonator['token'] ?? null,
            'auth_user' => $impersonator['user'] ?? null,
            'auth_user_id' => $impersonator['id'] ?? null,
            'auth_user_name' => $impersonator['name'] ?? 'Admin Senat',
            'auth_user_role' => $impersonator['role'] ?? 'admin',
            'auth_user_nip' => $impersonator['nip'] ?? '',
            'auth_user_email' => $impersonator['email'] ?? '',
        ]);

        session()->forget('impersonator');

        return redirect()->route('admin.preview')
            ->with('success', 'Kembali ke sesi Administrator Senat.');
    }

    /**
     * 12A — Dashboard Anggota · Ringkasan
     */
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
            'idUser' => (int) (auth()->id() ?: session('auth_user_id', (int) env('PORTAL_DEMO_USER_ID', 2))),
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
