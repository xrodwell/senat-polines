<?php

namespace App\Livewire\Admin\Traits;

use App\Contracts\CommitteeRepositoryInterface;
use App\Support\SenatDirectory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait HasCommitteeManagement
{
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

    // State Modal Konfirmasi Hapus
    public bool $showDeleteMemberModal = false;
    public ?string $deleteMemberId = null;
    public string $deleteMemberName = '';
    public string $deleteMemberCommitteeCode = '';

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
        ], [
            'memberNama.required' => 'Nama lengkap dan gelar wajib diisi.',
            'memberCommitteeCode.required' => 'Komisi wajib dipilih.',
            'memberRole.required' => 'Jabatan komisi wajib dipilih.',
        ]);

        $baseUrl = $this->getBackendUrl();
        $isEdit = $this->isEditingMember && !empty($this->memberEditId);

        // 1. Simpan ke Backend API jika tersedia
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
            ];

            if ($isEdit) {
                $payload['nama_lama'] = $this->memberNamaLama ?: $this->memberNama;
                Http::timeout(4)->put($url, $payload);
            } else {
                Http::timeout(4)->post($url, $payload);
            }
        } catch (\Throwable $e) {
            Log::info('Sync anggota ke Backend API gagal, melanjutkan ke local storage: ' . $e->getMessage());
        }

        // 2. Sinkronkan ke mock storage lokal (committees.json)
        $this->syncMemberToLocalMock(
            $isEdit,
            $this->memberNama,
            $this->memberNamaLama ?: $this->memberNama,
            $this->memberCommitteeCode,
            $this->memberRole
        );

        $this->flashMessage = $isEdit
            ? "Data anggota {$this->memberNama} berhasil diperbarui."
            : "Anggota baru {$this->memberNama} berhasil ditambahkan ke {$this->memberCommitteeCode}.";

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
        try {
            $targetId = $this->deleteMemberId ?: urlencode($this->deleteMemberName);
            Http::timeout(4)->delete("{$baseUrl}/committees/members/{$targetId}", [
                'nama' => $this->deleteMemberName,
                'committee_code' => $this->deleteMemberCommitteeCode,
            ]);
        } catch (\Throwable $e) {
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
            file_put_contents($mockPath, json_encode($committees, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        $this->flashMessage = "Anggota {$this->deleteMemberName} berhasil dihapus dari direktori.";
        $this->closeDeleteMemberModal();
    }

    private function syncMemberToLocalMock(bool $isEdit, string $newName, string $oldName, string $committeeCode, string $role): void
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
                        $c['members'][] = $newName;
                    }
                }
            }
            $leadCount = (!empty($c['lead']) && $c['lead'] !== 'Belum ditentukan') ? 1 : 0;
            $secCount = (!empty($c['secretary']) && $c['secretary'] !== 'Belum ditentukan') ? 1 : 0;
            $c['members_count'] = count($c['members'] ?? []) + $leadCount + $secCount;
        }
        unset($c);

        file_put_contents($mockPath, json_encode($committees, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
