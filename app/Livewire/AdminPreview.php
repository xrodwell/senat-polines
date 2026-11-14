<?php

namespace App\Livewire;

use App\Contracts\ArchiveRepositoryInterface;
use App\Contracts\AspirationRepositoryInterface;
use App\Contracts\CommitteeRepositoryInterface;
use App\Contracts\MeetingRepositoryInterface;
use App\Contracts\PostRepositoryInterface;
use App\Contracts\RegulationRepositoryInterface;
use App\Livewire\Admin\Traits\HasAspirationManagement;
use App\Livewire\Admin\Traits\HasAttendanceAndQr;
use App\Livewire\Admin\Traits\HasCommitteeManagement;
use App\Livewire\Admin\Traits\HasMeetingManagement;
use App\Livewire\Admin\Traits\HasNewsAndMedia;
use App\Livewire\Admin\Traits\HasNotulensiManagement;
use App\Livewire\Admin\Traits\HasPimpinanAndStruktur;
use App\Livewire\Admin\Traits\HasRegulasiManagement;
use App\Livewire\Admin\Traits\HasVotingManagement;
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
    use HasAspirationManagement;
    use HasAttendanceAndQr;
    use HasCommitteeManagement;
    use HasMeetingManagement;
    use HasNewsAndMedia;
    use HasNotulensiManagement;
    use HasPimpinanAndStruktur;
    use HasRegulasiManagement;
    use HasVotingManagement;

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

        if ($this->active === 'E-Voting & Pengambilan Keputusan') {
            $this->loadVotingData();
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


}
