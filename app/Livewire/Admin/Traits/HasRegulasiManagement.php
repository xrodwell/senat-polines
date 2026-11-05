<?php

namespace App\Livewire\Admin\Traits;

use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Computed;

trait HasRegulasiManagement
{
    // State Katalog Regulasi & Produk Hukum (Backend API Integration)
    public array $regulasiList = [];
    public array $periodeList = [];
    public bool $isRegulasiFormOpen = false;
    public ?int $regulasiId = null;
    public string $regulasiNomor = '';
    public string $regulasiJudul = '';
    public string $regulasiKategori = 'Peraturan Senat';
    public string $regulasiTahap = 'Draf';
    public ?int $regulasiPeriodeId = null;
    public string $regulasiFile = '';
    public string $regulasiTanggal = '';
    public string $regulasiSearch = '';
    public string $regulasiFilterKategori = 'all';
    public string $regulasiFilterTahap = 'all';
    public ?int $viewingRegulasiId = null;


    /* ================= KATALOG REGULASI & PRODUK HUKUM ================= */

    public function loadRegulasiData(): void
    {
        $base = $this->getBackendUrl();

        try {
            $res = Http::timeout(4)->get("{$base}/peraturan", ['all' => 1]);
            if ($res->successful()) {
                $data = $res->json();
                $this->regulasiList = is_array($data) ? (isset($data['data']) ? $data['data'] : $data) : [];
            }
        } catch (\Throwable $e) {}

        try {
            $periode = Http::timeout(4)->get("{$base}/periode-senat", ['all' => 1]);
            if ($periode->successful()) {
                $dataP = $periode->json();
                $this->periodeList = is_array($dataP) ? (isset($dataP['data']) ? $dataP['data'] : $dataP) : [];
            }
        } catch (\Throwable $e) {}

        if (empty($this->periodeList)) {
            $this->periodeList = [
                ['id_periode_senat' => 1, 'nama_periode' => '2025/2026'],
                ['id_periode_senat' => 2, 'nama_periode' => '2026-2029'],
            ];
        }

        if (! $this->regulasiPeriodeId) {
            $lastPeriode = end($this->periodeList);
            $this->regulasiPeriodeId = $lastPeriode['id_periode_senat'] ?? 1;
        }
    }

    public function openRegulasiForm(): void
    {
        $this->resetRegulasiForm();
        $this->regulasiTanggal = now()->format('Y-m-d');
        $this->isRegulasiFormOpen = true;
    }

    public function closeRegulasiForm(): void
    {
        $this->isRegulasiFormOpen = false;
        $this->resetRegulasiForm();
    }

    public function resetRegulasiForm(): void
    {
        $this->regulasiId = null;
        $this->regulasiNomor = '';
        $this->regulasiJudul = '';
        $this->regulasiKategori = 'Peraturan Senat';
        $this->regulasiTahap = 'Draf';
        $this->regulasiFile = '';
        $this->regulasiTanggal = '';
        if (empty($this->regulasiPeriodeId)) {
            $this->regulasiPeriodeId = $this->periodeList[0]['id_periode_senat'] ?? 1;
        }
    }

    public function editRegulasi(int $id): void
    {
        $item = collect($this->regulasiList)->firstWhere('id_peraturan', $id);
        if (! $item) {
            return;
        }

        $this->regulasiId = $id;
        $this->regulasiNomor = $item['nomor_sk'] ?? '';
        $this->regulasiJudul = $item['judul'] ?? '';
        $this->regulasiKategori = $item['kategori'] ?? 'Peraturan Senat';
        $this->regulasiTahap = $item['tahap_pembentukan'] ?? 'Draf';
        $this->regulasiPeriodeId = $item['id_periode_senat'] ?? 1;
        $this->regulasiFile = $item['file_dokumen'] ?? '';
        $this->regulasiTanggal = ! empty($item['tanggal_terbit'])
            ? substr((string) $item['tanggal_terbit'], 0, 10)
            : now()->format('Y-m-d');
        $this->isRegulasiFormOpen = true;
    }

    public function saveRegulasi(): void
    {
        if (trim($this->regulasiNomor) === '' || trim($this->regulasiJudul) === '') {
            $this->flashMessage = 'Peringatan: Nomor dokumen dan judul regulasi wajib diisi!';
            return;
        }

        if (! $this->regulasiPeriodeId) {
            $this->flashMessage = 'Peringatan: Periode senat wajib dipilih!';
            return;
        }

        $base = $this->getBackendUrl();
        $payload = [
            'id_periode_senat' => (int) $this->regulasiPeriodeId,
            'nomor_sk' => $this->regulasiNomor,
            'judul' => $this->regulasiJudul,
            'kategori' => $this->regulasiKategori,
            'tahap_pembentukan' => $this->regulasiTahap,
            'file_dokumen' => $this->regulasiFile ?: null,
            'tanggal_terbit' => $this->regulasiTanggal ?: now()->format('Y-m-d'),
        ];

        try {
            if ($this->regulasiId) {
                $res = Http::timeout(6)->put("{$base}/peraturan/{$this->regulasiId}", $payload);
                $this->flashMessage = $res->successful()
                    ? 'Berhasil! Produk hukum berhasil diperbarui di database Backend.'
                    : 'Gagal memperbarui: ' . ($res->json('message') ?? 'HTTP ' . $res->status());
            } else {
                $res = Http::timeout(6)->post("{$base}/peraturan", $payload);
                $this->flashMessage = $res->successful()
                    ? 'Berhasil! Produk hukum baru sukses ditambahkan ke database Backend.'
                    : 'Gagal menambahkan: ' . ($res->json('message') ?? 'HTTP ' . $res->status());
            }

            $this->loadRegulasiData();
            $this->closeRegulasiForm();
        } catch (\Throwable $e) {
            $this->flashMessage = 'Koneksi API Backend gagal: ' . $e->getMessage();
        }
    }

    public function deleteRegulasi(int $id): void
    {
        $base = $this->getBackendUrl();
        try {
            $res = Http::timeout(6)->delete("{$base}/peraturan/{$id}");
            $this->flashMessage = ($res->successful() || $res->status() === 204)
                ? "Produk hukum ID #{$id} berhasil dihapus dari database."
                : 'Gagal menghapus: HTTP ' . $res->status();
            $this->loadRegulasiData();
            if ($this->viewingRegulasiId === $id) {
                $this->viewingRegulasiId = null;
            }
        } catch (\Throwable $e) {
            $this->flashMessage = 'Koneksi API Backend gagal: ' . $e->getMessage();
        }
    }

    public function setRegulasiTahap(int $id, string $tahap): void
    {
        $base = $this->getBackendUrl();
        try {
            $res = Http::timeout(6)->put("{$base}/peraturan/{$id}", ['tahap_pembentukan' => $tahap]);
            if ($res->successful()) {
                $this->flashMessage = "Tahap produk hukum diubah menjadi: {$tahap}";
                $this->loadRegulasiData();
            }
        } catch (\Throwable $e) {
            $this->flashMessage = 'Gagal memperbarui tahap: ' . $e->getMessage();
        }
    }

    public function viewRegulasi(?int $id): void
    {
        $this->viewingRegulasiId = $id;
    }

    #[Computed]
    public function filteredRegulasi(): array
    {
        return collect($this->regulasiList)
            ->filter(function ($item) {
                if ($this->regulasiFilterKategori !== 'all'
                    && ($item['kategori'] ?? '') !== $this->regulasiFilterKategori) {
                    return false;
                }

                if ($this->regulasiFilterTahap !== 'all'
                    && strtolower($item['tahap_pembentukan'] ?? '') !== strtolower($this->regulasiFilterTahap)) {
                    return false;
                }

                if (! empty($this->regulasiSearch)) {
                    $search = strtolower($this->regulasiSearch);
                    $haystack = strtolower(
                        ($item['judul'] ?? '') . ' ' .
                        ($item['nomor_sk'] ?? '') . ' ' .
                        ($item['kategori'] ?? '') . ' ' .
                        ($item['tahap_pembentukan'] ?? '')
                    );
                    if (! str_contains($haystack, $search)) {
                        return false;
                    }
                }

                return true;
            })
            ->values()
            ->all();
    }

    public function regulasiStatistik(): array
    {
        $all = collect($this->regulasiList);
        return [
            'total' => $all->count(),
            'disahkan' => $all->filter(fn ($r) => strtolower($r['tahap_pembentukan'] ?? '') === 'disahkan')->count(),
            'proses' => $all->filter(fn ($r) => in_array(strtolower($r['tahap_pembentukan'] ?? ''), ['draf', 'pembahasan', 'harmonisasi'], true))->count(),
            'kategori' => $all->pluck('kategori')->filter()->unique()->count(),
        ];
    }

    public function chipState(string $val): string
    {
        if (str_contains($val, 'Pleno') || str_contains($val, 'Ditindaklanjuti')) {
            return 'warning';
        }
        if (str_contains($val, 'Komisi') || str_contains($val, 'Sedang') || str_contains($val, 'Penetapan')) {
            return 'info';
        }
        if (str_contains($val, 'Selesai') || str_contains($val, 'Disahkan') || str_contains($val, 'Keputusan')) {
            return 'success';
        }
        if (str_contains($val, 'Pertimbangan') || str_contains($val, 'Pimpinan') || str_contains($val, 'Terbuka')) {
            return 'warning';
        }
        return 'neutral';
    }

    public function chipLabel(string $val): string
    {
        if (str_contains($val, 'Pleno')) {
            return 'Pleno';
        }
        if (str_contains($val, 'Komisi')) {
            return 'Komisi';
        }
        return $val;
    }


}
