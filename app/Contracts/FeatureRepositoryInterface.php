<?php

namespace App\Contracts;

interface FeatureRepositoryInterface
{
    /**
     * Daftar fitur unggulan Senat (Daftar Fitur Unggulan SENAT).
     *
     * @return array<int, array<string, mixed>>
     */
    public function all(): array;
}
