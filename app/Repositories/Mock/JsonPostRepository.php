<?php

namespace App\Repositories\Mock;

use App\Contracts\PostRepositoryInterface;

class JsonPostRepository implements PostRepositoryInterface
{
    public function all(): array
    {
        return json_decode(file_get_contents(storage_path('mock/posts.json')), true) ?: [];
    }

    public function findBySlug(string $slug): ?array
    {
        foreach ($this->all() as $post) {
            if ($post['slug'] === $slug) {
                return $post;
            }
        }

        return null;
    }
}
