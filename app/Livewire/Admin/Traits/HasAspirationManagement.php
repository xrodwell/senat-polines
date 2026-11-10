<?php

namespace App\Livewire\Admin\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;

trait HasAspirationManagement
{
    // State untuk Modul Pusat Aspirasi & Tindak Lanjut (Tersambung ke API)
    public array $apiAspirations = [];
    public ?int $selectedAspirasiId = null;
    public string $tanggapanText = '';
    public string $tanggapanStatusChoice = 'diproses';
    public ?int $editingDisposisiId = null;

    // Filter & Search State
    public string $aspirationFilterKomisi = 'all';
    public string $aspirationFilterStatus = 'all';
    public string $aspirationSearch = '';

    /**
     * Ambil data tiket aspirasi asli dari Backend API dan transform secara detail
     */
    public function loadAspirationsFromApi(): void
    {
        try {
            $base = $this->getBackendUrl();
            $response = Http::timeout(3)->get("{$base}/aspirasi", ['all' => 1]);
            if ($response->successful()) {
                $data = $response->json();
                $items = is_array($data) ? ($data['data'] ?? $data) : [];

                $this->apiAspirations = collect($items)->map(function ($item) {
                    $rawText = $item['isi_aspirasi'] ?? '';
                    $subject = '';
                    $description = $rawText;
                    $senderName = '';
                    $senderEmail = '';
                    $jurusan = '';

                    // 1. Ekstrak judul [Subject] jika diformat dalam kurung siku
                    if (preg_match('/^\[(.*?)\]\s*(.*)$/s', $rawText, $matches)) {
                        $subject = trim($matches[1]);
                        $description = trim($matches[2]);
                    } else {
                        $subject = Str::limit($rawText, 55);
                    }

                    // 2. Ekstrak identitas pengirim asli: Pengirim: {Nama} ({Email}) - Jurusan: {Jurusan}
                    if (preg_match('/Pengirim:\s*(.*?)\s*\((.*?)\)\s*-\s*Jurusan:\s*(.*?)$/is', $description, $pMatches)) {
                        $senderName = trim($pMatches[1]);
                        $senderEmail = trim($pMatches[2]);
                        $jurusan = trim($pMatches[3]);
                        // Bersihkan footer pengirim dari teks pesan keluhan
                        $description = trim(preg_replace('/Pengirim:\s*(.*?)\s*\((.*?)\)\s*-\s*Jurusan:\s*(.*?)$/is', '', $description));
                    }

                    // Fallback nama & jurusan
                    $isAnonim = !empty($item['anonim']);
                    if (empty($senderName)) {
                        $senderName = $isAnonim ? 'Mahasiswa (Anonim)' : ($item['user']['nama'] ?? 'Civitas Akademika');
                    }
                    if ($isAnonim) {
                        $senderName = 'Mahasiswa (Anonim)';
                        $senderEmail = '-';
                    }
                    if (empty($jurusan)) {
                        $jurusan = $item['kategori']['nama_kategori'] ?? 'Umum';
                    }

                    // 3. Disposisi Komisi berdasarkan id_kategori / nama komisi
                    $idKategori = (int) ($item['id_kategori'] ?? 1);
                    $komisiInfo = $this->resolveKomisiInfo($idKategori);

                    // 4. Tanggapan komisi terakhir
                    $tanggapans = $item['tanggapans'] ?? [];
                    $lastTanggapan = !empty($tanggapans) ? (is_array(end($tanggapans)) ? end($tanggapans)['pesan'] ?? '' : '') : '';
                    if (empty($lastTanggapan)) {
                        $lastTanggapan = 'Belum ada tanggapan resmi dari komisi.';
                    }

                    $idAspirasi = $item['id_aspirasi'] ?? 1;
                    $ticketCode = 'ASP-POLINES-2026-' . str_pad((string) $idAspirasi, 4, '0', STR_PAD_LEFT);

                    return [
                        'id' => $idAspirasi,
                        'ticket_code' => $ticketCode,
                        'subject' => $subject,
                        'description' => $description,
                        'raw_text' => $rawText,
                        'sender_name' => $senderName,
                        'sender_email' => $senderEmail,
                        'jurusan' => $jurusan,
                        'is_anonim' => $isAnonim,
                        'status' => ucfirst($item['status'] ?? 'pending'),
                        'raw_status' => strtolower($item['status'] ?? 'pending'),
                        'response' => $lastTanggapan,
                        'submitted_at' => substr($item['tanggal_dibuat'] ?? $item['created_at'] ?? date('Y-m-d H:i'), 0, 16),
                        'id_kategori' => $idKategori,
                        'committee_id' => $komisiInfo['id'],
                        'committee_code' => $komisiInfo['code'],
                        'committee_name' => $komisiInfo['name'],
                        'committee_badge' => $komisiInfo['badge_class'],
                    ];
                })->toArray();
            }
        } catch (\Throwable $e) {}
    }

    /**
     * Resolusi metadata komisi dari id_kategori
     */
    private function resolveKomisiInfo(int $idKategori): array
    {
        return match ($idKategori) {
            1 => [
                'id' => 1,
                'code' => 'Komisi I',
                'name' => 'Komisi I — Bidang Pendidikan & Pengajaran',
                'badge_class' => 'bg-blue-50 text-blue-700 border-blue-200 ring-1 ring-blue-300',
            ],
            2 => [
                'id' => 2,
                'code' => 'Komisi II',
                'name' => 'Komisi II — Bidang Penelitian & Pengabdian Masyarakat',
                'badge_class' => 'bg-emerald-50 text-emerald-700 border-emerald-200 ring-1 ring-emerald-300',
            ],
            3 => [
                'id' => 3,
                'code' => 'Komisi III',
                'name' => 'Komisi III — Bidang Kelembagaan & SDM',
                'badge_class' => 'bg-purple-50 text-purple-700 border-purple-200 ring-1 ring-purple-300',
            ],
            4 => [
                'id' => 4,
                'code' => 'Komisi IV',
                'name' => 'Komisi IV — Bidang Pengembangan & Kerja Sama',
                'badge_class' => 'bg-amber-50 text-amber-700 border-amber-200 ring-1 ring-amber-300',
            ],
            5 => [
                'id' => 5,
                'code' => 'Komisi V',
                'name' => 'Komisi V — Komisi Etik',
                'badge_class' => 'bg-rose-50 text-rose-700 border-rose-200 ring-1 ring-rose-300',
            ],
            default => [
                'id' => 1,
                'code' => 'Komisi I',
                'name' => 'Komisi I — Bidang Pendidikan & Pengajaran',
                'badge_class' => 'bg-blue-50 text-blue-700 border-blue-200 ring-1 ring-blue-300',
            ],
        };
    }

    /**
     * Filter daftar aspirasi secara reaktif berdasarkan Komisi, Status, dan Pencarian
     */
    #[Computed]
    public function filteredAspirations(): array
    {
        $list = !empty($this->apiAspirations) ? $this->apiAspirations : ($this->aspirations ?? []);

        return array_values(array_filter($list, function ($asp) {
            // 1. Filter Komisi
            if ($this->aspirationFilterKomisi !== 'all') {
                $targetKomisi = (int) $this->aspirationFilterKomisi;
                $itemKomisi = (int) ($asp['committee_id'] ?? $asp['id_kategori'] ?? 1);
                if ($itemKomisi !== $targetKomisi) {
                    return false;
                }
            }

            // 2. Filter Status
            if ($this->aspirationFilterStatus !== 'all') {
                $rawStatus = strtolower($asp['raw_status'] ?? $asp['status'] ?? 'pending');
                if ($rawStatus !== strtolower($this->aspirationFilterStatus)) {
                    return false;
                }
            }

            // 3. Pencarian
            if (!empty(trim($this->aspirationSearch))) {
                $query = strtolower(trim($this->aspirationSearch));
                $searchable = strtolower(
                    ($asp['ticket_code'] ?? '') . ' ' .
                    ($asp['subject'] ?? '') . ' ' .
                    ($asp['description'] ?? '') . ' ' .
                    ($asp['sender_name'] ?? '') . ' ' .
                    ($asp['jurusan'] ?? '') . ' ' .
                    ($asp['committee_name'] ?? '')
                );
                if (!str_contains($searchable, $query)) {
                    return false;
                }
            }

            return true;
        }));
    }

    /**
     * Hitungan total tiket per status dan per komisi untuk badge navigasi
     */
    #[Computed]
    public function aspirationStats(): array
    {
        $list = !empty($this->apiAspirations) ? $this->apiAspirations : ($this->aspirations ?? []);
        $stats = [
            'total' => count($list),
            'pending' => 0,
            'diproses' => 0,
            'selesai' => 0,
            'komisi_1' => 0,
            'komisi_2' => 0,
            'komisi_3' => 0,
            'komisi_4' => 0,
            'komisi_5' => 0,
        ];

        foreach ($list as $asp) {
            $st = strtolower($asp['raw_status'] ?? 'pending');
            if (isset($stats[$st])) {
                $stats[$st]++;
            }
            $cId = (int) ($asp['committee_id'] ?? $asp['id_kategori'] ?? 1);
            if ($cId >= 1 && $cId <= 5) {
                $stats['komisi_' . $cId]++;
            }
        }

        return $stats;
    }

    /**
     * Ubah filter komisi
     */
    public function setFilterKomisi(string $komisi): void
    {
        $this->aspirationFilterKomisi = $komisi;
    }

    /**
     * Ubah filter status
     */
    public function setFilterStatus(string $status): void
    {
        $this->aspirationFilterStatus = $status;
    }

    /**
     * Ubah status aspirasi (Pending / Diproses / Selesai)
     */
    public function setAspirasiStatus(int $id, string $status): void
    {
        // 1. Ubah tampilan di layar secara instan (0 lag)
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
            $this->flashMessage = "Status tiket aspirasi #{$id} diubah menjadi: " . ucfirst($status);
        } catch (\Throwable $e) {
            $this->flashMessage = "Gagal memperbarui status: " . $e->getMessage();
        }
    }

    /**
     * Mutasi/Ubah Disposisi Komisi Tujuan
     */
    public function setAspirasiDisposisi(int $id, int $idKategori): void
    {
        $komisiInfo = $this->resolveKomisiInfo($idKategori);

        // 1. Update di array lokal instan
        foreach ($this->apiAspirations as $index => $item) {
            if ($item['id'] === $id) {
                $this->apiAspirations[$index]['id_kategori'] = $idKategori;
                $this->apiAspirations[$index]['committee_id'] = $komisiInfo['id'];
                $this->apiAspirations[$index]['committee_code'] = $komisiInfo['code'];
                $this->apiAspirations[$index]['committee_name'] = $komisiInfo['name'];
                $this->apiAspirations[$index]['committee_badge'] = $komisiInfo['badge_class'];
                break;
            }
        }

        $this->editingDisposisiId = null;

        // 2. Simpan ke Backend API
        try {
            $base = $this->getBackendUrl();
            Http::put("{$base}/aspirasi/{$id}", [
                'id_kategori' => $idKategori,
            ]);
            $this->flashMessage = "Disposisi tiket #{$id} berhasil dialihkan ke: " . $komisiInfo['code'];
        } catch (\Throwable $e) {
            $this->flashMessage = "Gagal mengalihkan disposisi: " . $e->getMessage();
        }
    }

    public function toggleDisposisiDropdown(int $id): void
    {
        $this->editingDisposisiId = ($this->editingDisposisiId === $id) ? null : $id;
    }

    /**
     * Buka form untuk mengetik tanggapan
     */
    public function openTanggapanForm(int $id): void
    {
        $this->selectedAspirasiId = ($this->selectedAspirasiId === $id) ? null : $id;
        $this->tanggapanText = '';
        $this->tanggapanStatusChoice = 'diproses';
    }

    /**
     * Template balasan cepat
     */
    public function setQuickTemplate(string $template): void
    {
        $this->tanggapanText = $template;
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

        $newStatus = $this->tanggapanStatusChoice ?: 'diproses';

        // 1. Update tampilan kotak tanggapan di layar secara instan
        foreach ($this->apiAspirations as $index => $item) {
            if ($item['id'] == $id) {
                $this->apiAspirations[$index]['response'] = $pesan;
                $this->apiAspirations[$index]['status'] = ucfirst($newStatus);
                $this->apiAspirations[$index]['raw_status'] = strtolower($newStatus);
                break;
            }
        }
        $this->selectedAspirasiId = null;
        $this->tanggapanText = '';

        // 2. Simpan tanggapan ke tabel tanggapan_aspirasis dan update status di Backend
        try {
            $base = $this->getBackendUrl();
            $respPost = Http::timeout(5)->post("{$base}/tanggapan-aspirasi", [
                'id_aspirasi' => $id,
                'id_user' => 1,
                'pesan' => $pesan,
            ]);
            $respPut = Http::timeout(5)->put("{$base}/aspirasi/{$id}", [
                'status' => $newStatus,
            ]);

            if ($respPost->successful()) {
                $this->flashMessage = "Berhasil! Tanggapan resmi komisi telah tersimpan ke database dan status diperbarui.";
            } else {
                $this->flashMessage = "Tanggapan telah diperbarui di layar!";
            }
        } catch (\Throwable $e) {
            $this->flashMessage = "Tanggapan disimpan di layar (Gagal ke Backend: " . $e->getMessage() . ")";
        }
    }
}

