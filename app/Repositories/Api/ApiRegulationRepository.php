<?php

namespace App\Repositories\Api;

use App\Contracts\RegulationRepositoryInterface;
use App\Support\PublicNewsApi;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiRegulationRepository implements RegulationRepositoryInterface
{
    public function all(): array
    {
        $base = PublicNewsApi::backendBaseUrl();
        $apiUrl = $base !== null
            ? $base.'/api'
            : rtrim((string) config('services.backend_api.url', 'http://127.0.0.1:8001/api'), '/');

        try {
            $response = Http::timeout(3)->get("{$apiUrl}/peraturan", ['all' => 1]);

            if ($response->successful()) {
                $payload = $response->json();
                $items = is_array($payload)
                    ? (isset($payload['data']) && is_array($payload['data']) ? $payload['data'] : $payload)
                    : [];

                $items = array_values(array_filter(
                    $items,
                    fn ($r) => is_array($r) && ! ApiArchiveRepository::isReportCategory((string) ($r['kategori'] ?? ''))
                ));

                return array_map(function ($r) use ($apiUrl) {
                    $id = $r['id_peraturan'] ?? 1;
                    $fileDokumen = trim((string) ($r['file_dokumen'] ?? ''));

                    if ($fileDokumen === '') {
                        $downloadUrl = '#';
                        $fileBadge = 'Belum ada berkas';
                    } elseif (str_starts_with($fileDokumen, 'http://') || str_starts_with($fileDokumen, 'https://')) {
                        $downloadUrl = $fileDokumen;
                        $ext = strtoupper((string) pathinfo(parse_url($fileDokumen, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
                        $fileBadge = $ext !== '' ? $ext : 'Tautan';
                    } else {
                        $downloadUrl = rtrim($apiUrl, '/') . "/peraturan/{$id}/download";
                        $ext = strtoupper((string) pathinfo($fileDokumen, PATHINFO_EXTENSION));
                        $fileBadge = $ext !== '' ? $ext : 'Dokumen';
                    }

                    return [
                        'id' => $id,
                        'nomor' => $r['nomor_sk'] ?? 'SK/00/2026',
                        'title' => $r['judul'] ?? '',
                        'year' => isset($r['tanggal_terbit']) ? (int) substr($r['tanggal_terbit'], 0, 4) : 2026,
                        'category' => $r['kategori'] ?? 'Peraturan Senat',
                        'status' => $r['tahap_pembentukan'] ?? 'Disahkan',
                        'file_size' => $fileBadge,
                        'download_url' => $downloadUrl,
                    ];
                }, $items);
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal mengambil data Peraturan dari API backend: ' . $e->getMessage());
        }

        return [];
    }
}
