<?php

namespace App\Livewire\Admin\Traits;

use App\Contracts\PostRepositoryInterface;
use Illuminate\Support\Str;

trait HasNewsAndMedia
{
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
    public array $postImages = [];
    public string $postImageUrl = '';
    public array $postAttachments = [];
    public string $postAttachmentName = '';
    public string $postAttachmentUrl = '';
    public string $postAuthor = 'Sekretariat Senat Polines';
    public bool $showMediaPicker = false;
    public bool $showMediaPreviewModal = false;
    public ?string $mediaPreviewId = null;

    // Search & Filter Berita
    public string $postSearch = '';
    public string $postFilterCategory = 'Semua';

    // Upload & Pengelolaan Media / Berkas
    public $mediaFile = null;
    public string $mediaTitle = '';


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
        $this->postImages = array_values(array_unique(array_filter(
            [$this->postThumbnail, ...(is_array($target['images'] ?? null) ? $target['images'] : [])],
            fn ($image) => is_string($image) && $image !== ''
        )));
        $this->postAttachments = is_array($target['attachments'] ?? null) ? $target['attachments'] : [];
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
        $this->postImages = [];
        $this->postImageUrl = '';
        $this->postAttachments = [];
        $this->postAttachmentName = '';
        $this->postAttachmentUrl = '';
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
                'postImages' => 'array|max:12',
                'postImages.*' => 'required|string|max:2048|url:http,https',
                'postAttachments' => 'array|max:12',
                'postAttachments.*.name' => 'required|string|max:255',
                'postAttachments.*.url' => 'required|string|max:2048|url:http,https',
            ], [
                'postTitle.required' => 'Judul berita wajib diisi.',
                'postTitle.min' => 'Judul berita minimal 3 karakter.',
                'postCategory.required' => 'Kategori berita wajib dipilih.',
                'postBody.required' => 'Isi berita wajib diisi.',
                'postStatus.in' => 'Status berita tidak valid.',
                'postImages.max' => 'Berita maksimal memiliki 12 gambar.',
                'postAttachments.max' => 'Berita maksimal memiliki 12 lampiran.',
            ]);

            $status = $publishNow
                ? ($this->editingPostId ? $this->postStatus : 'Terpublikasi')
                : 'Draf';
            $excerpt = !empty($this->postExcerpt) ? $this->postExcerpt : Str::limit(strip_tags($this->postBody), 180);
            $images = array_values(array_unique(array_filter(
                [...$this->postImages, $this->postThumbnail],
                fn ($image) => is_string($image) && $image !== ''
            )));
            $thumbnail = $images[0] ?? 'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?auto=format&fit=crop&w=800&q=80';

            $payload = [
                'title' => $this->postTitle,
                'category' => $this->postCategory,
                'date' => $this->postDate ?: date('Y-m-d'),
                'status' => $status,
                'excerpt' => $excerpt,
                'body_text' => $this->postBody,
                'body' => explode("\n\n", $this->postBody),
                'thumbnail' => $thumbnail,
                'images' => $images,
                'attachments' => array_values($this->postAttachments),
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
            'mediaFile' => 'required|file|mimetypes:application/pdf,image/jpeg,image/png,image/webp,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation|mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx,ppt,pptx|max:10240',
        ], [
            'mediaFile.required' => 'Pilih berkas terlebih dahulu sebelum menyimpan.',
            'mediaFile.mimetypes' => 'Format berkas harus berupa PDF, gambar, Word, Excel, atau PowerPoint.',
            'mediaFile.mimes' => 'Format berkas harus berupa PDF, gambar, Word, Excel, atau PowerPoint.',
            'mediaFile.max' => 'Ukuran berkas melebihi batas maksimal 10MB.',
            'mediaFile.file' => 'Berkas tidak valid.',
        ]);

        $origName = $this->mediaFile->getClientOriginalName();
        $ext = match ($this->mediaFile->getMimeType()) {
            'application/pdf' => 'pdf',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'application/vnd.ms-powerpoint' => 'ppt',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
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
        if (! $this->showPostModal) {
            $this->openCreatePostModal();
        }

        $this->addPostImage($url);
        $this->postThumbnail = $this->postImages[0] ?? '';
        $this->showMediaPicker = false;
        $this->flashMessage = 'Gambar ditambahkan ke dokumentasi berita.';
    }

    public function useMediaAsPostImage(string $id): void
    {
        $media = collect($this->getMediaList())->firstWhere('id', $id);
        if (! $media || ($media['type'] ?? '') !== 'image') {
            $this->flashMessage = 'Gambar media tidak ditemukan.';
            return;
        }

        $this->useMediaAsPostThumbnail($media['url']);
    }

    public function addPostImageFromUrl(): void
    {
        $this->validateOnly('postImageUrl', [
            'postImageUrl' => 'required|string|max:2048|url:http,https',
        ]);

        $this->addPostImage($this->postImageUrl);
        $this->postThumbnail = $this->postImages[0] ?? '';
        $this->postImageUrl = '';
    }

    public function removePostImage(int $index): void
    {
        if (! array_key_exists($index, $this->postImages)) {
            return;
        }

        array_splice($this->postImages, $index, 1);
        $this->postThumbnail = $this->postImages[0] ?? '';
    }

    public function openMediaPicker(): void
    {
        $this->showMediaPicker = true;
    }

    public function closeMediaPicker(): void
    {
        $this->showMediaPicker = false;
    }

    public function addMediaToPost(string $id): void
    {
        $media = collect($this->getMediaList())->firstWhere('id', $id);
        if (! $media) {
            $this->flashMessage = 'Berkas media tidak ditemukan.';
            return;
        }

        if (! $this->showPostModal) {
            $this->openCreatePostModal();
        }

        if (($media['type'] ?? '') === 'image') {
            $this->addPostImage($media['url']);
            $this->postThumbnail = $this->postImages[0] ?? '';
        } else {
            $this->addPostAttachment($media);
        }

        $this->closeMediaPreview();
    }

    public function toggleMediaInPost(string $id): void
    {
        $media = collect($this->getMediaList())->firstWhere('id', $id);
        if (! $media) {
            $this->flashMessage = 'Berkas media tidak ditemukan.';
            return;
        }

        if (($media['type'] ?? '') === 'image') {
            $index = array_search($media['url'], $this->postImages, true);
            if ($index === false) {
                $this->addPostImage($media['url']);
            } else {
                array_splice($this->postImages, $index, 1);
            }
            $this->postThumbnail = $this->postImages[0] ?? '';
            return;
        }

        $attachmentIndex = collect($this->postAttachments)->search(fn ($item) => ($item['url'] ?? '') === ($media['url'] ?? ''));
        if ($attachmentIndex === false) {
            $this->addPostAttachment($media);
        } else {
            array_splice($this->postAttachments, $attachmentIndex, 1);
        }
    }

    public function addPostAttachmentFromUrl(): void
    {
        $this->validateOnly('postAttachmentUrl', [
            'postAttachmentUrl' => 'required|string|max:2048|url:http,https',
        ]);

        $url = $this->postAttachmentUrl;
        $this->addPostAttachment([
            'name' => $this->postAttachmentName ?: basename((string) parse_url($url, PHP_URL_PATH)),
            'url' => $url,
        ]);
        $this->postAttachmentName = '';
        $this->postAttachmentUrl = '';
    }

    public function removePostAttachment(int $index): void
    {
        if (! array_key_exists($index, $this->postAttachments)) {
            return;
        }

        array_splice($this->postAttachments, $index, 1);
    }

    public function openMediaPreview(string $id): void
    {
        if (! collect($this->getMediaList())->contains(fn ($item) => (string)($item['id'] ?? '') === $id)) {
            $this->flashMessage = 'Berkas media tidak ditemukan.';
            return;
        }

        $this->mediaPreviewId = $id;
        $this->showMediaPreviewModal = true;
    }

    public function closeMediaPreview(): void
    {
        $this->mediaPreviewId = null;
        $this->showMediaPreviewModal = false;
    }

    private function addPostImage(string $url): void
    {
        if (! in_array($url, $this->postImages, true)) {
            $this->postImages[] = $url;
        }
    }

    private function addPostAttachment(array $media): void
    {
        $url = $media['url'] ?? '';
        if (! $url || collect($this->postAttachments)->contains('url', $url)) {
            return;
        }

        $this->postAttachments[] = [
            'name' => $media['original_name'] ?? $media['name'] ?? 'Lampiran',
            'url' => $url,
        ];
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


}
