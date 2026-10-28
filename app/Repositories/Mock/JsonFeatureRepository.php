<?php

namespace App\Repositories\Mock;

use App\Contracts\FeatureRepositoryInterface;

class JsonFeatureRepository implements FeatureRepositoryInterface
{
    public function all(): array
    {
        return json_decode(file_get_contents(storage_path('mock/features.json')), true) ?: [];
    }
}
