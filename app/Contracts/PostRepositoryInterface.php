<?php

namespace App\Contracts;

interface PostRepositoryInterface
{
    public function all(): array;

    public function findBySlug(string $slug): ?array;
}
