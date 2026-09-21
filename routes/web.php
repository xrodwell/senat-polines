<?php

use App\Http\Controllers\PortalController;
use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicController::class, 'home'])->name('public.home');
Route::get('/profil', [PublicController::class, 'profile'])->name('public.profile');
Route::get('/regulasi', [PublicController::class, 'regulations'])->name('public.regulations');
Route::get('/aspirasi', [PublicController::class, 'aspirations'])->name('public.aspirations');
Route::get('/aspirasi/track', [PublicController::class, 'trackAspiration'])->name('public.aspirations.track');
Route::get('/aspirasi/track/{ticket}', [PublicController::class, 'trackAspiration'])->name('public.aspirations.show');
Route::get('/arsip', [PublicController::class, 'archives'])->name('public.archives');

Route::prefix('portal')->group(function (): void {
    Route::get('/attendance', [PortalController::class, 'memberAttendance'])->name('portal.attendance');
    Route::get('/session', [PortalController::class, 'adminMeetingSession'])->name('portal.session');
});
