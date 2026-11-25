<?php

namespace App\Repositories\Api;

use App\Contracts\ArchiveRepositoryInterface;
use App\Support\PublicNewsApi;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Mengambil arsip risalah sidang (/api/notulensi) dan laporan tahunan (/api/peraturan)
 * langsung dari Backend API tanpa fallback ke data mock statis.
 */
class ApiArchiveRepository implements ArchiveRepositoryInterface
{
    /** @var list<string> */
    public const REPORT_CATEGORIES = [
        'Laporan Tahunan',
        'LPJ & Akuntabilitas',
        'Memori Jabatan',
    ];

    public static function isReportCategory(string $category): bool
    {
        $lower = mb_strtolower(trim($category));
        if ($lower === '') {
            return false;
        }

        foreach (['laporan', 'lpj', 'memori jabatan', 'akuntabilitas'] as $keyword) {
            if (str_contains($lower, $keyword)) {
                return true;
            }
        }

        return false;
    }

    public function all(): array
    {
        $base = PublicNewsApi::backendBaseUrl();
        if ($base === null) {
            return [];
        }

        $apiUrl = $base.'/api';
        $items = array_merge(
            $this->fetchMinutesArchives($apiUrl),
            $this->fetchReportArchives($apiUrl),
        );

        usort($items, static function (array $a, array $b): int {
            $dateCmp = strcmp((string) ($b['sort_date'] ?? ''), (string) ($a['sort_date'] ?? ''));

            return $dateCmp !== 0 ? $dateCmp : strcmp((string) ($b['id'] ?? ''), (string) ($a['id'] ?? ''));
        });

        return $items;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fetchMinutesArchives(string $apiUrl): array
    {
        try {
            $response = Http::acceptJson()->timeout(3)->get("{$apiUrl}/notulensi", ['all' => 1]);
            if (! $response->successful()) {
                return [];
            }

            $payload = $response->json();
            $rows = is_array($payload)
                ? (isset($payload['data']) && is_array($payload['data']) ? $payload['data'] : $payload)
                : [];

            if (! is_array($rows)) {
                return [];
            }

            $out = [];
            foreach ($rows as $row) {
                if (! is_array($row) || ! isset($row['id_notulensi'])) {
                    continue;
                }

                $id = (int) $row['id_notulensi'];
                $agenda = trim((string) ($row['rapat']['nama_agenda'] ?? ''));
                $jenisRapat = mb_strtolower(trim((string) ($row['rapat']['jenis_rapat']['nama_jenis'] ?? '')));
                $rawDate = (string) ($row['tanggal_dibuat'] ?? $row['rapat']['tanggal'] ?? date('Y-m-d'));
                $year = (int) substr($rawDate, 0, 4);
                $bodyText = trim((string) preg_replace('/\s+/', ' ', strip_tags((string) ($row['isi_notulensi'] ?? ''))));

                $category = match (true) {
                    str_contains($jenisRapat, 'komisi') => 'Notulen Komisi',
                    str_contains($jenisRapat, 'pimpinan'), str_contains($jenisRapat, 'badan pekerja') => 'Notulen Pimpinan & BP',
                    default => 'Notulen Sidang',
                };

                $out[] = [
                    'id' => 'notulen-'.$id,
                    'title' => $agenda !== '' ? "Risalah — {$agenda}" : "Notulen Sidang #{$id}",
                    'year' => $year > 0 ? $year : (int) date('Y'),
                    'category' => $category,
                    'summary' => $bodyText !== '' ? Str::limit($bodyText, 200) : null,
                    'status' => $row['status'] ?? null,
                    'file_size' => ! empty($row['berkas_pdf']) ? 'PDF Resmi' : 'PDF Siap Cetak',
                    'download_url' => "{$apiUrl}/notulensi/{$id}/pdf",
                    'sort_date' => substr($rawDate, 0, 10),
                ];
            }

            return $out;
        } catch (\Throwable $e) {
            Log::warning('Gagal mengambil arsip notulensi dari API backend: '.$e->getMessage());

            return [];
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fetchReportArchives(string $apiUrl): array
    {
        try {
            $response = Http::acceptJson()->timeout(3)->get("{$apiUrl}/peraturan", ['all' => 1]);
            if (! $response->successful()) {
                return [];
            }

            $payload = $response->json();
            $rows = is_array($payload)
                ? (isset($payload['data']) && is_array($payload['data']) ? $payload['data'] : $payload)
                : [];

            if (! is_array($rows)) {
                return [];
            }

            $out = [];
            foreach ($rows as $row) {
                if (! is_array($row)) {
                    continue;
                }

                $category = (string) ($row['kategori'] ?? '');
                if (! self::isReportCategory($category)) {
                    continue;
                }

                $id = (int) ($row['id_peraturan'] ?? 0);
                $fileDokumen = trim((string) ($row['file_dokumen'] ?? ''));
                $rawDate = (string) ($row['tanggal_terbit'] ?? date('Y-m-d'));
                $year = (int) substr($rawDate, 0, 4);

                if ($fileDokumen === '') {
                    $downloadUrl = '#';
                    $fileBadge = 'Belum ada berkas';
                } elseif (str_starts_with($fileDokumen, 'http://') || str_starts_with($fileDokumen, 'https://')) {
                    $downloadUrl = $fileDokumen;
                    $ext = strtoupper((string) pathinfo(parse_url($fileDokumen, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
                    $fileBadge = $ext !== '' ? $ext : 'Tautan';
                } else {
                    $downloadUrl = "{$apiUrl}/peraturan/{$id}/download";
                    $ext = strtoupper((string) pathinfo($fileDokumen, PATHINFO_EXTENSION));
                    $fileBadge = $ext !== '' ? $ext : 'Dokumen';
                }

                $metaParts = array_filter([
                    ! empty($row['nomor_sk']) ? 'Nomor: '.$row['nomor_sk'] : null,
                    ! empty($row['tahap_pembentukan']) ? 'Status: '.$row['tahap_pembentukan'] : null,
                ]);

                $out[] = [
                    'id' => 'doc-'.$id,
                    'title' => (string) ($row['judul'] ?? 'Laporan Tahunan Senat'),
                    'year' => $year > 0 ? $year : (int) date('Y'),
                    'category' => $category,
                    'summary' => ! empty($metaParts) ? implode(' · ', $metaParts) : null,
                    'status' => $row['tahap_pembentukan'] ?? null,
                    'file_size' => $fileBadge,
                    'download_url' => $downloadUrl,
                    'sort_date' => substr($rawDate, 0, 10),
                ];
            }

            return $out;
        } catch (\Throwable $e) {
            Log::warning('Gagal mengambil arsip laporan dari API backend: '.$e->getMessage());

            return [];
        }
    }
}
