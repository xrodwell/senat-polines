<?php

namespace App\Repositories\Mock;

use App\Contracts\RegulationRepositoryInterface;

class JsonRegulationRepository implements RegulationRepositoryInterface
{
    public function all(): array
    {
        return json_decode(file_get_contents(storage_path('mock/regulations.json')), true) ?: [];
    }
}
