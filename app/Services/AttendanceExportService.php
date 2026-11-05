<?php

namespace App\Services;

use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceExportService
{
    /**
     * Styling xls export
     */
    public function exportExcel(array $liveMeeting, array $liveAnggota, array $liveKuorum): StreamedResponse
    {
        $agendaTitle = $liveMeeting['title'] ?? ($liveMeeting['nama_agenda'] ?? 'Sidang Senat Akademik Polines');
        $slug = Str::slug($agendaTitle);
        $fileName = 'Daftar-Hadir-Kuorum-' . ($slug ?: 'sidang-senat') . '-' . date('Ymd-His') . '.xls';

        $totalInvited = $liveKuorum['total_invited'] ?? count($liveAnggota);
        $presentCount = $liveKuorum['present_count'] ?? 0;
        $izinCount = $liveKuorum['izin_count'] ?? 0;
        $tidakHadirCount = $liveKuorum['tidak_hadir_count'] ?? 0;
        $ambangKuorum = $liveKuorum['ambang_kuorum'] ?? (int) ceil(($totalInvited * 2) / 3);
        $persenKuorum = $liveKuorum['present_percentage'] ?? 0;
        $isKuorum = (bool) ($liveKuorum['is_kuorum'] ?? false);
        $statusKuorum = $isKuorum ? 'KUORUM TERPENUHI (SAH)' : 'BELUM MEMENUHI KUORUM';

        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->streamDownload(function () use (
            $agendaTitle,
            $totalInvited,
            $presentCount,
            $izinCount,
            $tidakHadirCount,
            $ambangKuorum,
            $persenKuorum,
            $isKuorum,
            $statusKuorum,
            $liveAnggota
        ) {
            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
            echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
            echo '<style>';
            echo 'body { font-family: "Segoe UI", Arial, sans-serif; font-size: 11pt; color: #1e293b; }';
            echo '.doc-header { font-size: 14pt; font-weight: bold; color: #003c75; text-align: left; }';
            echo '.doc-sub { font-size: 11pt; font-weight: bold; color: #475569; }';
            echo '.meta-label { font-weight: bold; background-color: #f1f5f9; width: 220px; }';
            echo '.meta-value { font-weight: normal; }';
            echo '.status-box-sah { background-color: #dcfce7; color: #166534; font-weight: bold; border: 1px solid #86efac; text-align: center; }';
            echo '.status-box-belum { background-color: #fee2e2; color: #991b1b; font-weight: bold; border: 1px solid #fca5a5; text-align: center; }';
            echo 'th { background-color: #003c75; color: #ffffff; font-weight: bold; text-align: center; border: 0.5pt solid #cbd5e1; height: 28px; }';
            echo 'td { border: 0.5pt solid #cbd5e1; padding: 4px 8px; vertical-align: middle; }';
            echo '.center { text-align: center; }';
            echo '.badge-hadir { background-color: #dcfce7; color: #166534; font-weight: bold; }';
            echo '.badge-izin { background-color: #fef3c7; color: #92400e; font-weight: bold; }';
            echo '.badge-absen { background-color: #fee2e2; color: #991b1b; font-weight: bold; }';
            echo '.badge-menunggu { background-color: #f1f5f9; color: #475569; }';
            echo '</style></head><body>';

            echo '<table>';
            echo '<tr><td colspan="8" class="doc-header">REKAPITULASI DAFTAR HADIR & KUORUM SIDANG SENAT AKADEMIK</td></tr>';
            echo '<tr><td colspan="8" class="doc-sub">POLITEKNIK NEGERI SEMARANG</td></tr>';
            echo '<tr><td colspan="8"></td></tr>';

            echo '<tr><td class="meta-label">Agenda Sidang</td><td colspan="7" class="meta-value"><b>' . htmlspecialchars($agendaTitle) . '</b></td></tr>';
            echo '<tr><td class="meta-label">Waktu Unduh Rekap</td><td colspan="7" class="meta-value">' . date('d/m/Y H:i:s') . ' WIB</td></tr>';
            echo '<tr><td class="meta-label">Status Kuorum</td><td colspan="7" class="' . ($isKuorum ? 'status-box-sah' : 'status-box-belum') . '">' . $statusKuorum . ' (' . $persenKuorum . '%)</td></tr>';
            echo '<tr><td class="meta-label">Total Anggota Diundang</td><td colspan="7">' . $totalInvited . ' Orang</td></tr>';
            echo '<tr><td class="meta-label">Ambang Kuorum Sah (2/3)</td><td colspan="7">' . $ambangKuorum . ' Orang</td></tr>';
            echo '<tr><td class="meta-label">Jumlah Anggota Hadir</td><td colspan="7" style="color: #166534; font-weight: bold;">' . $presentCount . ' Orang</td></tr>';
            echo '<tr><td class="meta-label">Jumlah Izin Resmi</td><td colspan="7" style="color: #92400e;">' . $izinCount . ' Orang</td></tr>';
            echo '<tr><td class="meta-label">Jumlah Belum / Tidak Hadir</td><td colspan="7" style="color: #991b1b;">' . $tidakHadirCount . ' Orang</td></tr>';
            echo '<tr><td colspan="8"></td></tr>';

            echo '<thead>';
            echo '<tr>';
            echo '<th style="width: 40px;">No</th>';
            echo '<th style="width: 260px;">Nama Anggota Senat</th>';
            echo '<th style="width: 170px;">NIP / ID Pengenal</th>';
            echo '<th style="width: 130px;">Status Kehadiran</th>';
            echo '<th style="width: 140px;">Waktu Presensi</th>';
            echo '<th style="width: 150px;">Metode Presensi</th>';
            echo '<th style="width: 160px;">Status Verifikasi</th>';
            echo '<th style="width: 220px;">Catatan Admin</th>';
            echo '</tr>';
            echo '</thead><tbody>';

            $no = 1;
            foreach ($liveAnggota as $a) {
                $statusClass = match($a['status_kehadiran'] ?? '') {
                    'hadir' => 'badge-hadir',
                    'izin' => 'badge-izin',
                    'tidak_hadir' => 'badge-absen',
                    default => 'badge-menunggu'
                };

                $statusLabel = match($a['status_kehadiran'] ?? '') {
                    'hadir' => 'Hadir',
                    'izin' => 'Izin Resmi',
                    'tidak_hadir' => 'Tidak Hadir',
                    default => 'Menunggu'
                };

                $metodeLabel = match($a['metode_presensi'] ?? '') {
                    'qr_scan' => 'QR Scan Mandiri',
                    'token_input' => 'Input Kode Sesi',
                    'manual_admin' => 'Manual Verifikasi Admin',
                    default => '-'
                };

                $verifikasiLabel = !empty($a['is_edited_by_admin']) ? 'Divalidasi Admin' : 'Otomatis Sistem';
                $waktu = !empty($a['waktu_presensi']) ? $a['waktu_presensi'] . ' WIB' : '-';
                $catatan = $a['catatan_admin'] ?? '-';
                $nip = !empty($a['nim_nip']) ? "'" . $a['nim_nip'] : '-';

                echo '<tr>';
                echo '<td class="center">' . $no++ . '</td>';
                echo '<td><b>' . htmlspecialchars($a['nama'] ?? '-') . '</b></td>';
                echo '<td class="center" style="mso-number-format:\'\@\';">' . htmlspecialchars($nip) . '</td>';
                echo '<td class="center ' . $statusClass . '">' . $statusLabel . '</td>';
                echo '<td class="center">' . $waktu . '</td>';
                echo '<td class="center">' . $metodeLabel . '</td>';
                echo '<td class="center">' . $verifikasiLabel . '</td>';
                echo '<td>' . htmlspecialchars($catatan) . '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
            echo '</body></html>';
        }, $fileName, $headers);
    }

    /**
     * Ekspor Rekapitulasi Daftar Hadir & Kuorum ke CSV
     */
    public function exportCsv(array $liveMeeting, array $liveAnggota, array $liveKuorum): StreamedResponse
    {
        $agendaTitle = $liveMeeting['title'] ?? ($liveMeeting['nama_agenda'] ?? 'Sidang Senat Akademik Polines');
        $slug = Str::slug($agendaTitle);
        $fileName = 'Daftar-Hadir-Kuorum-' . ($slug ?: 'sidang-senat') . '-' . date('Ymd-His') . '.csv';

        $totalInvited = $liveKuorum['total_invited'] ?? count($liveAnggota);
        $presentCount = $liveKuorum['present_count'] ?? 0;
        $izinCount = $liveKuorum['izin_count'] ?? 0;
        $tidakHadirCount = $liveKuorum['tidak_hadir_count'] ?? 0;
        $ambangKuorum = $liveKuorum['ambang_kuorum'] ?? (int) ceil(($totalInvited * 2) / 3);
        $persenKuorum = $liveKuorum['present_percentage'] ?? 0;
        $statusKuorum = ($liveKuorum['is_kuorum'] ?? false) ? 'KUORUM TERPENUHI (SAH)' : 'BELUM MEMENUHI KUORUM';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->streamDownload(function () use (
            $agendaTitle,
            $totalInvited,
            $presentCount,
            $izinCount,
            $tidakHadirCount,
            $ambangKuorum,
            $persenKuorum,
            $statusKuorum,
            $liveAnggota
        ) {
            $output = fopen('php://output', 'w');

            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputs($output, "sep=;\r\n");

            fputcsv($output, ['REKAPITULASI DAFTAR HADIR & KUORUM SIDANG SENAT AKADEMIK'], ';');
            fputcsv($output, ['POLITEKNIK NEGERI SEMARANG'], ';');
            fputcsv($output, [], ';');
            fputcsv($output, ['Agenda Sidang', $agendaTitle], ';');
            fputcsv($output, ['Waktu Pengunduhan', date('d/m/Y H:i:s') . ' WIB'], ';');
            fputcsv($output, ['Status Kuorum', $statusKuorum], ';');
            fputcsv($output, ['Total Anggota Diundang', $totalInvited . ' Orang'], ';');
            fputcsv($output, ['Ambang Batas Kuorum (2/3)', $ambangKuorum . ' Orang'], ';');
            fputcsv($output, ['Jumlah Peserta Hadir', $presentCount . ' Orang (' . $persenKuorum . '%)'], ';');
            fputcsv($output, ['Jumlah Izin Resmi', $izinCount . ' Orang'], ';');
            fputcsv($output, ['Jumlah Tidak Hadir', $tidakHadirCount . ' Orang'], ';');
            fputcsv($output, [], ';');

            fputcsv($output, [
                'No',
                'Nama Anggota Senat',
                'NIP / ID Pengenal',
                'Status Kehadiran',
                'Waktu Presensi',
                'Metode Presensi',
                'Status Verifikasi',
                'Catatan / Keterangan Admin'
            ], ';');

            $no = 1;
            foreach ($liveAnggota as $a) {
                $statusLabel = match($a['status_kehadiran'] ?? '') {
                    'hadir' => 'Hadir',
                    'izin' => 'Izin Resmi',
                    'tidak_hadir' => 'Tidak Hadir',
                    default => 'Menunggu'
                };

                $metodeLabel = match($a['metode_presensi'] ?? '') {
                    'qr_scan' => 'QR Scan Mandiri',
                    'token_input' => 'Input Kode Sesi',
                    'manual_admin' => 'Manual Verifikasi Admin',
                    default => '-'
                };

                $verifikasiLabel = !empty($a['is_edited_by_admin']) ? 'Diedit / Divalidasi Admin' : 'Otomatis Sistem';
                $waktu = !empty($a['waktu_presensi']) ? $a['waktu_presensi'] . ' WIB' : '-';
                $catatan = $a['catatan_admin'] ?? '-';
                $nip = !empty($a['nim_nip']) ? '="' . $a['nim_nip'] . '"' : '-';

                fputcsv($output, [
                    $no++,
                    $a['nama'] ?? '-',
                    $nip,
                    $statusLabel,
                    $waktu,
                    $metodeLabel,
                    $verifikasiLabel,
                    $catatan
                ], ';');
            }

            fclose($output);
        }, $fileName, $headers);
    }
}

