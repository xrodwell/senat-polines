<?php

namespace App\Livewire\Admin\Traits;

use Illuminate\Support\Facades\Http;

trait HasAspirationManagement
{
    // State untuk Modul Pusat Aspirasi & Tindak Lanjut (Tersambung ke API)
    public array $apiAspirations = [];
    public ?int $selectedAspirasiId = null;
    public string $tanggapanText = '';


    /**
     * Ambil data tiket aspirasi asli dari Backend API
     */
    public function loadAspirationsFromApi(): void
    {
        try {
            $base = $this->getBackendUrl();
            $response = Http::timeout(2)->get("{$base}/aspirasi");
            if ($response->successful()) {
                $items = $response->json()['data'] ?? [];
                $this->apiAspirations = collect($items)->map(function ($item) {
                    $tanggapans = $item['tanggapans'] ?? [];
                    $lastTanggapan = !empty($tanggapans) ? end($tanggapans)['pesan'] : 'Belum ada tanggapan resmi dari komisi.';
                    return [
                        'id' => $item['id_aspirasi'],
                        'ticket_code' => 'ASP-' . str_pad((string) $item['id_aspirasi'], 4, '0', STR_PAD_LEFT),
                        'sender_category' => $item['anonim'] ? 'Mahasiswa (Anonim)' : ($item['user']['nama'] ?? 'Mahasiswa'),
                        'jurusan' => $item['kategori']['nama_kategori'] ?? 'Umum',
                        'status' => ucfirst($item['status'] ?? 'pending'),
                        'raw_status' => strtolower($item['status'] ?? 'pending'),
                        'subject' => \Illuminate\Support\Str::limit($item['isi_aspirasi'], 65),
                        'description' => $item['isi_aspirasi'],
                        'response' => $lastTanggapan,
                    ];
                })->toArray();
            }
        } catch (\Throwable $e) {}
    }

    /**
     * Ubah status aspirasi (Pending / Diproses / Selesai)
     */
    public function setAspirasiStatus(int $id, string $status): void
    {
        // 1. Ubah tampilan di layar secara instan (0 lag) tanpa dangling reference
        foreach ($this->apiAspirations as $index => $item) {
            if ($item['id'] === $id) {
                $this->apiAspirations[$index]['status'] = ucfirst($status);
                $this->apiAspirations[$index]['raw_status'] = strtolower($status);
                break;
            }
        }
        // 2. Simpan perubahannya ke Backend
        try {
            $base = $this->getBackendUrl();
            Http::put("{$base}/aspirasi/{$id}", [
                'status' => $status,
            ]);
            $this->flashMessage = "Status aspirasi berhasil diubah menjadi: " . ucfirst($status);
        } catch (\Throwable $e) {
            $this->flashMessage = "Gagal memperbarui status: " . $e->getMessage();
        }
    }

    /**
     * Buka form untuk mengetik tanggapan
     */
    public function openTanggapanForm(int $id): void
    {
        $this->selectedAspirasiId = ($this->selectedAspirasiId === $id) ? null : $id;
        $this->tanggapanText = '';
    }

    /**
     * Kirim tanggapan resmi komisi ke database Backend
     */
    public function submitTanggapan(?int $aspirasiId = null): void
    {
        $id = $aspirasiId ?: $this->selectedAspirasiId;
        if (! $id) {
            $this->flashMessage = "Peringatan: ID tiket aspirasi tidak ditemukan.";
            return;
        }

        $pesan = trim($this->tanggapanText);
        if (empty($pesan)) {
            $this->flashMessage = "Peringatan: Mohon ketikkan isi tanggapan terlebih dahulu.";
            return;
        }

        // 1. Update tampilan kotak tanggapan di layar secara instan
        foreach ($this->apiAspirations as $index => $item) {
            if ($item['id'] == $id) {
                $this->apiAspirations[$index]['response'] = $pesan;
                $this->apiAspirations[$index]['status'] = 'Diproses';
                $this->apiAspirations[$index]['raw_status'] = 'diproses';
                break;
            }
        }
        $this->selectedAspirasiId = null;
        $this->tanggapanText = '';

        // 2. Simpan tanggapan ke tabel tanggapan_aspirasis di Backend
        try {
            $base = $this->getBackendUrl();
            $respPost = Http::timeout(5)->post("{$base}/tanggapan-aspirasi", [
                'id_aspirasi' => $id,
                'id_user' => 1,
                'pesan' => $pesan,
            ]);
            $respPut = Http::timeout(5)->put("{$base}/aspirasi/{$id}", [
                'status' => 'diproses',
            ]);

            if ($respPost->successful()) {
                $this->flashMessage = "Berhasil! Tanggapan resmi komisi telah tersimpan ke database Backend.";
            } else {
                $this->flashMessage = "Tanggapan telah diperbarui di layar!";
            }
        } catch (\Throwable $e) {
            $this->flashMessage = "Tanggapan disimpan di layar (Gagal ke Backend: " . $e->getMessage() . ")";
        }
    }

}
