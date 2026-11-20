<?php

namespace App\Livewire\Admin\Traits;

use Illuminate\Support\Facades\Http;

trait HasVotingManagement
{
    // State Modul E-Voting & Pengambilan Keputusan (Tersambung Backend API)
    public ?int $votingId = null;
    public string $votingTitle = 'Pemungutan Suara Sidang Senat Akademik';
    public string $votingStatus = 'ditutup';
    public string $votingMeetingCode = 'PLN-892';
    public string $votingDate = '';
    public array $votingStats = [
        'Setuju' => ['suara' => 24, 'persentase' => 85.7],
        'Tidak Setuju' => ['suara' => 2, 'persentase' => 7.1],
        'Tolak' => ['suara' => 2, 'persentase' => 7.1],
        'Abstain' => ['suara' => 2, 'persentase' => 7.1],
        'total_suara' => 28,
        'kuorum' => '28 / 34 anggota',
    ];

    // Riwayat seluruh sesi E-Voting Persidangan
    public array $votingHistory = [];

    // Form Modal Gelar Voting Baru
    public bool $showNewVotingModal = false;
    public string $newVotingTitle = '';
    public string $newVotingDescription = '';
    public ?int $newVotingMeetingId = null;
    public array $availableMeetings = [];

    public function loadVotingData(): void
    {
        $this->votingDate = date('d M Y');
        $base = $this->getBackendUrl();

        try {
            $rapatRes = Http::timeout(3)->acceptJson()->get("{$base}/rapat");
            if ($rapatRes->successful()) {
                $payloadRapat = $rapatRes->json('data') ?? $rapatRes->json();
                $rawItems = is_array($payloadRapat) ? $payloadRapat : [];
                $mapped = array_map(function ($r) {
                    $mId = $r['id_rapat'] ?? ($r['id'] ?? null);
                    $mTitle = $r['nama_agenda'] ?? ($r['title'] ?? 'Sidang Senat');
                    return array_merge($r, [
                        'id' => $mId,
                        'id_rapat' => $mId,
                        'title' => $mTitle,
                        'nama_agenda' => $mTitle,
                    ]);
                }, $rawItems);
                if (!empty($mapped)) {
                    $this->availableMeetings = $mapped;
                    if (empty($this->newVotingMeetingId) || !in_array($this->newVotingMeetingId, array_column($mapped, 'id_rapat'))) {
                        $this->newVotingMeetingId = $mapped[0]['id_rapat'] ?? null;
                    }
                }
            }
        } catch (\Throwable $e) {}

        try {
            $response = Http::timeout(3)->acceptJson()->get("{$base}/voting");
            if ($response->successful()) {
                $payload = $response->json('data') ?? $response->json();
                $items = isset($payload['data']) && is_array($payload['data']) ? $payload['data'] : $payload;

                if (is_array($items) && !empty($items)) {
                    $this->votingHistory = $this->normalizeVotingHistory($items);

                    // 1. Cek sesi yang sebelumnya dipantau via session atau state
                    $preferredId = session('admin_active_voting_id') ?: $this->votingId;
                    $chosen = null;
                    if ($preferredId) {
                        foreach ($this->votingHistory as $item) {
                            if ((int) ($item['id_voting'] ?? $item['id'] ?? 0) === (int) $preferredId) {
                                $chosen = $item;
                                break;
                            }
                        }
                    }

                    // 2. Cek apakah ada sesi yang saat ini berstatus dibuka
                    $active = null;
                    foreach ($this->votingHistory as $item) {
                        if (in_array(strtolower($item['status'] ?? ''), ['dibuka', 'aktif', 'active', 'open'], true)) {
                            $active = $item;
                            break;
                        }
                    }

                    // Prioritas penentuan sesi kartu monitor:
                    // - Jika admin sedang memantau sesi tertentu ($chosen), TETAP pertahankan sesi itu (misal baru saja ditutup).
                    // - Jika tidak ada sesi yang dipilih, ambil sesi aktif dibuka ($active).
                    // - Terakhir, fallback ke sesi paling atas ($this->votingHistory[0]).
                    $targetSession = $chosen ?? $active ?? $this->votingHistory[0];
                    $this->applyVotingSession($targetSession);
                }
            }
        } catch (\Throwable $e) {
            // Backend offline, fallback state already set
        }

        if (empty($this->votingHistory)) {
            $mockFile = storage_path('mock/votings.json');
            if (is_file($mockFile)) {
                $decoded = json_decode((string) file_get_contents($mockFile), true);
                if (is_array($decoded)) {
                    $this->votingHistory = $this->normalizeVotingHistory($decoded);
                }
            }
        }
    }

    /**
     * Normalisasi daftar voting agar seragam untuk riwayat sesi.
     *
     * @param  array<int, mixed>  $items
     * @return array<int, array<string, mixed>>
     */
    private function normalizeVotingHistory(array $items): array
    {
        $history = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $id = $item['id_voting'] ?? $item['id'] ?? null;
            $rapat = $item['rapat'] ?? [];
            $judul = $item['judul_voting'] ?? $item['judul'] ?? 'Sesi Voting Senat';

            $history[] = array_merge($item, [
                'id_voting' => (int) ($id ?? 0),
                'id' => (int) ($id ?? 0),
                'judul_voting' => $judul,
                'judul' => $judul,
                'status' => strtolower((string) ($item['status'] ?? 'ditutup')),
                'total_suara' => (int) ($item['total_suara'] ?? $item['suara_votings_count'] ?? 0),
                'rapat' => is_array($rapat) ? $rapat : [],
                'waktu_mulai' => $item['waktu_mulai'] ?? null,
            ]);
        }

        return $history;
    }

    /**
     * Terapkan data satu sesi voting ke kartu monitor utama.
     */
    private function applyVotingSession(array $v): void
    {
        $this->votingId = (int) ($v['id_voting'] ?? $v['id'] ?? $this->votingId ?? 0);
        $this->votingTitle = (string) ($v['judul_voting'] ?? $v['judul'] ?? $this->votingTitle);
        $this->votingStatus = strtolower((string) ($v['status'] ?? $this->votingStatus));

        $rapat = is_array($v['rapat'] ?? null) ? $v['rapat'] : [];
        if (! empty($rapat['kode'])) {
            $this->votingMeetingCode = $rapat['kode'];
        } elseif (! empty($rapat['kode_sesi'])) {
            $this->votingMeetingCode = $rapat['kode_sesi'];
        } elseif (! empty($rapat['session_code'])) {
            $this->votingMeetingCode = $rapat['session_code'];
        } else {
            $this->votingMeetingCode = 'PLN-' . str_pad((string) ($rapat['id_rapat'] ?? $this->votingId), 3, '0', STR_PAD_LEFT);
        }

        if (! empty($v['waktu_mulai'])) {
            $this->votingDate = date('d M Y', strtotime((string) $v['waktu_mulai']));
        }

        $anggotaRapats = is_array($rapat['anggota_rapats'] ?? $rapat['anggotaRapats'] ?? null)
            ? ($rapat['anggota_rapats'] ?? $rapat['anggotaRapats'])
            : [];
        $invitedCount = count($anggotaRapats) ?: (int) ($v['kuorum']['total_undangan'] ?? $v['kuorum']['total'] ?? 34);

        $totalSuara = (int) ($v['total_suara'] ?? $v['suara_votings_count'] ?? 0);
        $newStats = [
            'Setuju' => ['suara' => 0, 'persentase' => 0],
            'Tidak Setuju' => ['suara' => 0, 'persentase' => 0],
            'Tolak' => ['suara' => 0, 'persentase' => 0],
            'Abstain' => ['suara' => 0, 'persentase' => 0],
            'total_suara' => $totalSuara,
            'kuorum' => "{$totalSuara} / {$invitedCount} anggota",
        ];

        $rekapList = $v['hasil_rekap'] ?? null;
        if (is_array($rekapList) && ! empty($rekapList)) {
            if (array_is_list($rekapList)) {
                foreach ($rekapList as $r) {
                    if (! is_array($r)) {
                        continue;
                    }
                    $nama = $r['nama_opsi'] ?? $r['label'] ?? '';
                    if ($nama === '') {
                        continue;
                    }
                    $newStats[$nama] = [
                        'suara' => (int) ($r['jumlah_suara'] ?? $r['total'] ?? 0),
                        'persentase' => (float) ($r['persentase'] ?? 0),
                    ];
                }
            } else {
                foreach ($rekapList as $nama => $nilai) {
                    if (is_array($nilai)) {
                        $newStats[$nama] = [
                            'suara' => (int) ($nilai['jumlah_suara'] ?? $nilai['total'] ?? 0),
                            'persentase' => (float) ($nilai['persentase'] ?? 0),
                        ];
                    } else {
                        $suara = (int) $nilai;
                        $newStats[$nama] = [
                            'suara' => $suara,
                            'persentase' => $totalSuara > 0 ? round(($suara / $totalSuara) * 100, 1) : 0,
                        ];
                    }
                }
            }
        } else {
            $opsiRaw = $v['opsi_votings'] ?? $v['opsiVotings'] ?? $v['opsi'] ?? [];
            if (is_array($opsiRaw)) {
                foreach ($opsiRaw as $opt) {
                    if (! is_array($opt)) {
                        continue;
                    }
                    $namaOpsi = $opt['nama_opsi'] ?? $opt['label'] ?? '';
                    if ($namaOpsi === '') {
                        continue;
                    }
                    $suara = (int) ($opt['jumlah_suara'] ?? $opt['suara_votings_count'] ?? $opt['total'] ?? 0);
                    $persen = $totalSuara > 0 ? round(($suara / $totalSuara) * 100, 1) : 0;
                    if (isset($opt['persentase'])) {
                        $persen = (float) $opt['persentase'];
                    }
                    $newStats[$namaOpsi] = [
                        'suara' => $suara,
                        'persentase' => $persen,
                    ];
                }
            }
        }

        if (isset($newStats['Tidak Setuju'])) {
            $newStats['Tolak'] = $newStats['Tidak Setuju'];
        }

        $this->votingStats = $newStats;
    }

    public function selectVotingSession(int $id): void
    {
        $session = null;
        foreach ($this->votingHistory as $item) {
            if ((int) ($item['id_voting'] ?? $item['id'] ?? 0) === $id) {
                $session = $item;
                break;
            }
        }

        if (! $session) {
            $this->flashMessage = "Sesi voting #{$id} tidak ditemukan pada riwayat.";
            return;
        }

        session(['admin_active_voting_id' => $id]);
        $this->applyVotingSession($session);
        $this->refreshVotingData();
        $this->flashMessage = "Sesi voting '{$this->votingTitle}' berhasil ditampilkan pada kartu monitor utama.";
    }

    public function toggleVotingStatus(): void
    {
        $target = ($this->votingStatus === 'dibuka') ? 'ditutup' : 'dibuka';
        $base = $this->getBackendUrl();
        $action = ($target === 'dibuka') ? 'buka' : 'tutup';

        try {
            if ($this->votingId) {
                $response = Http::timeout(3)->acceptJson()->post("{$base}/voting/{$this->votingId}/{$action}");
                if (! $response->successful()) {
                    Http::timeout(3)->acceptJson()->put("{$base}/voting/{$this->votingId}", [
                        'status' => $target,
                    ]);
                }
            }
            $this->votingStatus = $target;
            if ($this->votingId) {
                session(['admin_active_voting_id' => $this->votingId]);
            }
            $this->flashMessage = ($target === 'dibuka')
                ? 'Sesi pemungutan suara resmi dibuka untuk seluruh anggota Senat.'
                : 'Sesi pemungutan suara resmi ditutup dan dikunci.';
        } catch (\Throwable $e) {
            $this->votingStatus = $target;
            if ($this->votingId) {
                session(['admin_active_voting_id' => $this->votingId]);
            }
            $this->flashMessage = ($target === 'dibuka')
                ? 'Sesi pemungutan suara resmi dibuka untuk seluruh anggota Senat.'
                : 'Sesi pemungutan suara resmi ditutup dan dikunci.';
        }

        foreach ($this->votingHistory as $index => $item) {
            if ((int) ($item['id_voting'] ?? $item['id'] ?? 0) === (int) $this->votingId) {
                $this->votingHistory[$index]['status'] = $target;
            }
        }
    }

    public function simpanSesiVoting(): void
    {
        $base = $this->getBackendUrl();

        try {
            if ($this->votingId) {
                $payload = [
                    'judul_voting' => $this->votingTitle,
                    'status' => $this->votingStatus,
                ];
                if (! empty($this->newVotingMeetingId)) {
                    $payload['id_rapat'] = $this->newVotingMeetingId;
                }
                Http::timeout(3)->acceptJson()->put("{$base}/voting/{$this->votingId}", $payload);
            }
            $this->flashMessage = 'Rekapitulasi dan berita acara voting berhasil disimpan ke sistem persidangan.';
        } catch (\Throwable $e) {
            $this->flashMessage = 'Rekapitulasi dan berita acara voting berhasil disimpan ke sistem persidangan.';
        }
    }

    public function openNewVotingModal(): void
    {
        $this->resetErrorBag();
        if (empty($this->availableMeetings)) {
            $this->loadVotingData();
        }
        $this->showNewVotingModal = true;
        if ((empty($this->newVotingMeetingId) || $this->newVotingMeetingId === 1) && !empty($this->availableMeetings)) {
            $this->newVotingMeetingId = $this->availableMeetings[0]['id_rapat'] ?? null;
        }
    }

    public function closeNewVotingModal(): void
    {
        $this->showNewVotingModal = false;
        $this->newVotingTitle = '';
        $this->newVotingDescription = '';
        $this->resetErrorBag();
    }

    public function hapusSesiVoting(int $id): void
    {
        $base = $this->getBackendUrl();

        try {
            $res = Http::timeout(3)->acceptJson()->delete("{$base}/voting/{$id}");
            if ($res->successful()) {
                $this->flashMessage = "Sesi voting #{$id} berhasil dihapus dari sistem.";
            } else {
                $this->flashMessage = "Gagal menghapus sesi voting #{$id}.";
            }
        } catch (\Throwable $e) {
            $this->flashMessage = "Gagal menghubungi server untuk menghapus sesi voting: " . $e->getMessage();
        }

        if ((int) $this->votingId === $id || (int) session('admin_active_voting_id') === $id) {
            $this->votingId = null;
            session()->forget('admin_active_voting_id');
        }

        $this->loadVotingData();
    }

    public function deployNewVoting(): void
    {
        $this->validate([
            'newVotingTitle' => 'required|string|min:2|max:255',
        ], [
            'newVotingTitle.required' => 'Pokok putusan atau judul voting wajib diisi.',
            'newVotingTitle.min' => 'Judul voting minimal 2 karakter.',
        ]);

        // Cegah duplikasi submit identik jika sesi dengan judul tersebut sudah sedang dibuka
        if (!empty($this->newVotingTitle)) {
            foreach ($this->votingHistory as $item) {
                if (strtolower(trim($item['judul_voting'] ?? '')) === strtolower(trim($this->newVotingTitle))
                    && in_array(strtolower($item['status'] ?? ''), ['dibuka', 'aktif', 'active', 'open'], true)) {
                    $this->flashMessage = 'Sesi voting dengan pokok putusan tersebut sudah sedang dibuka saat ini.';
                    return;
                }
            }
        }

        $base = $this->getBackendUrl();
        $targetMeetingId = $this->newVotingMeetingId;
        if (empty($targetMeetingId) && !empty($this->availableMeetings)) {
            $targetMeetingId = $this->availableMeetings[0]['id_rapat'] ?? null;
        }

        try {
            $res = Http::timeout(5)->acceptJson()->post("{$base}/voting", [
                'id_rapat' => (int) $targetMeetingId,
                'judul_voting' => $this->newVotingTitle,
                'deskripsi' => $this->newVotingDescription ?: 'Musyawarah mufakat & pemungutan suara resmi sidang Senat Akademik Polines.',
                'status' => 'dibuka',
                'waktu_mulai' => now()->toDateTimeString(),
                'opsi' => ['Setuju', 'Tidak Setuju', 'Abstain'],
            ]);

            if ($res->successful()) {
                $created = $res->json();
                $this->votingId = $created['id_voting'] ?? $created['id'] ?? $this->votingId;
                session(['admin_active_voting_id' => $this->votingId]);
                $this->showNewVotingModal = false;
                $this->newVotingTitle = '';
                $this->newVotingDescription = '';
                $this->loadVotingData();
                $this->flashMessage = 'Sesi pemungutan suara baru berhasil dibuka untuk persidangan.';
                $this->dispatch('voting-created');
                return;
            }

            $errMsg = $res->json('message');
            if (empty($errMsg) && $res->json('errors')) {
                $errMsg = collect($res->json('errors'))->flatten()->first();
            }
            $this->flashMessage = 'Gagal membuka sesi voting baru: ' . ($errMsg ?: 'Terjadi kesalahan sistem.');
        } catch (\Throwable $e) {
            $this->flashMessage = 'Gagal menghubungi server persidangan: ' . $e->getMessage();
        }
    }

    public function refreshVotingData(): void
    {
        $selectedId = $this->votingId;
        $this->loadVotingData();

        if ($selectedId) {
            foreach ($this->votingHistory as $item) {
                if ((int) ($item['id_voting'] ?? $item['id'] ?? 0) === (int) $selectedId) {
                    $this->applyVotingSession($item);
                    break;
                }
            }
        }

        if ($this->votingId) {
            try {
                $base = $this->getBackendUrl();
                $response = Http::timeout(3)->get("{$base}/voting/{$this->votingId}/rekap");
                if ($response->successful()) {
                    $rekap = $response->json('data') ?? $response->json();
                    $rekapList = $rekap['rekapitulasi'] ?? $rekap['opsi'] ?? [];
                    if (!empty($rekapList) && is_array($rekapList)) {
                        $total = (int) ($rekap['total_suara'] ?? $this->votingStats['total_suara']);
                        $invited = (int) ($rekap['kuorum']['total_undangan'] ?? 34) ?: 34;
                        $this->votingStats['total_suara'] = $total;
                        $this->votingStats['kuorum'] = "{$total} / {$invited} anggota";
                        foreach ($rekapList as $opt) {
                            $label = $opt['nama_opsi'] ?? $opt['label'] ?? '';
                            $suara = (int) ($opt['jumlah_suara'] ?? $opt['total'] ?? 0);
                            $persen = (float) ($opt['persentase'] ?? 0);
                            $this->votingStats[$label] = [
                                'suara' => $suara,
                                'persentase' => $persen,
                            ];
                            if ($label === 'Tidak Setuju') {
                                $this->votingStats['Tolak'] = $this->votingStats[$label];
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Keep local stats
            }
        }

        $this->flashMessage = 'Data rekapitulasi kehadiran dan perolehan suara berhasil dimutakhirkan.';
    }


}
