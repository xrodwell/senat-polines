<?php

namespace App\Contracts;

interface VotingRepositoryInterface
{
    public function getActive(): ?array;

    public function all(): array;

    public function castVote(int $votingId, int $optionId, ?int $userId = null): array;

    public function updateStatus(int $votingId, string $status): array;

    public function rekap(int $votingId): ?array;

    /**
     * Ballot milik user terautentikasi pada satu sesi (GET /voting/{id}/my-ballot).
     * Null saat tidak diketahui (backend offline / tanpa token).
     */
    public function myBallot(int $votingId): ?array;

    public function isAvailable(): bool;

    public function lastError(): ?string;
}
