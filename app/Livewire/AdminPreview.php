<?php

namespace App\Livewire;

use App\Contracts\ArchiveRepositoryInterface;
use App\Contracts\AspirationRepositoryInterface;
use App\Contracts\CommitteeRepositoryInterface;
use App\Contracts\MeetingRepositoryInterface;
use App\Contracts\PostRepositoryInterface;
use App\Contracts\RegulationRepositoryInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;

class AdminPreview extends Component
{
    /**
     * Sidebar IA — PRD 5. Dua kelompok kerja saja.
     * Menggunakan slug ID agar aman dari karakter HTML entity (&).
     *
     * @var array<string, list<string>>
     */
     public const MENU_KEYS = [
         'dashboard-rapat' => 'Dashboard Rapat',
         'jadwal-agenda-sidang' => 'Jadwal & Agenda Sidang',
         'presensi-verifikasi-qr' => 'Presensi & Verifikasi QR',
         'kuorum-kehadiran-anggota' => 'Kuorum & Kehadiran Anggota',
         'risalah-notulensi-sidang' => 'Risalah & Notulensi Sidang',
         'evoting-pengambilan-keputusan' => 'E-Voting & Pengambilan Keputusan',
         'pengaturan-informasi-hero' => 'Pengaturan Informasi & Hero',
         'profil-tupoksi-struktur' => 'Profil, Tupoksi & Struktur',
         'direktori-komisi-anggota' => 'Direktori Komisi & Anggota',
         'manajemen-berita-siaran-pers' => 'Manajemen Berita & Siaran Pers',
         'katalog-regulasi-produk-hukum' => 'Katalog Regulasi & Produk Hukum',
         'repositori-arsip-dokumen' => 'Repositori Arsip & Dokumen',
         'pusat-aspirasi-tindak-lanjut' => 'Pusat Aspirasi & Tindak Lanjut',
     ];

    public const GROUPS = [
        'Manajemen Persidangan & Rapat' => [
            'Dashboard Rapat',
            'Jadwal & Agenda Sidang',
            'Presensi & Verifikasi QR',
            'Kuorum & Kehadiran Anggota',
            'Risalah & Notulensi Sidang',
            'E-Voting & Pengambilan Keputusan',
        ],
        'Manajemen Publikasi & Portal Web' => [
            'Pengaturan Informasi & Hero',
            'Profil, Tupoksi & Struktur',
            'Direktori Komisi & Anggota',
            'Manajemen Berita & Siaran Pers',
            'Katalog Regulasi & Produk Hukum',
            'Repositori Arsip & Dokumen',
            'Pusat Aspirasi & Tindak Lanjut',
        ],
    ];

    #[Url(as: 'tab', history: true)]
    public string $active = 'Dashboard Rapat';

    // Form inputs state
    public string $formTitle = '';
    public string $formCategory = '';
    public string $formStatus = '';
    public string $flashMessage = '';

    // Form Pimpinan Kolektif Senat (Tersambung Backend API)
    public ?int $ketuaUserId = 1;
    public string $ketuaNama = 'Dr. Sukamto, S.Kom., M.T.';
    public string $ketuaNip = '196803121993031002';

    public ?int $sekretarisUserId = 2;
    public string $sekretarisNama = 'Andi Setyawan, S.E., M.M.';
    public string $sekretarisNip = '197405191999031001';

    public function mount(): void
    {
        // Query param 'tab' is automatically bound to $active via #[Url].
        // If tab is provided but invalid, fallback to Dashboard Rapat.
        if (isset(self::MENU_KEYS[$this->active])) {
            $this->active = self::MENU_KEYS[$this->active];
        }

        $allowed = ['Dashboard', 'Dashboard Rapat', ...array_merge(...array_values(self::GROUPS))];
        if (! in_array($this->active, $allowed, true)) {
            $this->active = 'Dashboard Rapat';
        }
    }

    public function select(string $screen): void
    {
        // Mendukung pemanggilan via slug key maupun nama langsung
        if (isset(self::MENU_KEYS[$screen])) {
            $screen = self::MENU_KEYS[$screen];
        }

        $allowed = ['Dashboard', 'Dashboard Rapat', ...array_merge(...array_values(self::GROUPS))];

        if (! in_array($screen, $allowed, true)) {
            return;
        }

        $this->active = $screen;
        $this->flashMessage = '';
    }

    public function saveAction(string $entity): void
    {
        $this->flashMessage = "Data {$entity} berhasil diperbarui (tersimpan di state lokal). Siap disambungkan ke API backend.";
    }

    public function getBackendUrl(): string
    {
        return rtrim(env('SENAT_BACKEND_API_URL', env('BACKEND_API_URL', 'http://127.0.0.1:8001/api')), '/');
    }

    public function updatePimpinan(): void
    {
        try {
            $base = $this->getBackendUrl();
            if ($this->ketuaUserId) {
                Http::timeout(5)->put("{$base}/user/{$this->ketuaUserId}", [
                    'nama' => $this->ketuaNama,
                    'nim_nip' => $this->ketuaNip,
                ]);
            }
            if ($this->sekretarisUserId) {
                Http::timeout(5)->put("{$base}/user/{$this->sekretarisUserId}", [
                    'nama' => $this->sekretarisNama,
                    'nim_nip' => $this->sekretarisNip,
                ]);
            }
            $this->flashMessage = 'Berhasil! Data Ketua & Sekretaris Senat sukses disimpan ke database Backend!';
        } catch (\Throwable $e) {
            $this->flashMessage = 'Gagal menyimpan ke Backend: ' . $e->getMessage();
        }
    }

    public function chipState(string $val): string
    {
        if (str_contains($val, 'Pleno') || str_contains($val, 'Ditindaklanjuti')) {
            return 'warning';
        }
        if (str_contains($val, 'Komisi') || str_contains($val, 'Sedang')) {
            return 'info';
        }
        if (str_contains($val, 'Selesai') || str_contains($val, 'Disahkan')) {
            return 'success';
        }
        return 'neutral';
    }

    public function chipLabel(string $val): string
    {
        if (str_contains($val, 'Pleno')) {
            return 'Pleno';
        }
        if (str_contains($val, 'Komisi')) {
            return 'Komisi';
        }
        return $val;
    }

    public function render(
        MeetingRepositoryInterface $meetingsRepo,
        AspirationRepositoryInterface $aspirationsRepo,
        ArchiveRepositoryInterface $archivesRepo,
        RegulationRepositoryInterface $regulationsRepo,
        PostRepositoryInterface $postsRepo,
        CommitteeRepositoryInterface $committeesRepo
    ): View {
        $meetings = $meetingsRepo->all();
        $aspirations = $aspirationsRepo->all();
        $archives = $archivesRepo->all();
        $regulations = $regulationsRepo->all();
        $posts = $postsRepo->all();
        $committees = $committeesRepo->all();

        $activeMeeting = $meetingsRepo->getActiveSession() ?? ($meetings[0] ?? null);
        $totalInvited = (int) collect($meetings)->sum('total_invited');
        $totalPresent = (int) collect($meetings)->sum('present_count');

        $stats = [
            'meetings_count' => count($meetings),
            'active_meeting' => $activeMeeting,
            'attendance_rate' => $totalInvited > 0 ? (int) round(($totalPresent / $totalInvited) * 100) : 82,
            'pending_aspirations' => collect($aspirations)->where('status', '!=', 'Selesai')->count(),
            'total_regulations' => count($regulations),
            'total_posts' => count($posts),
            'total_archives' => count($archives),
            'total_committees' => count($committees),
        ];

        $notulenItem = collect($archives)->where('category', 'Notulen Sidang')->first();
        $aspirasiItem = collect($aspirations)->where('status', 'Sedang Ditelaah')->first();
        $regDraft = collect($regulations)->where('status', 'Draf')->first();

        $antrean = collect([
            $notulenItem ? [
                'tone' => 'bg-[#a95100]',
                'title' => 'Verifikasi notulen pleno',
                'meta' => $notulenItem['title'],
            ] : null,
            $aspirasiItem ? [
                'tone' => 'bg-[#5131b4]',
                'title' => 'Penetapan status aspirasi',
                'meta' => $aspirasiItem['ticket_code'].' · '.$aspirasiItem['subject'],
            ] : null,
            $regDraft ? [
                'tone' => 'bg-[#155ead]',
                'title' => 'Validasi status hukum draf',
                'meta' => $regDraft['nomor'].' · '.$regDraft['title'],
            ] : null,
        ])->filter()->values();

        $aksiCepat = [
            ['action' => 'Jadwal & Agenda Sidang', 'icon' => 'calendar', 'title' => 'Buat Sidang', 'desc' => 'Atur jenis, waktu, lokasi, dan peserta', 'accent' => true],
            ['action' => 'Risalah & Notulensi Sidang', 'icon' => 'file-text', 'title' => 'Susun Notulensi', 'desc' => 'Catat pembahasan, keputusan, action items', 'accent' => false],
            ['action' => 'E-Voting & Pengambilan Keputusan', 'icon' => 'check', 'title' => 'Gelar Voting', 'desc' => 'Buka voting dan pantau kuorum', 'accent' => false],
        ];

        return view('livewire.admin-preview', [
            'meetings' => $meetings,
            'activeMeeting' => $activeMeeting,
            'aspirations' => $aspirations,
            'archives' => $archives,
            'notulenItem' => $notulenItem,
            'antrean' => $antrean,
            'aksiCepat' => $aksiCepat,
            'regulations' => $regulations,
            'posts' => $posts,
            'committees' => $committees,
            'stats' => $stats,
        ])->layout('layouts.admin-preview');
    }
}
