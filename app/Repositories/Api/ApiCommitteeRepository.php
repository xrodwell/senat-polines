<?php

namespace App\Repositories\Api;

use App\Contracts\CommitteeRepositoryInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiCommitteeRepository implements CommitteeRepositoryInterface
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.backend.url', 'http://localhost:8001'), '/');
    }

    public function all(): array
    {
        try {
            $response = Http::timeout(10)
                ->get("{$this->baseUrl}/api/committees");

            if ($response->successful()) {
                // Support both { "data": [...] } and plain [...] response formats
                $data = $response->json('data') ?? $response->json();

                return is_array($data) ? $data : [];
            }

            Log::warning('API committees: non-success status', [
                'status' => $response->status(),
                'url'    => "{$this->baseUrl}/api/committees",
            ]);
        } catch (\Exception $e) {
            Log::error('API committees: request failed', [
                'message' => $e->getMessage(),
                'url'     => "{$this->baseUrl}/api/committees",
            ]);
        }

        return [];
    }
}
