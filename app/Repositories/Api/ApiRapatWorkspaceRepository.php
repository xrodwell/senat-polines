<?php

namespace App\Repositories\Api;

use App\Contracts\RapatWorkspaceRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Proxy server-side ke Backend-Senat-Polines. Dipakai agar browser tetap
 * same-origin (tanpa CORS) dan agar tidak ada kredensial di sisi klien.
 */
class ApiRapatWorkspaceRepository implements RapatWorkspaceRepositoryInterface
{
    private function base(): string
    {
        return rtrim(env('SENAT_BACKEND_API_URL', env('BACKEND_API_URL', 'http://127.0.0.1:8001/api')), '/');
    }

    private function http()
    {
        $token = session('auth_token');
        $client = Http::acceptJson();
        return $token ? $client->withToken($token) : $client;
    }

    public function catatan(int $idRapat, int $idUser): ?array
    {
        try {
            $res = $this->http()->timeout(4)
                ->get($this->base() . "/rapat/{$idRapat}/catatan", ['id_user' => $idUser]);

            if (! $res->successful()) {
                return null;
            }

            $rows = $res->json();
            $row = is_array($rows) ? ($rows[0] ?? null) : null;

            return $row ? [
                'isi' => (string) ($row['isi'] ?? ''),
                'diperbarui' => $row['updated_at'] ?? null,
            ] : null;
        } catch (\Throwable $e) {
            Log::warning('Gagal memuat catatan rapat: ' . $e->getMessage());

            return null;
        }
    }

    public function simpanCatatan(int $idRapat, int $idUser, string $isi): array
    {
        $res = $this->http()->timeout(4)
            ->put($this->base() . "/rapat/{$idRapat}/catatan", [
                'id_user' => $idUser,
                'isi' => $isi,
            ]);

        if (! $res->successful()) {
            throw new RuntimeException('Backend menolak simpan catatan (' . $res->status() . ').');
        }

        return $res->json() ?? [];
    }

    public function lampiran(int $idRapat): array
    {
        try {
            $res = $this->http()->timeout(4)->get($this->base() . "/rapat/{$idRapat}/lampiran");

            return $res->successful() ? (array) $res->json() : [];
        } catch (\Throwable $e) {
            Log::warning('Gagal memuat lampiran rapat: ' . $e->getMessage());

            return [];
        }
    }

    public function unggahLampiran(int $idRapat, int $idUser, UploadedFile $file): array
    {
        $res = $this->http()->timeout(600)
            ->attach('file', file_get_contents($file->getRealPath()), $file->getClientOriginalName(), [
                'Content-Type' => $file->getClientMimeType() ?: 'application/octet-stream',
            ])
            ->post($this->base() . "/rapat/{$idRapat}/lampiran", ['id_user' => $idUser]);

        if (! $res->successful()) {
            throw new RuntimeException('Backend menolak unggahan (' . $res->status() . ').');
        }

        return $res->json() ?? [];
    }

    public function hapusLampiran(int $idRapat, int $idLampiran): bool
    {
        $res = $this->http()->timeout(4)
            ->delete($this->base() . "/rapat/{$idRapat}/lampiran/{$idLampiran}");

        return $res->successful();
    }

    public function berbagi(int $idRapat): array
    {
        try {
            $res = $this->http()->timeout(4)->get($this->base() . "/rapat/{$idRapat}/berbagi");

            return $res->successful() ? (array) $res->json() : [];
        } catch (\Throwable $e) {
            Log::warning('Gagal memuat berbagi rapat: ' . $e->getMessage());

            return [];
        }
    }

    public function bagikan(int $idRapat, int $idUser, array $payload): array
    {
        if (! empty($payload['file']) && $payload['file'] instanceof UploadedFile) {
            $file = $payload['file'];

            $res = $this->http()->timeout(600)
                ->attach('file', file_get_contents($file->getRealPath()), $file->getClientOriginalName(), [
                    'Content-Type' => $file->getClientMimeType() ?: 'application/octet-stream',
                ])
                ->post($this->base() . "/rapat/{$idRapat}/berbagi", ['id_user' => $idUser]);
        } else {
            $res = $this->http()->timeout(4)
                ->post($this->base() . "/rapat/{$idRapat}/berbagi", [
                    'id_user' => $idUser,
                    'jenis' => 'link',
                    'nama' => $payload['nama'] ?? null,
                    'url' => $payload['url'] ?? null,
                ]);
        }

        if (! $res->successful()) {
            throw new RuntimeException(
                $res->json('message') ?: 'Backend menolak berbagi (' . $res->status() . ').'
            );
        }

        return $res->json() ?? [];
    }

    public function hapusBerbagi(int $idRapat, int $idBerbagi): bool
    {
        $res = $this->http()->timeout(4)
            ->delete($this->base() . "/rapat/{$idRapat}/berbagi/{$idBerbagi}");

        return $res->successful();
    }
}
