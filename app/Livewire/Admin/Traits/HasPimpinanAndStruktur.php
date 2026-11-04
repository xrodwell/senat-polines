<?php

namespace App\Livewire\Admin\Traits;

use Illuminate\Support\Facades\Http;

trait HasPimpinanAndStruktur
{
    // Form Pimpinan Kolektif Senat (Tersambung Backend API)
    public ?int $ketuaUserId = 1;
    public string $ketuaNama = 'Dr. Sukamto, S.Kom., M.T.';
    public string $ketuaNip = '196803121993031002';

    public ?int $sekretarisUserId = 2;
    public string $sekretarisNama = 'Andi Setyawan, S.E., M.M.';
    public string $sekretarisNip = '197405191999031001';

    public function saveAction(string $entity): void
    {
        $this->flashMessage = "Data {$entity} berhasil disimpan.";
    }

    public function updatePimpinan(): void
    {
        try {
            $base = $this->getBackendUrl();
            if ($this->ketuaUserId) {
                Http::timeout(5)->put("{$base}/user/{$this->ketuaUserId}", [
                    'nama' => $this->ketuaNama,
                    'nim_nip' => $this->ketuaNip,
                ]);
            }
            if ($this->sekretarisUserId) {
                Http::timeout(5)->put("{$base}/user/{$this->sekretarisUserId}", [
                    'nama' => $this->sekretarisNama,
                    'nim_nip' => $this->sekretarisNip,
                ]);
            }
            $this->flashMessage = 'Data pimpinan Senat Akademik berhasil diperbarui.';
        } catch (\Throwable $e) {
            $this->flashMessage = 'Gagal menyimpan perubahan data pimpinan: ' . $e->getMessage();
        }
    }


    /**
     * Ambil data struktur pimpinan dari Backend API
     */
    public function loadStructureFromApi(): void
    {
        try {
            $base = $this->getBackendUrl();
            $response = Http::timeout(2)->get("{$base}/user");
            if ($response->successful()) {
                $users = $response->json()['data'] ?? $response->json() ?? [];
                foreach ($users as $user) {
                    $id = $user['id'] ?? $user['id_user'] ?? null;
                    if ($id == $this->ketuaUserId) {
                        $this->ketuaNama = $user['nama'] ?? $this->ketuaNama;
                        $this->ketuaNip = $user['nim_nip'] ?? $this->ketuaNip;
                    }
                    if ($id == $this->sekretarisUserId) {
                        $this->sekretarisNama = $user['nama'] ?? $this->sekretarisNama;
                        $this->sekretarisNip = $user['nim_nip'] ?? $this->sekretarisNip;
                    }
                }
            }
        } catch (\Throwable $e) {}
    }


}
