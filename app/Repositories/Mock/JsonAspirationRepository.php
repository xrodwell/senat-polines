<?php

namespace App\Repositories\Mock;

use App\Contracts\AspirationRepositoryInterface;

class JsonAspirationRepository implements AspirationRepositoryInterface
{
    public function all(): array
    {
        return json_decode(file_get_contents(storage_path('mock/aspirations.json')), true) ?: [];
    }

    public function findByTicket(string $ticket): ?array
    {
        foreach ($this->all() as $aspiration) {
            if ($aspiration['ticket_code'] === $ticket) {
                return $aspiration;
            }
        }

        return null;
    }
}
