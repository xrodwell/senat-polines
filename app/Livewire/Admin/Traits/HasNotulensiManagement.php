<?php

namespace App\Livewire\Admin\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;

trait HasNotulensiManagement
{
    // State Risalah & Notulensi Sidang (Backend API Integration)
    public array $notulensiList = [];
    public array $rapatList = [];
    public array $userList = [];
    public ?int $selectedNotulensiId = null;
    public ?int $notulensiRapatId = null;
    public ?int $notulensiUserId = 1;
    public string $notulensiIsi = '';
    public string $notulensiFile = '';
    public string $notulensiStatus = 'draf';
    public string $notulensiSearch = '';
    public string $notulensiFilterStatus = 'all';
    public ?int $viewingNotulensiId = null;
    public bool $isFormOpen = false;

    // State Notulensi Otomatis (rekaman audio/video → transkrip → draf notulensi)
    public $rekamanFile = null;
    public ?int $rekamanRapatId = null;
    public ?int $rekamanAktifId = null;
    public string $rekamanStatus = '';
    public int $rekamanProgres = 0;
    public string $rekamanTranskrip = '';
    public string $rekamanNotulen = '';
    public string $rekamanError = '';
    public string $rekamanNama = '';
    public int $rekamanDurasi = 0;
    public array $rekamanList = [];


    public function loadNotulensiData(): void
    {
        $base = $this->getBackendUrl();
        try {
            $res = Http::timeout(4)->get("{$base}/notulensi", ['all' => 1]);
            if ($res->successful()) {
                $data = $res->json();
                $this->notulensiList = is_array($data) ? (isset($data['data']) ? $data['data'] : $data) : [];
            }
        } catch (\Throwable $e) {}

        try {
            $resRapat = Http::timeout(4)->get("{$base}/rapat", ['all' => 1]);
            if ($resRapat->successful()) {
                $dataRapat = $resRapat->json();
                $this->rapatList = is_array($dataRapat) ? (isset($dataRapat['data']) ? $dataRapat['data'] : $dataRapat) : [];
                if (! $this->notulensiRapatId && ! empty($this->rapatList)) {
                    $this->notulensiRapatId = $this->rapatList[0]['id_rapat'] ?? null;
                }
            }
        } catch (\Throwable $e) {}

        try {
            $resUser = Http::timeout(4)->get("{$base}/user", ['all' => 1]);
            if ($resUser->successful()) {
                $dataUser = $resUser->json();
                $this->userList = is_array($dataUser) ? (isset($dataUser['data']) ? $dataUser['data'] : $dataUser) : [];
            }
        } catch (\Throwable $e) {}

        if (empty($this->rapatList)) {
            $this->rapatList = [
                ['id_rapat' => 1, 'nama_agenda' => 'Sidang Pleno Khusus Penetapan Draft OTK Senat 2026', 'tempat' => 'Ruang Sidang Utama', 'tanggal' => '2026-10-09'],
                ['id_rapat' => 2, 'nama_agenda' => 'Rapat Koordinasi Komisi I: Penyelarasan Kurikulum MBKM', 'tempat' => 'Ruang Rapat Senat', 'tanggal' => '2026-10-16'],
                ['id_rapat' => 3, 'nama_agenda' => 'Sidang Pleno Evaluasi Akreditasi Prodi Teknik', 'tempat' => 'Ruang Sidang Utama', 'tanggal' => '2026-10-24'],
            ];
            $this->notulensiRapatId = 1;
        }

        if (empty($this->userList)) {
            $this->userList = [
                ['id_user' => 1, 'nama' => 'Administrator Senat', 'nim_nip' => '198001012005011001'],
                ['id_user' => 2, 'nama' => 'Dr. Sukamto, S.Kom., M.T.', 'nim_nip' => '196803121993031002'],
                ['id_user' => 3, 'nama' => 'Andi Setyawan, S.E., M.M.', 'nim_nip' => '197405191999031001'],
            ];
        }

        // Modul Notulensi Otomatis memakai rapat yang sama dengan form notulensi
        if (! $this->rekamanRapatId) {
            $this->rekamanRapatId = $this->notulensiRapatId;
        }
        $this->muatRekamanList();
    }

    public function openNotulensiForm(): void
    {
        $this->isFormOpen = true;
    }

    public function closeNotulensiForm(): void
    {
        $this->isFormOpen = false;
        $this->resetNotulensiForm();
    }

    public function resetNotulensiForm(): void
    {
        $this->selectedNotulensiId = null;
        $this->notulensiRapatId = $this->rapatList[0]['id_rapat'] ?? null;
        $this->notulensiUserId = $this->userList[0]['id_user'] ?? 1;
        $this->notulensiIsi = '';
        $this->notulensiFile = '';
        $this->notulensiStatus = 'draf';
    }

    public function editNotulensi(int $id): void
    {
        $item = collect($this->notulensiList)->firstWhere('id_notulensi', $id);
        if ($item) {
            $this->selectedNotulensiId = $id;
            $this->notulensiRapatId = $item['id_rapat'] ?? null;
            $this->notulensiUserId = $item['id_user'] ?? 1;
            $this->notulensiIsi = $item['isi_notulensi'] ?? '';
            $this->notulensiFile = $item['file_risalah'] ?? '';
            $this->notulensiStatus = strtolower($item['status'] ?? 'draf');
            $this->isFormOpen = true;
        }
    }

    public function saveNotulensi(): void
    {
        if (empty(trim($this->notulensiIsi))) {
            $this->flashMessage = 'Peringatan: Jalannya pembahasan dan isi notulensi tidak boleh kosong!';
            return;
        }

        if (! $this->notulensiRapatId) {
            $this->flashMessage = 'Peringatan: Silakan pilih sidang/rapat terkait!';
            return;
        }

        $base = $this->getBackendUrl();
        $payload = [
            'id_rapat' => (int) $this->notulensiRapatId,
            'id_user' => (int) ($this->notulensiUserId ?: 1),
            'isi_notulensi' => $this->notulensiIsi,
            'file_risalah' => $this->notulensiFile ?: null,
            'status' => $this->notulensiStatus ?: 'draf',
        ];

        try {
            if ($this->selectedNotulensiId) {
                $res = Http::timeout(5)->put("{$base}/notulensi/{$this->selectedNotulensiId}", $payload);
                if ($res->successful()) {
                    $this->flashMessage = 'Berhasil! Notulensi & Risalah sidang berhasil diperbarui di database Backend.';
                } else {
                    $this->flashMessage = 'Gagal menyimpan: ' . ($res->json('message') ?? 'HTTP ' . $res->status());
                }
            } else {
                $res = Http::timeout(5)->post("{$base}/notulensi", $payload);
                if ($res->successful()) {
                    $this->flashMessage = 'Berhasil! Notulensi & Risalah sidang baru sukses disimpan ke database Backend.';
                } else {
                    $this->flashMessage = 'Gagal membuat notulensi: ' . ($res->json('message') ?? 'HTTP ' . $res->status());
                }
            }
            $this->loadNotulensiData();
            $this->closeNotulensiForm();
        } catch (\Throwable $e) {
            $this->flashMessage = 'Koneksi API Backend gagal: ' . $e->getMessage();
        }
    }

    public function deleteNotulensi(int $id): void
    {
        $base = $this->getBackendUrl();
        try {
            $res = Http::timeout(5)->delete("{$base}/notulensi/{$id}");
            if ($res->successful() || $res->status() === 204) {
                $this->flashMessage = "Notulensi ID #{$id} berhasil dihapus dari database.";
            } else {
                $this->flashMessage = "Gagal menghapus: HTTP " . $res->status();
            }
            $this->loadNotulensiData();
            if ($this->selectedNotulensiId === $id) {
                $this->resetNotulensiForm();
                $this->isFormOpen = false;
            }
            if ($this->viewingNotulensiId === $id) {
                $this->viewingNotulensiId = null;
            }
        } catch (\Throwable $e) {
            $this->flashMessage = 'Koneksi API Backend gagal: ' . $e->getMessage();
        }
    }

    public function setNotulensiStatus(int $id, string $status): void
    {
        $base = $this->getBackendUrl();
        try {
            $res = Http::timeout(5)->put("{$base}/notulensi/{$id}", ['status' => $status]);
            if ($res->successful()) {
                $this->flashMessage = "Status risalah berhasil diubah menjadi: " . ucfirst($status);
                $this->loadNotulensiData();
            }
        } catch (\Throwable $e) {
            $this->flashMessage = 'Gagal memperbarui status: ' . $e->getMessage();
        }
    }

    /* ================= NOTULENSI OTOMATIS (rekaman → teks → draf) ================= */

    public function updatedRekamanRapatId(): void
    {
        $this->muatRekamanList();
    }

    public function muatRekamanList(): void
    {
        $id = $this->rekamanRapatId ?: $this->notulensiRapatId;

        if (! $id) {
            $this->rekamanList = [];

            return;
        }

        try {
            $res = Http::timeout(6)->get($this->getBackendUrl() . "/rapat/{$id}/rekaman");

            if (! $res->successful()) {
                $this->rekamanList = [];

                return;
            }

            $this->rekamanList = array_map(fn ($r) => [
                'id_rekaman' => $r['id_rekaman'] ?? null,
                'nama_asli' => $r['nama_asli'] ?? '',
                'status' => $r['status'] ?? '',
                'progres' => (int) ($r['progres'] ?? 0),
                'durasi' => (int) ($r['durasi'] ?? 0),
                'ukuran' => (int) ($r['ukuran'] ?? 0),
                'id_notulensi' => $r['id_notulensi'] ?? null,
                'dibuat' => $r['dibuat'] ?? null,
            ], (array) $res->json());
        } catch (\Throwable $e) {
            $this->rekamanList = [];
        }
    }

    public function mulaiRekaman(): void
    {
        $this->validate([
            'rekamanFile' => 'required|file|max:1024000',
            'rekamanRapatId' => 'required|integer|min:1',
        ], [
            'rekamanFile.max' => 'Berkas terlalu besar. Maksimal 1 GB — untuk rapat panjang unggah audio (M4A/MP3) saja, bukan video.',
        ], ['rekamanFile' => 'berkas rekaman', 'rekamanRapatId' => 'rapat']);

        $this->reset(['rekamanTranskrip', 'rekamanNotulen', 'rekamanError', 'rekamanStatus', 'rekamanProgres', 'rekamanAktifId']);

        $this->rekamanNama = $this->rekamanFile->getClientOriginalName();

        try {
            $res = Http::timeout(900)
                ->attach(
                    'berkas',
                    file_get_contents($this->rekamanFile->getRealPath()),
                    $this->rekamanNama,
                    ['Content-Type' => $this->rekamanFile->getMimeType() ?: 'application/octet-stream']
                )
                ->post($this->getBackendUrl() . "/rapat/{$this->rekamanRapatId}/rekaman", ['id_user' => 1]);

            if (! $res->successful()) {
                $this->rekamanError = $res->json('message') ?? ('HTTP ' . $res->status());
                $this->flashMessage = 'Gagal mengunggah rekaman: ' . $this->rekamanError;

                return;
            }

            $this->rekamanAktifId = (int) $res->json('id_rekaman');
            $this->rekamanStatus = 'unggah';
            $this->rekamanProgres = 2;
            $this->rekamanFile = null;
            $this->flashMessage = 'Rekaman terunggah. Transkripsi dan penyusunan notulensi berjalan otomatis — halaman ini akan memperbarui sendiri.';

            $this->pollRekaman();
            $this->muatRekamanList();
        } catch (\Throwable $e) {
            $this->rekamanError = $e->getMessage();
            $this->flashMessage = 'Koneksi API Backend gagal: ' . $e->getMessage();
        }
    }

    /** Dipanggil wire:poll selama proses berjalan. */
    public function pollRekaman(): void
    {
        if (! $this->rekamanAktifId) {
            return;
        }

        $idRapat = $this->rekamanRapatId ?: $this->notulensiRapatId;

        try {
            $res = Http::timeout(15)->get($this->getBackendUrl() . "/rapat/{$idRapat}/rekaman/{$this->rekamanAktifId}");

            if (! $res->successful()) {
                return;
            }

            $d = $res->json();

            $this->rekamanStatus = (string) ($d['status'] ?? '');
            $this->rekamanProgres = (int) ($d['progres'] ?? 0);
            $this->rekamanDurasi = (int) ($d['durasi'] ?? 0);
            $this->rekamanError = (string) ($d['error'] ?? '');

            if (! empty($d['transkrip'])) {
                $this->rekamanTranskrip = (string) $d['transkrip'];
            }

            if (! empty($d['notulen'])) {
                $this->rekamanNotulen = (string) $d['notulen'];
            }

            if ($this->rekamanStatus === 'selesai') {
                $this->flashMessage = 'Notulensi otomatis selesai. Periksa dan sunting sebelum disahkan.';
                $this->loadNotulensiData();
                $this->muatRekamanList();
            }

            if ($this->rekamanStatus === 'gagal') {
                $this->flashMessage = 'Pemrosesan gagal: ' . $this->rekamanError;
                $this->muatRekamanList();
            }
        } catch (\Throwable $e) {
            // biarkan polling berikutnya mencoba lagi
        }
    }

    /** Buka hasil otomatis di formulir notulensi untuk disunting petugas. */
    public function suntingHasilRekaman(): void
    {
        if (trim($this->rekamanNotulen) === '') {
            $this->flashMessage = 'Belum ada hasil notulensi untuk disunting.';

            return;
        }

        $this->notulensiIsi = $this->rekamanNotulen;
        $this->notulensiRapatId = $this->rekamanRapatId ?: $this->notulensiRapatId;
        $this->notulensiStatus = 'draf';
        $this->notulensiFile = '';
        $this->selectedNotulensiId = null;
        $this->isFormOpen = true;

        $this->flashMessage = 'Draf hasil otomatis dimuat ke formulir. Periksa nama, angka, dan keputusan sebelum menyimpan.';
    }

    public function hapusRekaman(int $id): void
    {
        $idRapat = $this->rekamanRapatId ?: $this->notulensiRapatId;

        try {
            $res = Http::timeout(10)->delete($this->getBackendUrl() . "/rapat/{$idRapat}/rekaman/{$id}");

            if ($res->successful() || $res->status() === 204) {
                $this->flashMessage = 'Rekaman #' . $id . ' dihapus.';
            } else {
                $this->flashMessage = 'Gagal menghapus rekaman: HTTP ' . $res->status();
            }

            if ($this->rekamanAktifId === $id) {
                $this->reset(['rekamanAktifId', 'rekamanStatus', 'rekamanProgres', 'rekamanTranskrip', 'rekamanNotulen', 'rekamanError']);
            }

            $this->muatRekamanList();
        } catch (\Throwable $e) {
            $this->flashMessage = 'Koneksi API Backend gagal: ' . $e->getMessage();
        }
    }

    public function viewNotulensi(?int $id): void
    {
        $this->viewingNotulensiId = $id;
    }

    #[Computed]
    public function filteredNotulensi(): array
    {
        return collect($this->notulensiList)
            ->filter(function ($item) {
                if ($this->notulensiFilterStatus !== 'all') {
                    if (strtolower($item['status'] ?? 'draf') !== strtolower($this->notulensiFilterStatus)) {
                        return false;
                    }
                }
                if (! empty($this->notulensiSearch)) {
                    $search = strtolower($this->notulensiSearch);
                    $agenda = strtolower($item['rapat']['nama_agenda'] ?? '');
                    $isi = strtolower($item['isi_notulensi'] ?? '');
                    $tempat = strtolower($item['rapat']['tempat'] ?? '');
                    $notulis = strtolower($item['user']['nama'] ?? '');
                    if (! str_contains($agenda, $search) && ! str_contains($isi, $search) && ! str_contains($tempat, $search) && ! str_contains($notulis, $search)) {
                        return false;
                    }
                }
                return true;
            })
            ->values()
            ->all();
    }




}
