<?php

namespace App\Livewire;

use App\Contracts\ArchiveRepositoryInterface;
use App\Contracts\AspirationRepositoryInterface;
use App\Contracts\CommitteeRepositoryInterface;
use App\Contracts\MeetingRepositoryInterface;
use App\Contracts\PostRepositoryInterface;
use App\Contracts\RegulationRepositoryInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

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

    public function mount(): void
    {
        // Query param 'tab' is automatically bound to $active via #[Url].
        if (isset(self::MENU_KEYS[$this->active])) {
            $this->active = self::MENU_KEYS[$this->active];
        }

        $allowed = ['Dashboard', 'Dashboard Rapat', ...array_merge(...array_values(self::GROUPS))];
        if (! in_array($this->active, $allowed, true)) {
            $this->active = 'Dashboard Rapat';
        }

        $this->postDate = date('Y-m-d');
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
    }

    public function saveAction(string $entity): void
    {
        $this->flashMessage = "Data {$entity} berhasil diperbarui (tersimpan di state lokal). Siap disambungkan ke API backend.";
    }

    public function getBackendUrl(): string
    {
        $url = env('SENAT_BACKEND_API_URL', env('BACKEND_API_URL', 'http://localhost/pbl%20senat/Backend-Senat-Polines/public/api'));
        if (str_contains($url, ':3000')) {
            $url = 'http://localhost/pbl%20senat/Backend-Senat-Polines/public/api';
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
            'mediaList' => $mediaList,
            'committees' => $committees,
            'stats' => $stats,
        ])->layout('layouts.admin-preview');
    }
}
