<?php

namespace App\Services;

use App\Mail\MeetingInvitationMail;
use App\Support\SenatDirectory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class MeetingBroadcastService
{
    /**
     * Tentukan ketua dan jabatan penandatangan berdasarkan penyelenggara sidang.
     *
     * @return array{title: string, name: string}
     */
    public function resolveLeaderForOrganizer(string $organizer): array
    {
        $organizerLower = strtolower($organizer);

        if (str_contains($organizerLower, 'badan pekerja') || str_contains($organizerLower, 'bp')) {
            return [
                'title' => 'Ketua Badan Pekerja',
                'name' => 'ANDI SETIAWAN , S.E., M.M.',
            ];
        }

        if (str_contains($organizerLower, 'komisi 1') || str_contains($organizerLower, 'komisi i')) {
            return [
                'title' => 'Ketua Komisi I',
                'name' => 'TRI YUDANTORO , S.Kom., M.Kom.',
            ];
        }

        if (str_contains($organizerLower, 'komisi 2') || str_contains($organizerLower, 'komisi ii')) {
            return [
                'title' => 'Ketua Komisi II',
                'name' => 'Dr. JUMI , S.Kom., M.Kom.',
            ];
        }

        if (str_contains($organizerLower, 'komisi 3') || str_contains($organizerLower, 'komisi iii')) {
            return [
                'title' => 'Ketua Komisi III',
                'name' => 'Ir. RILES MELVY WATTIMENA , M.T., IPP.',
            ];
        }

        if (str_contains($organizerLower, 'komisi 4') || str_contains($organizerLower, 'komisi iv')) {
            return [
                'title' => 'Ketua Komisi IV',
                'name' => 'Dr. AMIN SUHARJONO , S.T., M.T.',
            ];
        }

        if (str_contains($organizerLower, 'komisi 5') || str_contains($organizerLower, 'komisi v')) {
            return [
                'title' => 'Ketua Komisi V',
                'name' => 'Drs. AHMAD MUSTOFA , M.Ag.',
            ];
        }

        return [
            'title' => 'Ketua Senat',
            'name' => 'Dr. Sukamto, S.Kom., M.T.',
        ];
    }

    /**
     * Format tanggal rapat dalam bahasa Indonesia (contoh: Rabu, 11 November 2026).
     */
    public function formatIndonesianDate(?string $dateStr): string
    {
        try {
            if (empty($dateStr)) {
                return Carbon::now()->locale('id')->isoFormat('dddd, D MMMM YYYY');
            }
            return Carbon::parse($dateStr)->locale('id')->isoFormat('dddd, D MMMM YYYY');
        } catch (\Throwable) {
            return $dateStr ?? '-';
        }
    }

    /**
     * Format waktu sidang sesuai format broadcast (contoh: 19.30 WIB s.d selesai atau 09:00 - 12:00 WIB).
     */
    public function formatBroadcastTime(string $timeStr): string
    {
        $timeStr = trim($timeStr);
        if (str_contains(strtolower($timeStr), 'selesai')) {
            // Misal: "19:00 WIB - Selesai" -> "19.00 WIB s.d selesai"
            $parts = explode(' ', $timeStr);
            $jam = str_replace(':', '.', $parts[0] ?? '19.00');
            return "{$jam} WIB s.d selesai";
        }

        return str_replace(':', '.', $timeStr);
    }

    /**
     * Cari alamat email resmi anggota/dosen.
     */
    public function resolveEmail(array|string $recipient): string
    {
        if (is_string($recipient)) {
            $name = $recipient;
            $nip = '';
        } else {
            if (!empty($recipient['email'])) {
                return strtolower(trim($recipient['email']));
            }
            $name = $recipient['nama'] ?? ($recipient['name'] ?? '');
            $nip = $recipient['nim_nip'] ?? ($recipient['nip'] ?? '');
        }

        foreach (SenatDirectory::LECTURER_DATA as $lecturerName => $data) {
            if (!empty($nip) && ($data['nip'] ?? '') === $nip) {
                return $data['email'];
            }
            if (Str::contains(strtolower($lecturerName), strtolower($name)) || Str::contains(strtolower($name), strtolower($lecturerName))) {
                return $data['email'];
            }
        }

        $clean = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($name));
        return ($clean ?: 'anggota.senat') . '@polines.ac.id';
    }

    /**
     * Susun teks broadcast email personal sesuai format yang diminta.
     */
    public function generateMessageText(array $meeting, array|string $recipient, bool $isUpdate = false): string
    {
        $recipientName = is_array($recipient) ? ($recipient['nama'] ?? ($recipient['name'] ?? 'Bapak/Ibu Anggota Senat')) : $recipient;
        $organizer = $meeting['organizer'] ?? 'Senat';
        $leader = $this->resolveLeaderForOrganizer($organizer);

        $tanggalStr = $this->formatIndonesianDate($meeting['date'] ?? null);
        $waktuStr = $this->formatBroadcastTime($meeting['time'] ?? '09:00 WIB s.d selesai');
        $tempatStr = $meeting['room'] ?? 'Ruang Senat Akademik';
        $agendaStr = $meeting['title'] ?? 'Rapat Senat Akademik';

        $updatePrefix = $isUpdate ? "[PEMBERITAHUAN PERUBAHAN JADWAL SIDANG]\n\n" : "";

        return "{$updatePrefix}POLINES: Yth. *{$recipientName}*\n"
            . "Politeknik Negeri Semarang\n\n"
            . "Mengharap kehadiran Bapak/Ibu/Saudara pada :\n\n"
            . "Hari, Tanggal : {$tanggalStr}\n"
            . "Pukul : {$waktuStr}\n"
            . "Tempat : {$tempatStr}\n"
            . "Agenda : {$agendaStr}\n\n"
            . "Atas perhatian dan kehadirannya, kami sampaikan terima kasih.\n\n"
            . "{$leader['title']},\n"
            . "*{$leader['name']}*";
    }

    /**
     * Kirim broadcast email ke seluruh daftar anggota yang diundang.
     *
     * @param array $meeting Data rapat (title, organizer, date, time, room)
     * @param array $recipients Daftar anggota ([['nama' => '...', 'nim_nip' => '...'], ...])
     * @param bool $isUpdate True jika berupa pembaruan/perubahan jadwal
     * @return array{total: int, sent: int, sample_message: string, logs: array}
     */
    public function broadcast(array $meeting, array $recipients, bool $isUpdate = false): array
    {
        $total = count($recipients);
        $sent = 0;
        $logs = [];
        $sampleMessage = '';

        $subjectPrefix = $isUpdate ? '[PEMBARUAN JADWAL] ' : '[UNDANGAN SIDANG SENAT] ';
        $subject = $subjectPrefix . ($meeting['title'] ?? 'Agenda Sidang Senat Polines');

        $enrichedMeeting = array_merge($meeting, [
            'date_formatted' => $this->formatIndonesianDate($meeting['date'] ?? null),
            'leader' => $this->resolveLeaderForOrganizer($meeting['organizer'] ?? 'Senat'),
        ]);

        foreach ($recipients as $idx => $r) {
            $name = is_array($r) ? ($r['nama'] ?? ($r['name'] ?? 'Anggota Senat')) : $r;
            $email = $this->resolveEmail($r);
            $body = $this->generateMessageText($meeting, $r, $isUpdate);

            if ($idx === 0) {
                $sampleMessage = $body;
            }

            try {
                Mail::to($email, $name)->send(new MeetingInvitationMail(
                    bodyContent: $body,
                    meetingSubject: $subject,
                    meeting: $enrichedMeeting,
                    recipient: is_array($r) ? $r : ['nama' => $r],
                    isUpdate: $isUpdate,
                ));
                $sent++;
                $logs[] = [
                    'nama'   => $name,
                    'email'  => $email,
                    'status' => 'Terkirim',
                ];
            } catch (\Throwable $e) {
                Log::warning("Gagal mengirim broadcast email sidang ke {$email}: " . $e->getMessage());
                $logs[] = [
                    'nama'   => $name,
                    'email'  => $email,
                    'status' => 'Gagal: ' . $e->getMessage(),
                ];
                // ⚠️ JANGAN increment $sent — email benar-benar tidak terkirim
            }
        }

        return [
            'total' => $total,
            'sent' => $sent,
            'sample_message' => $sampleMessage,
            'logs' => $logs,
        ];
    }
}
