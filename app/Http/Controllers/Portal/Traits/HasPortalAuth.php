<?php

namespace App\Http\Controllers\Portal\Traits;

use App\Http\Middleware\PortalAuthMiddleware;
use App\Services\GoogleOAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

trait HasPortalAuth
{
    private const LOGIN_FAILURE_TTL_MINUTES = 15;
    private const TIER1_FAILURE_THRESHOLD = 3;
    private const TIER1_LOCK_SECONDS = 30;
    private const TIER2_FAILURE_THRESHOLD = 5;
    private const TIER2_LOCK_SECONDS = 120;

    /**
     * Halaman Login Portal
     */
    public function login(GoogleOAuthService $google): View|RedirectResponse
    {
        if (session()->has('auth_user')) {
            if (session('auth_user_role') === 'admin') {
                return redirect()->route('admin.preview');
            }

            return redirect()->route('portal.dashboard');
        }

        $lockout = session('portal_login_lockout');
        $lockoutSeconds = 0;
        $lockoutIdentifier = '';

        if (is_array($lockout) && ($lockout['until'] ?? 0) > now()->timestamp) {
            $lockoutSeconds = max(0, (int) $lockout['until'] - now()->timestamp);
            $lockoutIdentifier = (string) ($lockout['identifier'] ?? '');
        } else {
            session()->forget('portal_login_lockout');
        }

        return view('portal.login', [
            'googleConfigured' => $google->isConfigured(),
            'lockoutSeconds' => $lockoutSeconds,
            'lockoutIdentifier' => $lockoutIdentifier,
        ]);
    }

    /**
     * Proses Autentikasi Login Portal (dengan timeout bertingkat jika salah kata sandi)
     */
    public function authenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'identifier' => 'required|string',
            'password'   => 'required|string',
        ], [
            'identifier.required' => 'Email atau NIP/NIM wajib diisi.',
            'password.required'   => 'Kata sandi wajib diisi.',
        ]);

        $identifier = trim((string) $credentials['identifier']);
        $throttleKey = $this->loginThrottleKey($request, $identifier);
        $remaining = $this->activeLockoutSeconds($throttleKey);

        if ($remaining > 0) {
            $this->rememberLockoutNotice($identifier, $remaining);

            return back()
                ->withInput($request->only('identifier'))
                ->withErrors(['login' => $this->formatLockoutMessage($remaining)]);
        }

        try {
            $response = Http::timeout(5)->post($this->backendApiUrl('login'), [
                'identifier' => $identifier,
                'password'   => $credentials['password'],
            ]);

            if ($response->successful()) {
                $data = $response->json() ?? [];
                $this->clearLoginFailures($request, $identifier, $data['user']['email'] ?? null);

                return $this->completePortalLogin($request, $data);
            }

            if ($response->status() === 429) {
                $retryAfter = max(30, (int) ($response->header('Retry-After') ?: 60));
                $this->applyLockout($throttleKey, $retryAfter);
                $this->rememberLockoutNotice($identifier, $retryAfter);

                return back()
                    ->withInput($request->only('identifier'))
                    ->withErrors(['login' => $this->formatLockoutMessage($retryAfter)]);
            }

            if ($response->serverError()) {
                return back()
                    ->withInput($request->only('identifier'))
                    ->withErrors(['login' => 'Layanan autentikasi sedang bermasalah. Silakan coba beberapa saat lagi.']);
            }

            $failure = $this->recordFailedLoginAttempt($throttleKey);
            if ($failure['lock_seconds'] > 0) {
                $this->rememberLockoutNotice($identifier, $failure['lock_seconds']);

                return back()
                    ->withInput($request->only('identifier'))
                    ->withErrors([
                        'login' => $this->formatLockoutMessage($failure['lock_seconds'], $failure['attempts']),
                    ]);
            }

            $baseMessage = $response->json('message') ?? 'NIP/Email atau kata sandi tidak valid.';
            $warning = $this->formatAttemptWarning($failure['attempts']);

            return back()
                ->withInput($request->only('identifier'))
                ->withErrors(['login' => trim($baseMessage . ' ' . $warning)]);
        } catch (\Throwable $e) {
            Log::error('Portal login failed: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());

            return back()
                ->withInput($request->only('identifier'))
                ->withErrors(['login' => 'Layanan autentikasi sedang tidak tersedia. Silakan coba beberapa saat lagi.']);
        }
    }

    /**
     * Redirect pengguna menuju halaman otorisasi Google OAuth 2.0.
     */
    public function redirectToGoogle(GoogleOAuthService $google): RedirectResponse
    {
        if (!$google->isConfigured()) {
            return redirect()
                ->route('portal.login')
                ->withErrors([
                    'login' => 'Login dengan Google belum dikonfigurasi. Admin perlu mengisi GOOGLE_CLIENT_ID dan GOOGLE_CLIENT_SECRET pada file .env.',
                ]);
        }

        $state = Str::random(40);
        session(['google_oauth_state' => $state]);

        return redirect()->away($google->buildAuthorizationUrl($state));
    }

    /**
     * Tangani callback OAuth dari Google lalu tukarkan dengan token backend Senat.
     */
    public function handleGoogleCallback(Request $request, GoogleOAuthService $google): RedirectResponse
    {
        if ($request->filled('error')) {
            session()->forget('google_oauth_state');

            return redirect()
                ->route('portal.login')
                ->with('info', 'Proses masuk dengan akun Google dibatalkan.');
        }

        $expectedState = (string) session()->pull('google_oauth_state', '');
        $actualState = (string) $request->query('state', '');
        $code = trim((string) $request->query('code', ''));

        if ($expectedState === '' || $actualState === '' || !hash_equals($expectedState, $actualState) || $code === '') {
            return redirect()
                ->route('portal.login')
                ->withErrors(['login' => 'Sesi autentikasi Google tidak valid atau telah kedaluwarsa. Silakan coba lagi.']);
        }

        try {
            $profile = $google->exchangeCodeForProfile($code);

            if (!$google->isPolinesEmail($profile['email'])) {
                return redirect()
                    ->route('portal.login')
                    ->withErrors([
                        'login' => "Akses Google ditolak. Akun ({$profile['email']}) wajib menggunakan domain resmi POLINES (*@polines.ac.id).",
                    ]);
            }

            $ssoSecret = trim((string) config('services.google.sso_secret', ''));
            $headers = [];
            if ($ssoSecret !== '') {
                $timestamp = (string) time();
                $headers = [
                    'X-Google-Bridge-Timestamp' => $timestamp,
                    'X-Google-Bridge-Signature' => hash_hmac('sha256', strtolower($profile['email']) . '|' . $timestamp, $ssoSecret),
                ];
            }

            $response = Http::timeout(8)
                ->withHeaders($headers)
                ->post($this->backendApiUrl('login/google'), [
                    'email' => $profile['email'],
                    'name' => $profile['name'],
                    'picture' => $profile['picture'],
                    'id_token' => $profile['id_token'],
                    'access_token' => $profile['access_token'],
                ]);

            if ($response->successful()) {
                $data = $response->json() ?? [];
                $this->clearLoginFailures($request, $profile['email'], $data['user']['email'] ?? null);

                return $this->completePortalLogin($request, $data);
            }

            $errorMessage = $response->json('message') ?? 'Akun Google gagal masuk ke Portal Senat.';

            return redirect()
                ->route('portal.login')
                ->withErrors(['login' => $errorMessage]);
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('portal.login')
                ->withErrors(['login' => $e->getMessage()]);
        } catch (\Throwable $e) {
            Log::error('Google login callback failed: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());

            return redirect()
                ->route('portal.login')
                ->withErrors(['login' => 'Layanan autentikasi Google sedang tidak tersedia. Silakan coba beberapa saat lagi.']);
        }
    }

    /**
     * Logout dari Portal
     */
    public function logout(Request $request): RedirectResponse
    {
        $token = session('auth_token');
        if ($token) {
            try {
                Http::timeout(3)
                    ->withToken($token)
                    ->post($this->backendApiUrl('logout'));
            } catch (\Throwable) {
                // Ignore failure if backend is unreachable
            }
        }

        PortalAuthMiddleware::clearAuthSession($request);
        session()->flush();

        return redirect()->route('portal.login')->with('info', 'Anda telah berhasil keluar dari akun.');
    }

    /**
     * Finalisasi sesi login setelah kredensial manual atau Google SSO terverifikasi.
     *
     * @param  array<string, mixed>  $data
     */
    private function completePortalLogin(Request $request, array $data): RedirectResponse
    {
        $user = is_array($data['user'] ?? null) ? $data['user'] : [];

        // Rotate the session id to prevent session fixation.
        $request->session()->regenerate();
        session()->forget('portal_login_lockout');

        session([
            'auth_token'       => $data['token'] ?? null,
            'auth_user'        => $user,
            'auth_user_id'     => $user['id_user'] ?? null,
            'auth_user_name'   => $user['nama'] ?? 'Anggota Senat',
            'auth_user_role'   => $user['role'] ?? 'anggota',
            'auth_user_nip'    => $user['nim_nip'] ?? '',
            'auth_user_email'  => $user['email'] ?? '',
            'auth_user_foto'   => $user['foto_profil'] ?? null,
            'auth_verified_at' => now()->timestamp,
            'auth_token_checked_at' => now()->timestamp,
        ]);

        if (($user['role'] ?? '') === 'admin') {
            return redirect()->route('admin.preview')->with('success', 'Selamat datang, ' . ($user['nama'] ?? 'Admin') . '!');
        }

        return redirect()->route('portal.dashboard')->with('success', 'Selamat datang, ' . ($user['nama'] ?? 'Anggota Senat') . '!');
    }

    private function backendApiUrl(string $path): string
    {
        $base = rtrim((string) config('services.backend_api.url', 'http://127.0.0.1:8001/api'), '/');
        if (!str_ends_with($base, '/api')) {
            $base .= '/api';
        }

        return $base . '/' . ltrim($path, '/');
    }

    private function loginThrottleKey(Request $request, string $identifier): string
    {
        return Str::lower(trim($identifier)) . '|' . ($request->ip() ?: '127.0.0.1');
    }

    private function activeLockoutSeconds(string $throttleKey): int
    {
        $lockedUntil = (int) Cache::get($this->lockCacheKey($throttleKey), 0);

        return max(0, $lockedUntil - now()->timestamp);
    }

    /**
     * Catat kegagalan kata sandi dan terapkan jeda bertingkat (3x -> 30s, 5x+ -> 120s).
     *
     * @return array{attempts: int, lock_seconds: int}
     */
    private function recordFailedLoginAttempt(string $throttleKey): array
    {
        $failKey = $this->failureCacheKey($throttleKey);
        $attempts = ((int) Cache::get($failKey, 0)) + 1;
        Cache::put($failKey, $attempts, now()->addMinutes(self::LOGIN_FAILURE_TTL_MINUTES));

        $lockSeconds = 0;
        if ($attempts >= self::TIER2_FAILURE_THRESHOLD) {
            $lockSeconds = self::TIER2_LOCK_SECONDS;
        } elseif ($attempts === self::TIER1_FAILURE_THRESHOLD) {
            $lockSeconds = self::TIER1_LOCK_SECONDS;
        }

        if ($lockSeconds > 0) {
            $this->applyLockout($throttleKey, $lockSeconds);
        }

        return [
            'attempts' => $attempts,
            'lock_seconds' => $lockSeconds,
        ];
    }

    private function applyLockout(string $throttleKey, int $seconds): void
    {
        Cache::put(
            $this->lockCacheKey($throttleKey),
            now()->timestamp + $seconds,
            now()->addSeconds($seconds)
        );
    }

    private function rememberLockoutNotice(string $identifier, int $seconds): void
    {
        session([
            'portal_login_lockout' => [
                'identifier' => $identifier,
                'until' => now()->timestamp + $seconds,
            ],
        ]);
    }

    private function clearLoginFailures(Request $request, string $identifier, ?string $email = null): void
    {
        $keys = [$this->loginThrottleKey($request, $identifier)];
        if ($email !== null && trim($email) !== '') {
            $keys[] = $this->loginThrottleKey($request, $email);
        }

        foreach (array_unique($keys) as $key) {
            Cache::forget($this->failureCacheKey($key));
            Cache::forget($this->lockCacheKey($key));
        }

        session()->forget('portal_login_lockout');
    }

    private function failureCacheKey(string $throttleKey): string
    {
        return 'portal_login_failures:' . sha1($throttleKey);
    }

    private function lockCacheKey(string $throttleKey): string
    {
        return 'portal_login_lock_until:' . sha1($throttleKey);
    }

    private function formatLockoutMessage(int $seconds, ?int $attempts = null): string
    {
        $durationText = $seconds >= 60
            ? (int) ceil($seconds / 60) . ' menit (' . $seconds . ' detik)'
            : $seconds . ' detik';

        if ($attempts !== null) {
            return "Terlalu banyak percobaan kata sandi salah ({$attempts}x). Silakan coba lagi dalam {$durationText}.";
        }

        return "Akses login sementara dijeda karena percobaan kata sandi salah berulang. Silakan tunggu {$seconds} detik sebelum mencoba lagi.";
    }

    private function formatAttemptWarning(int $attempts): string
    {
        if ($attempts === self::TIER1_FAILURE_THRESHOLD - 1) {
            return '(Peringatan: 1 percobaan salah lagi akan memberi jeda ' . self::TIER1_LOCK_SECONDS . ' detik.)';
        }

        if ($attempts === self::TIER2_FAILURE_THRESHOLD - 1) {
            return '(Peringatan: 1 percobaan salah lagi akan memberi jeda 2 menit.)';
        }

        return '';
    }
}
