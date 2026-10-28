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
            'Jadwal & Agenda Sidang',
            'Presensi & Verifikasi QR',
            'Kuorum & Kehadiran Anggota',
            'Risalah & Notulensi Sidang',
            'E-Voting & Pengambilan Keputusan',
        ], AdminPreview::GROUPS['Manajemen Persidangan & Rapat']);

        $this->assertSame([
            'Pengaturan Informasi & Hero',
            'Profil, Tupoksi & Struktur',
            'Direktori Komisi & Anggota',
            'Manajemen Berita & Siaran Pers',
            'Katalog Regulasi & Produk Hukum',
            'Repositori Arsip & Dokumen',
            'Pusat Aspirasi & Tindak Lanjut',
        ], AdminPreview::GROUPS['Manajemen Publikasi & Portal Web']);
    }
}
