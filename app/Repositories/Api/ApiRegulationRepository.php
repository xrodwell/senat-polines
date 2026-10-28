<?php

namespace App\Repositories\Api;

use App\Contracts\RegulationRepositoryInterface;
use App\Repositories\Mock\JsonRegulationRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiRegulationRepository implements RegulationRepositoryInterface
{
    public function __construct(
        private JsonRegulationRepository $fallback = new JsonRegulationRepository()
    ) {}

    public function all(): array
    {
        $apiUrl = env('SENAT_BACKEND_API_URL', 'http://127.0.0.1:8001/api');

        try {
            $response = Http::timeout(3)->get("{$apiUrl}/peraturan");

            if ($response->successful()) {
                $items = $response->json('data', []);
                if (!empty($items)) {
                    return array_map(function ($r) {
                        return [
                            'id' => $r['id_peraturan'] ?? 1,
                            'nomor' => $r['nomor_sk'] ?? 'SK/00/2026',
                            'title' => $r['judul'] ?? '',
                            'year' => isset($r['tanggal_terbit']) ? (int) substr($r['tanggal_terbit'], 0, 4) : 2026,
                            'category' => $r['kategori'] ?? 'Peraturan Senat',
                            'status' => $r['tahap_pembentukan'] ?? 'Disahkan',
                            'file_size' => '2.1 MB',
                            'download_url' => $r['file_dokumen'] ?? '#'
                        ];
                    }, $items);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Interoperabilitas API Peraturan fallback ke local cache: ' . $e->getMessage());
        }

        return $this->fallback->all();
    }
}
