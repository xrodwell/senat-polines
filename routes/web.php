<?php

use App\Http\Controllers\PortalController;
use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Portal Publik — Figma "01 — Portal Publik" (section 2293:1246)
|--------------------------------------------------------------------------
*/
Route::get('/', [PublicController::class, 'home'])->name('public.home');

// 02A / 02B / 02C — Profil Senat
Route::get('/profil', [PublicController::class, 'profile'])->name('public.profile');
Route::get('/profil/tugas-wewenang', [PublicController::class, 'tasks'])->name('public.profile.tasks');
Route::get('/profil/struktur-organisasi', [PublicController::class, 'structure'])->name('public.profile.structure');

// 03 / 04 — Direktori Komisi & Anggota
Route::get('/direktori-komisi', [PublicController::class, 'directory'])->name('public.directory');
Route::get('/direktori-komisi/{slug}', [PublicController::class, 'member'])->name('public.directory.member');

// 05A / 05B / 05C — Produk Hukum
Route::get('/regulasi', [PublicController::class, 'regulations'])->name('public.regulations');
Route::get('/regulasi/keputusan-senat', [PublicController::class, 'decrees'])->name('public.regulations.decrees');
Route::get('/regulasi/standar-pedoman', [PublicController::class, 'standards'])->name('public.regulations.standards');

// 06 / 09 — Kanal Aspirasi & Pelacak
Route::get('/aspirasi', [PublicController::class, 'aspirations'])->name('public.aspirations');
Route::get('/aspirasi/track', [PublicController::class, 'trackAspiration'])->name('public.aspirations.track');
Route::get('/aspirasi/track/{ticket}', [PublicController::class, 'trackAspiration'])->name('public.aspirations.show');

// 11A / 11B — Arsip
Route::get('/arsip', [PublicController::class, 'archives'])->name('public.archives');
Route::get('/arsip/laporan-tahunan', [PublicController::class, 'archivesReports'])->name('public.archives.reports');

// 14 / 14B — Berita & Warta
Route::get('/berita', [PublicController::class, 'news'])->name('public.news');
Route::get('/berita/{slug}', [PublicController::class, 'newsShow'])->name('public.news.show');

/*
|--------------------------------------------------------------------------
| Portal Internal — Figma section 2090:2 (anggota 12x, sekretariat 13x)
|--------------------------------------------------------------------------
*/
Route::prefix('portal')->group(function (): void {
    // 11 — Login Portal
    Route::get('/login', [PortalController::class, 'login'])->name('portal.login');

    // 12 — Anggota Senat
    Route::get('/dashboard', [PortalController::class, 'dashboard'])->name('portal.dashboard');
    Route::get('/agenda', [PortalController::class, 'agenda'])->name('portal.agenda');
    Route::get('/notulensi', [PortalController::class, 'minutes'])->name('portal.minutes');
    Route::get('/e-voting', [PortalController::class, 'voting'])->name('portal.voting');
    Route::get('/aspirasi', [PortalController::class, 'aspirations'])->name('portal.aspirations');
    Route::get('/dokumen', [PortalController::class, 'documents'])->name('portal.documents');
    Route::get('/profil', [PortalController::class, 'profile'])->name('portal.profile');

    // Layar operasional lama (dipertahankan)
    Route::get('/attendance', [PortalController::class, 'memberAttendance'])->name('portal.attendance');
    Route::get('/session', [PortalController::class, 'adminMeetingSession'])->name('portal.session');

    // 13 — Sekretariat
    Route::prefix('sekretariat')->group(function (): void {
        Route::get('/dashboard', [PortalController::class, 'secretariatDashboard'])->name('portal.secretariat.dashboard');
        Route::get('/sidang', [PortalController::class, 'secretariatSessions'])->name('portal.secretariat.sessions');
        Route::get('/presensi', [PortalController::class, 'secretariatAttendance'])->name('portal.secretariat.attendance');
        Route::get('/notulensi', [PortalController::class, 'secretariatMinutes'])->name('portal.secretariat.minutes');
        Route::get('/e-voting', [PortalController::class, 'secretariatVoting'])->name('portal.secretariat.voting');
        Route::get('/aspirasi', [PortalController::class, 'secretariatAspirations'])->name('portal.secretariat.aspirations');
        Route::get('/publikasi', [PortalController::class, 'secretariatPublications'])->name('portal.secretariat.publications');
    });
});

// API Proxy Frontend -> Backend untuk Chatbot Aspirasi
Route::post('/api/chatbot/send', [\App\Http\Controllers\ChatbotController::class, 'send'])->name('api.chatbot.send');
