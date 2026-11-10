<?php

namespace App\Http\Controllers\Portal\Traits;

use Illuminate\View\View;

trait HasSecretariatPortal
{
    /**
     * 13A — Dashboard Sekretariat · Ringkasan
     */
    public function secretariatDashboard(): View
    {
        return view('portal.secretariat.dashboard', [
            'stats' => $this->meetingStats(),
            'meetings' => $this->meetings->all(),
            'aspirations' => $this->aspirations->all(),
            'archives' => $this->archives->all(),
            'regulations' => $this->regulations->all(),
        ]);
    }

    /**
     * 13B — Sidang & Agenda
     */
    public function secretariatSessions(): View
    {
        return view('portal.secretariat.sessions', [
            'stats' => $this->meetingStats(),
            'meetings' => $this->meetings->all(),
        ]);
    }

    /**
     * 13D — Presensi & QR
     */
    public function secretariatAttendance(): View
    {
        return view('portal.secretariat.attendance', [
            'stats' => $this->meetingStats(),
            'meetings' => $this->meetings->all(),
        ]);
    }

    /**
     * 13C — Notulensi & Tindak Lanjut
     */
    public function secretariatMinutes(): View
    {
        return view('portal.secretariat.minutes', [
            'meetings' => $this->meetings->all(),
            'archives' => $this->archives->all(),
        ]);
    }

    /**
     * 13E — E-Voting
     */
    public function secretariatVoting(): View
    {
        return view('portal.secretariat.voting', [
            'stats' => $this->meetingStats(),
            'meetings' => $this->meetings->all(),
        ]);
    }

    /**
     * 13F — Aspirasi & RUU Kampus
     */
    public function secretariatAspirations(): View
    {
        return view('portal.secretariat.aspirations', [
            'aspirations' => $this->aspirations->all(),
        ]);
    }

    /**
     * 13G — Produk, Arsip & Berita
     */
    public function secretariatPublications(): View
    {
        return view('portal.secretariat.publications', [
            'regulations' => $this->regulations->all(),
            'archives' => $this->archives->all(),
            'posts' => $this->posts->all(),
        ]);
    }
}

