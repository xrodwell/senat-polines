<?php

namespace App\Contracts;

interface MeetingRepositoryInterface
{
    public function all(): array;

    public function getActiveSession(): ?array;

    public function getUpcoming(): array;

    public function submitPresensi(int $userId, ?string $tokenQr = null, ?string $sessionCode = null, ?int $meetingId = null): array;

    public function getLiveAttendance(int $meetingId): ?array;

    public function generateQr(int $meetingId): ?array;

    public function freezePresensi(int $meetingId): ?array;
}
