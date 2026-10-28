<?php

namespace App\Repositories\Api;

use App\Contracts\PostRepositoryInterface;
use App\Repositories\Mock\JsonPostRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ApiPostRepository implements PostRepositoryInterface
{
    public function __construct(
        private JsonPostRepository $fallback = new JsonPostRepository()
    ) {}

    public function resolveBackendUrl(): string
    {
        $apiUrl = env('SENAT_BACKEND_API_URL') ?: env('BACKEND_API_URL');

        // Jika kosong atau masih mengarah ke port 3000 yang tidak aktif, arahkan ke URL XAMPP/Apache
        if (empty($apiUrl) || !str_starts_with((string)$apiUrl, 'http') || str_contains((string)$apiUrl, ':3000')) {
            $apiUrl = 'http://localhost/pbl%20senat/Backend-Senat-Polines/public/api';
        }

        return rtrim((string)$apiUrl, '/');
    }

    public function all(): array
    {
        $apiUrl = $this->resolveBackendUrl();

        try {
            $response = Http::timeout(2)->get("{$apiUrl}/berita");

            if ($response->successful()) {
                $raw = $response->json();
                $items = $raw['data'] ?? (is_array($raw) && !isset($raw['current_page']) ? $raw : []);

                if (!empty($items) && is_array($items)) {
                    $backendPosts = array_map(function ($b) {
                        $isPublished = !empty($b['tanggal_publish']);
                        return [
                            'id' => $b['id_berita'] ?? 1,
                            'title' => $b['judul'] ?? '',
                            'slug' => Str::slug($b['judul'] ?? 'berita-' . ($b['id_berita'] ?? 1)),
                            'category' => 'Warta Senat',
                            'date' => $isPublished ? substr($b['tanggal_publish'], 0, 10) : date('Y-m-d'),
                            'status' => $isPublished ? 'Terpublikasi' : 'Draf',
                            'excerpt' => Str::limit(strip_tags($b['isi'] ?? ''), 180),
                            'thumbnail' => !empty($b['gambar']) ? $b['gambar'] : 'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?auto=format&fit=crop&w=800&q=80',
                            'author' => $b['user']['nama'] ?? 'Sekretariat Senat Polines',
                            'body' => explode("\n\n", $b['isi'] ?? '')
                        ];
                    }, $items);

                    // Sinkronisasi/merge dengan data lokal agar berita dokumentasi bawaan tetap utuh
                    $fallbackPosts = $this->fallback->all();
                    $seenSlugs = array_filter(array_column($backendPosts, 'slug'));
                    $seenIds = array_filter(array_column($backendPosts, 'id'));

                    foreach ($fallbackPosts as $fp) {
                        if (!in_array($fp['slug'], $seenSlugs) && !in_array($fp['id'], $seenIds)) {
                            $backendPosts[] = $fp;
                        }
                    }

                    // Urutkan berita: tanggal terbaru di posisi paling atas
                    usort($backendPosts, function ($a, $b) {
                        $cmp = strcmp($b['date'] ?? '', $a['date'] ?? '');
                        if ($cmp !== 0) {
                            return $cmp;
                        }
                        return ((int)($b['id'] ?? 0)) <=> ((int)($a['id'] ?? 0));
                    });

                    return $backendPosts;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Interoperabilitas API Berita fallback ke local cache: ' . $e->getMessage());
        }

        return $this->fallback->all();
    }

    public function findBySlug(string $slug): ?array
    {
        foreach ($this->all() as $post) {
            if (($post['slug'] ?? '') === $slug) {
                return $post;
            }
        }

        return $this->fallback->findBySlug($slug);
    }

    public function create(array $data): array
    {
        $apiUrl = $this->resolveBackendUrl();
        $bodyText = $data['body_text'] ?? (is_array($data['body'] ?? null) ? implode("\n\n", $data['body']) : ($data['body'] ?? ''));
        $isPublished = ($data['status'] ?? 'Terpublikasi') === 'Terpublikasi';

        try {
            $response = Http::timeout(3)->post("{$apiUrl}/berita", [
                'id_user' => 1,
                'judul' => $data['title'] ?? 'Warta Senat',
                'isi' => $bodyText,
                'gambar' => $data['thumbnail'] ?? null,
                'tanggal_publish' => $isPublished ? ($data['date'] ?? date('Y-m-d')) . ' ' . date('H:i:s') : null,
            ]);

            if ($response->successful()) {
                $created = $response->json();
                if (isset($created['id_berita'])) {
                    $data['id'] = (int)$created['id_berita'];
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal sinkronisasi create berita ke API backend: ' . $e->getMessage());
        }

        return $this->fallback->create($data);
    }

    public function update(int|string $id, array $data): bool
    {
        $apiUrl = $this->resolveBackendUrl();
        $payload = [];

        if (isset($data['title'])) {
            $payload['judul'] = $data['title'];
        }
        if (isset($data['body']) || isset($data['body_text'])) {
            $payload['isi'] = $data['body_text'] ?? (is_array($data['body']) ? implode("\n\n", $data['body']) : $data['body']);
        }
        if (isset($data['thumbnail'])) {
            $payload['gambar'] = $data['thumbnail'];
        }
        if (isset($data['status'])) {
            $payload['tanggal_publish'] = $data['status'] === 'Terpublikasi' ? date('Y-m-d H:i:s') : null;
        }

        $backendUpdated = false;
        if (!empty($payload)) {
            try {
                $response = Http::timeout(3)->put("{$apiUrl}/berita/{$id}", $payload);
                $backendUpdated = $response->successful();

                if (! $backendUpdated) {
                    Log::warning('Backend menolak update berita.', [
                        'post_id' => $id,
                        'status' => $response->status(),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::warning('Gagal sinkronisasi update berita ke API backend: ' . $e->getMessage());
            }
        }

        $localUpdated = $this->fallback->update($id, $data);

        return $backendUpdated || $localUpdated;
    }

    public function delete(int|string $id): bool
    {
        $apiUrl = $this->resolveBackendUrl();

        try {
            Http::timeout(3)->delete("{$apiUrl}/berita/{$id}");
        } catch (\Throwable $e) {
            Log::warning('Gagal sinkronisasi delete berita ke API backend: ' . $e->getMessage());
        }

        return $this->fallback->delete($id);
    }

    public function publish(int|string $id): bool
    {
        $apiUrl = $this->resolveBackendUrl();

        try {
            Http::timeout(3)->put("{$apiUrl}/berita/{$id}", [
                'tanggal_publish' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Gagal sinkronisasi publish berita ke API backend: ' . $e->getMessage());
        }

        return $this->fallback->publish($id);
    }
}
