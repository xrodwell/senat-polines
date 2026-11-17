<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GoogleOAuthService
{
    private const AUTH_ENDPOINT = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const TOKEN_ENDPOINT = 'https://oauth2.googleapis.com/token';
    private const USERINFO_ENDPOINT = 'https://www.googleapis.com/oauth2/v3/userinfo';

    /**
     * Determine whether Google OAuth credentials have been configured.
     */
    public function isConfigured(): bool
    {
        return trim((string) config('services.google.client_id', '')) !== ''
            && trim((string) config('services.google.client_secret', '')) !== '';
    }

    /**
     * Allow only institutional POLINES email addresses (@polines.ac.id or official subdomains).
     */
    public function isPolinesEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false
            && preg_match('/@(?:[a-z0-9-]+\.)*polines\.ac\.id$/i', $email) === 1;
    }

    /**
     * Resolve the OAuth callback URL, honoring explicit config or active route host.
     */
    public function resolveRedirectUri(): string
    {
        $configured = trim((string) config('services.google.redirect', ''));

        return $configured !== '' ? $configured : route('portal.login.google.callback');
    }

    /**
     * Build the Google OAuth 2.0 authorization redirect URL.
     */
    public function buildAuthorizationUrl(string $state): string
    {
        $query = http_build_query([
            'client_id' => (string) config('services.google.client_id'),
            'redirect_uri' => $this->resolveRedirectUri(),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'access_type' => 'online',
            'prompt' => 'select_account',
        ], '', '&', PHP_QUERY_RFC3986);

        return self::AUTH_ENDPOINT . '?' . $query;
    }

    /**
     * Exchange an OAuth authorization code for a verified Google user profile.
     *
     * @return array{email: string, name: string, picture: ?string, access_token: string, id_token: ?string}
     */
    public function exchangeCodeForProfile(string $code): array
    {
        $tokenResponse = $this->googleHttp()->asForm()->post(self::TOKEN_ENDPOINT, [
            'code' => $code,
            'client_id' => (string) config('services.google.client_id'),
            'client_secret' => (string) config('services.google.client_secret'),
            'redirect_uri' => $this->resolveRedirectUri(),
            'grant_type' => 'authorization_code',
        ]);

        if (!$tokenResponse->successful()) {
            Log::warning('Google OAuth token exchange failed', [
                'status' => $tokenResponse->status(),
                'error' => $tokenResponse->json('error'),
                'error_description' => $tokenResponse->json('error_description'),
                'redirect_uri' => $this->resolveRedirectUri(),
            ]);

            throw new RuntimeException('Gagal menukar kode otorisasi Google.');
        }

        $tokenPayload = $tokenResponse->json();
        $accessToken = trim((string) ($tokenPayload['access_token'] ?? ''));
        $idToken = isset($tokenPayload['id_token']) ? trim((string) $tokenPayload['id_token']) : null;

        if ($accessToken === '') {
            throw new RuntimeException('Token akses Google tidak ditemukan.');
        }

        $profileResponse = $this->googleHttp()
            ->withToken($accessToken)
            ->get(self::USERINFO_ENDPOINT);

        if (!$profileResponse->successful()) {
            throw new RuntimeException('Gagal mengambil informasi profil akun Google.');
        }

        $profile = $profileResponse->json();
        $email = strtolower(trim((string) ($profile['email'] ?? '')));
        $emailVerified = filter_var($profile['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if ($email === '' || !$emailVerified) {
            throw new RuntimeException('Akun Google Anda belum memiliki alamat email terverifikasi.');
        }

        return [
            'email' => $email,
            'name' => (string) ($profile['name'] ?? ''),
            'picture' => isset($profile['picture']) ? (string) $profile['picture'] : null,
            'access_token' => $accessToken,
            'id_token' => $idToken,
        ];
    }

    /**
     * Build a TLS-verified HTTP client that also locates local XAMPP CA bundles on Windows.
     */
    private function googleHttp(): PendingRequest
    {
        return Http::timeout(10)->withOptions([
            'force_ip_resolve' => 'v4',
            'verify' => $this->resolveCaVerify(),
        ]);
    }

    private function resolveCaVerify(): string|bool
    {
        foreach ([trim((string) ini_get('curl.cainfo')), trim((string) ini_get('openssl.cafile'))] as $configured) {
            if ($configured !== '' && is_file($configured)) {
                return $configured;
            }
        }

        $phpRoot = dirname(dirname(PHP_BINARY));
        foreach ([
            $phpRoot . '/phpMyAdmin/vendor/composer/ca-bundle/res/cacert.pem',
            $phpRoot . '/php/extras/ssl/cacert.pem',
            $phpRoot . '/apache/bin/curl-ca-bundle.crt',
            'D:/xampp/phpMyAdmin/vendor/composer/ca-bundle/res/cacert.pem',
            'D:/xampp/apache/bin/curl-ca-bundle.crt',
            'C:/xampp/apache/bin/curl-ca-bundle.crt',
        ] as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return true;
    }
}

