<?php

namespace Tests\Unit;

use App\Livewire\AdminPreview;
use PHPUnit\Framework\TestCase;

class AdminPreviewGroupsTest extends TestCase
{
    public function test_screen_labels_are_exact(): void
    {
        $this->assertSame([
            'Dashboard Rapat',
            'Jadwal & Buat Rapat',
            'Agenda',
            'Presensi & QR',
            'Kuorum',
            'Notulensi & Tindak Lanjut',
            'E-Voting',
        ], AdminPreview::GROUPS['Bagian Rapat']);

        $this->assertSame([
            'Beranda, Hero & Informasi',
            'Profil, Tugas & Struktur',
            'Komisi & Direktori Anggota',
            'Berita & Warta',
            'Produk Hukum',
            'Arsip & Laporan',
            'Aspirasi, Tanggapan & Tracker',
            'Media & Dokumen',
            'Pengaturan Konten Publik',
        ], AdminPreview::GROUPS['Bagian Urus Website']);
    }
}
