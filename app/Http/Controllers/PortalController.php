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
    public function login(): View|RedirectResponse
    {
        if (session()->has('auth_user')) {
            if (session('auth_user_role') === 'admin') {
                return redirect()->route('admin.preview');
            }
            return redirect()->route('portal.dashboard');
        }

        return view('portal.login');
    }

    /** Proses Autentikasi Login Portal */
    public function authenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'identifier' => 'required|string',
            'password'   => 'required|string',
        ], [
            'identifier.required' => 'Email atau NIP/NIM wajib diisi.',
            'password.required'   => 'Kata sandi wajib diisi.',
        ]);

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(5)->post('http://127.0.0.1:8001/api/login', [
                'identifier' => $credentials['identifier'],
                'password'   => $credentials['password'],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $user = $data['user'] ?? [];

                // Simpan ke session Laravel frontend
                session([
                    'auth_token'     => $data['token'] ?? null,
                    'auth_user'      => $user,
                    'auth_user_id'   => $user['id_user'] ?? null,
                    'auth_user_name' => $user['nama'] ?? 'Anggota Senat',
                    'auth_user_role' => $user['role'] ?? 'anggota',
                    'auth_user_nip'  => $user['nim_nip'] ?? '',
                    'auth_user_email'=> $user['email'] ?? '',
                ]);

                // Redirect sesuai role
                if (($user['role'] ?? '') === 'admin') {
                    return redirect()->route('admin.preview')->with('success', 'Selamat datang, ' . ($user['nama'] ?? 'Admin') . '!');
                }

                return redirect()->route('portal.dashboard')->with('success', 'Selamat datang, ' . ($user['nama'] ?? 'Anggota Senat') . '!');
            }

            $errorMessage = $response->json('message') ?? 'NIP/Email atau kata sandi tidak valid.';
            return back()->withInput($request->only('identifier'))->withErrors(['login' => $errorMessage]);
        } catch (\Throwable $e) {
            \Log::error('Portal login failed: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            return back()->withInput($request->only('identifier'))->withErrors(['login' => 'Gagal terhubung ke backend API (' . $e->getMessage() . '). Pastikan server backend aktif di port 8001.']);
        }
    }

    /** Logout dari Portal */
    public function logout(Request $request): RedirectResponse
    {
        session()->forget(['auth_token', 'auth_user', 'auth_user_id', 'auth_user_name', 'auth_user_role', 'auth_user_nip', 'auth_user_email']);
        session()->flush();

        return redirect()->route('portal.login')->with('info', 'Anda telah berhasil keluar dari akun.');
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
            'idUser' => (int) (auth()->id() ?: session('auth_user_id', (int) env('PORTAL_DEMO_USER_ID', 2))),
        ]);
    }

    /** 12D — E-Voting */
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
            $fallbackRepo = new \App\Repositories\Mock\JsonVotingRepository();
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

    /** Submit Suara E-Voting */
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
            'idUser' => (int) (auth()->id() ?: session('auth_user_id', (int) env('PORTAL_DEMO_USER_ID', 2))),
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
