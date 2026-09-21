<?php

namespace App\Contracts;

interface MeetingRepositoryInterface
{
    public function all(): array;

    public function getActiveSession(): ?array;

    public function getUpcoming(): array;
}
