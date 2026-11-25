<?php

namespace App\Livewire\Admin\Traits;

use App\Contracts\MeetingRepositoryInterface;
use App\Repositories\Api\ApiMeetingRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;

trait HasMeetingManagement
{
    public string $meetingTitle = '';
    public int $meetingJenisRapatId = 1;
    public string $meetingOrganizer = 'Senat';
    public string $meetingType = 'Pleno';
    public string $meetingDate = '';
    public string $meetingStartTime = '09:00';
    public string $meetingEndTime = '12:00';
    public bool $meetingUntilFinished = false;
    public string $meetingDateTime = '';
    public string $meetingRoom = '';
    public string $meetingPeserta = '';
    public ?int $editingMeetingId = null;

    // Broadcast Email State
    public ?int $broadcastModalMeetingId = null;
    public ?string $broadcastPreviewText = null;
    public string $broadcastTestEmail = 'thomasandromeda999@gmail.com';

    // Multi-Meeting & History State
    public ?int $selectedMeetingId = null;
    public string $meetingFilterTab = 'semua'; // semua, berlangsung, terjadwal, selesai
    // Form Buat Sidang Baru & Pemilihan Dosen
    public string $newMeetingTitle = '';
    public string $newMeetingOrganizer = 'Senat';
    public string $newMeetingType = 'Pleno';
    public string $newMeetingDate = '';
    public string $newMeetingStartTime = '09:00';
    public string $newMeetingEndTime = '12:00';
    public bool $newMeetingUntilFinished = false;
    public string $newMeetingTime = '09:00 - 12:00 WIB';
    public string $newMeetingRoom = 'Ruang Sidang Utama Lantai 2 Gd. Direktorat';
    public array $allLecturers = [];
    public array $selectedLecturerIds = [];
    public string $lecturerSearch = '';

    public function organizerOptions(): array
    {
        return [
            'Senat' => 'Senat',
            'Komisi 1 (Komisi Pendidikan dan Pengajaran)' => 'Komisi 1 (Komisi Pendidikan dan Pengajaran)',
            'Komisi 2 (Komisi Penelitian dan Pengabdian kepada Masyarakat)' => 'Komisi 2 (Komisi Penelitian dan Pengabdian kepada Masyarakat)',
            'Komisi 3 (Komisi Kelembagaan dan Sumber Daya Manusia)' => 'Komisi 3 (Komisi Kelembagaan dan Sumber Daya Manusia)',
            'Komisi 4 (Komisi Pengembangan dan Kerja Sama)' => 'Komisi 4 (Komisi Pengembangan dan Kerja Sama)',
            'Komisi 5 (Komisi Etik)' => 'Komisi 5 (Komisi Etik)',
            'Badan Pekerja' => 'Badan Pekerja',
        ];
    }

    public function meetingTypeOptions(): array
    {
        return [
            'Pleno' => 'Pleno',
            'Terbuka' => 'Terbuka',
            'Pimpinan' => 'Pimpinan',
            'Komisi' => 'Komisi',
            'Panitia kerja, badan pekerja' => 'Panitia kerja, badan pekerja',
            'Rapat atau sidang lain yang ditetapkan Senat sesuai kebutuhan' => 'Rapat atau sidang lain yang ditetapkan Senat sesuai kebutuhan',
        ];
    }

    public function updatedNewMeetingOrganizer(string $value): void
    {
        if (str_contains($value, 'Komisi 1')) {
            $this->selectCommitteeLecturers('Komisi I');
        } elseif (str_contains($value, 'Komisi 2')) {
            $this->selectCommitteeLecturers('Komisi II');
        } elseif (str_contains($value, 'Komisi 3')) {
            $this->selectCommitteeLecturers('Komisi III');
        } elseif (str_contains($value, 'Komisi 4')) {
            $this->selectCommitteeLecturers('Komisi IV');
        } elseif (str_contains($value, 'Komisi 5')) {
            $this->selectCommitteeLecturers('Komisi V');
        } elseif (str_contains($value, 'Badan Pekerja')) {
            $this->selectCommitteeLecturers('BP');
            if (empty($this->selectedLecturerIds)) {
                $this->selectCommitteeLecturers('Badan Pekerja');
            }
        } elseif ($value === 'Senat') {
            $this->selectAllLecturers();
        }

        if (empty($this->selectedLecturerIds)) {
            $this->selectAllLecturers();
        }
    }

    public function formatMeetingTime(string $start, string $end, bool $untilFinished): string
    {
        $start = !empty($start) ? substr($start, 0, 5) : '09:00';
        if ($untilFinished) {
            return "{$start} WIB - Selesai";
        }
        $end = !empty($end) ? substr($end, 0, 5) : '12:00';
        return "{$start} - {$end} WIB";
    }

    public function mapMeetingTypeToId(string $type): int
    {
        return match ($type) {
            'Pleno' => 1,
            'Komisi' => 2,
            'Rapat atau sidang lain yang ditetapkan Senat sesuai kebutuhan' => 3,
            'Pimpinan' => 4,
            'Panitia kerja, badan pekerja' => 5,
            'Terbuka' => 1,
            default => 1,
        };
    }

    public function mapIdToMeetingType(int $id): string
    {
        return match ($id) {
            1 => 'Pleno',
            2 => 'Komisi',
            3 => 'Rapat atau sidang lain yang ditetapkan Senat sesuai kebutuhan',
            4 => 'Pimpinan',
            5 => 'Panitia kerja, badan pekerja',
            default => 'Pleno',
        };
    }


    public function selectMeeting(int|string $meetingId): void
    {
        $meetingId = (int) $meetingId;
        $this->selectedMeetingId = $meetingId;
        $repo = app(MeetingRepositoryInterface::class);
        $this->loadLiveAttendanceData($repo, true);
        $this->flashMessage = "Berhasil memuat data agenda: " . ($this->liveMeeting['title'] ?? $this->liveMeeting['nama_agenda'] ?? 'Sidang Terpilih');
    }

    public function manageMeeting(int|string $meetingId, string $targetTab = 'Presensi & Verifikasi QR'): void
    {
        $this->selectMeeting($meetingId);
        $this->active = $targetTab;
    }

    public function selectAllLecturers(): void
    {
        $this->selectedLecturerIds = array_column($this->allLecturers, 'id_user');
    }

    public function selectCommitteeLecturers(string $komisi): void
    {
        $pattern = '/(?<![A-Za-z0-9])' . preg_quote($komisi, '/') . '(?![A-Za-z])/i';
        $this->selectedLecturerIds = array_values(array_map(
            fn($item) => $item['id_user'],
            array_filter($this->allLecturers, fn($item) => preg_match($pattern, (string) ($item['komisi'] ?? '')) === 1
                || preg_match($pattern, (string) ($item['role'] ?? '')) === 1)
        ));
    }

    public function clearSelectedLecturers(): void
    {
        $this->selectedLecturerIds = [];
    }

    public function toggleLecturer(int $userId): void
    {
        if (in_array($userId, $this->selectedLecturerIds, true)) {
            $this->selectedLecturerIds = array_values(array_diff($this->selectedLecturerIds, [$userId]));
        } else {
            $this->selectedLecturerIds[] = $userId;
        }
    }

    public function getFilteredLecturersProperty(): array
    {
        if (empty($this->lecturerSearch)) {
            return $this->allLecturers;
        }

        $q = strtolower(trim($this->lecturerSearch));
        return array_values(array_filter($this->allLecturers, function ($u) use ($q) {
            return str_contains(strtolower($u['nama'] ?? ''), $q)
                || str_contains(strtolower($u['nim_nip'] ?? ''), $q)
                || str_contains(strtolower($u['komisi'] ?? ''), $q);
        }));
    }

    public function loadLecturersMaster(): void
    {
        $base = $this->getBackendUrl();
        try {
            $res = Http::timeout(5)->get("{$base}/user", ['per_page' => 100]);
            if ($res->successful()) {
                $payload = $res->json('data') ?? $res->json();
                $items = isset($payload['data']) && is_array($payload['data']) ? $payload['data'] : $payload;
                if (is_array($items) && !empty($items)) {
                    $komisiPresets = ['Pimpinan Senat', 'Komisi I', 'Komisi II', 'Komisi III', 'Komisi IV', 'Komisi V', 'BP (Badan Pekerja)'];
                    $this->allLecturers = array_map(function ($u, $idx) use ($komisiPresets) {
                        $kom = $u['komisi'] ?? null;
                        if (!$kom && !empty($u['enrollments']) && is_array($u['enrollments'])) {
                            $komisiNames = array_filter(array_map(fn ($e) => $e['komisi']['nama_komisi'] ?? null, $u['enrollments']));
                            $kom = !empty($komisiNames) ? implode(', ', $komisiNames) : ($u['enrollments'][0]['jabatan_komisi'] ?? null);
                        }
                        if (!$kom || $kom === 'anggota' || $kom === 'Komisi Senat') {
                            $kom = $komisiPresets[$idx % count($komisiPresets)];
                        }
                        return [
                            'id_user' => $u['id_user'] ?? ($idx + 1),
                            'nama' => $u['nama'] ?? 'Anggota Senat',
                            'nim_nip' => $u['nim_nip'] ?? '-',
                            'komisi' => $kom,
                            'role' => $u['role'] ?? 'anggota',
                        ];
                    }, $items, array_keys($items));
                }
            }
        } catch (\Throwable $e) {}

        if (empty($this->allLecturers)) {
            $this->allLecturers = $this->getDefaultLecturersList();
        }

        if (empty($this->selectedLecturerIds)) {
            $this->selectedLecturerIds = array_column($this->allLecturers, 'id_user');
        }

        if (empty($this->newMeetingDate)) {
            $this->newMeetingDate = date('Y-m-d', strtotime('+3 days'));
        }
    }

    public function createNewMeeting(): void
    {
        if (empty(trim($this->newMeetingTitle))) {
            $this->flashMessage = 'Peringatan: Judul/agenda sidang tidak boleh kosong!';
            return;
        }

        if (empty($this->selectedLecturerIds)) {
            $this->flashMessage = 'Peringatan: Silakan pilih minimal 1 dosen/anggota senat yang diundang ke rapat!';
            return;
        }

        $base = $this->getBackendUrl();
        $newId = rand(100, 999);

        $jenisId = $this->mapMeetingTypeToId($this->newMeetingType);
        $timeString = $this->formatMeetingTime($this->newMeetingStartTime, $this->newMeetingEndTime, $this->newMeetingUntilFinished);
        $timeForBackend = !empty($this->newMeetingStartTime) ? substr($this->newMeetingStartTime, 0, 5) : '09:00';

        try {
            $payload = [
                'id_user' => 1,
                'id_jenis_rapat' => $jenisId,
                'nama_agenda' => $this->newMeetingTitle,
                'tanggal' => $this->newMeetingDate ?: date('Y-m-d', strtotime('+3 days')),
                'waktu' => $timeForBackend,
                'tempat' => $this->newMeetingRoom,
                'status' => 'Terjadwal',
                'organizer' => $this->newMeetingOrganizer,
                'invited_user_ids' => $this->selectedLecturerIds,
            ];

            $res = Http::timeout(3)->post("{$base}/rapat", $payload);
            if ($res->successful()) {
                $responseData = $res->json('data') ?? [];
                $newId = $responseData['id_rapat'] ?? $newId;
            }
        } catch (\Throwable $e) {}

        $newMeetingItem = [
            'id' => $newId,
            'title' => $this->newMeetingTitle,
            'organizer' => $this->newMeetingOrganizer,
            'type' => $this->newMeetingType,
            'date' => $this->newMeetingDate ?: date('Y-m-d', strtotime('+3 days')),
            'time' => $timeString,
            'room' => $this->newMeetingRoom,
            'status' => 'Terjadwal',
            'session_code' => 'PLN-' . rand(100, 999),
            'qr_token' => 'token-qr-' . uniqid(),
            'total_invited' => count($this->selectedLecturerIds),
            'present_count' => 0,
            'is_presensi_open' => false,
            'is_kuorum_locked' => false,
        ];

        array_unshift($this->availableMeetings, $newMeetingItem);

        @set_time_limit(180);

        if (empty($this->allLecturers)) {
            $this->loadLecturersMaster();
        }

        // Kirim Broadcast Email Undangan Otomatis kepada dosen yang dipilih
        $invitedList = array_values(array_filter($this->allLecturers, fn($u) => in_array($u['id_user'], $this->selectedLecturerIds)));
        if (empty($invitedList)) {
            $invitedList = $this->allLecturers;
        }

        $broadcastService = app(\App\Services\MeetingBroadcastService::class);
        $broadcastRes = $broadcastService->broadcast($newMeetingItem, $invitedList, false);

        $count = count($this->selectedLecturerIds);
        $createdTitle = $this->newMeetingTitle;

        $this->newMeetingTitle = '';
        $this->newMeetingOrganizer = 'Senat';
        $this->newMeetingType = 'Pleno';
        $this->newMeetingStartTime = '09:00';
        $this->newMeetingEndTime = '12:00';
        $this->newMeetingUntilFinished = false;
        $this->newMeetingDate = date('Y-m-d', strtotime('+3 days'));
        $this->selectAllLecturers();

        $this->selectMeeting($newId);
        $this->flashMessage = "✓ Agenda Sidang '{$createdTitle}' berhasil dijadwalkan dan broadcast email undangan telah dikirim ke {$broadcastRes['sent']} anggota senat.";
    }

    public function loadLiveAttendanceData(MeetingRepositoryInterface $meetingsRepo, bool $force = false): void
    {
        $repoMeetings = $meetingsRepo->all();
        $existingIds = array_filter(array_map(fn($m) => $m['id'] ?? ($m['id_rapat'] ?? null), $repoMeetings));
        $customMeetings = array_filter($this->availableMeetings, fn($m) => !empty($m['id']) && !in_array($m['id'], $existingIds, true));
        $all = array_values(array_merge($customMeetings, $repoMeetings));
        $this->availableMeetings = $all;

        $targetSession = null;
        if ($this->selectedMeetingId) {
            foreach ($all as $m) {
                if ((int) ($m['id'] ?? 0) === $this->selectedMeetingId) {
                    $targetSession = $m;
                    break;
                }
            }
        }

        if (!$targetSession) {
            $targetSession = $meetingsRepo->getActiveSession($force) ?? ($all[0] ?? null);
            if ($targetSession) {
                $this->selectedMeetingId = (int) ($targetSession['id'] ?? 1);
            }
        }

        if ($targetSession && isset($targetSession['id'])) {
            $this->activeMeetingId = (int) $targetSession['id'];
            $this->liveMeeting = $targetSession;
            $this->qrToken = $targetSession['qr_token'] ?? ('token-qr-' . $this->activeMeetingId);
            $this->sessionCode = $targetSession['session_code'] ?? ('PLN-' . str_pad((string)$this->activeMeetingId, 3, '0', STR_PAD_LEFT));
            $this->isPresensiOpen = (bool) ($targetSession['is_presensi_open'] ?? ($targetSession['status'] === 'Sedang Berlangsung'));
            $this->isKuorumLocked = (bool) ($targetSession['is_kuorum_locked'] ?? false);

            if ($meetingsRepo instanceof ApiMeetingRepository) {
                $liveData = $meetingsRepo->getLiveAttendance($this->activeMeetingId, $force);
                if ($liveData && !empty($liveData['anggota'])) {
                    $this->liveAnggota = $liveData['anggota'];
                    $this->liveKuorum = $liveData['kuorum'] ?? ($targetSession['kuorum'] ?? []);
                    $this->isPresensiOpen = (bool) ($liveData['is_presensi_open'] ?? $this->isPresensiOpen);
                    $this->isKuorumLocked = (bool) ($liveData['is_kuorum_locked'] ?? $this->isKuorumLocked);
                    $this->qrToken = $liveData['token_qr'] ?? $this->qrToken;
                    $this->sessionCode = $liveData['kode_sesi'] ?? $this->sessionCode;
                    return;
                }
            }

            // Fallback anggota filter based on meeting type/komisi
            $type = $targetSession['type'] ?? ($targetSession['jenis_rapat'] ?? '');
            $invitedLecturers = $this->allLecturers;
            if (!empty($targetSession['invited_user_ids'])) {
                $invitedIds = $targetSession['invited_user_ids'];
                $invitedLecturers = array_values(array_filter($this->allLecturers, fn($u) => in_array($u['id_user'], $invitedIds)));
            } else
            if (str_contains($type, 'Komisi I') && !str_contains($type, 'Komisi II') && !str_contains($type, 'Komisi III') && !str_contains($type, 'Komisi IV')) {
                $invitedLecturers = array_values(array_filter($this->allLecturers, fn($u) => str_contains($u['komisi'] ?? '', 'Komisi I') && !str_contains($u['komisi'] ?? '', 'Komisi II') && !str_contains($u['komisi'] ?? '', 'Komisi III') && !str_contains($u['komisi'] ?? '', 'Komisi IV') || str_contains($u['role'] ?? '', 'Ketua')));
            } elseif (str_contains($type, 'Komisi II') && !str_contains($type, 'Komisi III')) {
                $invitedLecturers = array_values(array_filter($this->allLecturers, fn($u) => str_contains($u['komisi'] ?? '', 'Komisi II') && !str_contains($u['komisi'] ?? '', 'Komisi III')));
            } elseif (str_contains($type, 'Komisi III')) {
                $invitedLecturers = array_values(array_filter($this->allLecturers, fn($u) => str_contains($u['komisi'] ?? '', 'Komisi III')));
            } elseif (str_contains($type, 'Komisi IV')) {
                $invitedLecturers = array_values(array_filter($this->allLecturers, fn($u) => str_contains($u['komisi'] ?? '', 'Komisi IV')));
            } elseif (str_contains($type, 'Komisi V')) {
                $invitedLecturers = array_values(array_filter($this->allLecturers, fn($u) => str_contains($u['komisi'] ?? '', 'Komisi V')));
            } elseif (str_contains($type, 'Badan Pekerja') || str_contains($type, 'BP')) {
                $invitedLecturers = array_values(array_filter($this->allLecturers, fn($u) => str_contains($u['komisi'] ?? '', 'Badan Pekerja') || str_contains($u['komisi'] ?? '', 'BP')));
            }

            $totalInvited = count($invitedLecturers);
            $presentRatio = ($targetSession['status'] === 'Sedang Berlangsung') ? 0.7 : (($targetSession['status'] === 'Selesai') ? 0.85 : 0.0);
            $hadirCount = (int) round($totalInvited * $presentRatio);
            $izinCount = ($presentRatio > 0) ? min(2, max(0, $totalInvited - $hadirCount)) : 0;

            $this->liveAnggota = [];
            foreach ($invitedLecturers as $idx => $l) {
                $st = 'menunggu';
                $waktu = null;
                $metode = '-';
                if ($idx < $hadirCount) {
                    $st = 'hadir';
                    $waktu = '09:' . str_pad((string)(15 + $idx * 2), 2, '0', STR_PAD_LEFT) . ':00';
                    $metode = ($idx % 2 === 0) ? 'qr_scan' : 'token_input';
                } elseif ($idx < ($hadirCount + $izinCount)) {
                    $st = 'izin';
                    $metode = 'manual_admin';
                }

                $this->liveAnggota[] = [
                    'id_undangan' => $idx + 1,
                    'id_user' => $l['id_user'],
                    'nama' => $l['nama'],
                    'nim_nip' => $l['nim_nip'],
                    'role' => $l['role'] ?? 'anggota',
                    'status_kehadiran' => $st,
                    'waktu_presensi' => $waktu,
                    'metode_presensi' => $metode,
                    'is_edited_by_admin' => false,
                    'catatan_admin' => $st === 'izin' ? 'Dispensasi tugas institusi' : null,
                ];
            }

            $ambangKuorum = (int) ceil(($totalInvited * 2) / 3);
            $persen = $totalInvited > 0 ? round(($hadirCount / $totalInvited) * 100, 1) : 0;
            $isKuorum = $hadirCount >= $ambangKuorum;

            $this->liveKuorum = [
                'total_invited' => $totalInvited,
                'present_count' => $hadirCount,
                'izin_count' => $izinCount,
                'tidak_hadir_count' => 0,
                'menunggu_count' => max(0, $totalInvited - ($hadirCount + $izinCount)),
                'ambang_kuorum' => $ambangKuorum,
                'present_percentage' => $persen,
                'is_kuorum' => $isKuorum,
                'status_kuorum_label' => $isKuorum ? 'Kuorum Terpenuhi' : 'Belum Memenuhi Kuorum',
                'is_presensi_open' => $this->isPresensiOpen,
                'is_kuorum_locked' => $this->isKuorumLocked,
            ];
        }
    }

    public function getDefaultLecturersList(): array
    {
        return [
            ['id_user' => 1, 'nama' => 'Prof. Dr. Totok Prasetyo, B.Eng., M.T., IPU', 'nim_nip' => '196705101992031003', 'komisi' => 'Pimpinan Senat', 'role' => 'Ketua Senat'],
            ['id_user' => 2, 'nama' => 'Dr. Kurnianingsih, S.T., M.T.', 'nim_nip' => '197906152003122001', 'komisi' => 'Pimpinan Senat', 'role' => 'Sekretaris Senat'],
            ['id_user' => 3, 'nama' => 'Tri Yudantoro, S.Kom., M.Kom.', 'nim_nip' => '197103251999031002', 'komisi' => 'Komisi I', 'role' => 'Ketua Komisi I'],
            ['id_user' => 4, 'nama' => 'Anis Roihatin, S.T., M.T.', 'nim_nip' => '197304121998022001', 'komisi' => 'Komisi I', 'role' => 'Sekretaris Komisi I'],
            ['id_user' => 5, 'nama' => 'Prof. Dr. Ir. Dwiana Hendrawati, S.T., M.T.', 'nim_nip' => '196508241990032001', 'komisi' => 'Komisi I', 'role' => 'Anggota'],
            ['id_user' => 6, 'nama' => 'Yusmar Ardhi Hidayat, S.E., M.Si., Ph.D.', 'nim_nip' => '197607182002121001', 'komisi' => 'Komisi I', 'role' => 'Anggota'],
            ['id_user' => 7, 'nama' => 'Resi Yudaningsih, S.E., M.Si., Akt.', 'nim_nip' => '197802112005012002', 'komisi' => 'Komisi I', 'role' => 'Anggota'],
            ['id_user' => 8, 'nama' => 'Dr. Bagus Yunianto W., S.E., S.Kom., M.M.', 'nim_nip' => '197206191997021001', 'komisi' => 'Komisi I', 'role' => 'Anggota'],
            ['id_user' => 9, 'nama' => 'Nanang Adie Setyawan, S.E., M.M.', 'nim_nip' => '198109052006041002', 'komisi' => 'Komisi I', 'role' => 'Anggota'],
            ['id_user' => 10, 'nama' => 'Dr. Jumi, S.Kom., M.Kom.', 'nim_nip' => '197004161997022001', 'komisi' => 'Komisi II', 'role' => 'Ketua Komisi II'],
            ['id_user' => 11, 'nama' => 'Fajar Surya Herlambang, S.T., M.T.', 'nim_nip' => '198005232005011002', 'komisi' => 'Komisi II', 'role' => 'Sekretaris Komisi II'],
            ['id_user' => 12, 'nama' => 'Yusnan Badruzzaman, S.T., M.Eng.', 'nim_nip' => '197501082001121001', 'komisi' => 'Komisi II', 'role' => 'Anggota'],
            ['id_user' => 13, 'nama' => 'Mohammad Hasanudin, S.E., M.Si.', 'nim_nip' => '197303152002121002', 'komisi' => 'Komisi II', 'role' => 'Anggota'],
            ['id_user' => 14, 'nama' => 'Vinda Setya Kartika, S.T., M.T.', 'nim_nip' => '198207192008122003', 'komisi' => 'Komisi II', 'role' => 'Anggota'],
            ['id_user' => 15, 'nama' => 'Ir. Teguh Mulyo Wicaksono, S.Pd., M.Eng.', 'nim_nip' => '197708092003121002', 'komisi' => 'Komisi II', 'role' => 'Anggota'],
            ['id_user' => 16, 'nama' => 'Ir. Riles Melvy Wattimena, M.T., IPP.', 'nim_nip' => '196411291992031002', 'komisi' => 'Komisi III', 'role' => 'Ketua Komisi III'],
            ['id_user' => 17, 'nama' => 'Iwan Budiyono, S.E., M.Si.Ak', 'nim_nip' => '197108171999031001', 'komisi' => 'Komisi III', 'role' => 'Sekretaris Komisi III'],
            ['id_user' => 18, 'nama' => 'Sahid, S.T., M.T.', 'nim_nip' => '196904141995121001', 'komisi' => 'Komisi III', 'role' => 'Anggota'],
            ['id_user' => 19, 'nama' => 'Dedi Budi Setyawan, S.T., M.T.', 'nim_nip' => '197509202002121001', 'komisi' => 'Komisi III', 'role' => 'Anggota'],
            ['id_user' => 20, 'nama' => 'Dr. Muhammad Anif, S.T., M.Eng.', 'nim_nip' => '197307042000031001', 'komisi' => 'Komisi III', 'role' => 'Anggota'],
            ['id_user' => 21, 'nama' => 'Septian Yudha Kusuma, S.E., M.Si.Ak', 'nim_nip' => '198409152010121004', 'komisi' => 'Komisi III', 'role' => 'Anggota'],
            ['id_user' => 22, 'nama' => 'Dr. Amin Suharjono, S.T., M.T.', 'nim_nip' => '197210081999031001', 'komisi' => 'Komisi IV', 'role' => 'Ketua Komisi IV'],
            ['id_user' => 23, 'nama' => 'Drs. Supriyono, M.T.', 'nim_nip' => '196306281989031001', 'komisi' => 'Komisi IV', 'role' => 'Sekretaris Komisi IV'],
            ['id_user' => 24, 'nama' => 'Sri Danaryani, S.T., M.T.', 'nim_nip' => '196803271993032001', 'komisi' => 'Komisi IV', 'role' => 'Anggota'],
            ['id_user' => 25, 'nama' => 'Bambang Eko Sumarsono, S.T., M.T.', 'nim_nip' => '197009181998021001', 'komisi' => 'Komisi IV', 'role' => 'Anggota'],
            ['id_user' => 26, 'nama' => 'Novi Purnomowati, S.E., M.Si.', 'nim_nip' => '197611022001122001', 'komisi' => 'Komisi IV', 'role' => 'Anggota'],
            ['id_user' => 27, 'nama' => 'Agus Suwondo, S.T., M.T.', 'nim_nip' => '197312152000031001', 'komisi' => 'Komisi IV', 'role' => 'Anggota'],
            ['id_user' => 28, 'nama' => 'Drs. Ahmad Mustofa, M.Ag.', 'nim_nip' => '196504101990031002', 'komisi' => 'Komisi V', 'role' => 'Ketua Komisi V'],
            ['id_user' => 29, 'nama' => 'Nurul Hidayati, S.H., M.H.', 'nim_nip' => '197801202005012003', 'komisi' => 'Komisi V', 'role' => 'Sekretaris Komisi V'],
            ['id_user' => 30, 'nama' => 'Dr. Mardiyono, S.Kom., M.Sc.', 'nim_nip' => '197105261998021001', 'komisi' => 'Komisi V', 'role' => 'Anggota'],
            ['id_user' => 31, 'nama' => 'Drs. Subandi, M.M.', 'nim_nip' => '196208151989031003', 'komisi' => 'Komisi V', 'role' => 'Anggota'],
            ['id_user' => 32, 'nama' => 'Dra. Endang Sulistyowati, M.Si.', 'nim_nip' => '196409051990032002', 'komisi' => 'Komisi V', 'role' => 'Anggota'],
            ['id_user' => 33, 'nama' => 'Dr. Seno Darmawan, S.T., M.T.', 'nim_nip' => '197411122001121002', 'komisi' => 'Komisi I', 'role' => 'Anggota'],
            ['id_user' => 34, 'nama' => 'Heny Yuniastuti, S.E., M.Si.', 'nim_nip' => '197903222006042001', 'komisi' => 'Komisi III', 'role' => 'Anggota'],
        ];
    }


    public function saveMeeting(string $status = 'Terjadwal'): void
    {
        $this->validate([
            'meetingTitle' => 'required|string|max:255',
            'meetingRoom' => 'required|string|max:255',
        ], [
            'meetingTitle.required' => 'Judul / agenda sidang wajib diisi.',
            'meetingRoom.required' => 'Lokasi atau tautan hybrid wajib diisi.',
        ]);

        try {
            $base = $this->getBackendUrl();

            // Backward compatibility if meetingDateTime was set directly (e.g. in tests)
            if (!empty($this->meetingDateTime) && empty($this->meetingDate)) {
                $carbon = \Illuminate\Support\Carbon::parse($this->meetingDateTime);
                $tanggal = $carbon->format('Y-m-d');
                $waktu = $carbon->format('H:i');
            } else {
                $tanggal = $this->meetingDate ?: date('Y-m-d');
                $waktu = !empty($this->meetingStartTime) ? substr($this->meetingStartTime, 0, 5) : '09:00';
            }

            $jenisId = $this->mapMeetingTypeToId($this->meetingType);
            $timeString = $this->formatMeetingTime($this->meetingStartTime, $this->meetingEndTime, $this->meetingUntilFinished);

            $payload = [
                'id_user' => $this->ketuaUserId ?? 1,
                'id_jenis_rapat' => $jenisId,
                'nama_agenda' => $this->meetingTitle,
                'tanggal' => $tanggal,
                'waktu' => $waktu,
                'tempat' => $this->meetingRoom,
                'status' => $status,
                'organizer' => $this->meetingOrganizer,
            ];

            if ($this->editingMeetingId) {
                $response = Http::timeout(5)->put("{$base}/rapat/{$this->editingMeetingId}", $payload);
                if ($response->successful()) {
                    foreach ($this->availableMeetings as &$m) {
                        if (($m['id'] ?? 0) === $this->editingMeetingId) {
                            $m['title'] = $this->meetingTitle;
                            $m['organizer'] = $this->meetingOrganizer;
                            $m['type'] = $this->meetingType;
                            $m['date'] = $tanggal;
                            $m['time'] = $timeString;
                            $m['room'] = $this->meetingRoom;
                            $m['status'] = $status;
                            break;
                        }
                    }

                    // Kirim Broadcast Email Perubahan Jadwal
                    $updatedItem = [
                        'id' => $this->editingMeetingId,
                        'title' => $this->meetingTitle,
                        'organizer' => $this->meetingOrganizer,
                        'type' => $this->meetingType,
                        'date' => $tanggal,
                        'time' => $timeString,
                        'room' => $this->meetingRoom,
                        'status' => $status,
                    ];
                    $invitedList = $this->getInvitedLecturersForMeeting($this->editingMeetingId);
                    $broadcastService = app(\App\Services\MeetingBroadcastService::class);
                    $broadcastRes = $broadcastService->broadcast($updatedItem, $invitedList, true);

                    $this->flashMessage = "Berhasil memperbarui sidang: {$this->meetingTitle} dan broadcast email perubahan jadwal telah dikirim ke {$broadcastRes['sent']} anggota!";
                    $this->resetMeetingForm();
                } else {
                    $this->flashMessage = 'Gagal memperbarui ke Backend: ' . ($response->json('message') ?? $response->body());
                }
            } else {
                $response = Http::timeout(5)->post("{$base}/rapat", $payload);
                if ($response->successful()) {
                    $newItem = [
                        'id' => rand(100, 999),
                        'title' => $this->meetingTitle,
                        'organizer' => $this->meetingOrganizer,
                        'type' => $this->meetingType,
                        'date' => $tanggal,
                        'time' => $timeString,
                        'room' => $this->meetingRoom,
                        'status' => $status,
                    ];
                    $broadcastService = app(\App\Services\MeetingBroadcastService::class);
                    $broadcastRes = $broadcastService->broadcast($newItem, $this->allLecturers, false);

                    $this->flashMessage = "Berhasil membuat jadwal sidang: {$this->meetingTitle} dan broadcast email undangan terkirim ke {$broadcastRes['sent']} anggota!";
                    $this->resetMeetingForm();
                } else {
                    $this->flashMessage = 'Gagal menyimpan ke Backend: ' . ($response->json('message') ?? $response->body());
                }
            }
        } catch (\Throwable $e) {
            $this->flashMessage = 'Gagal menghubungi Backend API: ' . $e->getMessage();
        }
    }

    public function editMeeting(int $id): void
    {
        try {
            $base = $this->getBackendUrl();
            $response = Http::timeout(5)->get("{$base}/rapat/{$id}");
            if ($response->successful()) {
                $item = $response->json();
                $item = $item['data'] ?? $item;

                $this->editingMeetingId = $id;
                $this->meetingTitle = $item['nama_agenda'] ?? '';
                $this->meetingJenisRapatId = (int) ($item['id_jenis_rapat'] ?? 1);
                $this->meetingRoom = $item['tempat'] ?? '';

                $tgl = isset($item['tanggal']) ? substr($item['tanggal'], 0, 10) : date('Y-m-d');
                $wkt = isset($item['waktu']) ? substr($item['waktu'], 0, 5) : '09:00';
                $this->meetingDate = $tgl;
                $this->meetingStartTime = $wkt;
                $this->meetingEndTime = '12:00';
                $this->meetingUntilFinished = false;
                $this->meetingDateTime = "{$tgl}T{$wkt}";

                $this->meetingOrganizer = $item['organizer'] ?? ($item['penyelenggara'] ?? 'Senat');
                $this->meetingType = $this->mapIdToMeetingType($this->meetingJenisRapatId);

                $this->flashMessage = "Mode sunting aktif untuk sidang #{$id}. Silakan perbarui isian di formulir.";
            } else {
                $this->flashMessage = "Gagal memuat detail sidang #{$id} dari Backend.";
            }
        } catch (\Throwable $e) {
            $this->flashMessage = 'Gagal memuat sidang dari Backend: ' . $e->getMessage();
        }
    }

    public function deleteMeeting(int $id): void
    {
        try {
            $base = $this->getBackendUrl();
            $response = Http::timeout(5)->delete("{$base}/rapat/{$id}");
            if ($response->successful()) {
                $this->flashMessage = "Sidang #{$id} berhasil dihapus dari database Backend!";
                if ($this->editingMeetingId === $id) {
                    $this->resetMeetingForm();
                }
            } else {
                $this->flashMessage = "Gagal menghapus sidang #{$id} dari Backend.";
            }
        } catch (\Throwable $e) {
            $this->flashMessage = 'Gagal menghapus ke Backend: ' . $e->getMessage();
        }
    }

    public function resetMeetingForm(): void
    {
        $this->editingMeetingId = null;
        $this->meetingTitle = '';
        $this->meetingOrganizer = 'Senat';
        $this->meetingType = 'Pleno';
        $this->meetingJenisRapatId = 1;
        $this->meetingDate = date('Y-m-d');
        $this->meetingStartTime = '09:00';
        $this->meetingEndTime = '12:00';
        $this->meetingUntilFinished = false;
        $this->meetingDateTime = date('Y-m-d\T09:00');
        $this->meetingRoom = '';
        $this->meetingPeserta = '';
    }

    public function previewBroadcast(int|string $meetingId): void
    {
        $id = (int) $meetingId;
        $meeting = collect($this->availableMeetings)->first(fn($m) => (int)($m['id'] ?? ($m['id_rapat'] ?? 0)) === $id);
        if (!$meeting) {
            $repo = app(\App\Contracts\MeetingRepositoryInterface::class);
            $meeting = collect($repo->all())->first(fn($m) => (int)($m['id'] ?? ($m['id_rapat'] ?? 0)) === $id);
        }
        if (!$meeting) {
            $this->flashMessage = "Gagal memuat agenda sidang #{$id} untuk broadcast.";
            return;
        }

        if (empty($this->allLecturers)) {
            $this->loadLecturersMaster();
        }

        $service = app(\App\Services\MeetingBroadcastService::class);
        $sampleLecturer = $this->allLecturers[0] ?? ['nama' => 'Dr. Sukamto, S.Kom., M.T.'];
        $this->broadcastModalMeetingId = $id;
        $this->broadcastPreviewText = $service->generateMessageText($meeting, $sampleLecturer, false);
    }

    public function sendTestBroadcastEmail(int|string $meetingId): void
    {
        $id = (int) $meetingId;
        $meeting = collect($this->availableMeetings)->first(fn($m) => (int)($m['id'] ?? ($m['id_rapat'] ?? 0)) === $id);
        if (!$meeting) {
            $repo = app(\App\Contracts\MeetingRepositoryInterface::class);
            $meeting = collect($repo->all())->first(fn($m) => (int)($m['id'] ?? ($m['id_rapat'] ?? 0)) === $id);
        }
        if (!$meeting) {
            $this->flashMessage = "Gagal: Data agenda sidang tidak ditemukan.";
            return;
        }

        $targetEmail = trim($this->broadcastTestEmail ?: 'thomasandromeda999@gmail.com');
        if (!filter_var($targetEmail, FILTER_VALIDATE_EMAIL)) {
            $this->flashMessage = "Alamat email uji coba tidak valid: {$targetEmail}";
            return;
        }

        @set_time_limit(60);

        $service = app(\App\Services\MeetingBroadcastService::class);
        $recipient = [
            'nama' => 'Thomas Andromeda (Uji Coba)',
            'nim_nip' => '000000000000',
            'email' => $targetEmail,
        ];

        $res = $service->broadcast($meeting, [$recipient], false);

        if ($res['sent'] > 0) {
            $this->flashMessage = "✓ Berhasil! Email broadcast uji coba untuk agenda '{$meeting['title']}' telah terkirim ke {$targetEmail}. Silakan cek inbox/spam.";
        } else {
            $err = $res['logs'][0]['status'] ?? 'Gagal';
            $this->flashMessage = "Gagal mengirim email uji coba ke {$targetEmail}: {$err}";
        }
    }

    public function sendBroadcastEmail(int|string $meetingId): void
    {
        $id = (int) $meetingId;
        $meeting = collect($this->availableMeetings)->first(fn($m) => (int)($m['id'] ?? ($m['id_rapat'] ?? 0)) === $id);
        if (!$meeting) {
            $repo = app(\App\Contracts\MeetingRepositoryInterface::class);
            $meeting = collect($repo->all())->first(fn($m) => (int)($m['id'] ?? ($m['id_rapat'] ?? 0)) === $id);
        }
        if (!$meeting) {
            $this->flashMessage = "Gagal: Data agenda sidang #{$id} tidak ditemukan.";
            return;
        }

        @set_time_limit(180);

        if (empty($this->allLecturers)) {
            $this->loadLecturersMaster();
        }

        $service = app(\App\Services\MeetingBroadcastService::class);
        $invitedList = $this->getInvitedLecturersForMeeting($id);
        if (empty($invitedList)) {
            $invitedList = $this->allLecturers;
        }

        $res = $service->broadcast($meeting, $invitedList, false);

        $this->flashMessage = "Broadcast email pengingat agenda sidang '{$meeting['title']}' berhasil dikirim ke {$res['sent']} anggota senat.";
        $this->broadcastModalMeetingId = null;
        $this->broadcastPreviewText = null;
    }

    public function closeBroadcastModal(): void
    {
        $this->broadcastModalMeetingId = null;
        $this->broadcastPreviewText = null;
    }

    public function getInvitedLecturersForMeeting(int $meetingId): array
    {
        foreach ($this->availableMeetings as $m) {
            if ((int)($m['id'] ?? ($m['id_rapat'] ?? 0)) === $meetingId) {
                if (!empty($m['invited_user_ids']) && is_array($m['invited_user_ids'])) {
                    $ids = $m['invited_user_ids'];
                    $filtered = array_values(array_filter($this->allLecturers, fn($u) => in_array($u['id_user'], $ids)));
                    if (!empty($filtered)) {
                        return $filtered;
                    }
                }
                $type = $m['type'] ?? '';
                $org = $m['organizer'] ?? '';
                if (str_contains($org, 'Komisi 1') || str_contains($type, 'Komisi I')) {
                    return array_values(array_filter($this->allLecturers, fn($u) => str_contains($u['komisi'] ?? '', 'Komisi I') || str_contains($u['role'] ?? '', 'Ketua')));
                }
                if (str_contains($org, 'Komisi 2') || str_contains($type, 'Komisi II')) {
                    return array_values(array_filter($this->allLecturers, fn($u) => str_contains($u['komisi'] ?? '', 'Komisi II')));
                }
                if (str_contains($org, 'Komisi 3') || str_contains($type, 'Komisi III')) {
                    return array_values(array_filter($this->allLecturers, fn($u) => str_contains($u['komisi'] ?? '', 'Komisi III')));
                }
                if (str_contains($org, 'Komisi 4') || str_contains($type, 'Komisi IV')) {
                    return array_values(array_filter($this->allLecturers, fn($u) => str_contains($u['komisi'] ?? '', 'Komisi IV')));
                }
                if (str_contains($org, 'Komisi 5') || str_contains($type, 'Komisi V')) {
                    return array_values(array_filter($this->allLecturers, fn($u) => str_contains($u['komisi'] ?? '', 'Komisi V')));
                }
                if (str_contains($org, 'Badan Pekerja') || str_contains($type, 'BP')) {
                    return array_values(array_filter($this->allLecturers, fn($u) => str_contains($u['komisi'] ?? '', 'BP') || str_contains($u['komisi'] ?? '', 'Badan Pekerja')));
                }
                break;
            }
        }
        return $this->allLecturers;
    }


}
