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
        return view('public.home', [
            'posts' => $this->publishedPosts(),
            'activeSession' => $this->meetings->getActiveSession(),
            'upcomingMeetings' => $this->meetings->getUpcoming(),
            'committees' => $this->committees->all(),
            'features' => $this->features->all(),
            'direktori' => SenatDirectory::stats(),
        ]);
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
        if (str_contains($committee, 'Pendidikan') || str_contains($committee, 'Akademik') || str_contains($committee, 'I')) {
            $idKategori = 1;
        } elseif (str_contains($committee, 'Penelitian') || str_contains($committee, 'Pengabdian') || str_contains($committee, 'Sarana') || str_contains($committee, 'Fasilitas') || str_contains($committee, 'II')) {
            $idKategori = 2;
        } elseif (str_contains($committee, 'Kemahasiswaan') || str_contains($committee, 'Tata Tertib') || str_contains($committee, 'III')) {
            $idKategori = 3;
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
    public function news(): View
    {
        return view('public.news', [
            'posts' => $this->publishedPosts(),
        ]);
    }

    /** 14B — Detail Warta */
    public function newsShow(string $slug): View
    {
        $post = collect($this->posts->all())->firstWhere('slug', $slug);

        abort_if($post === null, 404);

        $related = collect($this->posts->all())
            ->reject(fn ($item) => $item['slug'] === $slug)
            ->take(3)
            ->values()
            ->all();

        return view('public.news-show', [
            'post' => $post,
            'related' => $related,
        ]);
    }

    private function publishedPosts(): array
    {
        $published = array_values(array_filter(
            $this->posts->all(),
            fn ($post) => ($post['status'] ?? 'Terpublikasi') === 'Terpublikasi'
        ));

        usort($published, function ($a, $b): int {
            $dateOrder = strcmp(
                $b['published_at'] ?? $b['date'] ?? '',
                $a['published_at'] ?? $a['date'] ?? ''
            );
            return $dateOrder !== 0 ? $dateOrder : ((int)($b['id'] ?? 0) <=> (int)($a['id'] ?? 0));
        });

        return $published;
    }
}
