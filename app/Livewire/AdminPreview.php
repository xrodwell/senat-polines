<?php

namespace App\Livewire;

use Illuminate\View\View;
use Livewire\Component;

class AdminPreview extends Component
{
    /**
     * Sidebar IA — PRD 5. Dua kelompok kerja saja.
     *
     * @var array<string, list<string>>
     */
    public const GROUPS = [
        'Bagian Rapat' => [
            'Dashboard Rapat',
            'Jadwal & Buat Rapat',
            'Agenda',
            'Presensi & QR',
            'Kuorum',
            'Notulensi & Tindak Lanjut',
            'E-Voting',
        ],
        'Bagian Urus Website' => [
            'Beranda, Hero & Informasi',
            'Profil, Tugas & Struktur',
            'Komisi & Direktori Anggota',
            'Berita & Warta',
            'Produk Hukum',
            'Arsip & Laporan',
            'Aspirasi, Tanggapan & Tracker',
            'Media & Dokumen',
            'Pengaturan Konten Publik',
        ],
    ];

    public string $active = 'Dashboard';

    public function select(string $screen): void
    {
        $allowed = ['Dashboard', ...array_merge(...array_values(self::GROUPS))];

        if (! in_array($screen, $allowed, true)) {
            return;
        }

        $this->active = $screen;
    }

    public function render(): View
    {
        return view('livewire.admin-preview')->layout('layouts.admin-preview');
    }
}
