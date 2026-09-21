<?php

namespace App\Repositories\Mock;

use App\Contracts\CommitteeRepositoryInterface;

class JsonCommitteeRepository implements CommitteeRepositoryInterface
{
    public function all(): array
    {
        return json_decode(file_get_contents(storage_path('mock/committees.json')), true) ?: [];
    }
}
