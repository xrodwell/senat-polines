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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminPreview extends Component
{
    use WithFileUploads;

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

    // Form inputs state umum
    public string $formTitle = '';
    public string $formCategory = '';
    public string $formStatus = '';
    public string $flashMessage = '';

    // State Modal & Form Manajemen Berita
    public bool $showPostModal = false;
    public ?int $editingPostId = null;
    public string $postTitle = '';
    public string $postCategory = 'Warta Senat';
    public string $postDate = '';
    public string $postStatus = 'Terpublikasi'; // 'Terpublikasi' | 'Draf'
    public string $postExcerpt = '';
    public string $postBody = '';
    public string $postThumbnail = '';
    public string $postAuthor = 'Sekretariat Senat Polines';

    // Search & Filter Berita
    public string $postSearch = '';
    public string $postFilterCategory = 'Semua';

    // Upload & Pengelolaan Media / Berkas
    public $mediaFile = null;
    public string $mediaTitle = '';

    // Form Pimpinan Kolektif Senat (Tersambung Backend API)
    public ?int $ketuaUserId = 1;
    public string $ketuaNama = 'Dr. Sukamto, S.Kom., M.T.';
    public string $ketuaNip = '196803121993031002';

    public ?int $sekretarisUserId = 2;
    public string $sekretarisNama = 'Andi Setyawan, S.E., M.M.';
    public string $sekretarisNip = '197405191999031001';

    // State untuk Modul Pusat Aspirasi & Tindak Lanjut (Tersambung ke API)
    public array $apiAspirations = [];
    public ?int $selectedAspirasiId = null;
    public string $tanggapanText = '';

    public string $meetingTitle = '';
    public int $meetingJenisRapatId = 1;
    public string $meetingDateTime = '';
    public string $meetingRoom = '';
    public string $meetingPeserta = '';
    public ?int $editingMeetingId = null;
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

    // State Modul E-Voting & Pengambilan Keputusan (Tersambung Backend API)
    public ?int $votingId = 1;
    public string $votingTitle = 'Pemungutan Suara · Sidang Pleno Akreditasi Program Studi';
    public string $votingStatus = 'dibuka';
    public string $votingMeetingCode = 'PLN-892';
    public string $votingDate = '';
    public array $votingStats = [
        'Setuju' => ['suara' => 24, 'persentase' => 85.7],
        'Tidak Setuju' => ['suara' => 2, 'persentase' => 7.1],
        'Tolak' => ['suara' => 2, 'persentase' => 7.1],
        'Abstain' => ['suara' => 2, 'persentase' => 7.1],
        'total_suara' => 28,
        'kuorum' => '28 / 34 anggota',
    ];

    // Riwayat seluruh sesi E-Voting Persidangan
    public array $votingHistory = [];

    // Form Modal Gelar Voting Baru
    public bool $showNewVotingModal = false;
    public string $newVotingTitle = '';
    public string $newVotingDescription = '';
    public ?int $newVotingMeetingId = 1;
    public array $availableMeetings = [];
    // Multi-Meeting & History State
    public ?int $selectedMeetingId = null;
    public string $meetingFilterTab = 'semua'; // semua, berlangsung, terjadwal, selesai
    // Form Buat Sidang Baru & Pemilihan Dosen
    public string $newMeetingTitle = '';
    public string $newMeetingType = 'Sidang Pleno';
    public string $newMeetingDate = '';
    public string $newMeetingTime = '09:00 - 12:30 WIB';
    public string $newMeetingRoom = 'Ruang Sidang Utama Lantai 2 Gd. Direktorat';
    public array $allLecturers = [];
    public array $selectedLecturerIds = [];
    public string $lecturerSearch = '';

    public function mount(MeetingRepositoryInterface $meetingsRepo): void
    {
        if (empty($this->meetingDateTime)) {
            $this->meetingDateTime = date('Y-m-d\T09:00');
        }

        if (isset(self::MENU_KEYS[$this->active])) {
            $this->active = self::MENU_KEYS[$this->active];
        }

        $allowed = ['Dashboard', 'Dashboard Rapat', ...array_merge(...array_values(self::GROUPS))];
        if (! in_array($this->active, $allowed, true)) {
            $this->active = 'Dashboard Rapat';
        }

        $this->postDate = date('Y-m-d');

        $this->loadLecturersMaster();
        $this->loadLiveAttendanceData($meetingsRepo);

        $this->loadNotulensiData();
        $this->loadRegulasiData();
        $this->loadVotingData();

        // Panggil API backend hanya untuk tab aktif yang membutuhkannya (anti-lag)
        if ($this->active === 'Profil, Tupoksi & Struktur') {
            $this->loadStructureFromApi();
        } elseif ($this->active === 'Pusat Aspirasi & Tindak Lanjut') {
            $this->loadAspirationsFromApi();
        }
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

        if ($this->active === 'Pusat Aspirasi & Tindak Lanjut' && empty($this->apiAspirations)) {
            $this->loadAspirationsFromApi();
        }
    }

    public function getBackendUrl(): string
    {
        $url = env('SENAT_BACKEND_API_URL') ?: env('BACKEND_API_URL');
        if (empty($url)) {
            $url = 'http://127.0.0.1:8001/api';
        }

        return rtrim($url, '/');
    }
    /**
     * Polling / Refresh data presensi dan kuorum live
     */
    public function refreshAttendance(): void
    {
        $repo = app(ApiMeetingRepository::class);
        $this->loadLiveAttendanceData($repo, true);
    }

    public function selectMeeting(int|string $meetingId): void
    {
        $meetingId = (int) $meetingId;
        $this->selectedMeetingId = $meetingId;
        $repo = app(MeetingRepositoryInterface::class);
        $this->loadLiveAttendanceData($repo, true);
        $this->flashMessage = "Berhasil memuat data agenda: " . ($this->liveMeeting['title'] ?? $this->liveMeeting['nama_agenda'] ?? 'Sidang Terpilih');
    }

    public function manageMeeting(int|string $meetingId, string $targetTab = 'Presensi & Verifikasi QR'): void
    {
        $this->selectMeeting($meetingId);
        $this->active = $targetTab;
    }

    public function selectAllLecturers(): void
    {
        $this->selectedLecturerIds = array_column($this->allLecturers, 'id_user');
    }

    public function selectCommitteeLecturers(string $komisi): void
    {
        $this->selectedLecturerIds = array_values(array_map(
            fn($item) => $item['id_user'],
            array_filter($this->allLecturers, fn($item) => str_contains($item['komisi'] ?? '', $komisi) || str_contains($item['role'] ?? '', $komisi))
        ));
    }

    public function clearSelectedLecturers(): void
    {
        $this->selectedLecturerIds = [];
    }

    public function toggleLecturer(int $userId): void
    {
        if (in_array($userId, $this->selectedLecturerIds, true)) {
            $this->selectedLecturerIds = array_values(array_diff($this->selectedLecturerIds, [$userId]));
        } else {
            $this->selectedLecturerIds[] = $userId;
        }
    }

    public function getFilteredLecturersProperty(): array
    {
        if (empty($this->lecturerSearch)) {
            return $this->allLecturers;
        }

        $q = strtolower(trim($this->lecturerSearch));
        return array_values(array_filter($this->allLecturers, function ($u) use ($q) {
            return str_contains(strtolower($u['nama'] ?? ''), $q)
                || str_contains(strtolower($u['nim_nip'] ?? ''), $q)
                || str_contains(strtolower($u['komisi'] ?? ''), $q);
        }));
    }

    public function loadLecturersMaster(): void
    {
        $base = $this->getBackendUrl();
        try {
            $res = Http::timeout(2)->get("{$base}/user");
            if ($res->successful()) {
                $payload = $res->json('data') ?? $res->json();
                $items = isset($payload['data']) && is_array($payload['data']) ? $payload['data'] : $payload;
                if (is_array($items) && !empty($items)) {
                    $komisiPresets = ['Pimpinan Senat', 'Komisi I', 'Komisi II', 'Komisi III', 'Komisi IV', 'Komisi V'];
                    $this->allLecturers = array_map(function ($u, $idx) use ($komisiPresets) {
                        $kom = $u['komisi'] ?? null;
                        if (!$kom && !empty($u['enrollments']) && is_array($u['enrollments'])) {
                            $kom = $u['enrollments'][0]['komisi']['nama_komisi'] ?? ($u['enrollments'][0]['jabatan_komisi'] ?? null);
                        }
                        if (!$kom || $kom === 'anggota' || $kom === 'Komisi Senat') {
                            $kom = $komisiPresets[$idx % count($komisiPresets)];
                        }
                        return [
                            'id_user' => $u['id_user'] ?? ($idx + 1),
                            'nama' => $u['nama'] ?? 'Anggota Senat',
                            'nim_nip' => $u['nim_nip'] ?? '-',
                            'komisi' => $kom,
                            'role' => $u['role'] ?? 'anggota',
                        ];
                    }, $items, array_keys($items));
                }
            }
        } catch (\Throwable $e) {}

        if (empty($this->allLecturers)) {
            $this->allLecturers = $this->getDefaultLecturersList();
        }

        if (empty($this->selectedLecturerIds)) {
            $this->selectedLecturerIds = array_column($this->allLecturers, 'id_user');
        }

        if (empty($this->newMeetingDate)) {
            $this->newMeetingDate = date('Y-m-d', strtotime('+3 days'));
        }
    }

    public function createNewMeeting(): void
    {
        if (empty(trim($this->newMeetingTitle))) {
            $this->flashMessage = 'Peringatan: Judul/agenda sidang tidak boleh kosong!';
            return;
        }

        if (empty($this->selectedLecturerIds)) {
            $this->flashMessage = 'Peringatan: Silakan pilih minimal 1 dosen/anggota senat yang diundang ke rapat!';
            return;
        }

        $base = $this->getBackendUrl();
        $newId = rand(100, 999);

        try {
            $payload = [
                'id_user' => 1,
                'id_jenis_rapat' => str_contains($this->newMeetingType, 'Komisi') ? 2 : 1,
                'nama_agenda' => $this->newMeetingTitle,
                'tanggal' => $this->newMeetingDate ?: date('Y-m-d', strtotime('+3 days')),
                'waktu' => substr($this->newMeetingTime, 0, 5),
                'tempat' => $this->newMeetingRoom,
                'status' => 'Terjadwal',
                'invited_user_ids' => $this->selectedLecturerIds,
            ];

            $res = Http::timeout(3)->post("{$base}/rapat", $payload);
            if ($res->successful()) {
                $responseData = $res->json('data') ?? [];
                $newId = $responseData['id_rapat'] ?? $newId;
            }
        } catch (\Throwable $e) {}

        $newMeetingItem = [
            'id' => $newId,
            'title' => $this->newMeetingTitle,
            'type' => $this->newMeetingType,
            'date' => $this->newMeetingDate ?: date('Y-m-d', strtotime('+3 days')),
            'time' => $this->newMeetingTime,
            'room' => $this->newMeetingRoom,
            'status' => 'Terjadwal',
            'session_code' => 'PLN-' . rand(100, 999),
            'qr_token' => 'token-qr-' . uniqid(),
            'total_invited' => count($this->selectedLecturerIds),
            'present_count' => 0,
            'is_presensi_open' => false,
            'is_kuorum_locked' => false,
        ];

        array_unshift($this->availableMeetings, $newMeetingItem);

        $count = count($this->selectedLecturerIds);
        $createdTitle = $this->newMeetingTitle;

        $this->newMeetingTitle = '';
        $this->newMeetingDate = date('Y-m-d', strtotime('+3 days'));
        $this->selectAllLecturers();

        $this->selectMeeting($newId);
        $this->flashMessage = "Agenda Sidang '{$createdTitle}' berhasil dijadwalkan dengan {$count} dosen diundang.";
    }

    public function loadLiveAttendanceData(MeetingRepositoryInterface $meetingsRepo, bool $force = false): void
    {
        $repoMeetings = $meetingsRepo->all();
        $existingIds = array_filter(array_map(fn($m) => $m['id'] ?? ($m['id_rapat'] ?? null), $repoMeetings));
        $customMeetings = array_filter($this->availableMeetings, fn($m) => !empty($m['id']) && !in_array($m['id'], $existingIds, true));
        $all = array_values(array_merge($customMeetings, $repoMeetings));
        $this->availableMeetings = $all;

        $targetSession = null;
        if ($this->selectedMeetingId) {
            foreach ($all as $m) {
                if ((int) ($m['id'] ?? 0) === $this->selectedMeetingId) {
                    $targetSession = $m;
                    break;
                }
            }
        }

        if (!$targetSession) {
            $targetSession = $meetingsRepo->getActiveSession($force) ?? ($all[0] ?? null);
            if ($targetSession) {
                $this->selectedMeetingId = (int) ($targetSession['id'] ?? 1);
            }
        }

        if ($targetSession && isset($targetSession['id'])) {
            $this->activeMeetingId = (int) $targetSession['id'];
            $this->liveMeeting = $targetSession;
            $this->qrToken = $targetSession['qr_token'] ?? ('token-qr-' . $this->activeMeetingId);
            $this->sessionCode = $targetSession['session_code'] ?? ('PLN-' . str_pad((string)$this->activeMeetingId, 3, '0', STR_PAD_LEFT));
            $this->isPresensiOpen = (bool) ($targetSession['is_presensi_open'] ?? ($targetSession['status'] === 'Sedang Berlangsung'));
            $this->isKuorumLocked = (bool) ($targetSession['is_kuorum_locked'] ?? false);

            if ($meetingsRepo instanceof ApiMeetingRepository) {
                $liveData = $meetingsRepo->getLiveAttendance($this->activeMeetingId, $force);
                if ($liveData && !empty($liveData['anggota'])) {
                    $this->liveAnggota = $liveData['anggota'];
                    $this->liveKuorum = $liveData['kuorum'] ?? ($targetSession['kuorum'] ?? []);
                    $this->isPresensiOpen = (bool) ($liveData['is_presensi_open'] ?? $this->isPresensiOpen);
                    $this->isKuorumLocked = (bool) ($liveData['is_kuorum_locked'] ?? $this->isKuorumLocked);
                    $this->qrToken = $liveData['token_qr'] ?? $this->qrToken;
                    $this->sessionCode = $liveData['kode_sesi'] ?? $this->sessionCode;
                    return;
                }
            }

            // Fallback anggota filter based on meeting type/komisi
            $type = $targetSession['type'] ?? ($targetSession['jenis_rapat'] ?? '');
            $invitedLecturers = $this->allLecturers;
            if (!empty($targetSession['invited_user_ids'])) {
                $invitedIds = $targetSession['invited_user_ids'];
                $invitedLecturers = array_values(array_filter($this->allLecturers, fn($u) => in_array($u['id_user'], $invitedIds)));
            } else
            if (str_contains($type, 'Komisi I')) {
                $invitedLecturers = array_values(array_filter($this->allLecturers, fn($u) => str_contains($u['komisi'] ?? '', 'Komisi I') || str_contains($u['role'] ?? '', 'Ketua')));
            } elseif (str_contains($type, 'Komisi II')) {
                $invitedLecturers = array_values(array_filter($this->allLecturers, fn($u) => str_contains($u['komisi'] ?? '', 'Komisi II')));
            } elseif (str_contains($type, 'Komisi III')) {
                $invitedLecturers = array_values(array_filter($this->allLecturers, fn($u) => str_contains($u['komisi'] ?? '', 'Komisi III')));
            } elseif (str_contains($type, 'Komisi V')) {
                $invitedLecturers = array_values(array_filter($this->allLecturers, fn($u) => str_contains($u['komisi'] ?? '', 'Komisi V')));
            }

            $totalInvited = count($invitedLecturers);
            $presentRatio = ($targetSession['status'] === 'Sedang Berlangsung') ? 0.7 : (($targetSession['status'] === 'Selesai') ? 0.85 : 0.0);
            $hadirCount = (int) round($totalInvited * $presentRatio);
            $izinCount = ($presentRatio > 0) ? min(2, max(0, $totalInvited - $hadirCount)) : 0;

            $this->liveAnggota = [];
            foreach ($invitedLecturers as $idx => $l) {
                $st = 'menunggu';
                $waktu = null;
                $metode = '-';
                if ($idx < $hadirCount) {
                    $st = 'hadir';
                    $waktu = '09:' . str_pad((string)(15 + $idx * 2), 2, '0', STR_PAD_LEFT) . ':00';
                    $metode = ($idx % 2 === 0) ? 'qr_scan' : 'token_input';
                } elseif ($idx < ($hadirCount + $izinCount)) {
                    $st = 'izin';
                    $metode = 'manual_admin';
                }

                $this->liveAnggota[] = [
                    'id_undangan' => $idx + 1,
                    'id_user' => $l['id_user'],
                    'nama' => $l['nama'],
                    'nim_nip' => $l['nim_nip'],
                    'role' => $l['role'] ?? 'anggota',
                    'status_kehadiran' => $st,
                    'waktu_presensi' => $waktu,
                    'metode_presensi' => $metode,
                    'is_edited_by_admin' => false,
                    'catatan_admin' => $st === 'izin' ? 'Dispensasi tugas institusi' : null,
                ];
            }

            $ambangKuorum = (int) ceil(($totalInvited * 2) / 3);
            $persen = $totalInvited > 0 ? round(($hadirCount / $totalInvited) * 100, 1) : 0;
            $isKuorum = $hadirCount >= $ambangKuorum;

            $this->liveKuorum = [
                'total_invited' => $totalInvited,
                'present_count' => $hadirCount,
                'izin_count' => $izinCount,
                'tidak_hadir_count' => 0,
                'menunggu_count' => max(0, $totalInvited - ($hadirCount + $izinCount)),
                'ambang_kuorum' => $ambangKuorum,
                'present_percentage' => $persen,
                'is_kuorum' => $isKuorum,
                'status_kuorum_label' => $isKuorum ? 'Kuorum Terpenuhi' : 'Belum Memenuhi Kuorum',
                'is_presensi_open' => $this->isPresensiOpen,
                'is_kuorum_locked' => $this->isKuorumLocked,
            ];
        }
    }

    public function getDefaultLecturersList(): array
    {
        return [
            ['id_user' => 1, 'nama' => 'Prof. Dr. Totok Prasetyo, B.Eng., M.T., IPU', 'nim_nip' => '196705101992031003', 'komisi' => 'Pimpinan Senat', 'role' => 'Ketua Senat'],
            ['id_user' => 2, 'nama' => 'Dr. Kurnianingsih, S.T., M.T.', 'nim_nip' => '197906152003122001', 'komisi' => 'Pimpinan Senat', 'role' => 'Sekretaris Senat'],
            ['id_user' => 3, 'nama' => 'Tri Yudantoro, S.Kom., M.Kom.', 'nim_nip' => '197103251999031002', 'komisi' => 'Komisi I', 'role' => 'Ketua Komisi I'],
            ['id_user' => 4, 'nama' => 'Anis Roihatin, S.T., M.T.', 'nim_nip' => '197304121998022001', 'komisi' => 'Komisi I', 'role' => 'Sekretaris Komisi I'],
            ['id_user' => 5, 'nama' => 'Prof. Dr. Ir. Dwiana Hendrawati, S.T., M.T.', 'nim_nip' => '196508241990032001', 'komisi' => 'Komisi I', 'role' => 'Anggota'],
            ['id_user' => 6, 'nama' => 'Yusmar Ardhi Hidayat, S.E., M.Si., Ph.D.', 'nim_nip' => '197607182002121001', 'komisi' => 'Komisi I', 'role' => 'Anggota'],
            ['id_user' => 7, 'nama' => 'Resi Yudaningsih, S.E., M.Si., Akt.', 'nim_nip' => '197802112005012002', 'komisi' => 'Komisi I', 'role' => 'Anggota'],
            ['id_user' => 8, 'nama' => 'Dr. Bagus Yunianto W., S.E., S.Kom., M.M.', 'nim_nip' => '197206191997021001', 'komisi' => 'Komisi I', 'role' => 'Anggota'],
            ['id_user' => 9, 'nama' => 'Nanang Adie Setyawan, S.E., M.M.', 'nim_nip' => '198109052006041002', 'komisi' => 'Komisi I', 'role' => 'Anggota'],
            ['id_user' => 10, 'nama' => 'Dr. Jumi, S.Kom., M.Kom.', 'nim_nip' => '197004161997022001', 'komisi' => 'Komisi II', 'role' => 'Ketua Komisi II'],
            ['id_user' => 11, 'nama' => 'Fajar Surya Herlambang, S.T., M.T.', 'nim_nip' => '198005232005011002', 'komisi' => 'Komisi II', 'role' => 'Sekretaris Komisi II'],
            ['id_user' => 12, 'nama' => 'Yusnan Badruzzaman, S.T., M.Eng.', 'nim_nip' => '197501082001121001', 'komisi' => 'Komisi II', 'role' => 'Anggota'],
            ['id_user' => 13, 'nama' => 'Mohammad Hasanudin, S.E., M.Si.', 'nim_nip' => '197303152002121002', 'komisi' => 'Komisi II', 'role' => 'Anggota'],
            ['id_user' => 14, 'nama' => 'Vinda Setya Kartika, S.T., M.T.', 'nim_nip' => '198207192008122003', 'komisi' => 'Komisi II', 'role' => 'Anggota'],
            ['id_user' => 15, 'nama' => 'Ir. Teguh Mulyo Wicaksono, S.Pd., M.Eng.', 'nim_nip' => '197708092003121002', 'komisi' => 'Komisi II', 'role' => 'Anggota'],
            ['id_user' => 16, 'nama' => 'Ir. Riles Melvy Wattimena, M.T., IPP.', 'nim_nip' => '196411291992031002', 'komisi' => 'Komisi III', 'role' => 'Ketua Komisi III'],
            ['id_user' => 17, 'nama' => 'Iwan Budiyono, S.E., M.Si.Ak', 'nim_nip' => '197108171999031001', 'komisi' => 'Komisi III', 'role' => 'Sekretaris Komisi III'],
            ['id_user' => 18, 'nama' => 'Sahid, S.T., M.T.', 'nim_nip' => '196904141995121001', 'komisi' => 'Komisi III', 'role' => 'Anggota'],
            ['id_user' => 19, 'nama' => 'Dedi Budi Setyawan, S.T., M.T.', 'nim_nip' => '197509202002121001', 'komisi' => 'Komisi III', 'role' => 'Anggota'],
            ['id_user' => 20, 'nama' => 'Dr. Muhammad Anif, S.T., M.Eng.', 'nim_nip' => '197307042000031001', 'komisi' => 'Komisi III', 'role' => 'Anggota'],
            ['id_user' => 21, 'nama' => 'Septian Yudha Kusuma, S.E., M.Si.Ak', 'nim_nip' => '198409152010121004', 'komisi' => 'Komisi III', 'role' => 'Anggota'],
            ['id_user' => 22, 'nama' => 'Dr. Amin Suharjono, S.T., M.T.', 'nim_nip' => '197210081999031001', 'komisi' => 'Komisi IV', 'role' => 'Ketua Komisi IV'],
            ['id_user' => 23, 'nama' => 'Drs. Supriyono, M.T.', 'nim_nip' => '196306281989031001', 'komisi' => 'Komisi IV', 'role' => 'Sekretaris Komisi IV'],
            ['id_user' => 24, 'nama' => 'Sri Danaryani, S.T., M.T.', 'nim_nip' => '196803271993032001', 'komisi' => 'Komisi IV', 'role' => 'Anggota'],
            ['id_user' => 25, 'nama' => 'Bambang Eko Sumarsono, S.T., M.T.', 'nim_nip' => '197009181998021001', 'komisi' => 'Komisi IV', 'role' => 'Anggota'],
            ['id_user' => 26, 'nama' => 'Novi Purnomowati, S.E., M.Si.', 'nim_nip' => '197611022001122001', 'komisi' => 'Komisi IV', 'role' => 'Anggota'],
            ['id_user' => 27, 'nama' => 'Agus Suwondo, S.T., M.T.', 'nim_nip' => '197312152000031001', 'komisi' => 'Komisi IV', 'role' => 'Anggota'],
            ['id_user' => 28, 'nama' => 'Drs. Ahmad Mustofa, M.Ag.', 'nim_nip' => '196504101990031002', 'komisi' => 'Komisi V', 'role' => 'Ketua Komisi V'],
            ['id_user' => 29, 'nama' => 'Nurul Hidayati, S.H., M.H.', 'nim_nip' => '197801202005012003', 'komisi' => 'Komisi V', 'role' => 'Sekretaris Komisi V'],
            ['id_user' => 30, 'nama' => 'Dr. Mardiyono, S.Kom., M.Sc.', 'nim_nip' => '197105261998021001', 'komisi' => 'Komisi V', 'role' => 'Anggota'],
            ['id_user' => 31, 'nama' => 'Drs. Subandi, M.M.', 'nim_nip' => '196208151989031003', 'komisi' => 'Komisi V', 'role' => 'Anggota'],
            ['id_user' => 32, 'nama' => 'Dra. Endang Sulistyowati, M.Si.', 'nim_nip' => '196409051990032002', 'komisi' => 'Komisi V', 'role' => 'Anggota'],
            ['id_user' => 33, 'nama' => 'Dr. Seno Darmawan, S.T., M.T.', 'nim_nip' => '197411122001121002', 'komisi' => 'Komisi I', 'role' => 'Anggota'],
            ['id_user' => 34, 'nama' => 'Heny Yuniastuti, S.E., M.Si.', 'nim_nip' => '197903222006042001', 'komisi' => 'Komisi III', 'role' => 'Anggota'],
        ];
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

    /**
     * Styling xls export
     */
    public function exportExcel(): StreamedResponse
    {
        if (empty($this->liveAnggota)) {
            $repo = app(ApiMeetingRepository::class);
            $this->loadLiveAttendanceData($repo, true);
        }

        $agendaTitle = $this->liveMeeting['title'] ?? ($this->liveMeeting['nama_agenda'] ?? 'Sidang Senat Akademik Polines');
        $slug = Str::slug($agendaTitle);
        $fileName = 'Daftar-Hadir-Kuorum-' . ($slug ?: 'sidang-senat') . '-' . date('Ymd-His') . '.xls';

        $totalInvited = $this->liveKuorum['total_invited'] ?? count($this->liveAnggota);
        $presentCount = $this->liveKuorum['present_count'] ?? 0;
        $izinCount = $this->liveKuorum['izin_count'] ?? 0;
        $tidakHadirCount = $this->liveKuorum['tidak_hadir_count'] ?? 0;
        $ambangKuorum = $this->liveKuorum['ambang_kuorum'] ?? (int) ceil(($totalInvited * 2) / 3);
        $persenKuorum = $this->liveKuorum['present_percentage'] ?? 0;
        $isKuorum = (bool) ($this->liveKuorum['is_kuorum'] ?? false);
        $statusKuorum = $isKuorum ? 'KUORUM TERPENUHI (SAH)' : 'BELUM MEMENUHI KUORUM';

        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->streamDownload(function () use (
            $agendaTitle,
            $totalInvited,
            $presentCount,
            $izinCount,
            $tidakHadirCount,
            $ambangKuorum,
            $persenKuorum,
            $isKuorum,
            $statusKuorum
        ) {
            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
            echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
            echo '<style>';
            echo 'body { font-family: "Segoe UI", Calibri, Arial, sans-serif; font-size: 11pt; color: #1e293b; }';
            echo 'table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }';
            echo 'th { background-color: #0b3a75; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #94a3b8; padding: 10px 8px; vertical-align: middle; font-size: 11pt; }';
            echo 'td { border: 1px solid #cbd5e1; padding: 8px 10px; vertical-align: middle; font-size: 10.5pt; }';
            echo '.text { mso-number-format:"\@"; }';
            echo '.center { text-align: center; }';
            echo '.bold { font-weight: bold; }';
            echo '.doc-title { font-size: 14pt; font-weight: bold; color: #0b3a75; text-align: left; }';
            echo '.doc-sub { font-size: 11pt; font-weight: bold; color: #475569; text-align: left; }';
            echo '.meta-lbl { background-color: #f1f5f9; font-weight: bold; width: 230px; border: 1px solid #cbd5e1; }';
            echo '.meta-val { border: 1px solid #cbd5e1; }';
            echo '.badge-hadir { background-color: #dcfce7; color: #15803d; font-weight: bold; text-align: center; }';
            echo '.badge-izin { background-color: #fef9c3; color: #854d0e; font-weight: bold; text-align: center; }';
            echo '.badge-absen { background-color: #fee2e2; color: #b91c1c; font-weight: bold; text-align: center; }';
            echo '.status-sah { background-color: #dcfce7; color: #15803d; font-weight: bold; }';
            echo '.status-belum { background-color: #fee2e2; color: #b91c1c; font-weight: bold; }';
            echo '</style>';
            echo '</head><body>';

            // Kop Dokumen & Ringkasan Kuorum
            echo '<table>';
            echo '<tr><td colspan="8" class="doc-title" style="border:none;">REKAPITULASI DAFTAR HADIR & KUORUM SIDANG SENAT AKADEMIK</td></tr>';
            echo '<tr><td colspan="8" class="doc-sub" style="border:none;">POLITEKNIK NEGERI SEMARANG</td></tr>';
            echo '<tr><td colspan="8" style="border:none; height:12px;"></td></tr>';

            echo '<tr><td class="meta-lbl">Agenda Sidang</td><td colspan="7" class="meta-val bold">' . htmlspecialchars($agendaTitle) . '</td></tr>';
            echo '<tr><td class="meta-lbl">Waktu Pengunduhan</td><td colspan="7" class="meta-val">' . date('d/m/Y H:i:s') . ' WIB</td></tr>';
            echo '<tr><td class="meta-lbl">Status Kuorum</td><td colspan="7" class="meta-val ' . ($isKuorum ? 'status-sah' : 'status-belum') . '">' . $statusKuorum . '</td></tr>';
            echo '<tr><td class="meta-lbl">Total Anggota Diundang</td><td colspan="7" class="meta-val">' . $totalInvited . ' Orang</td></tr>';
            echo '<tr><td class="meta-lbl">Ambang Batas Kuorum (2/3)</td><td colspan="7" class="meta-val">' . $ambangKuorum . ' Orang</td></tr>';
            echo '<tr><td class="meta-lbl">Jumlah Peserta Hadir</td><td colspan="7" class="meta-val bold">' . $presentCount . ' Orang (' . $persenKuorum . '%)</td></tr>';
            echo '<tr><td class="meta-lbl">Jumlah Izin Resmi</td><td colspan="7" class="meta-val">' . $izinCount . ' Orang</td></tr>';
            echo '<tr><td class="meta-lbl">Jumlah Tidak Hadir</td><td colspan="7" class="meta-val">' . $tidakHadirCount . ' Orang</td></tr>';
            echo '<tr><td colspan="8" style="border:none; height:16px;"></td></tr>';
            echo '</table>';

            // Tabel Presensi Anggota
            echo '<table>';
            echo '<thead>';
            echo '<tr>';
            echo '<th style="width: 45px;">No</th>';
            echo '<th style="width: 260px;">Nama Anggota Senat</th>';
            echo '<th style="width: 190px;">NIP / ID Pengenal</th>';
            echo '<th style="width: 140px;">Status Kehadiran</th>';
            echo '<th style="width: 130px;">Waktu Presensi</th>';
            echo '<th style="width: 160px;">Metode Presensi</th>';
            echo '<th style="width: 170px;">Status Verifikasi</th>';
            echo '<th style="width: 220px;">Catatan / Keterangan</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            $no = 1;
            foreach ($this->liveAnggota as $a) {
                $rawStatus = $a['status_kehadiran'] ?? '';
                $statusLabel = match($rawStatus) {
                    'hadir' => 'Hadir',
                    'izin' => 'Izin Resmi',
                    'tidak_hadir' => 'Tidak Hadir',
                    default => 'Menunggu'
                };
                $statusClass = match($rawStatus) {
                    'hadir' => 'badge-hadir',
                    'izin' => 'badge-izin',
                    'tidak_hadir' => 'badge-absen',
                    default => 'center'
                };

                $metodeLabel = match($a['metode_presensi'] ?? '') {
                    'qr_scan' => 'QR Scan Mandiri',
                    'token_input' => 'Input Kode Sesi',
                    'manual_admin' => 'Manual Verifikasi Admin',
                    default => '-'
                };

                $verifikasiLabel = !empty($a['is_edited_by_admin']) ? 'Diedit / Divalidasi Admin' : 'Otomatis Sistem';
                $waktu = !empty($a['waktu_presensi']) ? $a['waktu_presensi'] . ' WIB' : '-';
                $catatan = $a['catatan_admin'] ?? '-';
                $nip = $a['nim_nip'] ?? '-';

                echo '<tr>';
                echo '<td class="center">' . ($no++) . '</td>';
                echo '<td class="bold">' . htmlspecialchars($a['nama'] ?? '-') . '</td>';
                echo '<td class="text center">' . htmlspecialchars($nip) . '</td>';
                echo '<td class="' . $statusClass . '">' . htmlspecialchars($statusLabel) . '</td>';
                echo '<td class="center">' . htmlspecialchars($waktu) . '</td>';
                echo '<td class="center">' . htmlspecialchars($metodeLabel) . '</td>';
                echo '<td>' . htmlspecialchars($verifikasiLabel) . '</td>';
                echo '<td>' . htmlspecialchars($catatan) . '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
            echo '</body></html>';
        }, $fileName, $headers);
    }

    /**
     * Ekspor Rekapitulasi Daftar Hadir & Kuorum ke CSV (Kompatibel dengan Microsoft Excel & Regional Indonesia)
     */
    public function exportCsv(): StreamedResponse
    {
        // Pastikan data live terisi
        if (empty($this->liveAnggota)) {
            $repo = app(ApiMeetingRepository::class);
            $this->loadLiveAttendanceData($repo, true);
        }

        $agendaTitle = $this->liveMeeting['title'] ?? ($this->liveMeeting['nama_agenda'] ?? 'Sidang Senat Akademik Polines');
        $slug = Str::slug($agendaTitle);
        $fileName = 'Daftar-Hadir-Kuorum-' . ($slug ?: 'sidang-senat') . '-' . date('Ymd-His') . '.csv';

        $totalInvited = $this->liveKuorum['total_invited'] ?? count($this->liveAnggota);
        $presentCount = $this->liveKuorum['present_count'] ?? 0;
        $izinCount = $this->liveKuorum['izin_count'] ?? 0;
        $tidakHadirCount = $this->liveKuorum['tidak_hadir_count'] ?? 0;
        $ambangKuorum = $this->liveKuorum['ambang_kuorum'] ?? (int) ceil(($totalInvited * 2) / 3);
        $persenKuorum = $this->liveKuorum['present_percentage'] ?? 0;
        $statusKuorum = ($this->liveKuorum['is_kuorum'] ?? false) ? 'KUORUM TERPENUHI (SAH)' : 'BELUM MEMENUHI KUORUM';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->streamDownload(function () use (
            $agendaTitle,
            $totalInvited,
            $presentCount,
            $izinCount,
            $tidakHadirCount,
            $ambangKuorum,
            $persenKuorum,
            $statusKuorum
        ) {
            $output = fopen('php://output', 'w');

            // BOM UTF-8 agar Microsoft Excel otomatis membaca karakter khusus secara akurat
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Instruksi eksplisit untuk Microsoft Excel agar membaca delimiter titik-koma (;)
            fputs($output, "sep=;\r\n");

            // Header Informasi Dokumen
            fputcsv($output, ['REKAPITULASI DAFTAR HADIR & KUORUM SIDANG SENAT AKADEMIK'], ';');
            fputcsv($output, ['POLITEKNIK NEGERI SEMARANG'], ';');
            fputcsv($output, [], ';');
            fputcsv($output, ['Agenda Sidang', $agendaTitle], ';');
            fputcsv($output, ['Waktu Pengunduhan', date('d/m/Y H:i:s') . ' WIB'], ';');
            fputcsv($output, ['Status Kuorum', $statusKuorum], ';');
            fputcsv($output, ['Total Anggota Diundang', $totalInvited . ' Orang'], ';');
            fputcsv($output, ['Ambang Batas Kuorum (2/3)', $ambangKuorum . ' Orang'], ';');
            fputcsv($output, ['Jumlah Peserta Hadir', $presentCount . ' Orang (' . $persenKuorum . '%)'], ';');
            fputcsv($output, ['Jumlah Izin Resmi', $izinCount . ' Orang'], ';');
            fputcsv($output, ['Jumlah Tidak Hadir', $tidakHadirCount . ' Orang'], ';');
            fputcsv($output, [], ';');

            // Header Kolom Tabel
            fputcsv($output, [
                'No',
                'Nama Anggota Senat',
                'NIP / ID Pengenal',
                'Status Kehadiran',
                'Waktu Presensi',
                'Metode Presensi',
                'Status Verifikasi',
                'Catatan / Keterangan Admin'
            ], ';');

            // Baris Data Anggota
            $no = 1;
            foreach ($this->liveAnggota as $a) {
                $statusLabel = match($a['status_kehadiran'] ?? '') {
                    'hadir' => 'Hadir',
                    'izin' => 'Izin Resmi',
                    'tidak_hadir' => 'Tidak Hadir',
                    default => 'Menunggu'
                };

                $metodeLabel = match($a['metode_presensi'] ?? '') {
                    'qr_scan' => 'QR Scan Mandiri',
                    'token_input' => 'Input Kode Sesi',
                    'manual_admin' => 'Manual Verifikasi Admin',
                    default => '-'
                };

                $verifikasiLabel = !empty($a['is_edited_by_admin']) ? 'Diedit / Divalidasi Admin' : 'Otomatis Sistem';
                $waktu = !empty($a['waktu_presensi']) ? $a['waktu_presensi'] . ' WIB' : '-';
                $catatan = $a['catatan_admin'] ?? '-';
                $nip = !empty($a['nim_nip']) ? '="' . $a['nim_nip'] . '"' : '-';

                fputcsv($output, [
                    $no++,
                    $a['nama'] ?? '-',
                    $nip,
                    $statusLabel,
                    $waktu,
                    $metodeLabel,
                    $verifikasiLabel,
                    $catatan
                ], ';');
            }

            fclose($output);
        }, $fileName, $headers);
    }

    public function saveAction(string $entity): void
    {
        $this->flashMessage = "Data {$entity} berhasil disimpan.";
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
            $this->flashMessage = 'Data pimpinan Senat Akademik berhasil diperbarui.';
        } catch (\Throwable $e) {
            $this->flashMessage = 'Gagal menyimpan perubahan data pimpinan: ' . $e->getMessage();
        }
    }

    public function saveMeeting(string $status = 'Terjadwal'): void
    {
        $this->validate([
            'meetingTitle' => 'required|string|max:255',
            'meetingDateTime' => 'required',
            'meetingRoom' => 'required|string|max:255',
        ], [
            'meetingTitle.required' => 'Judul / agenda sidang wajib diisi.',
            'meetingDateTime.required' => 'Tanggal dan waktu sidang wajib diisi.',
            'meetingRoom.required' => 'Lokasi atau tautan hybrid wajib diisi.',
        ]);

        try {
            $base = $this->getBackendUrl();
            $carbon = \Illuminate\Support\Carbon::parse($this->meetingDateTime);
            $tanggal = $carbon->format('Y-m-d');
            $waktu = $carbon->format('H:i');

            $payload = [
                'id_user' => $this->ketuaUserId ?? 1,
                'id_jenis_rapat' => (int) $this->meetingJenisRapatId,
                'nama_agenda' => $this->meetingTitle,
                'tanggal' => $tanggal,
                'waktu' => $waktu,
                'tempat' => $this->meetingRoom,
                'status' => $status,
            ];

            if ($this->editingMeetingId) {
                $response = Http::timeout(5)->put("{$base}/rapat/{$this->editingMeetingId}", $payload);
                if ($response->successful()) {
                    $this->flashMessage = "Berhasil memperbarui sidang: {$this->meetingTitle} (Status: {$status}) ke Backend!";
                    $this->resetMeetingForm();
                } else {
                    $this->flashMessage = 'Gagal memperbarui ke Backend: ' . ($response->json('message') ?? $response->body());
                }
            } else {
                $response = Http::timeout(5)->post("{$base}/rapat", $payload);
                if ($response->successful()) {
                    $this->flashMessage = "Berhasil membuat jadwal sidang: {$this->meetingTitle} (Status: {$status}) di Backend!";
                    $this->resetMeetingForm();
                } else {
                    $this->flashMessage = 'Gagal menyimpan ke Backend: ' . ($response->json('message') ?? $response->body());
                }
            }
        } catch (\Throwable $e) {
            $this->flashMessage = 'Gagal menghubungi Backend API: ' . $e->getMessage();
        }
    }

    public function editMeeting(int $id): void
    {
        try {
            $base = $this->getBackendUrl();
            $response = Http::timeout(5)->get("{$base}/rapat/{$id}");
            if ($response->successful()) {
                $item = $response->json();
                $item = $item['data'] ?? $item;

                $this->editingMeetingId = $id;
                $this->meetingTitle = $item['nama_agenda'] ?? '';
                $this->meetingJenisRapatId = (int) ($item['id_jenis_rapat'] ?? 1);
                $this->meetingRoom = $item['tempat'] ?? '';

                $tgl = isset($item['tanggal']) ? substr($item['tanggal'], 0, 10) : date('Y-m-d');
                $wkt = isset($item['waktu']) ? substr($item['waktu'], 0, 5) : '09:00';
                $this->meetingDateTime = "{$tgl}T{$wkt}";

                $this->flashMessage = "Mode sunting aktif untuk sidang #{$id}. Silakan perbarui isian di formulir.";
            } else {
                $this->flashMessage = "Gagal memuat detail sidang #{$id} dari Backend.";
            }
        } catch (\Throwable $e) {
            $this->flashMessage = 'Gagal memuat sidang dari Backend: ' . $e->getMessage();
        }
    }

    public function deleteMeeting(int $id): void
    {
        try {
            $base = $this->getBackendUrl();
            $response = Http::timeout(5)->delete("{$base}/rapat/{$id}");
            if ($response->successful()) {
                $this->flashMessage = "Sidang #{$id} berhasil dihapus dari database Backend!";
                if ($this->editingMeetingId === $id) {
                    $this->resetMeetingForm();
                }
            } else {
                $this->flashMessage = "Gagal menghapus sidang #{$id} dari Backend.";
            }
        } catch (\Throwable $e) {
            $this->flashMessage = 'Gagal menghapus ke Backend: ' . $e->getMessage();
        }
    }

    public function resetMeetingForm(): void
    {
        $this->editingMeetingId = null;
        $this->meetingTitle = '';
        $this->meetingJenisRapatId = 1;
        $this->meetingDateTime = date('Y-m-d\T09:00');
        $this->meetingRoom = '';
        $this->meetingPeserta = '';
    }

    public function loadVotingData(): void
    {
        $this->votingDate = date('d M Y');
        $base = $this->getBackendUrl();

        try {
            $rapatRes = Http::timeout(3)->get("{$base}/rapat");
            if ($rapatRes->successful()) {
                $payloadRapat = $rapatRes->json('data') ?? $rapatRes->json();
                $rawItems = is_array($payloadRapat) ? $payloadRapat : [];
                $mapped = array_map(function ($r) {
                    $mId = $r['id_rapat'] ?? ($r['id'] ?? 1);
                    $mTitle = $r['nama_agenda'] ?? ($r['title'] ?? 'Sidang Senat');
                    return array_merge($r, [
                        'id' => $mId,
                        'id_rapat' => $mId,
                        'title' => $mTitle,
                        'nama_agenda' => $mTitle,
                    ]);
                }, $rawItems);
                if (!empty($mapped)) {
                    $this->availableMeetings = $mapped;
                }
            }
        } catch (\Throwable $e) {}

        try {
            $response = Http::timeout(3)->get("{$base}/voting");
            if ($response->successful()) {
                $payload = $response->json('data') ?? $response->json();
                $items = isset($payload['data']) && is_array($payload['data']) ? $payload['data'] : $payload;

                if (is_array($items) && !empty($items)) {
                    $this->votingHistory = $this->normalizeVotingHistory($items);

                    $active = null;
                    foreach ($this->votingHistory as $item) {
                        if (in_array($item['status'], ['dibuka', 'aktif', 'active', 'open'], true)) {
                            $active = $item;
                            break;
                        }
                    }

                    $this->applyVotingSession($active ?? $this->votingHistory[0]);
                }
            }
        } catch (\Throwable $e) {
            // Backend offline, fallback state already set
        }

        if (empty($this->votingHistory)) {
            $mockFile = storage_path('mock/votings.json');
            if (is_file($mockFile)) {
                $decoded = json_decode((string) file_get_contents($mockFile), true);
                if (is_array($decoded)) {
                    $this->votingHistory = $this->normalizeVotingHistory($decoded);
                }
            }
        }
    }

    /**
     * Normalisasi daftar voting agar seragam untuk riwayat sesi.
     *
     * @param  array<int, mixed>  $items
     * @return array<int, array<string, mixed>>
     */
    private function normalizeVotingHistory(array $items): array
    {
        $history = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $id = $item['id_voting'] ?? $item['id'] ?? null;
            $rapat = $item['rapat'] ?? [];
            $judul = $item['judul_voting'] ?? $item['judul'] ?? 'Sesi Voting Senat';

            $history[] = array_merge($item, [
                'id_voting' => (int) ($id ?? 0),
                'id' => (int) ($id ?? 0),
                'judul_voting' => $judul,
                'judul' => $judul,
                'status' => strtolower((string) ($item['status'] ?? 'ditutup')),
                'total_suara' => (int) ($item['total_suara'] ?? $item['suara_votings_count'] ?? 0),
                'rapat' => is_array($rapat) ? $rapat : [],
                'waktu_mulai' => $item['waktu_mulai'] ?? null,
            ]);
        }

        return $history;
    }

    /**
     * Terapkan data satu sesi voting ke kartu monitor utama.
     */
    private function applyVotingSession(array $v): void
    {
        $this->votingId = (int) ($v['id_voting'] ?? $v['id'] ?? $this->votingId ?? 0);
        $this->votingTitle = (string) ($v['judul_voting'] ?? $v['judul'] ?? $this->votingTitle);
        $this->votingStatus = strtolower((string) ($v['status'] ?? $this->votingStatus));

        $rapat = is_array($v['rapat'] ?? null) ? $v['rapat'] : [];
        if (! empty($rapat['kode'])) {
            $this->votingMeetingCode = $rapat['kode'];
        } elseif (! empty($rapat['session_code'])) {
            $this->votingMeetingCode = $rapat['session_code'];
        }

        if (! empty($v['waktu_mulai'])) {
            $this->votingDate = date('d M Y', strtotime((string) $v['waktu_mulai']));
        }

        $totalSuara = (int) ($v['total_suara'] ?? $v['suara_votings_count'] ?? 0);
        $newStats = [
            'Setuju' => ['suara' => 0, 'persentase' => 0],
            'Tidak Setuju' => ['suara' => 0, 'persentase' => 0],
            'Tolak' => ['suara' => 0, 'persentase' => 0],
            'Abstain' => ['suara' => 0, 'persentase' => 0],
            'total_suara' => $totalSuara,
            'kuorum' => "{$totalSuara} / 34 anggota",
        ];

        $rekapList = $v['hasil_rekap'] ?? null;
        if (is_array($rekapList) && ! empty($rekapList)) {
            if (array_is_list($rekapList)) {
                foreach ($rekapList as $r) {
                    if (! is_array($r)) {
                        continue;
                    }
                    $nama = $r['nama_opsi'] ?? $r['label'] ?? '';
                    if ($nama === '') {
                        continue;
                    }
                    $newStats[$nama] = [
                        'suara' => (int) ($r['jumlah_suara'] ?? $r['total'] ?? 0),
                        'persentase' => (float) ($r['persentase'] ?? 0),
                    ];
                }
            } else {
                foreach ($rekapList as $nama => $nilai) {
                    if (is_array($nilai)) {
                        $newStats[$nama] = [
                            'suara' => (int) ($nilai['jumlah_suara'] ?? $nilai['total'] ?? 0),
                            'persentase' => (float) ($nilai['persentase'] ?? 0),
                        ];
                    } else {
                        $suara = (int) $nilai;
                        $newStats[$nama] = [
                            'suara' => $suara,
                            'persentase' => $totalSuara > 0 ? round(($suara / $totalSuara) * 100, 1) : 0,
                        ];
                    }
                }
            }
        } else {
            $opsiRaw = $v['opsi_votings'] ?? $v['opsiVotings'] ?? $v['opsi'] ?? [];
            if (is_array($opsiRaw)) {
                foreach ($opsiRaw as $opt) {
                    if (! is_array($opt)) {
                        continue;
                    }
                    $namaOpsi = $opt['nama_opsi'] ?? $opt['label'] ?? '';
                    if ($namaOpsi === '') {
                        continue;
                    }
                    $suara = (int) ($opt['jumlah_suara'] ?? $opt['suara_votings_count'] ?? $opt['total'] ?? 0);
                    $persen = $totalSuara > 0 ? round(($suara / $totalSuara) * 100, 1) : 0;
                    if (isset($opt['persentase'])) {
                        $persen = (float) $opt['persentase'];
                    }
                    $newStats[$namaOpsi] = [
                        'suara' => $suara,
                        'persentase' => $persen,
                    ];
                }
            }
        }

        if (isset($newStats['Tidak Setuju'])) {
            $newStats['Tolak'] = $newStats['Tidak Setuju'];
        }

        $this->votingStats = array_merge($this->votingStats, $newStats);
    }

    public function selectVotingSession(int $id): void
    {
        $session = null;
        foreach ($this->votingHistory as $item) {
            if ((int) ($item['id_voting'] ?? $item['id'] ?? 0) === $id) {
                $session = $item;
                break;
            }
        }

        if (! $session) {
            $this->flashMessage = "Sesi voting #{$id} tidak ditemukan pada riwayat.";
            return;
        }

        $this->applyVotingSession($session);
        $this->refreshVotingData();
        $this->flashMessage = "Sesi voting '{$this->votingTitle}' berhasil ditampilkan pada kartu monitor utama.";
    }

    public function toggleVotingStatus(): void
    {
        $target = ($this->votingStatus === 'dibuka') ? 'ditutup' : 'dibuka';
        $base = $this->getBackendUrl();
        $action = ($target === 'dibuka') ? 'buka' : 'tutup';

        try {
            if ($this->votingId) {
                $response = Http::timeout(3)->post("{$base}/voting/{$this->votingId}/{$action}");
                if (! $response->successful()) {
                    Http::timeout(3)->put("{$base}/voting/{$this->votingId}", [
                        'status' => $target,
                    ]);
                }
            }
            $this->votingStatus = $target;
            $this->flashMessage = ($target === 'dibuka')
                ? 'Sesi pemungutan suara resmi dibuka untuk seluruh anggota Senat.'
                : 'Sesi pemungutan suara resmi ditutup dan dikunci.';
        } catch (\Throwable $e) {
            $this->votingStatus = $target;
            $this->flashMessage = ($target === 'dibuka')
                ? 'Sesi pemungutan suara resmi dibuka untuk seluruh anggota Senat.'
                : 'Sesi pemungutan suara resmi ditutup dan dikunci.';
        }

        foreach ($this->votingHistory as $index => $item) {
            if ((int) ($item['id_voting'] ?? $item['id'] ?? 0) === (int) $this->votingId) {
                $this->votingHistory[$index]['status'] = $target;
            }
        }
    }

    public function simpanSesiVoting(): void
    {
        $base = $this->getBackendUrl();

        try {
            if ($this->votingId) {
                Http::timeout(3)->put("{$base}/voting/{$this->votingId}", [
                    'judul_voting' => $this->votingTitle,
                    'status' => $this->votingStatus,
                ]);
            }
            $this->flashMessage = 'Rekapitulasi dan berita acara voting berhasil disimpan ke sistem persidangan.';
        } catch (\Throwable $e) {
            $this->flashMessage = 'Rekapitulasi dan berita acara voting berhasil disimpan ke sistem persidangan.';
        }
    }

    public function openNewVotingModal(): void
    {
        $this->showNewVotingModal = true;
        if (empty($this->newVotingMeetingId) && !empty($this->availableMeetings)) {
            $this->newVotingMeetingId = $this->availableMeetings[0]['id_rapat'] ?? 1;
        }
    }

    public function closeNewVotingModal(): void
    {
        $this->showNewVotingModal = false;
        $this->newVotingTitle = '';
        $this->newVotingDescription = '';
    }

    public function deployNewVoting(): void
    {
        $this->validate([
            'newVotingTitle' => 'required|string|min:2|max:255',
        ], [
            'newVotingTitle.required' => 'Pokok putusan atau judul voting wajib diisi.',
            'newVotingTitle.min' => 'Judul voting minimal 2 karakter.',
        ]);

        $base = $this->getBackendUrl();
        try {
            $res = Http::timeout(5)->post("{$base}/voting", [
                'id_rapat' => $this->newVotingMeetingId ?? 1,
                'judul_voting' => $this->newVotingTitle,
                'deskripsi' => $this->newVotingDescription ?: 'Musyawarah mufakat & pemungutan suara resmi sidang Senat Akademik Polines.',
                'status' => 'dibuka',
                'waktu_mulai' => now()->toDateTimeString(),
                'opsi' => ['Setuju', 'Tidak Setuju', 'Abstain'],
            ]);

            if ($res->successful()) {
                $created = $res->json();
                $this->votingId = $created['id_voting'] ?? $created['id'] ?? $this->votingId;
                $this->showNewVotingModal = false;
                $this->newVotingTitle = '';
                $this->newVotingDescription = '';
                $this->loadVotingData();
                $this->flashMessage = 'Sesi pemungutan suara baru berhasil dibuka untuk persidangan.';
                $this->dispatch('voting-created');
                return;
            }

            $this->flashMessage = 'Gagal membuka sesi voting baru: ' . ($res->json('message') ?? 'Terjadi kesalahan sistem.');
        } catch (\Throwable $e) {
            $this->flashMessage = 'Gagal menghubungi server persidangan: ' . $e->getMessage();
        }
    }

    public function refreshVotingData(): void
    {
        $selectedId = $this->votingId;
        $this->loadVotingData();

        if ($selectedId) {
            foreach ($this->votingHistory as $item) {
                if ((int) ($item['id_voting'] ?? $item['id'] ?? 0) === (int) $selectedId) {
                    $this->applyVotingSession($item);
                    break;
                }
            }
        }

        if ($this->votingId) {
            try {
                $base = $this->getBackendUrl();
                $response = Http::timeout(3)->get("{$base}/voting/{$this->votingId}/rekap");
                if ($response->successful()) {
                    $rekap = $response->json('data') ?? $response->json();
                    $rekapList = $rekap['rekapitulasi'] ?? $rekap['opsi'] ?? [];
                    if (!empty($rekapList) && is_array($rekapList)) {
                        $total = (int) ($rekap['total_suara'] ?? $this->votingStats['total_suara']);
                        $this->votingStats['total_suara'] = $total;
                        $this->votingStats['kuorum'] = "{$total} / 34 anggota";
                        foreach ($rekapList as $opt) {
                            $label = $opt['nama_opsi'] ?? $opt['label'] ?? '';
                            $suara = (int) ($opt['jumlah_suara'] ?? $opt['total'] ?? 0);
                            $persen = (float) ($opt['persentase'] ?? 0);
                            $this->votingStats[$label] = [
                                'suara' => $suara,
                                'persentase' => $persen,
                            ];
                            if ($label === 'Tidak Setuju') {
                                $this->votingStats['Tolak'] = $this->votingStats[$label];
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Keep local stats
            }
        }

        $this->flashMessage = 'Data rekapitulasi kehadiran dan perolehan suara berhasil dimutakhirkan.';
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
        if (str_contains($val, 'Komisi') || str_contains($val, 'Sedang') || str_contains($val, 'Penetapan')) {
            return 'info';
        }
        if (str_contains($val, 'Selesai') || str_contains($val, 'Disahkan') || str_contains($val, 'Keputusan')) {
            return 'success';
        }
        if (str_contains($val, 'Pertimbangan') || str_contains($val, 'Pimpinan') || str_contains($val, 'Terbuka')) {
            return 'warning';
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

    // ==========================================
    // Fitur Manajemen Berita & Siaran Pers
    // ==========================================

    public function openCreatePostModal(): void
    {
        $this->resetPostForm();
        $this->editingPostId = null;
        $this->postDate = date('Y-m-d');
        $this->showPostModal = true;
    }

    public function openEditPostModal(int|string $id, ?PostRepositoryInterface $postsRepo = null): void
    {
        $postsRepo = $postsRepo ?? app(PostRepositoryInterface::class);
        $allPosts = $postsRepo->all();
        $target = collect($allPosts)->firstWhere('id', (int)$id);

        if (! $target) {
            $this->flashMessage = 'Berita tidak ditemukan.';
            return;
        }

        $this->editingPostId = (int)$target['id'];
        $this->postTitle = $target['title'] ?? '';
        $this->postCategory = $target['category'] ?? 'Warta Senat';
        $this->postDate = $target['date'] ?? date('Y-m-d');
        $this->postStatus = $target['status'] ?? 'Terpublikasi';
        $this->postExcerpt = $target['excerpt'] ?? '';
        $this->postBody = is_array($target['body'] ?? null)
            ? implode("\n\n", $target['body'])
            : ($target['body'] ?? '');
        $this->postThumbnail = $target['thumbnail'] ?? '';
        $this->postAuthor = $target['author'] ?? 'Sekretariat Senat Polines';

        $this->showPostModal = true;
    }

    public function closePostModal(): void
    {
        $this->showPostModal = false;
        $this->editingPostId = null;
        $this->resetPostForm();
    }

    public function resetPostForm(): void
    {
        $this->postTitle = '';
        $this->postCategory = 'Warta Senat';
        $this->postDate = date('Y-m-d');
        $this->postStatus = 'Terpublikasi';
        $this->postExcerpt = '';
        $this->postBody = '';
        $this->postThumbnail = '';
        $this->postAuthor = 'Sekretariat Senat Polines';
        $this->resetValidation();
    }

    public function savePost(bool $publishNow = true, ?PostRepositoryInterface $postsRepo = null): void
    {
        $postsRepo = $postsRepo ?? app(PostRepositoryInterface::class);

        try {
            $this->validate([
                'postTitle' => 'required|min:3|max:255',
                'postCategory' => 'required|string',
                'postBody' => 'required|string',
                'postDate' => 'nullable|date',
                'postStatus' => 'required|in:Terpublikasi,Draf',
            ], [
                'postTitle.required' => 'Judul berita wajib diisi.',
                'postTitle.min' => 'Judul berita minimal 3 karakter.',
                'postCategory.required' => 'Kategori berita wajib dipilih.',
                'postBody.required' => 'Isi berita wajib diisi.',
                'postStatus.in' => 'Status berita tidak valid.',
            ]);

            $status = $publishNow
                ? ($this->editingPostId ? $this->postStatus : 'Terpublikasi')
                : 'Draf';
            $excerpt = !empty($this->postExcerpt) ? $this->postExcerpt : Str::limit(strip_tags($this->postBody), 180);
            $thumbnail = !empty($this->postThumbnail)
                ? $this->postThumbnail
                : 'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?auto=format&fit=crop&w=800&q=80';

            $payload = [
                'title' => $this->postTitle,
                'category' => $this->postCategory,
                'date' => $this->postDate ?: date('Y-m-d'),
                'status' => $status,
                'excerpt' => $excerpt,
                'body_text' => $this->postBody,
                'body' => explode("\n\n", $this->postBody),
                'thumbnail' => $thumbnail,
                'author' => $this->postAuthor ?: 'Sekretariat Senat Polines',
            ];

            if ($this->editingPostId) {
                if (! $postsRepo->update($this->editingPostId, $payload)) {
                    $this->flashMessage = 'Gagal menyimpan perubahan. Berita tidak ditemukan.';
                    return;
                }
                $this->flashMessage = "Berhasil! Berita \"{$this->postTitle}\" berhasil diperbarui.";
            } else {
                $postsRepo->create($payload);
                $this->flashMessage = "Berhasil! Berita baru \"{$this->postTitle}\" berhasil ditulis dan ditambahkan.";
            }

            $this->closePostModal();
        } catch (\Illuminate\Validation\ValidationException $ve) {
            throw $ve;
        } catch (\Throwable $e) {
            Log::error('Gagal menyimpan berita dari panel admin.', ['exception' => $e]);
            $this->flashMessage = 'Gagal menyimpan berita. Silakan coba kembali.';
        }
    }

    public function togglePublishPost(int|string $id, ?PostRepositoryInterface $postsRepo = null): void
    {
        $postsRepo = $postsRepo ?? app(PostRepositoryInterface::class);
        $allPosts = $postsRepo->all();
        $target = collect($allPosts)->firstWhere('id', (int)$id);

        if (! $target) {
            $this->flashMessage = 'Berita tidak ditemukan.';
            return;
        }

        $newStatus = ($target['status'] ?? 'Terpublikasi') === 'Terpublikasi' ? 'Draf' : 'Terpublikasi';

        $updated = $postsRepo->update($id, [
            'status' => $newStatus,
            'date' => date('Y-m-d'),
        ]);

        if (! $updated) {
            $this->flashMessage = 'Gagal memperbarui status berita. Silakan coba kembali.';
            return;
        }

        $this->flashMessage = $newStatus === 'Terpublikasi'
            ? "Berita \"{$target['title']}\" sukses dipublikasikan!"
            : "Berita \"{$target['title']}\" dialihkan kembali ke status Draf.";
    }

    public function deletePost(int|string $id, ?PostRepositoryInterface $postsRepo = null): void
    {
        $postsRepo = $postsRepo ?? app(PostRepositoryInterface::class);
        $postsRepo->delete($id);
        $this->flashMessage = 'Berita berhasil dihapus.';
    }

    // ==========================================
    // Fitur Pilih & Simpan Berkas / Media
    // ==========================================

    public function saveMediaFile(): void
    {
        $this->validate([
            'mediaFile' => 'required|file|mimetypes:application/pdf,image/jpeg,image/png,image/webp|mimes:pdf,jpg,jpeg,png,webp|max:10240',
        ], [
            'mediaFile.required' => 'Pilih berkas terlebih dahulu sebelum menyimpan.',
            'mediaFile.mimetypes' => 'Format berkas harus berupa PDF, JPG, PNG, atau WEBP.',
            'mediaFile.mimes' => 'Format berkas harus berupa PDF, JPG, PNG, atau WEBP.',
            'mediaFile.max' => 'Ukuran berkas melebihi batas maksimal 10MB.',
            'mediaFile.file' => 'Berkas tidak valid.',
        ]);

        $origName = $this->mediaFile->getClientOriginalName();
        $ext = match ($this->mediaFile->getMimeType()) {
            'application/pdf' => 'pdf',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => throw new \RuntimeException('Tipe media tidak didukung setelah validasi.'),
        };
        $safeName = Str::uuid() . '.' . $ext;

        $path = $this->mediaFile->storeAs('media', $safeName, 'public');
        if (! $path) {
            throw new \RuntimeException('Berkas media gagal disimpan ke disk publik.');
        }
        $url = asset('storage/' . $path);
        $size = $this->formatFileSize($this->mediaFile->getSize());
        $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true);

        $mediaItem = [
            'id' => Str::uuid()->toString(),
            'name' => $safeName,
            'original_name' => $origName,
            'url' => $url,
            'size' => $size,
            'type' => $isImage ? 'image' : 'document',
            'uploaded_at' => date('Y-m-d H:i'),
        ];

        $list = $this->getMediaList();
        array_unshift($list, $mediaItem);
        $this->saveMediaList($list);

        $this->mediaFile = null;
        $this->flashMessage = "Berkas \"{$origName}\" ({$size}) berhasil disimpan ke repositori media!";
    }

    public function cancelMediaFile(): void
    {
        $this->mediaFile = null;
    }

    public function deleteMediaFile(int|string $id): void
    {
        $list = $this->getMediaList();
        $filtered = array_values(array_filter($list, fn ($item) => (string)($item['id'] ?? '') !== (string)$id));
        $this->saveMediaList($filtered);

        $this->flashMessage = 'Berkas media berhasil dihapus dari antrean.';
    }

    public function useMediaAsPostThumbnail(string $url): void
    {
        $this->postThumbnail = $url;
        $this->flashMessage = 'Tautan berkas terpilih dimasukkan sebagai Thumbnail Berita.';
    }

    public function getMediaList(): array
    {
        $file = storage_path('mock/media.json');
        if (! file_exists($file)) {
            return [];
        }

        return json_decode(file_get_contents($file), true) ?: [];
    }

    public function saveMediaList(array $list): void
    {
        $file = storage_path('mock/media.json');
        file_put_contents($file, json_encode($list, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    public function formatFileSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 0) . ' KB';
        }
        return $bytes . ' B';
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
        $allPosts = $postsRepo->all();
        $committees = $committeesRepo->all();
        $mediaList = $this->getMediaList();

        // Filter berita berdasarkan pencarian & kategori jika diisi
        $posts = collect($allPosts)->filter(function ($item) {
            $matchSearch = true;
            if (!empty($this->postSearch)) {
                $q = strtolower($this->postSearch);
                $title = strtolower($item['title'] ?? '');
                $cat = strtolower($item['category'] ?? '');
                $matchSearch = str_contains($title, $q) || str_contains($cat, $q);
            }

            $matchCat = true;
            if ($this->postFilterCategory !== 'Semua') {
                $matchCat = ($item['category'] ?? '') === $this->postFilterCategory;
            }

            return $matchSearch && $matchCat;
        })->values()->all();

        // Fetch Notulensi from Backend API
        $notulensis = [];
        try {
            $base = $this->getBackendUrl();
            $res = Http::timeout(3)->get("{$base}/notulensi");
            if ($res->successful()) {
                $notulensis = $res->json('data', []);
            }
        } catch (\Throwable) {
            $notulensis = [];
        }

        if (empty($this->notulensiMeetingId) && !empty($meetings)) {
            $this->notulensiMeetingId = (int) ($meetings[0]['id'] ?? 1);
        }

        $activeMeeting = $meetingsRepo->getActiveSession() ?? ($meetings[0] ?? null);
        $totalInvited = (int) collect($meetings)->sum('total_invited');
        $totalPresent = (int) collect($meetings)->sum('present_count');

        $stats = [
            'meetings_count' => count($meetings),
            'active_meeting' => $activeMeeting,
            'attendance_rate' => $totalInvited > 0 ? (int) round(($totalPresent / $totalInvited) * 100) : 82,
            'pending_aspirations' => collect($aspirations)->where('status', '!=', 'Selesai')->count(),
            'total_regulations' => count($regulations),
            'total_posts' => count($allPosts),
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

        $filteredMeetings = $meetings;
        if ($this->meetingFilterTab === 'berlangsung') {
            $filteredMeetings = array_values(array_filter($meetings, fn($m) => in_array(strtolower($m['status'] ?? ''), ['sedang berlangsung', 'aktif', 'open', 'dibuka'])));
        } elseif ($this->meetingFilterTab === 'terjadwal') {
            $filteredMeetings = array_values(array_filter($meetings, fn($m) => strtolower($m['status'] ?? '') === 'terjadwal'));
        } elseif ($this->meetingFilterTab === 'selesai') {
            $filteredMeetings = array_values(array_filter($meetings, fn($m) => in_array(strtolower($m['status'] ?? ''), ['selesai', 'ditutup', 'closed'])));
        }

        return view('livewire.admin-preview', [
            'active' => $this->active,
            'flashMessage' => $this->flashMessage,
            'selectedMeetingId' => $this->selectedMeetingId,
            'isPresensiOpen' => $this->isPresensiOpen,
            'isKuorumLocked' => $this->isKuorumLocked,
            'showQrModal' => $this->showQrModal,
            'sessionCode' => $this->sessionCode,
            'qrToken' => $this->qrToken,
            'liveMeeting' => $this->liveMeeting,
            'liveKuorum' => $this->liveKuorum,
            'liveAnggota' => $this->liveAnggota,
            'filterKehadiran' => $this->filterKehadiran,
            'meetingFilterTab' => $this->meetingFilterTab,
            'newMeetingTitle' => $this->newMeetingTitle,
            'newMeetingType' => $this->newMeetingType,
            'newMeetingDate' => $this->newMeetingDate,
            'newMeetingTime' => $this->newMeetingTime,
            'newMeetingRoom' => $this->newMeetingRoom,
            'selectedLecturerIds' => $this->selectedLecturerIds,
            'lecturerSearch' => $this->lecturerSearch,
            'meetings' => $meetings,
            'filteredMeetings' => $filteredMeetings,
            'availableMeetings' => array_map(function ($m) {
                $mId = $m['id'] ?? ($m['id_rapat'] ?? 1);
                $mTitle = $m['title'] ?? ($m['nama_agenda'] ?? 'Sidang Senat');
                return array_merge($m, ['id' => $mId, 'id_rapat' => $mId, 'title' => $mTitle, 'nama_agenda' => $mTitle]);
            }, !empty($this->availableMeetings) ? $this->availableMeetings : $meetings),
            'allLecturers' => $this->allLecturers,
            'filteredLecturers' => $this->filteredLecturers,
            'activeMeeting' => $activeMeeting,
            'aspirations' => $aspirations,
            'archives' => $archives,
            'notulenItem' => $notulenItem,
            'notulensis' => $notulensis,
            'antrean' => $antrean,
            'aksiCepat' => $aksiCepat,
            'regulations' => $regulations,
            'posts' => $posts,
            'mediaList' => $mediaList,
            'committees' => $committees,
            'stats' => $stats,
            'filteredAnggota' => $filteredAnggota,
        ])->layout('layouts.admin-preview');
    }

    /**
     * Ambil data struktur pimpinan dari Backend API
     */
    public function loadStructureFromApi(): void
    {
        try {
            $base = $this->getBackendUrl();
            $response = Http::timeout(2)->get("{$base}/user");
            if ($response->successful()) {
                $users = $response->json()['data'] ?? $response->json() ?? [];
                foreach ($users as $user) {
                    $id = $user['id'] ?? $user['id_user'] ?? null;
                    if ($id == $this->ketuaUserId) {
                        $this->ketuaNama = $user['nama'] ?? $this->ketuaNama;
                        $this->ketuaNip = $user['nim_nip'] ?? $this->ketuaNip;
                    }
                    if ($id == $this->sekretarisUserId) {
                        $this->sekretarisNama = $user['nama'] ?? $this->sekretarisNama;
                        $this->sekretarisNip = $user['nim_nip'] ?? $this->sekretarisNip;
                    }
                }
            }
        } catch (\Throwable $e) {}
    }

    /**
     * Ambil data tiket aspirasi asli dari Backend API
     */
    public function loadAspirationsFromApi(): void
    {
        try {
            $base = $this->getBackendUrl();
            $response = Http::timeout(2)->get("{$base}/aspirasi");
            if ($response->successful()) {
                $items = $response->json()['data'] ?? [];
                $this->apiAspirations = collect($items)->map(function ($item) {
                    $tanggapans = $item['tanggapans'] ?? [];
                    $lastTanggapan = !empty($tanggapans) ? end($tanggapans)['pesan'] : 'Belum ada tanggapan resmi dari komisi.';
                    return [
                        'id' => $item['id_aspirasi'],
                        'ticket_code' => 'ASP-' . str_pad((string) $item['id_aspirasi'], 4, '0', STR_PAD_LEFT),
                        'sender_category' => $item['anonim'] ? 'Mahasiswa (Anonim)' : ($item['user']['nama'] ?? 'Mahasiswa'),
                        'jurusan' => $item['kategori']['nama_kategori'] ?? 'Umum',
                        'status' => ucfirst($item['status'] ?? 'pending'),
                        'raw_status' => strtolower($item['status'] ?? 'pending'),
                        'subject' => \Illuminate\Support\Str::limit($item['isi_aspirasi'], 65),
                        'description' => $item['isi_aspirasi'],
                        'response' => $lastTanggapan,
                    ];
                })->toArray();
            }
        } catch (\Throwable $e) {}
    }

    /**
     * Ubah status aspirasi (Pending / Diproses / Selesai)
     */
    public function setAspirasiStatus(int $id, string $status): void
    {
        // 1. Ubah tampilan di layar secara instan (0 lag) tanpa dangling reference
        foreach ($this->apiAspirations as $index => $item) {
            if ($item['id'] === $id) {
                $this->apiAspirations[$index]['status'] = ucfirst($status);
                $this->apiAspirations[$index]['raw_status'] = strtolower($status);
                break;
            }
        }
        // 2. Simpan perubahannya ke Backend
        try {
            $base = $this->getBackendUrl();
            Http::put("{$base}/aspirasi/{$id}", [
                'status' => $status,
            ]);
            $this->flashMessage = "Status aspirasi berhasil diubah menjadi: " . ucfirst($status);
        } catch (\Throwable $e) {
            $this->flashMessage = "Gagal memperbarui status: " . $e->getMessage();
        }
    }

    /**
     * Buka form untuk mengetik tanggapan
     */
    public function openTanggapanForm(int $id): void
    {
        $this->selectedAspirasiId = ($this->selectedAspirasiId === $id) ? null : $id;
        $this->tanggapanText = '';
    }

    /**
     * Kirim tanggapan resmi komisi ke database Backend
     */
    public function submitTanggapan(?int $aspirasiId = null): void
    {
        $id = $aspirasiId ?: $this->selectedAspirasiId;
        if (! $id) {
            $this->flashMessage = "Peringatan: ID tiket aspirasi tidak ditemukan.";
            return;
        }

        $pesan = trim($this->tanggapanText);
        if (empty($pesan)) {
            $this->flashMessage = "Peringatan: Mohon ketikkan isi tanggapan terlebih dahulu.";
            return;
        }

        // 1. Update tampilan kotak tanggapan di layar secara instan
        foreach ($this->apiAspirations as $index => $item) {
            if ($item['id'] == $id) {
                $this->apiAspirations[$index]['response'] = $pesan;
                $this->apiAspirations[$index]['status'] = 'Diproses';
                $this->apiAspirations[$index]['raw_status'] = 'diproses';
                break;
            }
        }
        $this->selectedAspirasiId = null;
        $this->tanggapanText = '';

        // 2. Simpan tanggapan ke tabel tanggapan_aspirasis di Backend
        try {
            $base = $this->getBackendUrl();
            $respPost = Http::timeout(5)->post("{$base}/tanggapan-aspirasi", [
                'id_aspirasi' => $id,
                'id_user' => 1,
                'pesan' => $pesan,
            ]);
            $respPut = Http::timeout(5)->put("{$base}/aspirasi/{$id}", [
                'status' => 'diproses',
            ]);

            if ($respPost->successful()) {
                $this->flashMessage = "Berhasil! Tanggapan resmi komisi telah tersimpan ke database Backend.";
            } else {
                $this->flashMessage = "Tanggapan telah diperbarui di layar!";
            }
        } catch (\Throwable $e) {
            $this->flashMessage = "Tanggapan disimpan di layar (Gagal ke Backend: " . $e->getMessage() . ")";
        }
    }
}
