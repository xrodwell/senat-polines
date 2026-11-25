<?php

namespace App\Livewire\Admin\Traits;

use App\Contracts\CommitteeRepositoryInterface;
use App\Support\MemberPhotoStorage;
use App\Support\SenatDirectory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait HasCommitteeManagement
{
    // State Tahun Jabatan / Periode Senat
    public array $periodeList = [];
    public ?int $selectedPeriodeId = null;
    public bool $showPeriodeModal = false;
    public bool $isEditingPeriode = false;
    public ?int $periodeEditId = null;
    public string $periodeNama = '';
    public string $periodeTanggalMulai = '';
    public string $periodeTanggalSelesai = '';
    public bool $periodeIsActive = false;

    public bool $showDeletePeriodeModal = false;
    public ?int $deletePeriodeId = null;
    public string $deletePeriodeName = '';

    // State Modal & Form CRUD Anggota Komisi
    public bool $showMemberModal = false;
    public bool $isEditingMember = false;
    public ?string $memberEditId = null;

    // Field Form
    public string $memberNama = '';
    public string $memberNamaLama = '';
    public string $memberNimNip = '';
    public string $memberEmail = '';
    public string $memberCommitteeCode = 'KOM-I';
    public string $memberRole = 'Anggota';
    public string $memberOrgan = '';
    public string $memberFocus = '';

    // Foto profil anggota (unggahan baru & foto tersimpan saat ini)
    public $memberPhoto = null;
    public ?string $memberExistingPhoto = null;

    // State Modal Konfirmasi Hapus
    public bool $showDeleteMemberModal = false;
    public ?string $deleteMemberId = null;
    public string $deleteMemberName = '';
    public string $deleteMemberCommitteeCode = '';

    private function committeeBackendClient()
    {
        $req = Http::acceptJson()->timeout(5);
        if ($token = session('auth_token')) {
            $req = $req->withToken($token);
        }

        return $req;
    }

    public function loadPeriodeList(): void
    {
        $baseUrl = $this->getBackendUrl();
        try {
            $res = $this->committeeBackendClient()->get("{$baseUrl}/periode-senat", ['all' => 'true']);
            if ($res->successful()) {
                $data = $res->json('data') ?? $res->json();
                if (is_array($data)) {
                    $this->periodeList = $data;
                    return;
                }
            }
        } catch (\Throwable $e) {
            Log::info('Gagal memuat daftar periode senat: ' . $e->getMessage());
        }

        if (empty($this->periodeList)) {
            $this->periodeList = [[
                'id_periode_senat' => 1,
                'nama_periode' => SenatDirectory::PERIOD,
                'tanggal_mulai' => '2026-09-01',
                'tanggal_selesai' => '2030-08-31',
                'is_active' => true,
                'enrollments_count' => 0,
            ]];
        }
    }

    public function selectPeriode(int $periodeId): void
    {
        $this->selectedPeriodeId = $periodeId;
    }

    public function backToPeriodeList(): void
    {
        $this->selectedPeriodeId = null;
        $this->loadPeriodeList();
    }

    public function activatePeriode(int $periodeId): void
    {
        $baseUrl = $this->getBackendUrl();
        try {
            $res = $this->committeeBackendClient()->post("{$baseUrl}/periode-senat/{$periodeId}/activate");
            if ($res->successful()) {
                $this->loadPeriodeList();
                $nama = collect($this->periodeList)->firstWhere('id_periode_senat', $periodeId)['nama_periode'] ?? "#{$periodeId}";
                $this->flashMessage = "Tahun jabatan {$nama} berhasil dijadikan periode aktif.";
                return;
            }
            $this->flashMessage = 'Gagal mengaktifkan periode: ' . ($res->json('message') ?? $res->status());
        } catch (\Throwable $e) {
            $this->flashMessage = 'Gagal menghubungi Backend API: ' . $e->getMessage();
        }
    }

    public function openCreatePeriodeModal(): void
    {
        $this->isEditingPeriode = false;
        $this->periodeEditId = null;
        $this->periodeNama = '';
        $this->periodeTanggalMulai = date('Y') . '-01-01';
        $this->periodeTanggalSelesai = (date('Y') + 4) . '-12-31';
        $this->periodeIsActive = false;
        $this->resetValidation(['periodeNama', 'periodeTanggalMulai', 'periodeTanggalSelesai']);
        $this->showPeriodeModal = true;
    }

    public function openEditPeriodeModal(array $periode): void
    {
        $this->isEditingPeriode = true;
        $this->periodeEditId = (int) ($periode['id_periode_senat'] ?? 0);
        $this->periodeNama = (string) ($periode['nama_periode'] ?? '');
        $this->periodeTanggalMulai = isset($periode['tanggal_mulai']) ? substr((string) $periode['tanggal_mulai'], 0, 10) : '';
        $this->periodeTanggalSelesai = isset($periode['tanggal_selesai']) ? substr((string) $periode['tanggal_selesai'], 0, 10) : '';
        $this->periodeIsActive = (bool) ($periode['is_active'] ?? false);
        $this->resetValidation(['periodeNama', 'periodeTanggalMulai', 'periodeTanggalSelesai']);
        $this->showPeriodeModal = true;
    }

    public function closePeriodeModal(): void
    {
        $this->showPeriodeModal = false;
        $this->periodeEditId = null;
    }

    public function savePeriode(): void
    {
        $this->validate([
            'periodeNama' => ['required', 'string', 'max:255'],
            'periodeTanggalMulai' => ['required', 'date'],
            'periodeTanggalSelesai' => ['required', 'date', 'after_or_equal:periodeTanggalMulai'],
        ], [
            'periodeNama.required' => 'Nama periode tahun jabatan wajib diisi (mis. 2026–2030).',
            'periodeTanggalMulai.required' => 'Tanggal mulai wajib diisi.',
            'periodeTanggalSelesai.required' => 'Tanggal selesai wajib diisi.',
            'periodeTanggalSelesai.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
        ]);

        $baseUrl = $this->getBackendUrl();
        $payload = [
            'nama_periode' => trim($this->periodeNama),
            'tanggal_mulai' => $this->periodeTanggalMulai,
            'tanggal_selesai' => $this->periodeTanggalSelesai,
            'is_active' => $this->periodeIsActive,
        ];

        try {
            if ($this->isEditingPeriode && $this->periodeEditId) {
                $res = $this->committeeBackendClient()->put("{$baseUrl}/periode-senat/{$this->periodeEditId}", $payload);
                if ($res->successful() && $this->periodeIsActive) {
                    $this->committeeBackendClient()->post("{$baseUrl}/periode-senat/{$this->periodeEditId}/activate");
                }
            } else {
                $res = $this->committeeBackendClient()->post("{$baseUrl}/periode-senat", $payload);
            }

            if ($res->successful()) {
                $this->loadPeriodeList();
                $this->flashMessage = $this->isEditingPeriode
                    ? "Tahun jabatan {$this->periodeNama} berhasil diperbarui."
                    : "Tahun jabatan baru {$this->periodeNama} berhasil ditambahkan.";
                $this->closePeriodeModal();
                return;
            }

            $this->flashMessage = 'Gagal menyimpan tahun jabatan: ' . ($res->json('message') ?? $res->body());
        } catch (\Throwable $e) {
            $this->flashMessage = 'Gagal menghubungi Backend API: ' . $e->getMessage();
        }
    }

    public function confirmDeletePeriode(array $periode): void
    {
        $this->deletePeriodeId = (int) ($periode['id_periode_senat'] ?? 0);
        $this->deletePeriodeName = (string) ($periode['nama_periode'] ?? '');
        $this->showDeletePeriodeModal = true;
    }

    public function closeDeletePeriodeModal(): void
    {
        $this->showDeletePeriodeModal = false;
        $this->deletePeriodeId = null;
        $this->deletePeriodeName = '';
    }

    public function deletePeriode(): void
    {
        if (! $this->deletePeriodeId) {
            $this->closeDeletePeriodeModal();
            return;
        }

        $baseUrl = $this->getBackendUrl();
        try {
            $res = $this->committeeBackendClient()->delete("{$baseUrl}/periode-senat/{$this->deletePeriodeId}");
            if ($res->successful()) {
                if ($this->selectedPeriodeId === $this->deletePeriodeId) {
                    $this->selectedPeriodeId = null;
                }
                $this->loadPeriodeList();
                $this->flashMessage = "Tahun jabatan {$this->deletePeriodeName} berhasil dihapus.";
            } else {
                $this->flashMessage = 'Gagal menghapus tahun jabatan: ' . ($res->json('message') ?? 'Tidak dapat menghapus.');
            }
        } catch (\Throwable $e) {
            $this->flashMessage = 'Gagal menghubungi Backend API: ' . $e->getMessage();
        }

        $this->closeDeletePeriodeModal();
    }

    public function openCreateMemberModal(?string $committeeCode = null): void
    {
        $this->resetMemberForm();
        $this->isEditingMember = false;
        $this->memberEditId = null;
        if (!empty($committeeCode)) {
            $this->memberCommitteeCode = $committeeCode;
        }
        $this->showMemberModal = true;
    }

    public function openEditMemberModal(array $person, ?string $committeeCode = null): void
    {
        $this->resetMemberForm();
        $this->isEditingMember = true;
        $this->memberEditId = (string) ($person['id'] ?? $person['slug'] ?? $person['name'] ?? '');
        $this->memberNama = $person['name'] ?? '';
        $this->memberNamaLama = $person['name'] ?? '';
        $this->memberNimNip = $person['nip'] ?? '';
        $this->memberEmail = $person['email'] ?? '';
        $this->memberCommitteeCode = $person['committee_code'] ?? $committeeCode ?? 'KOM-I';

        $role = $person['role'] ?? 'Anggota';
        if (str_starts_with($role, 'Ketua')) {
            $this->memberRole = 'Ketua';
        } elseif (str_starts_with($role, 'Sekretaris')) {
            $this->memberRole = 'Sekretaris';
        } else {
            $this->memberRole = 'Anggota';
        }

        $this->memberOrgan = $person['organ'] ?? '';
        $this->memberFocus = $person['focus'] ?? '';
        $this->memberExistingPhoto = $person['photo'] ?? null;
        $this->showMemberModal = true;
    }

    public function closeMemberModal(): void
    {
        $this->showMemberModal = false;
        $this->resetMemberForm();
    }

    public function resetMemberForm(): void
    {
        $this->memberNama = '';
        $this->memberNamaLama = '';
        $this->memberNimNip = '';
        $this->memberEmail = '';
        $this->memberCommitteeCode = 'KOM-I';
        $this->memberRole = 'Anggota';
        $this->memberOrgan = '';
        $this->memberFocus = '';
        $this->memberPhoto = null;
        $this->memberExistingPhoto = null;
        $this->resetValidation([
            'memberNama',
            'memberNimNip',
            'memberEmail',
            'memberCommitteeCode',
            'memberRole',
        ]);
    }

    public function saveMember(): void
    {
        $this->validate([
            'memberNama' => ['required', 'string', 'max:255'],
            'memberNimNip' => ['nullable', 'string', 'max:50'],
            'memberEmail' => ['nullable', 'email', 'max:255'],
            'memberCommitteeCode' => ['required', 'string'],
            'memberRole' => ['required', 'string'],
            'memberPhoto' => ['nullable', 'image', 'max:2048'],
        ], [
            'memberNama.required' => 'Nama lengkap dan gelar wajib diisi.',
            'memberCommitteeCode.required' => 'Komisi wajib dipilih.',
            'memberRole.required' => 'Jabatan komisi wajib dipilih.',
            'memberPhoto.image' => 'Foto profil harus berupa gambar (JPG, PNG, atau WebP).',
            'memberPhoto.max' => 'Ukuran foto profil maksimal 2 MB.',
        ]);

        $baseUrl = $this->getBackendUrl();
        $isEdit = $this->isEditingMember && !empty($this->memberEditId);

        // Simpan foto baru (jika dipilih) ke folder publik frontend, lalu
        // sinkronkan path-nya ke Backend API dan penyimpanan lokal.
        $photoPath = $this->memberExistingPhoto;
        if ($this->memberPhoto) {
            $photoPath = MemberPhotoStorage::store(
                $this->memberPhoto,
                $this->memberNimNip ?: null,
                $this->memberNama
            );
            $this->memberExistingPhoto = $photoPath;
            $this->memberPhoto = null;
        }

        // 1. Simpan ke Backend API (sumber data panel admin & halaman publik)
        $apiSaved = false;
        $apiError = null;
        try {
            $url = $isEdit
                ? "{$baseUrl}/committees/members/{$this->memberEditId}"
                : "{$baseUrl}/committees/members";

            $payload = [
                'nama' => $this->memberNama,
                'nim_nip' => $this->memberNimNip,
                'email' => $this->memberEmail,
                'id_komisi' => $this->memberCommitteeCode,
                'jabatan_komisi' => $this->memberRole,
                'organ' => $this->memberOrgan,
                'foto_profil' => $photoPath,
                'id_periode_senat' => $this->selectedPeriodeId,
            ];

            if ($isEdit) {
                $payload['nama_lama'] = $this->memberNamaLama ?: $this->memberNama;
                $response = $this->committeeBackendClient()->put($url, $payload);
            } else {
                $response = $this->committeeBackendClient()->post($url, $payload);
            }

            $apiSaved = $response->successful();
            if (! $apiSaved) {
                $apiError = $response->json('message')
                    ?: 'Backend menolak data (HTTP ' . $response->status() . ').';
                Log::warning('Gagal menyimpan anggota komisi ke API: ' . $apiError);
            }
        } catch (\Throwable $e) {
            $apiError = 'Backend tidak merespons: ' . $e->getMessage();
            Log::info('Sync anggota ke Backend API gagal, melanjutkan ke local storage: ' . $e->getMessage());
        }

        // 2. Sinkronkan ke mock storage lokal (fallback bila backend offline)
        $this->syncMemberToLocalMock(
            $isEdit,
            $this->memberNama,
            $this->memberNamaLama ?: $this->memberNama,
            $this->memberCommitteeCode,
            $this->memberRole,
            $this->memberNimNip ?: null,
            $photoPath
        );

        $this->flashMessage = $apiSaved
            ? ($isEdit
                ? "Data anggota {$this->memberNama} berhasil diperbarui dan langsung tampil di direktori publik."
                : "Anggota baru {$this->memberNama} berhasil ditambahkan ke {$this->memberCommitteeCode} dan langsung tampil di direktori publik.")
            : "Perhatian: data {$this->memberNama} tersimpan lokal, tetapi gagal dikirim ke backend. {$apiError}";

        $this->closeMemberModal();
    }

    public function confirmDeleteMember(array $person, ?string $committeeCode = null): void
    {
        $this->deleteMemberId = (string) ($person['id'] ?? $person['slug'] ?? $person['name'] ?? '');
        $this->deleteMemberName = $person['name'] ?? '';
        $this->deleteMemberCommitteeCode = $person['committee_code'] ?? $committeeCode ?? '';
        $this->showDeleteMemberModal = true;
    }

    public function closeDeleteMemberModal(): void
    {
        $this->showDeleteMemberModal = false;
        $this->deleteMemberId = null;
        $this->deleteMemberName = '';
        $this->deleteMemberCommitteeCode = '';
    }

    public function deleteMember(): void
    {
        if (empty($this->deleteMemberId) && empty($this->deleteMemberName)) {
            $this->closeDeleteMemberModal();
            return;
        }

        $baseUrl = $this->getBackendUrl();

        // 1. Hapus dari Backend API
        $apiDeleted = false;
        $apiError = null;
        try {
            $targetId = $this->deleteMemberId ?: urlencode($this->deleteMemberName);
            $response = $this->committeeBackendClient()->delete("{$baseUrl}/committees/members/{$targetId}", [
                'nama' => $this->deleteMemberName,
                'committee_code' => $this->deleteMemberCommitteeCode,
            ]);

            $apiDeleted = $response->successful();
            if (! $apiDeleted) {
                $apiError = $response->json('message')
                    ?: 'Backend menolak penghapusan (HTTP ' . $response->status() . ').';
                Log::warning('Gagal menghapus anggota komisi di API: ' . $apiError);
            }
        } catch (\Throwable $e) {
            $apiError = 'Backend tidak merespons: ' . $e->getMessage();
            Log::info('Hapus anggota di Backend API gagal: ' . $e->getMessage());
        }

        // 2. Hapus dari mock JSON lokal
        $mockPath = storage_path('mock/committees.json');
        if (file_exists($mockPath) && $this->deleteMemberName) {
            $committees = json_decode(file_get_contents($mockPath), true) ?: [];
            foreach ($committees as &$c) {
                if (empty($this->deleteMemberCommitteeCode) || $c['code'] === $this->deleteMemberCommitteeCode) {
                    if (isset($c['members']) && is_array($c['members'])) {
                        $c['members'] = array_values(array_filter($c['members'], fn($m) => (is_array($m) ? ($m['nama'] ?? '') : $m) !== $this->deleteMemberName));
                    }
                    if (($c['lead'] ?? '') === $this->deleteMemberName) {
                        $c['lead'] = 'Belum ditentukan';
                    }
                    if (($c['secretary'] ?? '') === $this->deleteMemberName) {
                        $c['secretary'] = 'Belum ditentukan';
                    }
                }
                $leadCount = (!empty($c['lead']) && $c['lead'] !== 'Belum ditentukan') ? 1 : 0;
                $secCount = (!empty($c['secretary']) && $c['secretary'] !== 'Belum ditentukan') ? 1 : 0;
                $c['members_count'] = count($c['members'] ?? []) + $leadCount + $secCount;
            }
            unset($c);
            file_put_contents($mockPath, json_encode($committees, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL);
        }

        $this->flashMessage = $apiDeleted
            ? "Anggota {$this->deleteMemberName} berhasil dihapus dan langsung hilang dari direktori publik."
            : "Perhatian: {$this->deleteMemberName} dihapus dari cadangan lokal, tetapi backend gagal memproses. {$apiError}";

        $this->closeDeleteMemberModal();
    }

    private function syncMemberToLocalMock(bool $isEdit, string $newName, string $oldName, string $committeeCode, string $role, ?string $nip = null, ?string $photoPath = null): void
    {
        $mockPath = storage_path('mock/committees.json');
        if (!file_exists($mockPath)) {
            return;
        }

        $committees = json_decode(file_get_contents($mockPath), true) ?: [];

        // Jika mode edit, bersihkan posisi lama terlebih dahulu agar tidak duplikat
        if ($isEdit) {
            foreach ($committees as &$c) {
                if (($c['lead'] ?? '') === $oldName) {
                    $c['lead'] = 'Belum ditentukan';
                }
                if (($c['secretary'] ?? '') === $oldName) {
                    $c['secretary'] = 'Belum ditentukan';
                }
                if (isset($c['members']) && is_array($c['members'])) {
                    $c['members'] = array_values(array_filter($c['members'], function ($m) use ($oldName) {
                        $val = is_array($m) ? ($m['nama'] ?? '') : $m;
                        return $val !== $oldName;
                    }));
                }
            }
            unset($c);
        }

        // Tambah atau posisikan ke komisi target
        foreach ($committees as &$c) {
            if ($c['code'] === $committeeCode) {
                if ($role === 'Ketua') {
                    $c['lead'] = $newName;
                } elseif ($role === 'Sekretaris') {
                    $c['secretary'] = $newName;
                } else {
                    if (!isset($c['members']) || !is_array($c['members'])) {
                        $c['members'] = [];
                    }
                    $exists = false;
                    foreach ($c['members'] as $m) {
                        $val = is_array($m) ? ($m['nama'] ?? '') : $m;
                        if ($val === $newName) {
                            $exists = true;
                            break;
                        }
                    }
                    if (!$exists) {
                        // Simpan objek anggota agar NIP & foto dapat ditampilkan
                        // oleh halaman publik (resolusi foto berdasarkan NIP).
                        $c['members'][] = array_filter([
                            'nama' => $newName,
                            'nim_nip' => $nip,
                            'photo' => $photoPath,
                        ], static fn ($value) => $value !== null && $value !== '');
                    }
                }
            }
            $leadCount = (!empty($c['lead']) && $c['lead'] !== 'Belum ditentukan') ? 1 : 0;
            $secCount = (!empty($c['secretary']) && $c['secretary'] !== 'Belum ditentukan') ? 1 : 0;
            $c['members_count'] = count($c['members'] ?? []) + $leadCount + $secCount;
        }
        unset($c);

        file_put_contents($mockPath, json_encode($committees, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL);
    }
}
