<?php

namespace App\Http\Controllers;

use App\Contracts\ArchiveRepositoryInterface;
use App\Contracts\AspirationRepositoryInterface;
use App\Contracts\CommitteeRepositoryInterface;
use App\Contracts\FeatureRepositoryInterface;
use App\Contracts\MeetingRepositoryInterface;
use App\Contracts\PostRepositoryInterface;
use App\Contracts\RegulationRepositoryInterface;
use App\Support\SenatDirectory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Support\Facades\Http;


class PublicController extends Controller
{
    public function __construct(
        private PostRepositoryInterface $posts,
        private MeetingRepositoryInterface $meetings,
        private RegulationRepositoryInterface $regulations,
        private CommitteeRepositoryInterface $committees,
        private FeatureRepositoryInterface $features,
        private AspirationRepositoryInterface $aspirations,
        private ArchiveRepositoryInterface $archives,
    ) {}

    /** 01 — Beranda */
    public function home(): View
    {
        $landingNews = $this->landingNews();

        return view('public.home', [
            'hero' => \App\Support\HeroApi::publicRead(),
            'posts' => $landingNews ?? [],
            'landingNewsAvailable' => $landingNews !== null,
            'upcomingMeetings' => $this->meetings->getUpcoming(),
            'committees' => $this->committees->all(),
            'features' => $this->features->all(),
            'landingStats' => $this->landingStats(),
        ]);
    }

    private function landingNews(): ?array
    {
        $base = rtrim((string) config('services.backend_api.url'), '/');
        if ($base === '') {
            return null;
        }
        $base = preg_replace('~/api$~', '', $base);

        try {
            $response = Http::acceptJson()->timeout(3)->get($base.'/api/landing-news');
            $items = $response->json('data');
            if (! $response->successful() || ! is_array($items) || ! array_is_list($items) || count($items) > 6) {
                return null;
            }
            $posts = [];
            foreach ($items as $item) {
                if (! is_array($item) || ! isset($item['id_berita'], $item['judul'], $item['isi'], $item['tanggal_publish']) || ! is_string($item['judul']) || ! is_string($item['isi']) || ! is_string($item['tanggal_publish'])) {
                    return null;
                }
                $posts[] = [
                    'slug' => \Illuminate\Support\Str::slug($item['judul']),
                    'title' => $item['judul'],
                    'category' => 'Warta Senat',
                    'excerpt' => \Illuminate\Support\Str::limit(strip_tags($item['isi']), 180),
                    'thumbnail' => is_string($item['gambar'] ?? null) ? $item['gambar'] : null,
                ];
            }
            return $posts;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function landingStats(): ?array
    {
        $base = rtrim((string) config('services.backend_api.url'), '/');
        if ($base === '') {
            return null;
        }
        $base = preg_replace('~/api$~', '', $base);

        try {
            $response = Http::acceptJson()->timeout(3)->get($base.'/api/landing-stats');
            $data = $response->json();
            if (! $response->successful() || ! is_array($data)) {
                return null;
            }
            foreach (['members', 'committees', 'published_news'] as $key) {
                if (! isset($data[$key]) || ! is_int($data[$key]) || $data[$key] < 0) {
                    return null;
                }
            }
            return array_intersect_key($data, array_flip(['members', 'committees', 'published_news']));
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** 02A — Profil · Visi, Misi & Tujuan */
    public function profile(): View
    {
        $baseUrl = config('services.backend_api.url');

        try {
            $response = Http::timeout(3)->get("{$baseUrl}/api/struktur-organisasi");
            $struktur = $response->json()['data'] ?? [];
        } catch (\Throwable $e) {
            $struktur = [];
        }

        $ketua = collect($struktur)->firstWhere('jabatan', 'Ketua');
        $sekretaris = collect($struktur)->firstWhere('jabatan', 'Sekretaris');

        return view('public.profile.vision', [
            'committees' => $this->committees->all(),
            'direktori' => SenatDirectory::stats(),
            'struktur'   => $struktur,
            'ketua'      => $ketua,
            'sekretaris' => $sekretaris,
        ]);
    }

    /** 02B — Profil · Tugas & Wewenang */
    public function tasks(): View
    {
        return view('public.profile.tasks', [
            'committees' => $this->committees->all(),
            'features' => $this->features->all(),
        ]);
    }

    /** 02C — Profil · Struktur Organisasi */
    public function structure(): View
    {
        return view('public.profile.structure', [
            'committees' => $this->committees->all(),
            'leadership' => [
                'chair' => SenatDirectory::chair(),
                'secretary' => SenatDirectory::secretary(),
                'bph' => SenatDirectory::bph(),
            ],
            'period' => SenatDirectory::PERIOD,
            'direktori' => SenatDirectory::stats(),
        ]);
    }

    /** 03 — Direktori Komisi & Anggota */
    public function directory(): View
    {
        return view('public.directory', [
            'committees' => $this->committees->all(),
            'members' => SenatDirectory::members(),
            'leadership' => [
                'chair' => SenatDirectory::chair(),
                'secretary' => SenatDirectory::secretary(),
                'bph' => SenatDirectory::bph(),
            ],
            'period' => SenatDirectory::PERIOD,
            'direktori' => SenatDirectory::stats(),
        ]);
    }

    /** Simpan Anggota Komisi Baru (CRUD Direktori) */
    public function storeMember(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'nim_nip' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'committee_code' => ['required', 'string'],
            'role' => ['required', 'string'],
            'organ' => ['nullable', 'string', 'max:255'],
            'focus' => ['nullable', 'string', 'max:500'],
        ]);

        $baseUrl = rtrim((string)(config('services.backend_api.url') ?? env('BACKEND_API_URL', 'http://127.0.0.1:8001')), '/');
        $apiSuccess = false;

        // 1. Kirim ke Backend API jika aktif
        try {
            $apiUrl = str_contains($baseUrl, '/api')
                ? "{$baseUrl}/committees/members"
                : "{$baseUrl}/api/committees/members";

            $response = Http::timeout(4)->post($apiUrl, [
                'nama' => $validated['nama'],
                'nim_nip' => $validated['nim_nip'] ?? '',
                'email' => $validated['email'] ?? '',
                'id_komisi' => $validated['committee_code'],
                'jabatan_komisi' => $validated['role'],
                'organ' => $validated['organ'] ?? '',
            ]);

            if ($response->successful()) {
                $apiSuccess = true;
            }
        } catch (\Throwable $e) {
            Log::info('Simpan anggota ke Backend API gagal, melanjutkan ke local storage: ' . $e->getMessage());
        }

        // 2. Sinkronkan ke mock storage lokal (committees.json)
        $mockPath = storage_path('mock/committees.json');
        if (file_exists($mockPath)) {
            $committees = json_decode(file_get_contents($mockPath), true) ?: [];
            foreach ($committees as &$c) {
                if ($c['code'] === $validated['committee_code']) {
                    if ($validated['role'] === 'Ketua') {
                        $c['lead'] = $validated['nama'];
                    } elseif ($validated['role'] === 'Sekretaris') {
                        $c['secretary'] = $validated['nama'];
                    } else {
                        if (!isset($c['members']) || !is_array($c['members'])) {
                            $c['members'] = [];
                        }
                        if (!in_array($validated['nama'], $c['members'])) {
                            $c['members'][] = $validated['nama'];
                        }
                    }
                }
                $leadCount = (!empty($c['lead']) && $c['lead'] !== 'Belum ditentukan') ? 1 : 0;
                $secCount = (!empty($c['secretary']) && $c['secretary'] !== 'Belum ditentukan') ? 1 : 0;
                $c['members_count'] = count($c['members'] ?? []) + $leadCount + $secCount;
            }
            unset($c);
            file_put_contents($mockPath, json_encode($committees, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Anggota Senat berhasil ditambahkan ke ' . $validated['committee_code'],
                'api_synced' => $apiSuccess,
            ]);
        }

        return redirect()->route('public.directory')->with('success', 'Anggota Senat berhasil ditambahkan!');
    }

    /** Update Anggota Komisi (CRUD Direktori) */
    public function updateMember(Request $request, string $id)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'nama_lama' => ['nullable', 'string', 'max:255'],
            'nim_nip' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'committee_code' => ['required', 'string'],
            'role' => ['required', 'string'],
            'organ' => ['nullable', 'string', 'max:255'],
        ]);

        $baseUrl = rtrim((string)(config('services.backend_api.url') ?? env('BACKEND_API_URL', 'http://127.0.0.1:8001')), '/');
        $apiSuccess = false;

        try {
            $apiUrl = str_contains($baseUrl, '/api')
                ? "{$baseUrl}/committees/members/{$id}"
                : "{$baseUrl}/api/committees/members/{$id}";

            $response = Http::timeout(4)->put($apiUrl, [
                'nama' => $validated['nama'],
                'nama_lama' => $validated['nama_lama'] ?? null,
                'nim_nip' => $validated['nim_nip'] ?? '',
                'email' => $validated['email'] ?? '',
                'id_komisi' => $validated['committee_code'],
                'jabatan_komisi' => $validated['role'],
            ]);

            if ($response->successful()) {
                $apiSuccess = true;
            }
        } catch (\Throwable $e) {
            Log::info('Update anggota ke Backend API gagal, melanjutkan ke local storage: ' . $e->getMessage());
        }

        // Update local mock
        $mockPath = storage_path('mock/committees.json');
        if (file_exists($mockPath)) {
            $committees = json_decode(file_get_contents($mockPath), true) ?: [];
            $targetOldName = $validated['nama_lama'] ?? $validated['nama'];
            $newCode = $validated['committee_code'];
            $newRole = $validated['role'];
            $newName = $validated['nama'];

            // Hapus nama lama dari posisi sebelumnya di semua komisi agar tidak duplikat
            foreach ($committees as &$c) {
                if ($c['lead'] === $targetOldName) {
                    $c['lead'] = 'Belum ditentukan';
                }
                if ($c['secretary'] === $targetOldName) {
                    $c['secretary'] = 'Belum ditentukan';
                }
                if (isset($c['members']) && is_array($c['members'])) {
                    $c['members'] = array_values(array_filter($c['members'], fn($m) => $m !== $targetOldName));
                }
            }
            unset($c);

            // Masukkan data baru ke komisi tujuan sesuai perannya
            foreach ($committees as &$c) {
                if ($c['code'] === $newCode) {
                    if ($newRole === 'Ketua') {
                        $c['lead'] = $newName;
                    } elseif ($newRole === 'Sekretaris') {
                        $c['secretary'] = $newName;
                    } else {
                        if (!isset($c['members']) || !is_array($c['members'])) {
                            $c['members'] = [];
                        }
                        if (!in_array($newName, $c['members'])) {
                            $c['members'][] = $newName;
                        }
                    }
                }
                $leadCount = (!empty($c['lead']) && $c['lead'] !== 'Belum ditentukan') ? 1 : 0;
                $secCount = (!empty($c['secretary']) && $c['secretary'] !== 'Belum ditentukan') ? 1 : 0;
                $c['members_count'] = count($c['members'] ?? []) + $leadCount + $secCount;
            }
            unset($c);

            file_put_contents($mockPath, json_encode($committees, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Data anggota Senat berhasil diperbarui',
                'api_synced' => $apiSuccess,
            ]);
        }

        return redirect()->route('public.directory')->with('success', 'Data anggota berhasil diperbarui!');
    }

    /** Hapus Anggota Komisi (CRUD Direktori) */
    public function destroyMember(Request $request, string $id)
    {
        $name = $request->input('nama');
        $committeeCode = $request->input('committee_code');

        $baseUrl = rtrim((string)(config('services.backend_api.url') ?? env('BACKEND_API_URL', 'http://127.0.0.1:8001')), '/');
        $apiSuccess = false;

        try {
            $apiUrl = str_contains($baseUrl, '/api')
                ? "{$baseUrl}/committees/members/{$id}"
                : "{$baseUrl}/api/committees/members/{$id}";

            $response = Http::timeout(4)->delete($apiUrl, [
                'nama' => $name,
                'committee_code' => $committeeCode,
            ]);
            if ($response->successful()) {
                $apiSuccess = true;
            }
        } catch (\Throwable $e) {
            Log::info('Hapus anggota ke Backend API gagal, melanjutkan ke local storage: ' . $e->getMessage());
        }

        // Hapus dari mock storage lokal
        $mockPath = storage_path('mock/committees.json');
        if (file_exists($mockPath) && $name) {
            $committees = json_decode(file_get_contents($mockPath), true) ?: [];
            foreach ($committees as &$c) {
                if (empty($committeeCode) || $c['code'] === $committeeCode) {
                    if (isset($c['members']) && is_array($c['members'])) {
                        $c['members'] = array_values(array_filter($c['members'], fn($m) => $m !== $name));
                    }
                    if ($c['lead'] === $name) {
                        $c['lead'] = 'Belum ditentukan';
                    }
                    if ($c['secretary'] === $name) {
                        $c['secretary'] = 'Belum ditentukan';
                    }
                }
                $leadCount = (!empty($c['lead']) && $c['lead'] !== 'Belum ditentukan') ? 1 : 0;
                $secCount = (!empty($c['secretary']) && $c['secretary'] !== 'Belum ditentukan') ? 1 : 0;
                $c['members_count'] = count($c['members'] ?? []) + $leadCount + $secCount;
            }
            unset($c);
            file_put_contents($mockPath, json_encode($committees, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Anggota Senat berhasil dihapus dari komisi',
                'api_synced' => $apiSuccess,
            ]);
        }

        return redirect()->route('public.directory')->with('success', 'Anggota Senat berhasil dihapus!');
    }

    /** 04 — Detail Anggota */
    public function member(string $slug): View
    {
        $member = SenatDirectory::findBySlug($slug);

        abort_if($member === null, 404);

        $committee = isset($member['committee_code'])
            ? SenatDirectory::committeeByCode($member['committee_code'])
            : null;

        $peers = collect(SenatDirectory::members())
            ->filter(fn ($person) => ($person['committee_code'] ?? null) === ($member['committee_code'] ?? null)
                && $person['slug'] !== $member['slug'])
            ->values()
            ->all();

        return view('public.member', [
            'member' => $member,
            'committee' => $committee,
            'peers' => $peers,
            'period' => SenatDirectory::PERIOD,
        ]);
    }

    /** 05A — Produk Hukum · Peraturan Senat */
    public function regulations(): View
    {
        return view('public.regulations', [
            'regulations' => $this->regulations->all(),
        ]);
    }

    /** 05B — Produk Hukum · Keputusan Senat */
    public function decrees(): View
    {
        $all = $this->regulations->all();
        $filtered = array_values(array_filter($all, fn ($item) => str_contains($item['category'] ?? '', 'SK')
            || str_contains($item['category'] ?? '', 'Keputusan')));

        return view('public.regulations-decrees', [
            'regulations' => $filtered ?: $all,
        ]);
    }

    /** 05C — Produk Hukum · Standar & Pedoman Akademik */
    public function standards(): View
    {
        $standards = array_values(array_filter(
            $this->regulations->all(),
            fn ($item) => ! str_contains($item['category'] ?? '', 'SK')
        ));

        return view('public.regulations-standards', [
            'standards' => $standards ?: $this->regulations->all(),
        ]);
    }

    /** 06 — Kanal Aspirasi */
    public function aspirations(): View
    {
        return view('public.aspirations', [
            'aspirations' => $this->aspirations->all(),
            'committees' => $this->committees->all(),
        ]);
    }

    /** 06A — Kirim Kode OTP Verifikasi Email Aspirasi */
    public function sendOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email|max:100',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
        ]);

        $email = strtolower(trim($validated['email']));
        $otp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        $cacheKey = 'otp_aspiration_' . md5($email);
        Cache::put($cacheKey, [
            'otp' => $otp,
            'verified' => false,
            'expires_at' => now()->addMinutes(10),
        ], now()->addMinutes(10));

        $mailSent = false;
        $mailError = null;
        try {
            Mail::html("
                <div style='font-family: Arial, sans-serif; max-width: 540px; margin: 0 auto; padding: 24px; border: 1px solid #e2e8f0; border-radius: 12px; background-color: #ffffff;'>
                    <div style='text-align: center; border-bottom: 2px solid #003c75; padding-bottom: 16px;'>
                        <h2 style='color: #003c75; margin: 0; font-size: 20px;'>SENAT AKADEMIK POLINES</h2>
                        <p style='color: #64748b; margin: 4px 0 0; font-size: 12px;'>Kanal Aspirasi Sivitas Akademika</p>
                    </div>
                    <div style='padding: 24px 0;'>
                        <p style='color: #334155; font-size: 14px; line-height: 1.5;'>Halo Civitas Akademika,</p>
                        <p style='color: #334155; font-size: 14px; line-height: 1.5;'>Berikut adalah kode verifikasi OTP Anda untuk pengajuan aspirasi ke Senat Akademik Politeknik Negeri Semarang:</p>
                        <div style='text-align: center; margin: 24px 0;'>
                            <div style='display: inline-block; background-color: #f0f7ff; border: 2px dashed #00509d; border-radius: 8px; padding: 12px 32px; font-size: 32px; font-weight: bold; letter-spacing: 6px; color: #003c75;'>
                                {$otp}
                            </div>
                        </div>
                        <p style='color: #64748b; font-size: 12px; line-height: 1.5;'>Kode ini berlaku selama <strong>10 menit</strong>. Jangan berikan kode ini kepada siapa pun.</p>
                    </div>
                    <div style='border-top: 1px solid #e2e8f0; padding-top: 16px; text-align: center;'>
                        <p style='color: #94a3b8; font-size: 11px; margin: 0;'>Pesan otomatis dari Sistem Informasi Senat Akademik Polines.</p>
                    </div>
                </div>
            ", function ($message) use ($email) {
                $message->to($email)
                    ->subject('Kode Verifikasi OTP Aspirasi - Senat Akademik POLINES');
            });
            $mailSent = true;
        } catch (\Throwable $e) {
            $mailError = $e->getMessage();
            Log::error("Kirim email OTP ke {$email} gagal: " . $mailError);
        }

        $msg = $mailSent
            ? "Kode verifikasi 6 digit telah dikirim ke {$email}."
            : "Gagal mengirim email: " . ($mailError ?: "Periksa konfigurasi SMTP");

        return response()->json([
            'success' => $mailSent,
            'message' => $msg,
            'mail_sent' => $mailSent,
            'mail_error' => $mailError,
        ]);
    }

    /** 06A2 — Verifikasi Kode OTP Email */
    public function verifyOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email|max:100',
            'otp'   => 'required|string|size:6',
        ], [
            'email.required' => 'Email wajib diisi.',
            'otp.required'   => 'Kode OTP wajib diisi.',
            'otp.size'       => 'Kode OTP harus berupa 6 digit angka.',
        ]);

        $email = strtolower(trim($validated['email']));
        $otp = trim($validated['otp']);
        $cacheKey = 'otp_aspiration_' . md5($email);

        $cached = Cache::get($cacheKey);

        if (!$cached || ($cached['otp'] ?? '') !== $otp) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP salah atau telah kedaluwarsa. Silakan periksa kembali atau minta kode baru.',
            ], 422);
        }

        Cache::put($cacheKey, [
            'otp' => $otp,
            'verified' => true,
            'verified_at' => now(),
        ], now()->addHour());

        return response()->json([
            'success' => true,
            'message' => 'Alamat email berhasil diverifikasi.',
            'email'   => $email,
        ]);
    }

    /** 06B — Kirim Aspirasi ke Backend API (Terproteksi Verifikasi OTP) */
    public function submitAspiration(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'category' => 'nullable|string',
            'jurusan' => 'nullable|string',
            'committee' => 'nullable|string',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $email = strtolower(trim($validated['email']));
        $cacheKey = 'otp_aspiration_' . md5($email);
        $cached = Cache::get($cacheKey);

        if (!$cached || empty($cached['verified'])) {
            return response()->json([
                'success' => false,
                'message' => 'Alamat email belum diverifikasi dengan kode OTP. Silakan lakukan verifikasi email Anda terlebih dahulu.',
            ], 422);
        } 



        $apiUrl = rtrim(env('SENAT_BACKEND_API_URL', env('BACKEND_API_URL', 'http://127.0.0.1:8001/api')), '/');

        $committee = $validated['committee'] ?? '';
        $idKategori = 4;
        if (str_contains($committee, 'Badan Pekerja') || str_contains($committee, 'BP')) {
            $idKategori = 6;
        } elseif (str_contains($committee, 'Etik') || str_contains($committee, 'V')) {
            $idKategori = 5;
        } elseif (str_contains($committee, 'Pengembangan') || str_contains($committee, 'Kerja Sama') || str_contains($committee, 'IV')) {
            $idKategori = 4;
        } elseif (str_contains($committee, 'Kemahasiswaan') || str_contains($committee, 'Kelembagaan') || str_contains($committee, 'Tata Tertib') || str_contains($committee, 'III')) {
            $idKategori = 3;
        } elseif (str_contains($committee, 'Penelitian') || str_contains($committee, 'Pengabdian') || str_contains($committee, 'Sarana') || str_contains($committee, 'Fasilitas') || str_contains($committee, 'II')) {
            $idKategori = 2;
        } elseif (str_contains($committee, 'Pendidikan') || str_contains($committee, 'Akademik') || preg_match('/\bI\b/', $committee)) {
            $idKategori = 1;
        }

        $isiFormatted = "[{$validated['subject']}]
{$validated['description']}

Pengirim: {$validated['name']} ({$validated['email']}) - Jurusan: " . ($validated['jurusan'] ?? 'Umum');

        try {
            $response = Http::acceptJson()->timeout(5)->post("{$apiUrl}/aspirasi", [
                'id_user' => 1,
                'id_kategori' => $idKategori,
                'isi_aspirasi' => $isiFormatted,
                'status' => 'pending',
                'anonim' => false,
            ]);

            if ($response->successful()) {
                $created = $response->json('data') ?? $response->json();
                $id = $created['id_aspirasi'] ?? 1;
                $ticketCode = 'ASP-POLINES-2026-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT);

                return response()->json([
                    'success' => true,
                    'ticket' => $ticketCode,
                    'message' => 'Aspirasi Anda berhasil dikirim ke database Senat.',
                ]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Gagal kirim aspirasi ke API: ' . $e->getMessage());
        }

        return response()->json([
            'success' => false,
            'message' => 'Layanan database Senat sedang tidak dapat dijangkau. Mohon pastikan backend aktif dan coba lagi.',
        ], 500);
    }

    public function trackAspiration(Request $request): View
    {
        $ticket = $request->input('ticket', $request->route('ticket'));

        return view('public.track-aspiration', [
            'ticket' => $ticket,
            'aspiration' => $ticket ? $this->aspirations->findByTicket($ticket) : null,
        ]);
    }

    /** 11A — Arsip · Risalah Sidang */
    public function archives(): View
    {
        return view('public.archives', [
            'archives' => $this->archives->all(),
            'meetings' => $this->meetings->all(),
        ]);
    }

    /** 11B — Arsip · Laporan Tahunan & LPJ */
    public function archivesReports(): View
    {
        $archives = $this->archives->all();
        $reports = array_values(array_filter($archives, fn ($item) => str_contains($item['category'] ?? '', 'Laporan')));

        return view('public.archives-reports', [
            'archives' => $reports ?: $archives,
        ]);
    }

    /** 14 — Kanal Berita & Warta */
    public function news(Request $request): View
    {
        $result = $this->fetchPublicNews($request);

        return view('public.news', [
            'posts' => $result['posts'] ?? [],
            'pagination' => $result['pagination'] ?? [
                'total' => 0,
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 12,
                'from' => 0,
                'to' => 0,
            ],
            'newsAvailable' => $result !== null,
        ]);
    }

    /** 14B — Detail Warta */
    public function newsShow(string $slug): View
    {
        [$post, $isOutage] = $this->fetchPublicNewsDetail($slug);

        if ($isOutage) {
            abort(503, 'Warta sementara tidak tersedia.');
        }

        abort_if($post === null, 404, 'Berita tidak ditemukan.');

        $related = $this->fetchRelatedNews($slug);

        return view('public.news-show', [
            'post' => $post,
            'related' => $related,
        ]);
    }

    private function fetchPublicNews(Request $request): ?array
    {
        $base = rtrim((string) config('services.backend_api.url'), '/');
        if ($base === '') {
            return null;
        }
        $base = preg_replace('~/api$~', '', $base);

        $params = [
            'page' => max(1, $request->integer('page', 1)),
            'per_page' => max(1, $request->integer('per_page', 12)),
        ];

        $q = trim((string) ($request->query('q') ?? $request->query('search') ?? ''));
        if ($q !== '') {
            $params['search'] = $q;
        }

        try {
            $response = Http::acceptJson()->timeout(3)->get($base . '/api/public-news', $params);

            if ($response->status() === 404) {
                // If public-news endpoint is missing on backend, fall back to landing-news (database-backed API only, NO mock)
                $fallbackResp = Http::acceptJson()->timeout(3)->get($base . '/api/landing-news');
                if (! $fallbackResp->successful()) {
                    return null;
                }
                $rawItems = $fallbackResp->json('data') ?? [];
                if (! is_array($rawItems)) {
                    return null;
                }
                $posts = array_map(fn ($item) => $this->formatNewsItem($item), $rawItems);
                return [
                    'posts' => $posts,
                    'pagination' => [
                        'total' => count($posts),
                        'current_page' => 1,
                        'last_page' => 1,
                        'per_page' => count($posts),
                        'from' => count($posts) > 0 ? 1 : 0,
                        'to' => count($posts),
                    ],
                ];
            }

            if (! $response->successful()) {
                return null;
            }

            $json = $response->json();
            if (! is_array($json)) {
                return null;
            }

            $rawItems = $json['data'] ?? [];
            if (! is_array($rawItems)) {
                return null;
            }

            $posts = array_map(fn ($item) => $this->formatNewsItem($item), $rawItems);

            return [
                'posts' => $posts,
                'pagination' => [
                    'total' => (int) ($json['total'] ?? count($posts)),
                    'current_page' => (int) ($json['current_page'] ?? 1),
                    'last_page' => (int) ($json['last_page'] ?? 1),
                    'per_page' => (int) ($json['per_page'] ?? count($posts)),
                    'from' => (int) ($json['from'] ?? (count($posts) > 0 ? 1 : 0)),
                    'to' => (int) ($json['to'] ?? count($posts)),
                ],
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * @return array{0: ?array, 1: bool} [post, isOutage]
     */
    private function fetchPublicNewsDetail(string $slug): array
    {
        $base = rtrim((string) config('services.backend_api.url'), '/');
        if ($base === '') {
            return [null, true];
        }
        $base = preg_replace('~/api$~', '', $base);

        try {
            $response = Http::acceptJson()->timeout(3)->get($base . '/api/public-news/' . urlencode($slug));

            if ($response->status() === 404) {
                // Check landing-news in case backend only has landing-news
                $landingResp = Http::acceptJson()->timeout(3)->get($base . '/api/landing-news');
                if ($landingResp->successful()) {
                    $items = $landingResp->json('data') ?? [];
                    if (is_array($items)) {
                        foreach ($items as $item) {
                            if (\Illuminate\Support\Str::slug($item['judul'] ?? '') === $slug) {
                                return [$this->formatNewsItem($item), false];
                            }
                        }
                    }
                }
                return [null, false];
            }

            if (! $response->successful()) {
                return [null, true];
            }

            $raw = $response->json('data') ?? $response->json();
            if (! is_array($raw) || empty($raw)) {
                return [null, false];
            }

            return [$this->formatNewsItem($raw), false];
        } catch (\Throwable $e) {
            return [null, true];
        }
    }

    private function fetchRelatedNews(string $slug): array
    {
        $base = rtrim((string) config('services.backend_api.url'), '/');
        if ($base === '') {
            return [];
        }
        $base = preg_replace('~/api$~', '', $base);

        try {
            $response = Http::acceptJson()->timeout(3)->get($base . '/api/public-news', ['per_page' => 4]);
            $items = $response->json('data');
            if (! $response->successful() || ! is_array($items)) {
                $resp2 = Http::acceptJson()->timeout(3)->get($base . '/api/landing-news');
                $items = $resp2->json('data');
                if (! $resp2->successful() || ! is_array($items)) {
                    return [];
                }
            }

            $related = [];
            foreach ($items as $item) {
                $post = $this->formatNewsItem($item);
                if ($post['slug'] !== $slug) {
                    $related[] = $post;
                }
                if (count($related) >= 3) {
                    break;
                }
            }

            return $related;
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function formatNewsItem(array $item): array
    {
        $title = (string) ($item['judul'] ?? $item['title'] ?? '');
        $slug = (string) ($item['slug'] ?? \Illuminate\Support\Str::slug($title));
        $dateStr = $item['tanggal_publish'] ?? $item['date'] ?? null;
        $isPublished = ! empty($dateStr);
        $dateOnly = $isPublished ? substr((string) $dateStr, 0, 10) : date('Y-m-d');
        $rawIsi = $item['isi'] ?? $item['body'] ?? '';
        $body = is_array($rawIsi) ? $rawIsi : explode("\n\n", (string) $rawIsi);
        $bodyText = is_array($rawIsi) ? implode("\n\n", $rawIsi) : (string) $rawIsi;

        $thumbnail = ! empty($item['gambar']) ? $item['gambar'] : ($item['thumbnail'] ?? null);
        $gallery = is_array($item['galeri'] ?? null) ? $item['galeri'] : [];
        $images = array_values(array_unique(array_filter(
            [$thumbnail, ...$gallery, ...(is_array($item['images'] ?? null) ? $item['images'] : [])],
            fn ($img) => is_string($img) && $img !== ''
        )));

        $attachments = is_array($item['lampiran'] ?? null) ? $item['lampiran'] : (is_array($item['attachments'] ?? null) ? $item['attachments'] : []);

        $author = $item['author'] ?? $item['user']['nama'] ?? 'Sekretariat Senat Polines';

        return [
            'id' => $item['id_berita'] ?? $item['id'] ?? 0,
            'title' => $title,
            'slug' => $slug,
            'category' => $item['kategori'] ?? $item['category'] ?? 'Warta Senat',
            'date' => $dateOnly,
            'published_at' => $dateStr,
            'status' => 'Terpublikasi',
            'excerpt' => \Illuminate\Support\Str::limit(strip_tags($bodyText), 180),
            'thumbnail' => $thumbnail,
            'images' => $images,
            'attachments' => $attachments,
            'author' => $author,
            'body' => $body,
        ];
    }
}
