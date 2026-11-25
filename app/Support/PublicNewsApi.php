<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Helper terpusat untuk membaca & mengirim berita publik langsung ke Backend API (tanpa fallback mock).
 */
class PublicNewsApi
{
    public static function backendBaseUrl(): ?string
    {
        $base = rtrim((string) config('services.backend_api.url'), '/');
        if ($base === '') {
            return null;
        }

        return preg_replace('~/api$~', '', $base) ?: null;
    }

    /**
     * Ambil daftar berita terpublikasi terpaginasi dari Backend API.
     *
     * @return ?array{posts: array<int, array<string, mixed>>, pagination: array<string, int>}
     */
    public static function fetchPage(int $page = 1, int $perPage = 12, string $search = ''): ?array
    {
        $base = self::backendBaseUrl();
        if ($base === null) {
            return null;
        }

        $params = [
            'page' => max(1, $page),
            'per_page' => max(1, $perPage),
        ];

        $q = trim($search);
        if ($q !== '') {
            $params['search'] = $q;
        }

        try {
            $response = Http::acceptJson()->timeout(3)->get($base.'/api/public-news', $params);

            if ($response->status() === 404) {
                $fallbackResp = Http::acceptJson()->timeout(3)->get($base.'/api/landing-news');
                if (! $fallbackResp->successful()) {
                    return null;
                }
                $rawItems = $fallbackResp->json('data') ?? [];
                if (! is_array($rawItems)) {
                    return null;
                }
                $posts = array_map(fn ($item) => self::formatItem($item), $rawItems);

                return [
                    'posts' => $posts,
                    'pagination' => [
                        'total' => count($posts),
                        'current_page' => 1,
                        'last_page' => 1,
                        'per_page' => count($posts),
                        'from' => count($posts) > 0 ? 1 : 0,
                        'to' => count($posts),
                    ],
                ];
            }

            if (! $response->successful()) {
                return null;
            }

            $json = $response->json();
            if (! is_array($json)) {
                return null;
            }

            $rawItems = $json['data'] ?? [];
            if (! is_array($rawItems)) {
                return null;
            }

            $posts = array_map(fn ($item) => self::formatItem($item), $rawItems);

            return [
                'posts' => $posts,
                'pagination' => [
                    'total' => (int) ($json['total'] ?? count($posts)),
                    'current_page' => (int) ($json['current_page'] ?? 1),
                    'last_page' => (int) ($json['last_page'] ?? 1),
                    'per_page' => (int) ($json['per_page'] ?? count($posts)),
                    'from' => (int) ($json['from'] ?? (count($posts) > 0 ? 1 : 0)),
                    'to' => (int) ($json['to'] ?? count($posts)),
                ],
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * @return array{0: ?array<string, mixed>, 1: bool} [post, isOutage]
     */
    public static function fetchDetail(string $slug): array
    {
        $base = self::backendBaseUrl();
        if ($base === null) {
            return [null, true];
        }

        try {
            $response = Http::acceptJson()->timeout(3)->get($base.'/api/public-news/'.urlencode($slug));

            if ($response->status() === 404) {
                $landingResp = Http::acceptJson()->timeout(3)->get($base.'/api/landing-news');
                if ($landingResp->successful()) {
                    $items = $landingResp->json('data') ?? [];
                    if (is_array($items)) {
                        foreach ($items as $item) {
                            if (Str::slug($item['judul'] ?? '') === $slug) {
                                return [self::formatItem($item), false];
                            }
                        }
                    }
                }

                return [null, false];
            }

            if (! $response->successful()) {
                return [null, true];
            }

            $raw = $response->json('data') ?? $response->json();
            if (! is_array($raw) || empty($raw)) {
                return [null, false];
            }

            return [self::formatItem($raw), false];
        } catch (\Throwable $e) {
            return [null, true];
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function fetchRelated(string $slug): array
    {
        $base = self::backendBaseUrl();
        if ($base === null) {
            return [];
        }

        try {
            $response = Http::acceptJson()->timeout(3)->get($base.'/api/public-news', ['per_page' => 4]);
            $items = $response->json('data');
            if (! $response->successful() || ! is_array($items)) {
                $resp2 = Http::acceptJson()->timeout(3)->get($base.'/api/landing-news');
                $items = $resp2->json('data');
                if (! $resp2->successful() || ! is_array($items)) {
                    return [];
                }
            }

            $related = [];
            foreach ($items as $item) {
                $post = self::formatItem($item);
                if ($post['slug'] !== $slug) {
                    $related[] = $post;
                }
                if (count($related) >= 3) {
                    break;
                }
            }

            return $related;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Kirim berita baru ke Backend API menggunakan token sesi pengguna.
     *
     * @param  array<string, mixed>  $payload
     * @return array{ok: bool, status: int, message: string}
     */
    public static function createNews(string $token, array $payload): array
    {
        $base = self::backendBaseUrl();
        if ($base === null) {
            return ['ok' => false, 'status' => 503, 'message' => 'URL Backend API belum dikonfigurasi.'];
        }

        try {
            $client = Http::acceptJson()->timeout(4);
            if ($token !== '') {
                $client = $client->withToken($token);
            }

            $response = $client->post($base.'/api/berita', $payload);

            if ($response->successful()) {
                return ['ok' => true, 'status' => $response->status(), 'message' => 'Berita berhasil disimpan.'];
            }

            $message = match ($response->status()) {
                401 => 'Sesi login Anda tidak valid untuk menyimpan berita.',
                403 => 'Akun Anda tidak memiliki izin untuk mempublikasikan berita.',
                default => (string) ($response->json('message') ?? 'Backend gagal menyimpan berita (HTTP '.$response->status().').'),
            };

            return ['ok' => false, 'status' => $response->status(), 'message' => $message];
        } catch (\Throwable $e) {
            return ['ok' => false, 'status' => 503, 'message' => 'Gagal menghubungi Backend API saat menyimpan berita.'];
        }
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    public static function formatItem(array $item): array
    {
        $title = (string) ($item['judul'] ?? $item['title'] ?? '');
        $slug = (string) ($item['slug'] ?? Str::slug($title));
        $dateStr = $item['tanggal_publish'] ?? $item['date'] ?? null;
        $isPublished = ! empty($dateStr);
        $dateOnly = $isPublished ? substr((string) $dateStr, 0, 10) : date('Y-m-d');
        $rawIsi = $item['isi'] ?? $item['body'] ?? '';
        $body = is_array($rawIsi) ? $rawIsi : explode("\n\n", (string) $rawIsi);
        $bodyText = is_array($rawIsi) ? implode("\n\n", $rawIsi) : (string) $rawIsi;

        $thumbnail = ! empty($item['gambar']) ? $item['gambar'] : ($item['thumbnail'] ?? null);
        $gallery = is_array($item['galeri'] ?? null) ? $item['galeri'] : [];
        $images = array_values(array_unique(array_filter(
            [$thumbnail, ...$gallery, ...(is_array($item['images'] ?? null) ? $item['images'] : [])],
            fn ($img) => is_string($img) && $img !== ''
        )));

        $attachments = is_array($item['lampiran'] ?? null) ? $item['lampiran'] : (is_array($item['attachments'] ?? null) ? $item['attachments'] : []);
        $author = $item['author'] ?? $item['user']['nama'] ?? 'Sekretariat Senat Polines';

        return [
            'id' => $item['id_berita'] ?? $item['id'] ?? 0,
            'title' => $title,
            'slug' => $slug,
            'category' => $item['kategori'] ?? $item['category'] ?? 'Warta Senat',
            'date' => $dateOnly,
            'published_at' => $dateStr,
            'status' => 'Terpublikasi',
            'excerpt' => Str::limit(strip_tags($bodyText), 180),
            'thumbnail' => $thumbnail,
            'images' => $images,
            'attachments' => $attachments,
            'author' => $author,
            'body' => $body,
        ];
    }
}
