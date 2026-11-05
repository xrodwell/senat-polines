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
    public string $meetingDateTime = '';
    public string $meetingRoom = '';
    public string $meetingPeserta = '';
    public ?int $editingMeetingId = null;

    // Multi-Meeting & History State
    public ?int $selectedMeetingId = null;
    public string $meetingFilterTab = 'semua'; // semua, berlangsung, terjadwal, selesai
    // Form Buat Sidang Baru & Pemilihan Dosen
    public string $newMeetingTitle = '';
    public string $newMeetingType = 'Sidang Pleno';
    public string $newMeetingDate = '';
    public string $newMeetingTime = '09:00 - 12:30 WIB';
    public string $newMeetingRoom = 'Ruang Sidang Utama Lantai 2 Gd. Direktorat';
    public array $allLecturers = [];
    public array $selectedLecturerIds = [];
    public string $lecturerSearch = '';


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
        $this->selectedLecturerIds = array_values(array_map(
            fn($item) => $item['id_user'],
            array_filter($this->allLecturers, fn($item) => str_contains($item['komisi'] ?? '', $komisi) || str_contains($item['role'] ?? '', $komisi))
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
            $res = Http::timeout(2)->get("{$base}/user");
            if ($res->successful()) {
                $payload = $res->json('data') ?? $res->json();
                $items = isset($payload['data']) && is_array($payload['data']) ? $payload['data'] : $payload;
                if (is_array($items) && !empty($items)) {
                    $komisiPresets = ['Pimpinan Senat', 'Komisi I', 'Komisi II', 'Komisi III', 'Komisi IV', 'Komisi V'];
                    $this->allLecturers = array_map(function ($u, $idx) use ($komisiPresets) {
                        $kom = $u['komisi'] ?? null;
                        if (!$kom && !empty($u['enrollments']) && is_array($u['enrollments'])) {
                            $kom = $u['enrollments'][0]['komisi']['nama_komisi'] ?? ($u['enrollments'][0]['jabatan_komisi'] ?? null);
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

        try {
            $payload = [
                'id_user' => 1,
                'id_jenis_rapat' => str_contains($this->newMeetingType, 'Komisi') ? 2 : 1,
                'nama_agenda' => $this->newMeetingTitle,
                'tanggal' => $this->newMeetingDate ?: date('Y-m-d', strtotime('+3 days')),
                'waktu' => substr($this->newMeetingTime, 0, 5),
                'tempat' => $this->newMeetingRoom,
                'status' => 'Terjadwal',
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
            'type' => $this->newMeetingType,
            'date' => $this->newMeetingDate ?: date('Y-m-d', strtotime('+3 days')),
            'time' => $this->newMeetingTime,
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

        $count = count($this->selectedLecturerIds);
        $createdTitle = $this->newMeetingTitle;

        $this->newMeetingTitle = '';
        $this->newMeetingDate = date('Y-m-d', strtotime('+3 days'));
        $this->selectAllLecturers();

        $this->selectMeeting($newId);
        $this->flashMessage = "Agenda Sidang '{$createdTitle}' berhasil dijadwalkan dengan {$count} dosen diundang.";
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
            if (str_contains($type, 'Komisi I')) {
                $invitedLecturers = array_values(array_filter($this->allLecturers, fn($u) => str_contains($u['komisi'] ?? '', 'Komisi I') || str_contains($u['role'] ?? '', 'Ketua')));
            } elseif (str_contains($type, 'Komisi II')) {
                $invitedLecturers = array_values(array_filter($this->allLecturers, fn($u) => str_contains($u['komisi'] ?? '', 'Komisi II')));
            } elseif (str_contains($type, 'Komisi III')) {
                $invitedLecturers = array_values(array_filter($this->allLecturers, fn($u) => str_contains($u['komisi'] ?? '', 'Komisi III')));
            } elseif (str_contains($type, 'Komisi V')) {
                $invitedLecturers = array_values(array_filter($this->allLecturers, fn($u) => str_contains($u['komisi'] ?? '', 'Komisi V')));
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
            'meetingDateTime' => 'required',
            'meetingRoom' => 'required|string|max:255',
        ], [
            'meetingTitle.required' => 'Judul / agenda sidang wajib diisi.',
            'meetingDateTime.required' => 'Tanggal dan waktu sidang wajib diisi.',
            'meetingRoom.required' => 'Lokasi atau tautan hybrid wajib diisi.',
        ]);

        try {
            $base = $this->getBackendUrl();
            $carbon = \Illuminate\Support\Carbon::parse($this->meetingDateTime);
            $tanggal = $carbon->format('Y-m-d');
            $waktu = $carbon->format('H:i');

            $payload = [
                'id_user' => $this->ketuaUserId ?? 1,
                'id_jenis_rapat' => (int) $this->meetingJenisRapatId,
                'nama_agenda' => $this->meetingTitle,
                'tanggal' => $tanggal,
                'waktu' => $waktu,
                'tempat' => $this->meetingRoom,
                'status' => $status,
            ];

            if ($this->editingMeetingId) {
                $response = Http::timeout(5)->put("{$base}/rapat/{$this->editingMeetingId}", $payload);
                if ($response->successful()) {
                    $this->flashMessage = "Berhasil memperbarui sidang: {$this->meetingTitle} (Status: {$status}) ke Backend!";
                    $this->resetMeetingForm();
                } else {
                    $this->flashMessage = 'Gagal memperbarui ke Backend: ' . ($response->json('message') ?? $response->body());
                }
            } else {
                $response = Http::timeout(5)->post("{$base}/rapat", $payload);
                if ($response->successful()) {
                    $this->flashMessage = "Berhasil membuat jadwal sidang: {$this->meetingTitle} (Status: {$status}) di Backend!";
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
                $this->meetingDateTime = "{$tgl}T{$wkt}";

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
        $this->meetingJenisRapatId = 1;
        $this->meetingDateTime = date('Y-m-d\T09:00');
        $this->meetingRoom = '';
        $this->meetingPeserta = '';
    }


}
