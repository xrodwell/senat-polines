<?php

namespace App\Repositories\Mock;

use App\Contracts\ArchiveRepositoryInterface;

class JsonArchiveRepository implements ArchiveRepositoryInterface
{
    public function all(): array
    {
        return json_decode(file_get_contents(storage_path('mock/archives.json')), true) ?: [];
    }
}
