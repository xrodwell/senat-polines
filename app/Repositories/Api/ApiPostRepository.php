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

    public function all(): array
    {
        $apiUrl = env('SENAT_BACKEND_API_URL', 'http://127.0.0.1:8001/api');

        try {
            $response = Http::timeout(3)->get("{$apiUrl}/berita");

            if ($response->successful()) {
                $items = $response->json('data', []);
                if (!empty($items)) {
                    return array_map(function ($b) {
                        return [
                            'id' => $b['id_berita'] ?? 1,
                            'title' => $b['judul'] ?? '',
                            'slug' => Str::slug($b['judul'] ?? 'berita-' . ($b['id_berita'] ?? 1)),
                            'category' => 'Warta Senat',
                            'date' => isset($b['tanggal_publish']) ? substr($b['tanggal_publish'], 0, 10) : date('Y-m-d'),
                            'excerpt' => Str::limit(strip_tags($b['isi'] ?? ''), 180),
                            'thumbnail' => $b['gambar'] ?? 'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?auto=format&fit=crop&w=800&q=80',
                            'author' => 'Sekretariat Senat Polines (via API)',
                            'body' => explode("\n\n", $b['isi'] ?? '')
                        ];
                    }, $items);
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
            if ($post['slug'] === $slug) {
                return $post;
            }
        }

        return $this->fallback->findBySlug($slug);
    }
}
