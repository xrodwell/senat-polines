<?php

namespace App\Repositories\Api;

use App\Contracts\CommitteeRepositoryInterface;
use App\Repositories\Mock\JsonCommitteeRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiCommitteeRepository implements CommitteeRepositoryInterface
{
    public function __construct(
        private JsonCommitteeRepository $fallback = new JsonCommitteeRepository()
    ) {}

    public function all(): array
    {
        $apiUrl = env('SENAT_BACKEND_API_URL', 'http://127.0.0.1:8001/api');

        try {
            $response = Http::timeout(3)->get("{$apiUrl}/komisi");

            if ($response->successful()) {
                $apiData = $response->json('data', []);
                $fallbackData = $this->fallback->all();

                if (empty($apiData)) {
                    return $fallbackData;
                }

                // Interoperabilitas: Sinkronkan nama dan deskripsi komisi langsung dari Backend API berdasarkan ID komisi
                $apiById = collect($apiData)->keyBy('id_komisi');

                return array_map(function ($fb) use ($apiById) {
                    $matched = $apiById->get($fb['id']);
                    if ($matched) {
                        $fb['name'] = $matched['nama_komisi'] ?? $fb['name'];
                        $fb['focus'] = $matched['deskripsi'] ?? $fb['focus'];
                    }
                    return $fb;
                }, $fallbackData);
            }
        } catch (\Throwable $e) {
            Log::warning('Interoperabilitas API Komisi fallback ke local cache: ' . $e->getMessage());
        }

        return $this->fallback->all();
    }
}
