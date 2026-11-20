<?php

namespace App\Contracts;

interface PostRepositoryInterface
{
    public function all(): array;

    public function findBySlug(string $slug): ?array;

    public function create(array $data): array;

    public function update(int|string $id, array $data): bool;

    public function delete(int|string $id): bool;

    public function publish(int|string $id): bool;
}
