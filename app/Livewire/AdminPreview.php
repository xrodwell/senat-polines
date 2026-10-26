<?php

namespace App\Livewire;

use App\Contracts\ArchiveRepositoryInterface;
use App\Contracts\AspirationRepositoryInterface;
use App\Contracts\CommitteeRepositoryInterface;
use App\Contracts\MeetingRepositoryInterface;
use App\Contracts\PostRepositoryInterface;
use App\Contracts\RegulationRepositoryInterface;
use App\Repositories\Api\ApiMeetingRepository;
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

    // State Presensi QR & Kuorum Live
    public ?int $activeMeetingId = null;
    public array $liveMeeting = [];
    public array $liveAnggota = [];
    public array $liveKuorum = [];
    public bool $showQrModal = false;
    public string $qrToken = '';
    public string $sessionCode = '';
    public bool $isPresensiOpen = false;
    public bool $isKuorumLocked = false;
    public string $filterKehadiran = 'semua'; // semua, hadir, izin, menunggu

    public function mount(MeetingRepositoryInterface $meetingsRepo): void
    {
        if (isset(self::MENU_KEYS[$this->active])) {
            $this->active = self::MENU_KEYS[$this->active];
        }

        $allowed = ['Dashboard', 'Dashboard Rapat', ...array_merge(...array_values(self::GROUPS))];
        if (! in_array($this->active, $allowed, true)) {
            $this->active = 'Dashboard Rapat';
        }

        $this->loadLiveAttendanceData($meetingsRepo);
    }

    public function select(string $screen): void
    {
        if (isset(self::MENU_KEYS[$screen])) {
            $screen = self::MENU_KEYS[$screen];
        }

        $allowed = ['Dashboard', 'Dashboard Rapat', ...array_merge(...array_values(self::GROUPS))];

        if (! in_array($screen, $allowed, true)) {
            return;
        }

        $this->active = $screen;
        $this->flashMessage = '';

        if (in_array($screen, ['Presensi & Verifikasi QR', 'Kuorum & Kehadiran Anggota'])) {
            $this->refreshAttendance();
        }
    }

    public function getBackendUrl(): string
    {
        return rtrim(env('SENAT_BACKEND_API_URL', env('BACKEND_API_URL', 'http://127.0.0.1:3000/api')), '/');
    }

    /**
     * Polling / Refresh data presensi dan kuorum live
     */
    public function refreshAttendance(): void
    {
        $repo = app(ApiMeetingRepository::class);
        $this->loadLiveAttendanceData($repo, true);
    }

    public function loadLiveAttendanceData(MeetingRepositoryInterface $meetingsRepo, bool $force = false): void
    {
        $activeSession = $meetingsRepo->getActiveSession($force);
        if ($activeSession && isset($activeSession['id'])) {
            $this->activeMeetingId = (int) $activeSession['id'];
            $this->liveMeeting = $activeSession;
            $this->qrToken = $activeSession['qr_token'] ?? '';
            $this->sessionCode = $activeSession['session_code'] ?? 'PLN-892';
            $this->isPresensiOpen = (bool) ($activeSession['is_presensi_open'] ?? true);
            $this->isKuorumLocked = (bool) ($activeSession['is_kuorum_locked'] ?? false);

            if ($meetingsRepo instanceof ApiMeetingRepository) {
                $liveData = $meetingsRepo->getLiveAttendance($this->activeMeetingId, $force);
                if ($liveData) {
                    $this->liveAnggota = $liveData['anggota'] ?? [];
                    $this->liveKuorum = $liveData['kuorum'] ?? ($activeSession['kuorum'] ?? []);
                    $this->isPresensiOpen = (bool) ($liveData['is_presensi_open'] ?? $this->isPresensiOpen);
                    $this->isKuorumLocked = (bool) ($liveData['is_kuorum_locked'] ?? $this->isKuorumLocked);
                    $this->qrToken = $liveData['token_qr'] ?? $this->qrToken;
                    $this->sessionCode = $liveData['kode_sesi'] ?? $this->sessionCode;
                    return;
                }
            }

            // Fallback kuorum mock
            $this->liveKuorum = [
                'total_invited' => $activeSession['total_invited'] ?? 34,
                'present_count' => $activeSession['present_count'] ?? 23,
                'izin_count' => 3,
                'tidak_hadir_count' => 0,
                'menunggu_count' => 8,
                'ambang_kuorum' => 23,
                'present_percentage' => 67.6,
                'is_kuorum' => true,
                'status_kuorum_label' => 'Kuorum Terpenuhi',
                'is_presensi_open' => $this->isPresensiOpen,
                'is_kuorum_locked' => $this->isKuorumLocked,
            ];
        }
    }

    /**
     * Generate QR Presensi & Kode Sesi Baru
     */
    public function generateQrPresensi(): void
    {
        if (!$this->activeMeetingId) {
            $this->flashMessage = 'Tidak ada sidang aktif yang dapat diaktifkan QR presensinya.';
            return;
        }

        $repo = app(ApiMeetingRepository::class);
        $res = $repo->generateQr($this->activeMeetingId);

        if ($res) {
            $this->qrToken = $res['token_qr'] ?? '';
            $this->sessionCode = $res['kode_sesi'] ?? '';
            $this->isPresensiOpen = true;
            $this->isKuorumLocked = false;
            if (!empty($res['kuorum'])) {
                $this->liveKuorum = $res['kuorum'];
            }
            $this->flashMessage = 'QR Presensi dan Kode Sesi resmi telah diaktifkan!';
            $this->showQrModal = true;
            $this->dispatch('open-qr-modal');
            $this->dispatch('qr-updated', token: $this->qrToken);
        } else {
            $this->flashMessage = 'Gagal menghasilkan token QR dari Backend.';
        }
    }

    /**
     * Buka Modal Tampilan QR
     */
    public function openQrModal(): void
    {
        if (empty($this->qrToken) || !$this->isPresensiOpen) {
            $this->generateQrPresensi();
        } else {
            $this->showQrModal = true;
            $this->dispatch('open-qr-modal');
            $this->dispatch('qr-updated', token: $this->qrToken);
        }
    }

    public function closeQrModal(): void
    {
        $this->showQrModal = false;
    }

    /**
     * Bekukan sesi presensi dan kunci status kuorum
     */
    public function freezePresensi(): void
    {
        if (!$this->activeMeetingId) {
            $this->flashMessage = 'Tidak ada sidang aktif yang dipilih.';
            return;
        }

        $repo = app(ApiMeetingRepository::class);
        $res = $repo->freezePresensi($this->activeMeetingId);

        $this->isPresensiOpen = false;
        $this->isKuorumLocked = true;
        $this->showQrModal = false;
        if ($res && !empty($res['kuorum'])) {
            $this->liveKuorum = $res['kuorum'];
        }
        $this->flashMessage = 'Sesi presensi telah dibekukan. QR Code langsung kedaluwarsa dan status kuorum telah dikunci!';
    }

    /**
     * Validasi manual kehadiran anggota dengan cap Admin
     */
    public function validateMember(int $userId, string $status): void
    {
        if (!$this->activeMeetingId) {
            return;
        }

        $repo = app(ApiMeetingRepository::class);
        $notes = 'Divalidasi Sekretariat (' . ucfirst($status) . ') pada ' . now()->format('H:i');
        $res = $repo->validateMemberAttendance($this->activeMeetingId, $userId, $status, $notes);

        if ($res && isset($res['undangan'])) {
            $undangan = $res['undangan'];
            // Update in-memory liveAnggota directly - avoids extra HTTP roundtrips that cause UI freeze
            foreach ($this->liveAnggota as &$item) {
                if ((int) $item['id_user'] === $userId) {
                    $item['status_kehadiran'] = $undangan['status_kehadiran'];
                    $item['is_edited_by_admin'] = (bool) $undangan['is_edited_by_admin'];
                    $item['catatan_admin'] = $undangan['catatan_admin'];
                    $item['waktu_presensi'] = $undangan['waktu_presensi'];
                    $item['metode_presensi'] = 'manual_admin';
                    break;
                }
            }
            unset($item);

            if (!empty($res['kuorum'])) {
                $this->liveKuorum = $res['kuorum'];
            }

            $label = match($status) {
                'hadir' => 'HADIR',
                'izin' => 'IZIN RESMI',
                'tidak_hadir' => 'TIDAK HADIR',
                default => strtoupper($status)
            };
            $this->flashMessage = "Status kehadiran anggota berhasil diubah menjadi: {$label} (Berstempel Verifikasi Admin).";
        } else {
            $this->flashMessage = "Gagal memperbarui status kehadiran anggota.";
        }
    }

    public function saveAction(string $entity): void
    {
        $this->flashMessage = "Data {$entity} berhasil diperbarui (tersimpan di state lokal). Siap disambungkan ke API backend.";
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

        // Filter anggota live
        $filteredAnggota = collect($this->liveAnggota)->filter(function ($a) {
            if ($this->filterKehadiran === 'hadir') return $a['status_kehadiran'] === 'hadir';
            if ($this->filterKehadiran === 'izin') return $a['status_kehadiran'] === 'izin';
            if ($this->filterKehadiran === 'tidak_hadir') return $a['status_kehadiran'] === 'tidak_hadir';
            if ($this->filterKehadiran === 'menunggu') return !in_array($a['status_kehadiran'], ['hadir', 'izin', 'tidak_hadir']);
            return true;
        })->values()->all();

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
            'filteredAnggota' => $filteredAnggota,
        ])->layout('layouts.admin-preview');
    }
}
