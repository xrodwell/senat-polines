<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class PortalAuthMiddleware
{
    public const REAUTH_MAX_AGE_SECONDS = 259200; // 3 hari (72 jam)

    /** Jeda minimum verifikasi token ke backend (detik). */
    public const TOKEN_CHECK_TTL_SECONDS = 300;

    /**
     * Session keys cleared when portal authentication is invalidated.
     *
     * @var list<string>
     */
    public const SESSION_AUTH_KEYS = [
        'auth_token',
        'auth_user',
        'auth_user_id',
        'auth_user_name',
        'auth_user_role',
        'auth_user_nip',
        'auth_user_email',
        'auth_user_foto',
        'auth_verified_at',
        'auth_token_checked_at',
        'impersonator',
    ];

    /**
     * Ensure user is authenticated into the senate portal session and still within 3-day window.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!session()->has('auth_user') || !session()->has('auth_token')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Silakan login terlebih dahulu untuk mengakses portal.',
                ], 401);
            }

            return redirect()->route('portal.login')
                ->withErrors(['login' => 'Silakan login terlebih dahulu untuk mengakses portal internal Senat.']);
        }

        if (self::isSessionVerificationExpired()) {
            self::clearAuthSession($request);

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Sesi verifikasi 3 hari Anda telah berakhir. Silakan login kembali.',
                ], 401);
            }

            return redirect()->route('portal.login')
                ->with('info', 'Sesi login Anda telah melewati 3 hari. Silakan masuk kembali untuk verifikasi ulang akun.');
        }

        if (self::isBackendTokenInvalid($request)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Sesi login Anda tidak lagi valid. Silakan login kembali.',
                ], 401);
            }

            return redirect()->route('portal.login')
                ->withErrors(['login' => 'Sesi login Anda tidak lagi valid. Silakan masuk kembali.']);
        }

        return $next($request);
    }

    /**
     * Verifikasi berkala token portal ke backend (/me).
     * 401 = token dicabut/kedaluwarsa -> invalidate sesi.
     * 5xx/timeout = outage backend -> JANGAN logout pengguna.
     */
    private static function isBackendTokenInvalid(Request $request): bool
    {
        $checkedAt = (int) session('auth_token_checked_at', 0);
        if (time() - $checkedAt < self::TOKEN_CHECK_TTL_SECONDS) {
            return false;
        }

        $base = rtrim((string) config('services.backend_api.url', 'http://127.0.0.1:8001'), '/');
        if (!str_ends_with($base, '/api')) {
            $base .= '/api';
        }
        try {
            $response = Http::timeout(3)->acceptJson()
                ->withToken((string) session('auth_token'))
                ->get($base . '/me');
        } catch (\Throwable $e) {
            return false;
        }

        if ($response->status() === 401) {
            self::clearAuthSession($request);
            return true;
        }

        if ($response->successful()) {
            session(['auth_token_checked_at' => time()]);
        }

        return false;
    }

    public static function isSessionVerificationExpired(): bool
    {
        $verifiedAt = session('auth_verified_at');
        if ($verifiedAt === null) {
            return false;
        }

        return (now()->timestamp - (int) $verifiedAt) >= self::REAUTH_MAX_AGE_SECONDS;
    }

    public static function clearAuthSession(Request $request): void
    {
        $request->session()->forget(self::SESSION_AUTH_KEYS);
        $request->session()->regenerateToken();
    }
}
