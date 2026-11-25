<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MeetingInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $bodyContent,
        public string $meetingSubject,
        public ?array $meeting = null,
        public ?array $recipient = null,
        public bool $isUpdate = false,
    ) {}

    /**
     * Susun template email resmi, elegan, dan profesional.
     */
    public function build(): static
    {
        $recipientName = $this->recipient['nama'] ?? ($this->recipient['name'] ?? 'Bapak/Ibu Anggota Senat');
        $title = $this->meeting['title'] ?? ($this->meeting['nama_agenda'] ?? 'Sidang Senat Akademik');
        $organizer = $this->meeting['organizer'] ?? ($this->meeting['penyelenggara'] ?? 'Senat Akademik');
        $type = $this->meeting['type'] ?? ($this->meeting['jenis_rapat'] ?? 'Pleno');
        $date = $this->meeting['date_formatted'] ?? ($this->meeting['date'] ?? '-');
        $time = $this->meeting['time'] ?? '09:00 - 12:00 WIB';
        $room = $this->meeting['room'] ?? ($this->meeting['tempat'] ?? 'Ruang Sidang Senat Polines');
        $leader = $this->meeting['leader'] ?? [
            'title' => 'Ketua Senat',
            'name'  => 'Dr. Sukamto, S.Kom., M.T.',
        ];

        $portalUrl = config('app.url', 'http://localhost:8000') . '/portal/login';
        $statusBadge = $this->isUpdate ? 'PEMBARUAN JADWAL' : 'SURAT UNDANGAN RESMI';
        $badgeBg = $this->isUpdate ? '#d97706' : '#0284c7';

        $html = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$this->meetingSubject}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b; -webkit-font-smoothing: antialiased;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #f1f5f9; padding: 32px 16px;">
        <tr>
            <td align="center">
                <!-- Container Card -->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width: 620px; background-color: #ffffff; border-radius: 14px; overflow: hidden; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.08), 0 8px 10px -6px rgba(15, 23, 42, 0.04); border: 1px solid #e2e8f0;">
                    
                    <!-- Header Banner -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #092540 0%, #0f3d6c 100%); padding: 32px 32px 28px 32px; text-align: left; border-bottom: 4px solid #f59e0b;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td>
                                        <p style="margin: 0; font-size: 11px; font-weight: 700; color: #93c5fd; text-transform: uppercase; letter-spacing: 0.12em;">KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI</p>
                                        <h1 style="margin: 4px 0 0 0; font-size: 19px; font-weight: 800; color: #ffffff; letter-spacing: -0.01em;">SENAT AKADEMIK POLINES</h1>
                                        <p style="margin: 2px 0 0 0; font-size: 12px; font-weight: 500; color: #cbd5e1;">Politeknik Negeri Semarang — Periode 2026–2030</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Status Pill & Title -->
                    <tr>
                        <td style="padding: 28px 32px 12px 32px;">
                            <span style="display: inline-block; background-color: {$badgeBg}; color: #ffffff; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; padding: 4px 12px; border-radius: 9999px;">
                                {$statusBadge}
                            </span>
                            <h2 style="margin: 14px 0 6px 0; font-size: 20px; line-height: 1.35; font-weight: 700; color: #0f172a;">
                                Undangan Sidang Senat Akademik
                            </h2>
                            <p style="margin: 0; font-size: 13.5px; color: #475569; line-height: 1.5;">
                                Yth. <strong style="color: #0f172a;">{$recipientName}</strong><br>
                                <span style="font-size: 12px; color: #64748b;">Anggota Senat Akademik Politeknik Negeri Semarang</span>
                            </p>
                        </td>
                    </tr>

                    <!-- Intro Text -->
                    <tr>
                        <td style="padding: 8px 32px 18px 32px;">
                            <p style="margin: 0; font-size: 13px; line-height: 1.6; color: #334155;">
                                Dengan hormat, mengharap kehadiran Bapak/Ibu/Saudara dalam agenda persidangan Senat Akademik yang diselenggarakan oleh <strong>{$organizer}</strong> dengan rincian sebagai berikut:
                            </p>
                        </td>
                    </tr>

                    <!-- Detailed Meeting Box Card -->
                    <tr>
                        <td style="padding: 0 32px 24px 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden;">
                                <tr>
                                    <td style="padding: 14px 18px; border-bottom: 1px solid #e2e8f0; width: 140px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em;">
                                        📅 Hari, Tanggal
                                    </td>
                                    <td style="padding: 14px 18px; border-bottom: 1px solid #e2e8f0; font-size: 13.5px; font-weight: 700; color: #0f172a;">
                                        {$date}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 14px 18px; border-bottom: 1px solid #e2e8f0; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em;">
                                        ⏰ Waktu
                                    </td>
                                    <td style="padding: 14px 18px; border-bottom: 1px solid #e2e8f0; font-size: 13.5px; font-weight: 600; color: #0f172a;">
                                        {$time}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 14px 18px; border-bottom: 1px solid #e2e8f0; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em;">
                                        📍 Ruangan / Tempat
                                    </td>
                                    <td style="padding: 14px 18px; border-bottom: 1px solid #e2e8f0; font-size: 13.5px; font-weight: 600; color: #0284c7;">
                                        {$room}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 14px 18px; border-bottom: 1px solid #e2e8f0; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em;">
                                        📋 Agenda Sidang
                                    </td>
                                    <td style="padding: 14px 18px; border-bottom: 1px solid #e2e8f0; font-size: 13.5px; font-weight: 700; color: #0f172a; line-height: 1.45;">
                                        {$title}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 14px 18px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em;">
                                        🏛️ Penyelenggara
                                    </td>
                                    <td style="padding: 14px 18px; font-size: 13px; font-weight: 600; color: #334155;">
                                        {$organizer} <span style="font-size: 11px; font-weight: normal; color: #64748b;">({$type})</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- CTA Button -->
                    <tr>
                        <td align="center" style="padding: 4px 32px 28px 32px;">
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="border-radius: 8px; background-color: #0369a1;">
                                        <a href="{$portalUrl}" target="_blank" style="display: inline-block; padding: 13px 28px; font-size: 13px; font-weight: 700; color: #ffffff; text-decoration: none; border-radius: 8px; letter-spacing: 0.02em;">
                                            Masuk Portal Senat &amp; Lihat Agenda ›
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin: 10px 0 0 0; font-size: 11px; color: #64748b;">
                                Presensi digital (QR Code &amp; Token) akan diaktifkan saat sidang dibuka oleh Sekretariat.
                            </p>
                        </td>
                    </tr>

                    <!-- Signature & Closing -->
                    <tr>
                        <td style="padding: 20px 32px 28px 32px; border-top: 1px solid #e2e8f0; background-color: #fafbfd;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td>
                                        <p style="margin: 0; font-size: 12.5px; color: #475569; line-height: 1.6;">
                                            Atas perhatian dan kehadiran Bapak/Ibu/Saudara, kami sampaikan terima kasih.
                                        </p>
                                        <div style="margin-top: 18px;">
                                            <p style="margin: 0; font-size: 11.5px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em;">{$leader['title']},</p>
                                            <p style="margin: 4px 0 0 0; font-size: 15px; font-weight: 800; color: #0f172a;">{$leader['name']}</p>
                                            <p style="margin: 2px 0 0 0; font-size: 11.5px; color: #64748b;">Senat Akademik Politeknik Negeri Semarang</p>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer Note -->
                    <tr>
                        <td style="background-color: #0f172a; padding: 20px 32px; text-align: center;">
                            <p style="margin: 0; font-size: 11px; color: #94a3b8; line-height: 1.6;">
                                © 2026 Senat Akademik Politeknik Negeri Semarang.<br>
                                Jl. Prof. H. Soedarto, S.H., Tembalang, Semarang 50275<br>
                                <span style="font-size: 10px; color: #64748b;">Email ini dikirim secara otomatis oleh Sistem Informasi Senat Akademik. Mohon tidak membalas email ini.</span>
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;

        return $this
            ->subject($this->meetingSubject)
            ->html($html)
            ->text('emails.meeting-invitation-text');
    }
}
