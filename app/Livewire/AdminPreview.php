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

    // Form Jadwal & Agenda Sidang (Tersambung Backend API)
    public string $meetingTitle = '';
    public int $meetingJenisRapatId = 1;
    public string $meetingDateTime = '';
    public string $meetingRoom = '';
    public string $meetingPeserta = '';
    public ?int $editingMeetingId = null;

    // Form Notulensi Sidang (Tersambung Backend API)
    public ?int $notulensiMeetingId = null;
    public string $notulensiIsi = '';
    public string $notulensiRekomendasi = 'Keputusan';
    public string $notulensiFileRisalah = '';
    public ?int $editingNotulensiId = null;

    public function mount(): void
    {
        if (empty($this->meetingDateTime)) {
            $this->meetingDateTime = date('Y-m-d\T09:00');
        }

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
        $url = env('SENAT_BACKEND_API_URL') ?: env('BACKEND_API_URL');
        if (empty($url)) {
            $url = 'http://127.0.0.1:8001/api';
        }

        return rtrim($url, '/');
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

    public function saveNotulensi(string $actionType = 'Draft'): void
    {
        $this->validate([
            'notulensiMeetingId' => 'required',
            'notulensiIsi' => 'required|string',
            'notulensiRekomendasi' => 'required|in:Pertimbangan,Penetapan,Keputusan',
        ], [
            'notulensiMeetingId.required' => 'Pilih agenda sidang yang dinotulensikan.',
            'notulensiIsi.required' => 'Catatan jalannya pembahasan notulensi wajib diisi.',
            'notulensiRekomendasi.required' => 'Pilih rekomendasi keputusan.',
            'notulensiRekomendasi.in' => 'Rekomendasi keputusan harus berupa Pertimbangan, Penetapan, atau Keputusan.',
        ]);

        try {
            $base = $this->getBackendUrl();
            $payload = [
                'id_rapat' => (int) $this->notulensiMeetingId,
                'id_user' => $this->ketuaUserId ?? 1,
                'isi_notulensi' => $this->notulensiIsi,
                'rekomendasi_keputusan' => $this->notulensiRekomendasi,
                'file_risalah' => !empty($this->notulensiFileRisalah) ? $this->notulensiFileRisalah : null,
            ];

            if ($this->editingNotulensiId) {
                $response = Http::timeout(5)->put("{$base}/notulensi/{$this->editingNotulensiId}", $payload);
                if ($response->successful()) {
                    $this->flashMessage = "Notulensi sidang berhasil diperbarui (Rekomendasi: {$this->notulensiRekomendasi})!";
                    $this->resetNotulensiForm();
                } else {
                    $this->flashMessage = 'Gagal memperbarui notulensi: ' . ($response->json('message') ?? $response->body());
                }
            } else {
                $response = Http::timeout(5)->post("{$base}/notulensi", $payload);
                if ($response->successful()) {
                    $this->flashMessage = "Notulensi sidang berhasil disimpan ke Backend (Rekomendasi: {$this->notulensiRekomendasi})!";
                    $this->resetNotulensiForm();
                } else {
                    $this->flashMessage = 'Gagal menyimpan notulensi: ' . ($response->json('message') ?? $response->body());
                }
            }
        } catch (\Throwable $e) {
            $this->flashMessage = 'Gagal menghubungi Backend API: ' . $e->getMessage();
        }
    }

    public function editNotulensi(int $id): void
    {
        try {
            $base = $this->getBackendUrl();
            $response = Http::timeout(5)->get("{$base}/notulensi/{$id}");
            if ($response->successful()) {
                $item = $response->json();
                $item = $item['data'] ?? $item;

                $this->editingNotulensiId = $id;
                $this->notulensiMeetingId = (int) ($item['id_rapat'] ?? null);
                $this->notulensiIsi = $item['isi_notulensi'] ?? '';
                $this->notulensiRekomendasi = $item['rekomendasi_keputusan'] ?? 'Keputusan';
                $this->notulensiFileRisalah = $item['file_risalah'] ?? '';

                $this->flashMessage = "Mode sunting aktif untuk notulensi #{$id}.";
            }
        } catch (\Throwable $e) {
            $this->flashMessage = 'Gagal memuat notulensi: ' . $e->getMessage();
        }
    }

    public function deleteNotulensi(int $id): void
    {
        try {
            $base = $this->getBackendUrl();
            $response = Http::timeout(5)->delete("{$base}/notulensi/{$id}");
            if ($response->successful()) {
                $this->flashMessage = "Notulensi #{$id} berhasil dihapus dari Backend!";
                if ($this->editingNotulensiId === $id) {
                    $this->resetNotulensiForm();
                }
            }
        } catch (\Throwable $e) {
            $this->flashMessage = 'Gagal menghapus notulensi: ' . $e->getMessage();
        }
    }

    public function resetNotulensiForm(): void
    {
        $this->editingNotulensiId = null;
        $this->notulensiIsi = '';
        $this->notulensiRekomendasi = 'Keputusan';
        $this->notulensiFileRisalah = '';
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
            'notulensis' => $notulensis,
            'antrean' => $antrean,
            'aksiCepat' => $aksiCepat,
            'regulations' => $regulations,
            'posts' => $posts,
            'committees' => $committees,
            'stats' => $stats,
        ])->layout('layouts.admin-preview');
    }
}
