<?php

namespace App\Repositories\Mock;

use App\Contracts\PostRepositoryInterface;
use Illuminate\Support\Str;

class JsonPostRepository implements PostRepositoryInterface
{
    private string $filePath;

    public function __construct()
    {
        $this->filePath = storage_path('mock/posts.json');
    }

    public function all(): array
    {
        if (!file_exists($this->filePath)) {
            return [];
        }
        $posts = json_decode(file_get_contents($this->filePath), true) ?: [];

        $mapped = array_map(function ($p) {
            if (!isset($p['status'])) {
                $p['status'] = 'Terpublikasi';
            }
            return $p;
        }, $posts);

        // Urutkan berita: tanggal terbaru di atas, kemudian ID terbesar
        usort($mapped, function ($a, $b) {
            $cmp = strcmp($b['date'] ?? '', $a['date'] ?? '');
            if ($cmp !== 0) {
                return $cmp;
            }
            return ((int)($b['id'] ?? 0)) <=> ((int)($a['id'] ?? 0));
        });

        return $mapped;
    }

    public function findBySlug(string $slug): ?array
    {
        foreach ($this->all() as $post) {
            if (($post['slug'] ?? '') === $slug) {
                return $post;
            }
        }

        return null;
    }

    public function create(array $data): array
    {
        $posts = $this->all();
        $newId = isset($data['id']) && !empty($data['id'])
            ? (int)$data['id']
            : (empty($posts) ? 1 : ((int)max(array_column($posts, 'id')) + 1));

        $bodyText = $data['body_text'] ?? (is_array($data['body'] ?? null) ? implode("\n\n", $data['body']) : ($data['body'] ?? ''));

        $newPost = [
            'id' => $newId,
            'title' => $data['title'] ?? 'Warta Senat',
            'slug' => Str::slug($data['title'] ?? 'berita-' . $newId),
            'category' => $data['category'] ?? 'Warta Senat',
            'date' => $data['date'] ?? date('Y-m-d'),
            'status' => $data['status'] ?? 'Terpublikasi',
            'excerpt' => !empty($data['excerpt']) ? $data['excerpt'] : Str::limit(strip_tags($bodyText), 180),
            'thumbnail' => !empty($data['thumbnail']) ? $data['thumbnail'] : 'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?auto=format&fit=crop&w=800&q=80',
            'author' => $data['author'] ?? 'Sekretariat Senat Polines',
            'body' => is_array($data['body'] ?? null) ? $data['body'] : explode("\n\n", $bodyText),
        ];

        // Jika ID sudah ada (misal dari backend), ganti record yang lama
        $posts = array_values(array_filter($posts, fn($p) => (int)($p['id'] ?? 0) !== $newId));
        array_unshift($posts, $newPost);

        usort($posts, function ($a, $b) {
            $cmp = strcmp($b['date'] ?? '', $a['date'] ?? '');
            if ($cmp !== 0) {
                return $cmp;
            }
            return ((int)($b['id'] ?? 0)) <=> ((int)($a['id'] ?? 0));
        });

        file_put_contents($this->filePath, json_encode($posts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return $newPost;
    }

    public function update(int|string $id, array $data): bool
    {
        $posts = $this->all();
        $updated = false;

        foreach ($posts as &$post) {
            if ((int)($post['id'] ?? 0) === (int)$id) {
                if (isset($data['title'])) {
                    $post['title'] = $data['title'];
                    $post['slug'] = Str::slug($data['title']);
                }
                if (isset($data['category'])) {
                    $post['category'] = $data['category'];
                }
                if (isset($data['date'])) {
                    $post['date'] = $data['date'];
                }
                if (isset($data['status'])) {
                    $post['status'] = $data['status'];
                }
                if (isset($data['excerpt'])) {
                    $post['excerpt'] = $data['excerpt'];
                }
                if (isset($data['thumbnail'])) {
                    $post['thumbnail'] = $data['thumbnail'];
                }
                if (isset($data['author'])) {
                    $post['author'] = $data['author'];
                }
                if (isset($data['body'])) {
                    $post['body'] = is_array($data['body']) ? $data['body'] : explode("\n\n", $data['body']);
                } elseif (isset($data['body_text'])) {
                    $post['body'] = explode("\n\n", $data['body_text']);
                }

                $updated = true;
                break;
            }
        }

        if ($updated) {
            usort($posts, function ($a, $b) {
                $cmp = strcmp($b['date'] ?? '', $a['date'] ?? '');
                if ($cmp !== 0) {
                    return $cmp;
                }
                return ((int)($b['id'] ?? 0)) <=> ((int)($a['id'] ?? 0));
            });

            file_put_contents($this->filePath, json_encode($posts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }

        return $updated;
    }

    public function delete(int|string $id): bool
    {
        $posts = $this->all();
        $filtered = array_values(array_filter($posts, fn($p) => (int)($p['id'] ?? 0) !== (int)$id));

        if (count($filtered) !== count($posts)) {
            file_put_contents($this->filePath, json_encode($filtered, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            return true;
        }

        return false;
    }

    public function publish(int|string $id): bool
    {
        return $this->update($id, [
            'status' => 'Terpublikasi',
            'date' => date('Y-m-d'),
        ]);
    }
}
