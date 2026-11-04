<?php

namespace App\Livewire\Admin\Traits;

use App\Contracts\MeetingRepositoryInterface;
use App\Repositories\Api\ApiMeetingRepository;
use App\Services\AttendanceExportService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait HasAttendanceAndQr
{
    // State Presensi QR & Kuorum Live
    public ?int $activeMeetingId = null;
    public array $liveMeeting = [];
    public array $liveAnggota = [];
    public array $liveKuorum = [];
    public bool $showQrModal = false;
    public string $qrToken = '';
    public string $sessionCode = '';
    public bool $isPresensiOpen = false;
    public bool $isKuorumLocked = false;
    public string $filterKehadiran = 'semua'; // semua, hadir, izin, menunggu


    /**
     * Polling / Refresh data presensi dan kuorum live
     */
    public function refreshAttendance(): void
    {
        $repo = app(ApiMeetingRepository::class);
        $this->loadLiveAttendanceData($repo, true);
    }


    /**
     * Generate QR Presensi & Kode Sesi Baru
     */
    public function generateQrPresensi(): void
    {
        if (!$this->activeMeetingId) {
            $this->flashMessage = 'Tidak ada sidang aktif yang dapat diaktifkan QR presensinya.';
            return;
        }

        $repo = app(ApiMeetingRepository::class);
        $res = $repo->generateQr($this->activeMeetingId);

        if ($res) {
            $this->qrToken = $res['token_qr'] ?? '';
            $this->sessionCode = $res['kode_sesi'] ?? '';
            $this->isPresensiOpen = true;
            $this->isKuorumLocked = false;
            if (!empty($res['kuorum'])) {
                $this->liveKuorum = $res['kuorum'];
            }
            $this->flashMessage = 'QR Presensi dan Kode Sesi resmi telah diaktifkan!';
            $this->showQrModal = true;
            $this->dispatch('open-qr-modal');
            $this->dispatch('qr-updated', token: $this->qrToken);
        } else {
            $this->flashMessage = 'Gagal menghasilkan token QR dari Backend.';
        }
    }

    /**
     * Buka Modal Tampilan QR
     */
    public function openQrModal(): void
    {
        if (empty($this->qrToken) || !$this->isPresensiOpen) {
            $this->generateQrPresensi();
        } else {
            $this->showQrModal = true;
            $this->dispatch('open-qr-modal');
            $this->dispatch('qr-updated', token: $this->qrToken);
        }
    }

    public function closeQrModal(): void
    {
        $this->showQrModal = false;
    }

    /**
     * Bekukan sesi presensi dan kunci status kuorum
     */
    public function freezePresensi(): void
    {
        if (!$this->activeMeetingId) {
            $this->flashMessage = 'Tidak ada sidang aktif yang dipilih.';
            return;
        }

        $repo = app(ApiMeetingRepository::class);
        $res = $repo->freezePresensi($this->activeMeetingId);

        $this->isPresensiOpen = false;
        $this->isKuorumLocked = true;
        $this->showQrModal = false;
        if ($res && !empty($res['kuorum'])) {
            $this->liveKuorum = $res['kuorum'];
        }
        $this->flashMessage = 'Sesi presensi telah dibekukan. QR Code langsung kedaluwarsa dan status kuorum telah dikunci!';
    }

    /**
     * Validasi manual kehadiran anggota dengan cap Admin
     */
    public function validateMember(int $userId, string $status): void
    {
        if (!$this->activeMeetingId) {
            return;
        }

        $repo = app(ApiMeetingRepository::class);
        $notes = 'Divalidasi Sekretariat (' . ucfirst($status) . ') pada ' . now()->format('H:i');
        $res = $repo->validateMemberAttendance($this->activeMeetingId, $userId, $status, $notes);

        if ($res && isset($res['undangan'])) {
            $undangan = $res['undangan'];
            // Update in-memory liveAnggota directly - avoids extra HTTP roundtrips that cause UI freeze
            foreach ($this->liveAnggota as &$item) {
                if ((int) $item['id_user'] === $userId) {
                    $item['status_kehadiran'] = $undangan['status_kehadiran'];
                    $item['is_edited_by_admin'] = (bool) $undangan['is_edited_by_admin'];
                    $item['catatan_admin'] = $undangan['catatan_admin'];
                    $item['waktu_presensi'] = $undangan['waktu_presensi'];
                    $item['metode_presensi'] = 'manual_admin';
                    break;
                }
            }
            unset($item);

            if (!empty($res['kuorum'])) {
                $this->liveKuorum = $res['kuorum'];
            }

            $label = match($status) {
                'hadir' => 'HADIR',
                'izin' => 'IZIN RESMI',
                'tidak_hadir' => 'TIDAK HADIR',
                default => strtoupper($status)
            };
            $this->flashMessage = "Status kehadiran anggota berhasil diubah menjadi: {$label} (Berstempel Verifikasi Admin).";
        } else {
            $this->flashMessage = "Gagal memperbarui status kehadiran anggota.";
        }
    }

    /**
     * Styling xls export
     */
    public function exportExcel(): StreamedResponse
    {
        if (empty($this->liveAnggota)) {
            $repo = app(ApiMeetingRepository::class);
            $this->loadLiveAttendanceData($repo, true);
        }

        $agendaTitle = $this->liveMeeting['title'] ?? ($this->liveMeeting['nama_agenda'] ?? 'Sidang Senat Akademik Polines');
        $slug = Str::slug($agendaTitle);
        $fileName = 'Daftar-Hadir-Kuorum-' . ($slug ?: 'sidang-senat') . '-' . date('Ymd-His') . '.xls';

        $totalInvited = $this->liveKuorum['total_invited'] ?? count($this->liveAnggota);
        $presentCount = $this->liveKuorum['present_count'] ?? 0;
        $izinCount = $this->liveKuorum['izin_count'] ?? 0;
        $tidakHadirCount = $this->liveKuorum['tidak_hadir_count'] ?? 0;
        $ambangKuorum = $this->liveKuorum['ambang_kuorum'] ?? (int) ceil(($totalInvited * 2) / 3);
        $persenKuorum = $this->liveKuorum['present_percentage'] ?? 0;
        $isKuorum = (bool) ($this->liveKuorum['is_kuorum'] ?? false);
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
            $statusKuorum
        ) {
            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
            echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
            echo '<style>';
            echo 'body { font-family: "Segoe UI", Calibri, Arial, sans-serif; font-size: 11pt; color: #1e293b; }';
            echo 'table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }';
            echo 'th { background-color: #0b3a75; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #94a3b8; padding: 10px 8px; vertical-align: middle; font-size: 11pt; }';
            echo 'td { border: 1px solid #cbd5e1; padding: 8px 10px; vertical-align: middle; font-size: 10.5pt; }';
            echo '.text { mso-number-format:"\@"; }';
            echo '.center { text-align: center; }';
            echo '.bold { font-weight: bold; }';
            echo '.doc-title { font-size: 14pt; font-weight: bold; color: #0b3a75; text-align: left; }';
            echo '.doc-sub { font-size: 11pt; font-weight: bold; color: #475569; text-align: left; }';
            echo '.meta-lbl { background-color: #f1f5f9; font-weight: bold; width: 230px; border: 1px solid #cbd5e1; }';
            echo '.meta-val { border: 1px solid #cbd5e1; }';
            echo '.badge-hadir { background-color: #dcfce7; color: #15803d; font-weight: bold; text-align: center; }';
            echo '.badge-izin { background-color: #fef9c3; color: #854d0e; font-weight: bold; text-align: center; }';
            echo '.badge-absen { background-color: #fee2e2; color: #b91c1c; font-weight: bold; text-align: center; }';
            echo '.status-sah { background-color: #dcfce7; color: #15803d; font-weight: bold; }';
            echo '.status-belum { background-color: #fee2e2; color: #b91c1c; font-weight: bold; }';
            echo '</style>';
            echo '</head><body>';

            // Kop Dokumen & Ringkasan Kuorum
            echo '<table>';
            echo '<tr><td colspan="8" class="doc-title" style="border:none;">REKAPITULASI DAFTAR HADIR & KUORUM SIDANG SENAT AKADEMIK</td></tr>';
            echo '<tr><td colspan="8" class="doc-sub" style="border:none;">POLITEKNIK NEGERI SEMARANG</td></tr>';
            echo '<tr><td colspan="8" style="border:none; height:12px;"></td></tr>';

            echo '<tr><td class="meta-lbl">Agenda Sidang</td><td colspan="7" class="meta-val bold">' . htmlspecialchars($agendaTitle) . '</td></tr>';
            echo '<tr><td class="meta-lbl">Waktu Pengunduhan</td><td colspan="7" class="meta-val">' . date('d/m/Y H:i:s') . ' WIB</td></tr>';
            echo '<tr><td class="meta-lbl">Status Kuorum</td><td colspan="7" class="meta-val ' . ($isKuorum ? 'status-sah' : 'status-belum') . '">' . $statusKuorum . '</td></tr>';
            echo '<tr><td class="meta-lbl">Total Anggota Diundang</td><td colspan="7" class="meta-val">' . $totalInvited . ' Orang</td></tr>';
            echo '<tr><td class="meta-lbl">Ambang Batas Kuorum (2/3)</td><td colspan="7" class="meta-val">' . $ambangKuorum . ' Orang</td></tr>';
            echo '<tr><td class="meta-lbl">Jumlah Peserta Hadir</td><td colspan="7" class="meta-val bold">' . $presentCount . ' Orang (' . $persenKuorum . '%)</td></tr>';
            echo '<tr><td class="meta-lbl">Jumlah Izin Resmi</td><td colspan="7" class="meta-val">' . $izinCount . ' Orang</td></tr>';
            echo '<tr><td class="meta-lbl">Jumlah Tidak Hadir</td><td colspan="7" class="meta-val">' . $tidakHadirCount . ' Orang</td></tr>';
            echo '<tr><td colspan="8" style="border:none; height:16px;"></td></tr>';
            echo '</table>';

            // Tabel Presensi Anggota
            echo '<table>';
            echo '<thead>';
            echo '<tr>';
            echo '<th style="width: 45px;">No</th>';
            echo '<th style="width: 260px;">Nama Anggota Senat</th>';
            echo '<th style="width: 190px;">NIP / ID Pengenal</th>';
            echo '<th style="width: 140px;">Status Kehadiran</th>';
            echo '<th style="width: 130px;">Waktu Presensi</th>';
            echo '<th style="width: 160px;">Metode Presensi</th>';
            echo '<th style="width: 170px;">Status Verifikasi</th>';
            echo '<th style="width: 220px;">Catatan / Keterangan</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            $no = 1;
            foreach ($this->liveAnggota as $a) {
                $rawStatus = $a['status_kehadiran'] ?? '';
                $statusLabel = match($rawStatus) {
                    'hadir' => 'Hadir',
                    'izin' => 'Izin Resmi',
                    'tidak_hadir' => 'Tidak Hadir',
                    default => 'Menunggu'
                };
                $statusClass = match($rawStatus) {
                    'hadir' => 'badge-hadir',
                    'izin' => 'badge-izin',
                    'tidak_hadir' => 'badge-absen',
                    default => 'center'
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
                $nip = $a['nim_nip'] ?? '-';

                echo '<tr>';
                echo '<td class="center">' . ($no++) . '</td>';
                echo '<td class="bold">' . htmlspecialchars($a['nama'] ?? '-') . '</td>';
                echo '<td class="text center">' . htmlspecialchars($nip) . '</td>';
                echo '<td class="' . $statusClass . '">' . htmlspecialchars($statusLabel) . '</td>';
                echo '<td class="center">' . htmlspecialchars($waktu) . '</td>';
                echo '<td class="center">' . htmlspecialchars($metodeLabel) . '</td>';
                echo '<td>' . htmlspecialchars($verifikasiLabel) . '</td>';
                echo '<td>' . htmlspecialchars($catatan) . '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
            echo '</body></html>';
        }, $fileName, $headers);
    }

    /**
     * Ekspor Rekapitulasi Daftar Hadir & Kuorum ke CSV (Kompatibel dengan Microsoft Excel & Regional Indonesia)
     */
    public function exportCsv(): StreamedResponse
    {
        // Pastikan data live terisi
        if (empty($this->liveAnggota)) {
            $repo = app(ApiMeetingRepository::class);
            $this->loadLiveAttendanceData($repo, true);
        }

        $agendaTitle = $this->liveMeeting['title'] ?? ($this->liveMeeting['nama_agenda'] ?? 'Sidang Senat Akademik Polines');
        $slug = Str::slug($agendaTitle);
        $fileName = 'Daftar-Hadir-Kuorum-' . ($slug ?: 'sidang-senat') . '-' . date('Ymd-His') . '.csv';

        $totalInvited = $this->liveKuorum['total_invited'] ?? count($this->liveAnggota);
        $presentCount = $this->liveKuorum['present_count'] ?? 0;
        $izinCount = $this->liveKuorum['izin_count'] ?? 0;
        $tidakHadirCount = $this->liveKuorum['tidak_hadir_count'] ?? 0;
        $ambangKuorum = $this->liveKuorum['ambang_kuorum'] ?? (int) ceil(($totalInvited * 2) / 3);
        $persenKuorum = $this->liveKuorum['present_percentage'] ?? 0;
        $statusKuorum = ($this->liveKuorum['is_kuorum'] ?? false) ? 'KUORUM TERPENUHI (SAH)' : 'BELUM MEMENUHI KUORUM';

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
            $statusKuorum
        ) {
            $output = fopen('php://output', 'w');

            // BOM UTF-8 agar Microsoft Excel otomatis membaca karakter khusus secara akurat
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Instruksi eksplisit untuk Microsoft Excel agar membaca delimiter titik-koma (;)
            fputs($output, "sep=;\r\n");

            // Header Informasi Dokumen
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

            // Header Kolom Tabel
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

            // Baris Data Anggota
            $no = 1;
            foreach ($this->liveAnggota as $a) {
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
