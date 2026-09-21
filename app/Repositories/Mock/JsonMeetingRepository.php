<?php

namespace App\Repositories\Mock;

use App\Contracts\MeetingRepositoryInterface;

class JsonMeetingRepository implements MeetingRepositoryInterface
{
    public function all(): array
    {
        return json_decode(file_get_contents(storage_path('mock/meetings.json')), true) ?: [];
    }

    public function getActiveSession(): ?array
    {
        foreach ($this->all() as $meeting) {
            if ($meeting['status'] === 'Sedang Berlangsung') {
                return $meeting;
            }
        }

        return null;
    }

    public function getUpcoming(): array
    {
        return array_values(array_filter(
            $this->all(),
            fn (array $meeting): bool => $meeting['status'] === 'Terjadwal'
        ));
    }
}
