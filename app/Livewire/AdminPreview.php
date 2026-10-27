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
use Livewire\Attributes\Computed;
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

    // State Risalah & Notulensi Sidang (Backend API Integration)
    public array $notulensiList = [];
    public array $rapatList = [];
    public array $userList = [];
    public ?int $selectedNotulensiId = null;
    public ?int $notulensiRapatId = null;
    public ?int $notulensiUserId = 1;
    public string $notulensiIsi = '';
    public string $notulensiFile = '';
    public string $notulensiStatus = 'draf';
    public string $notulensiSearch = '';
    public string $notulensiFilterStatus = 'all';
    public ?int $viewingNotulensiId = null;
    public bool $isFormOpen = false;

    // State Katalog Regulasi & Produk Hukum (Backend API Integration)
    public array $regulasiList = [];
    public array $periodeList = [];
    public bool $isRegulasiFormOpen = false;
    public ?int $regulasiId = null;
    public string $regulasiNomor = '';
    public string $regulasiJudul = '';
    public string $regulasiKategori = 'Peraturan Senat';
    public string $regulasiTahap = 'Draf';
    public ?int $regulasiPeriodeId = null;
    public string $regulasiFile = '';
    public string $regulasiTanggal = '';
    public string $regulasiSearch = '';
    public string $regulasiFilterKategori = 'all';
    public string $regulasiFilterTahap = 'all';
    public ?int $viewingRegulasiId = null;

    public const REGULASI_KATEGORI = [
        'Peraturan Senat',
        'Peraturan Direktur',
        'SK Pertimbangan',
        'Rancangan Peraturan',
        'Standar & Pedoman',
        'SOP Akademik',
    ];

    public const REGULASI_TAHAP = [
        'Draf',
        'Pembahasan',
        'Harmonisasi',
        'Disahkan',
        'Ditolak',
    ];

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

        $this->loadNotulensiData();
        $this->loadRegulasiData();
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

    public function getBackendUrl(): string
    {
        $url = env('SENAT_BACKEND_API_URL') ?: env('BACKEND_API_URL') ?: 'http://127.0.0.1:8001/api';

        return rtrim($url, '/');
    }

    public function loadNotulensiData(): void
    {
        $base = $this->getBackendUrl();
        try {
            $res = Http::timeout(4)->get("{$base}/notulensi", ['all' => 1]);
            if ($res->successful()) {
                $data = $res->json();
                $this->notulensiList = is_array($data) ? (isset($data['data']) ? $data['data'] : $data) : [];
            }
        } catch (\Throwable $e) {}

        try {
            $resRapat = Http::timeout(4)->get("{$base}/rapat", ['all' => 1]);
            if ($resRapat->successful()) {
                $dataRapat = $resRapat->json();
                $this->rapatList = is_array($dataRapat) ? (isset($dataRapat['data']) ? $dataRapat['data'] : $dataRapat) : [];
                if (! $this->notulensiRapatId && ! empty($this->rapatList)) {
                    $this->notulensiRapatId = $this->rapatList[0]['id_rapat'] ?? null;
                }
            }
        } catch (\Throwable $e) {}

        try {
            $resUser = Http::timeout(4)->get("{$base}/user", ['all' => 1]);
            if ($resUser->successful()) {
                $dataUser = $resUser->json();
                $this->userList = is_array($dataUser) ? (isset($dataUser['data']) ? $dataUser['data'] : $dataUser) : [];
            }
        } catch (\Throwable $e) {}

        if (empty($this->rapatList)) {
            $this->rapatList = [
                ['id_rapat' => 1, 'nama_agenda' => 'Sidang Pleno Khusus Penetapan Draft OTK Senat 2026', 'tempat' => 'Ruang Sidang Utama', 'tanggal' => '2026-10-09'],
                ['id_rapat' => 2, 'nama_agenda' => 'Rapat Koordinasi Komisi I: Penyelarasan Kurikulum MBKM', 'tempat' => 'Ruang Rapat Senat', 'tanggal' => '2026-10-16'],
                ['id_rapat' => 3, 'nama_agenda' => 'Sidang Pleno Evaluasi Akreditasi Prodi Teknik', 'tempat' => 'Ruang Sidang Utama', 'tanggal' => '2026-10-24'],
            ];
            $this->notulensiRapatId = 1;
        }

        if (empty($this->userList)) {
            $this->userList = [
                ['id_user' => 1, 'nama' => 'Administrator Senat', 'nim_nip' => '198001012005011001'],
                ['id_user' => 2, 'nama' => 'Dr. Sukamto, S.Kom., M.T.', 'nim_nip' => '196803121993031002'],
                ['id_user' => 3, 'nama' => 'Andi Setyawan, S.E., M.M.', 'nim_nip' => '197405191999031001'],
            ];
        }
    }

    public function openNotulensiForm(): void
    {
        $this->isFormOpen = true;
    }

    public function closeNotulensiForm(): void
    {
        $this->isFormOpen = false;
        $this->resetNotulensiForm();
    }

    public function resetNotulensiForm(): void
    {
        $this->selectedNotulensiId = null;
        $this->notulensiRapatId = $this->rapatList[0]['id_rapat'] ?? null;
        $this->notulensiUserId = $this->userList[0]['id_user'] ?? 1;
        $this->notulensiIsi = '';
        $this->notulensiFile = '';
        $this->notulensiStatus = 'draf';
    }

    public function editNotulensi(int $id): void
    {
        $item = collect($this->notulensiList)->firstWhere('id_notulensi', $id);
        if ($item) {
            $this->selectedNotulensiId = $id;
            $this->notulensiRapatId = $item['id_rapat'] ?? null;
            $this->notulensiUserId = $item['id_user'] ?? 1;
            $this->notulensiIsi = $item['isi_notulensi'] ?? '';
            $this->notulensiFile = $item['file_risalah'] ?? '';
            $this->notulensiStatus = strtolower($item['status'] ?? 'draf');
            $this->isFormOpen = true;
        }
    }

    public function saveNotulensi(): void
    {
        if (empty(trim($this->notulensiIsi))) {
            $this->flashMessage = 'Peringatan: Jalannya pembahasan dan isi notulensi tidak boleh kosong!';
            return;
        }

        if (! $this->notulensiRapatId) {
            $this->flashMessage = 'Peringatan: Silakan pilih sidang/rapat terkait!';
            return;
        }

        $base = $this->getBackendUrl();
        $payload = [
            'id_rapat' => (int) $this->notulensiRapatId,
            'id_user' => (int) ($this->notulensiUserId ?: 1),
            'isi_notulensi' => $this->notulensiIsi,
            'file_risalah' => $this->notulensiFile ?: null,
            'status' => $this->notulensiStatus ?: 'draf',
        ];

        try {
            if ($this->selectedNotulensiId) {
                $res = Http::timeout(5)->put("{$base}/notulensi/{$this->selectedNotulensiId}", $payload);
                if ($res->successful()) {
                    $this->flashMessage = 'Berhasil! Notulensi & Risalah sidang berhasil diperbarui di database Backend.';
                } else {
                    $this->flashMessage = 'Gagal menyimpan: ' . ($res->json('message') ?? 'HTTP ' . $res->status());
                }
            } else {
                $res = Http::timeout(5)->post("{$base}/notulensi", $payload);
                if ($res->successful()) {
                    $this->flashMessage = 'Berhasil! Notulensi & Risalah sidang baru sukses disimpan ke database Backend.';
                } else {
                    $this->flashMessage = 'Gagal membuat notulensi: ' . ($res->json('message') ?? 'HTTP ' . $res->status());
                }
            }
            $this->loadNotulensiData();
            $this->closeNotulensiForm();
        } catch (\Throwable $e) {
            $this->flashMessage = 'Koneksi API Backend gagal: ' . $e->getMessage();
        }
    }

    public function deleteNotulensi(int $id): void
    {
        $base = $this->getBackendUrl();
        try {
            $res = Http::timeout(5)->delete("{$base}/notulensi/{$id}");
            if ($res->successful() || $res->status() === 204) {
                $this->flashMessage = "Notulensi ID #{$id} berhasil dihapus dari database.";
            } else {
                $this->flashMessage = "Gagal menghapus: HTTP " . $res->status();
            }
            $this->loadNotulensiData();
            if ($this->selectedNotulensiId === $id) {
                $this->resetNotulensiForm();
                $this->isFormOpen = false;
            }
            if ($this->viewingNotulensiId === $id) {
                $this->viewingNotulensiId = null;
            }
        } catch (\Throwable $e) {
            $this->flashMessage = 'Koneksi API Backend gagal: ' . $e->getMessage();
        }
    }

    public function setNotulensiStatus(int $id, string $status): void
    {
        $base = $this->getBackendUrl();
        try {
            $res = Http::timeout(5)->put("{$base}/notulensi/{$id}", ['status' => $status]);
            if ($res->successful()) {
                $this->flashMessage = "Status risalah berhasil diubah menjadi: " . ucfirst($status);
                $this->loadNotulensiData();
            }
        } catch (\Throwable $e) {
            $this->flashMessage = 'Gagal memperbarui status: ' . $e->getMessage();
        }
    }

    public function viewNotulensi(?int $id): void
    {
        $this->viewingNotulensiId = $id;
    }

    #[Computed]
    public function filteredNotulensi(): array
    {
        return collect($this->notulensiList)
            ->filter(function ($item) {
                if ($this->notulensiFilterStatus !== 'all') {
                    if (strtolower($item['status'] ?? 'draf') !== strtolower($this->notulensiFilterStatus)) {
                        return false;
                    }
                }
                if (! empty($this->notulensiSearch)) {
                    $search = strtolower($this->notulensiSearch);
                    $agenda = strtolower($item['rapat']['nama_agenda'] ?? '');
                    $isi = strtolower($item['isi_notulensi'] ?? '');
                    $tempat = strtolower($item['rapat']['tempat'] ?? '');
                    $notulis = strtolower($item['user']['nama'] ?? '');
                    if (! str_contains($agenda, $search) && ! str_contains($isi, $search) && ! str_contains($tempat, $search) && ! str_contains($notulis, $search)) {
                        return false;
                    }
                }
                return true;
            })
            ->values()
            ->all();
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

    /* ================= KATALOG REGULASI & PRODUK HUKUM ================= */

    public function loadRegulasiData(): void
    {
        $base = $this->getBackendUrl();

        try {
            $res = Http::timeout(4)->get("{$base}/peraturan", ['all' => 1]);
            if ($res->successful()) {
                $data = $res->json();
                $this->regulasiList = is_array($data) ? (isset($data['data']) ? $data['data'] : $data) : [];
            }
        } catch (\Throwable $e) {}

        try {
            $periode = Http::timeout(4)->get("{$base}/periode-senat", ['all' => 1]);
            if ($periode->successful()) {
                $dataP = $periode->json();
                $this->periodeList = is_array($dataP) ? (isset($dataP['data']) ? $dataP['data'] : $dataP) : [];
            }
        } catch (\Throwable $e) {}

        if (empty($this->periodeList)) {
            $this->periodeList = [
                ['id_periode_senat' => 1, 'nama_periode' => '2025/2026'],
                ['id_periode_senat' => 2, 'nama_periode' => '2026-2029'],
            ];
        }

        if (! $this->regulasiPeriodeId) {
            $lastPeriode = end($this->periodeList);
            $this->regulasiPeriodeId = $lastPeriode['id_periode_senat'] ?? 1;
        }
    }

    public function openRegulasiForm(): void
    {
        $this->resetRegulasiForm();
        $this->regulasiTanggal = now()->format('Y-m-d');
        $this->isRegulasiFormOpen = true;
    }

    public function closeRegulasiForm(): void
    {
        $this->isRegulasiFormOpen = false;
        $this->resetRegulasiForm();
    }

    public function resetRegulasiForm(): void
    {
        $this->regulasiId = null;
        $this->regulasiNomor = '';
        $this->regulasiJudul = '';
        $this->regulasiKategori = 'Peraturan Senat';
        $this->regulasiTahap = 'Draf';
        $this->regulasiFile = '';
        $this->regulasiTanggal = '';
        if (empty($this->regulasiPeriodeId)) {
            $this->regulasiPeriodeId = $this->periodeList[0]['id_periode_senat'] ?? 1;
        }
    }

    public function editRegulasi(int $id): void
    {
        $item = collect($this->regulasiList)->firstWhere('id_peraturan', $id);
        if (! $item) {
            return;
        }

        $this->regulasiId = $id;
        $this->regulasiNomor = $item['nomor_sk'] ?? '';
        $this->regulasiJudul = $item['judul'] ?? '';
        $this->regulasiKategori = $item['kategori'] ?? 'Peraturan Senat';
        $this->regulasiTahap = $item['tahap_pembentukan'] ?? 'Draf';
        $this->regulasiPeriodeId = $item['id_periode_senat'] ?? 1;
        $this->regulasiFile = $item['file_dokumen'] ?? '';
        $this->regulasiTanggal = ! empty($item['tanggal_terbit'])
            ? substr((string) $item['tanggal_terbit'], 0, 10)
            : now()->format('Y-m-d');
        $this->isRegulasiFormOpen = true;
    }

    public function saveRegulasi(): void
    {
        if (trim($this->regulasiNomor) === '' || trim($this->regulasiJudul) === '') {
            $this->flashMessage = 'Peringatan: Nomor dokumen dan judul regulasi wajib diisi!';
            return;
        }

        if (! $this->regulasiPeriodeId) {
            $this->flashMessage = 'Peringatan: Periode senat wajib dipilih!';
            return;
        }

        $base = $this->getBackendUrl();
        $payload = [
            'id_periode_senat' => (int) $this->regulasiPeriodeId,
            'nomor_sk' => $this->regulasiNomor,
            'judul' => $this->regulasiJudul,
            'kategori' => $this->regulasiKategori,
            'tahap_pembentukan' => $this->regulasiTahap,
            'file_dokumen' => $this->regulasiFile ?: null,
            'tanggal_terbit' => $this->regulasiTanggal ?: now()->format('Y-m-d'),
        ];

        try {
            if ($this->regulasiId) {
                $res = Http::timeout(6)->put("{$base}/peraturan/{$this->regulasiId}", $payload);
                $this->flashMessage = $res->successful()
                    ? 'Berhasil! Produk hukum berhasil diperbarui di database Backend.'
                    : 'Gagal memperbarui: ' . ($res->json('message') ?? 'HTTP ' . $res->status());
            } else {
                $res = Http::timeout(6)->post("{$base}/peraturan", $payload);
                $this->flashMessage = $res->successful()
                    ? 'Berhasil! Produk hukum baru sukses ditambahkan ke database Backend.'
                    : 'Gagal menambahkan: ' . ($res->json('message') ?? 'HTTP ' . $res->status());
            }

            $this->loadRegulasiData();
            $this->closeRegulasiForm();
        } catch (\Throwable $e) {
            $this->flashMessage = 'Koneksi API Backend gagal: ' . $e->getMessage();
        }
    }

    public function deleteRegulasi(int $id): void
    {
        $base = $this->getBackendUrl();
        try {
            $res = Http::timeout(6)->delete("{$base}/peraturan/{$id}");
            $this->flashMessage = ($res->successful() || $res->status() === 204)
                ? "Produk hukum ID #{$id} berhasil dihapus dari database."
                : 'Gagal menghapus: HTTP ' . $res->status();
            $this->loadRegulasiData();
            if ($this->viewingRegulasiId === $id) {
                $this->viewingRegulasiId = null;
            }
        } catch (\Throwable $e) {
            $this->flashMessage = 'Koneksi API Backend gagal: ' . $e->getMessage();
        }
    }

    public function setRegulasiTahap(int $id, string $tahap): void
    {
        $base = $this->getBackendUrl();
        try {
            $res = Http::timeout(6)->put("{$base}/peraturan/{$id}", ['tahap_pembentukan' => $tahap]);
            if ($res->successful()) {
                $this->flashMessage = "Tahap produk hukum diubah menjadi: {$tahap}";
                $this->loadRegulasiData();
            }
        } catch (\Throwable $e) {
            $this->flashMessage = 'Gagal memperbarui tahap: ' . $e->getMessage();
        }
    }

    public function viewRegulasi(?int $id): void
    {
        $this->viewingRegulasiId = $id;
    }

    #[Computed]
    public function filteredRegulasi(): array
    {
        return collect($this->regulasiList)
            ->filter(function ($item) {
                if ($this->regulasiFilterKategori !== 'all'
                    && ($item['kategori'] ?? '') !== $this->regulasiFilterKategori) {
                    return false;
                }

                if ($this->regulasiFilterTahap !== 'all'
                    && strtolower($item['tahap_pembentukan'] ?? '') !== strtolower($this->regulasiFilterTahap)) {
                    return false;
                }

                if (! empty($this->regulasiSearch)) {
                    $search = strtolower($this->regulasiSearch);
                    $haystack = strtolower(
                        ($item['judul'] ?? '') . ' ' .
                        ($item['nomor_sk'] ?? '') . ' ' .
                        ($item['kategori'] ?? '') . ' ' .
                        ($item['tahap_pembentukan'] ?? '')
                    );
                    if (! str_contains($haystack, $search)) {
                        return false;
                    }
                }

                return true;
            })
            ->values()
            ->all();
    }

    public function regulasiStatistik(): array
    {
        $all = collect($this->regulasiList);
        return [
            'total' => $all->count(),
            'disahkan' => $all->filter(fn ($r) => strtolower($r['tahap_pembentukan'] ?? '') === 'disahkan')->count(),
            'proses' => $all->filter(fn ($r) => in_array(strtolower($r['tahap_pembentukan'] ?? ''), ['draf', 'pembahasan', 'harmonisasi'], true))->count(),
            'kategori' => $all->pluck('kategori')->filter()->unique()->count(),
        ];
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
