<?php

namespace App\Repositories\Api;

use App\Contracts\CommitteeRepositoryInterface;
use App\Repositories\Mock\JsonCommitteeRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiCommitteeRepository implements CommitteeRepositoryInterface
{
    private string $baseUrl;
    private JsonCommitteeRepository $fallback;

    public function __construct(?JsonCommitteeRepository $fallback = null)
    {
        $this->fallback = $fallback ?? new JsonCommitteeRepository();
        $this->baseUrl = rtrim(
            config('services.backend.url')
            ?? config('services.backend_api.url')
            ?? env('SENAT_BACKEND_API_URL', 'http://127.0.0.1:8001'),
            '/'
        );
    }

    public function all(): array
    {
        // 1. Coba endpoint /api/committees (format lengkap dengan relasi anggota)
        try {
            $url = str_contains($this->baseUrl, '/api')
                ? "{$this->baseUrl}/committees"
                : "{$this->baseUrl}/api/committees";

            $response = Http::timeout(4)->get($url);

            if ($response->successful()) {
                $data = $response->json('data') ?? $response->json();
                if (is_array($data) && !empty($data)) {
                    return $data;
                }
            }
        } catch (\Throwable $e) {
            Log::info('API committees request failed, attempting fallback: ' . $e->getMessage());
        }

        // 2. Coba endpoint alternatif /api/komisi
        try {
            $urlKomisi = str_contains($this->baseUrl, '/api')
                ? "{$this->baseUrl}/komisi"
                : "{$this->baseUrl}/api/komisi";

            $response = Http::timeout(3)->get($urlKomisi);

            if ($response->successful()) {
                $apiData = $response->json('data', []);
                $fallbackData = $this->fallback->all();

                if (!empty($apiData)) {
                    return array_map(function ($fb, $index) use ($apiData) {
                        if (isset($apiData[$index])) {
                            $fb['name'] = $apiData[$index]['nama_komisi'] ?? $fb['name'];
                            $fb['focus'] = $apiData[$index]['deskripsi'] ?? $fb['focus'];
                        }
                        return $fb;
                    }, $fallbackData, array_keys($fallbackData));
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Interoperabilitas API Komisi fallback ke local cache: ' . $e->getMessage());
        }

        // 3. Fallback ke data mock lokal jika backend tidak aktif
        return $this->fallback->all();
    }
}

