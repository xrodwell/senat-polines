<?php

namespace App\Contracts;

interface AspirationRepositoryInterface
{
    public function all(): array;

    public function findByTicket(string $ticket): ?array;
}
