<?php

namespace App\Contracts;

interface VotingRepositoryInterface
{
    public function getActive(): ?array;

    public function all(): array;

    public function castVote(int $votingId, int $optionId, ?int $userId = null): array;

    public function updateStatus(int $votingId, string $status): array;

    public function rekap(int $votingId): ?array;
}
