<?php

namespace Tests\Feature;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PortalLoginSecurityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Carbon::setTestNow(Carbon::create(2026, 11, 17, 10, 0, 0));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_login_page_removes_demo_account_chooser_and_renders_google_option(): void
    {
        $response = $this->get(route('portal.login'));

        $response->assertOk()
            ->assertDontSee('Akun Uji Coba')
            ->assertDontSee('Klik untuk isi cepat')
            ->assertDontSee('fillDemo(')
            ->assertDontSee('admin@senat.test')
            ->assertDontSee('Ingat saya')
            ->assertDontSee('name="remember"', false)
            ->assertSee('Masuk ke Portal')
            ->assertSee('Masuk dengan Google')
            ->assertSee('*@polines.ac.id')
            ->assertSee('3 hari')
            ->assertSee(route('portal.login.google'), false);
    }

    public function test_tiered_password_failure_lockout_enforces_30s_at_3_fails_and_120s_at_5_fails(): void
    {
        $backendCalls = 0;

        Http::fake(function () use (&$backendCalls) {
            $backendCalls++;

            return Http::response([
                'status' => 'error',
                'message' => 'NIP/Email atau kata sandi yang Anda masukkan salah.',
            ], 401);
        });

        $payload = [
            'identifier' => 'dosen@polines.ac.id',
            'password' => 'salah',
        ];

        // 1st failure
        $this->post(route('portal.login.submit'), $payload)
            ->assertRedirect()
            ->assertSessionHasErrors('login');
        $this->assertSame(1, $backendCalls);

        // 2nd failure -> warning before 30s tier
        $res2 = $this->post(route('portal.login.submit'), $payload);
        $res2->assertRedirect()->assertSessionHasErrors('login');
        $this->assertStringContainsString('30 detik', session('errors')->first('login'));
        $this->assertSame(2, $backendCalls);

        // 3rd failure -> 30s lockout
        $res3 = $this->post(route('portal.login.submit'), $payload);
        $res3->assertRedirect()
            ->assertSessionHasErrors('login')
            ->assertSessionHas('portal_login_lockout');
        $this->assertStringContainsString('30 detik', session('errors')->first('login'));
        $this->assertSame(3, $backendCalls);

        // Immediate retry while locked MUST NOT hit backend
        Carbon::setTestNow(now()->addSeconds(15));
        $resLocked = $this->post(route('portal.login.submit'), $payload);
        $resLocked->assertRedirect()->assertSessionHasErrors('login');
        $this->assertSame(3, $backendCalls);

        // Different account MUST NOT be blocked
        $this->post(route('portal.login.submit'), [
            'identifier' => 'oranglain@polines.ac.id',
            'password' => 'salah',
        ])->assertRedirect();
        $this->assertSame(4, $backendCalls);

        // After 30s expires -> 4th failure warns about 2-minute tier
        Carbon::setTestNow(now()->addSeconds(16)); // total +31s
        $res4 = $this->post(route('portal.login.submit'), $payload);
        $res4->assertRedirect()->assertSessionHasErrors('login');
        $this->assertStringContainsString('2 menit', session('errors')->first('login'));
        $this->assertSame(5, $backendCalls);

        // 5th failure -> 120s (2 minutes) lockout
        $res5 = $this->post(route('portal.login.submit'), $payload);
        $res5->assertRedirect()->assertSessionHasErrors('login');
        $this->assertStringContainsString('2 menit (120 detik)', session('errors')->first('login'));
        $this->assertSame(6, $backendCalls);

        // Still locked at +100s
        Carbon::setTestNow(now()->addSeconds(100));
        $this->post(route('portal.login.submit'), $payload)->assertRedirect();
        $this->assertSame(6, $backendCalls);

        // Unlocked after 121s
        Carbon::setTestNow(now()->addSeconds(21));
        $this->post(route('portal.login.submit'), $payload)->assertRedirect();
        $this->assertSame(7, $backendCalls);
    }

    public function test_successful_login_resets_failure_counter_and_redirects_by_role(): void
    {
        Http::fakeSequence()
            ->push(['status' => 'error', 'message' => 'Salah'], 401)
            ->push(['status' => 'error', 'message' => 'Salah'], 401)
            ->push([
                'status' => 'success',
                'token' => 'sanctum-admin-token',
                'user' => [
                    'id_user' => 1,
                    'nim_nip' => 'admin',
                    'nama' => 'Admin Senat',
                    'email' => 'admin@polines.ac.id',
                    'role' => 'admin',
                ],
            ], 200);

        $this->post(route('portal.login.submit'), ['identifier' => 'admin@polines.ac.id', 'password' => 'x']);
        $this->post(route('portal.login.submit'), ['identifier' => 'admin@polines.ac.id', 'password' => 'x']);

        $res = $this->post(route('portal.login.submit'), [
            'identifier' => 'admin@polines.ac.id',
            'password' => 'password-benar',
        ]);

        $res->assertRedirect(route('admin.preview'))
            ->assertSessionHas('auth_token', 'sanctum-admin-token')
            ->assertSessionHas('auth_user_role', 'admin')
            ->assertSessionMissing('portal_login_lockout');
    }

    public function test_google_redirect_requires_configuration(): void
    {
        config()->set('services.google.client_id', null);
        config()->set('services.google.client_secret', null);

        $response = $this->get(route('portal.login.google'));

        $response->assertRedirect(route('portal.login'))
            ->assertSessionHasErrors('login');
    }

    public function test_google_redirect_sends_user_to_google_with_state(): void
    {
        config()->set('services.google.client_id', 'google-client-123');
        config()->set('services.google.client_secret', 'google-secret-456');
        config()->set('services.google.redirect', 'http://localhost/portal/login/google/callback');

        $response = $this->get(route('portal.login.google'));

        $response->assertRedirect();
        $target = (string) $response->headers->get('Location');
        $this->assertStringStartsWith('https://accounts.google.com/o/oauth2/v2/auth?', $target);
        $this->assertStringContainsString('client_id=google-client-123', $target);
        $this->assertStringContainsString('prompt=select_account', $target);
        $this->assertNotEmpty(session('google_oauth_state'));
    }

    public function test_google_callback_rejects_state_mismatch_and_handles_cancel(): void
    {
        $this->withSession(['google_oauth_state' => 'expected-state'])
            ->get(route('portal.login.google.callback', ['state' => 'wrong-state', 'code' => 'abc']))
            ->assertRedirect(route('portal.login'))
            ->assertSessionHasErrors('login')
            ->assertSessionMissing('auth_user');

        $this->withSession(['google_oauth_state' => 'expected-state'])
            ->get(route('portal.login.google.callback', ['error' => 'access_denied']))
            ->assertRedirect(route('portal.login'))
            ->assertSessionHas('info');
    }

    public function test_google_callback_authenticates_registered_user(): void
    {
        config()->set('services.google.client_id', 'google-client-123');
        config()->set('services.google.client_secret', 'google-secret-456');
        config()->set('services.google.sso_secret', 'shared-bridge-secret');

        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'google-access-token',
                'id_token' => 'google-id-token',
            ], 200),
            'https://www.googleapis.com/oauth2/v3/userinfo' => Http::response([
                'email' => 'anggota@polines.ac.id',
                'email_verified' => true,
                'name' => 'Anggota Senat',
            ], 200),
            '*/api/login/google' => Http::response([
                'status' => 'success',
                'token' => 'sanctum-google-token',
                'user' => [
                    'id_user' => 7,
                    'nim_nip' => '198001012005011001',
                    'nama' => 'Anggota Senat',
                    'email' => 'anggota@polines.ac.id',
                    'role' => 'anggota',
                ],
            ], 200),
        ]);

        $response = $this->withSession(['google_oauth_state' => 'valid-oauth-state'])
            ->get(route('portal.login.google.callback', [
                'state' => 'valid-oauth-state',
                'code' => 'google-auth-code',
            ]));

        $response->assertRedirect(route('portal.dashboard'))
            ->assertSessionHas('auth_token', 'sanctum-google-token')
            ->assertSessionHas('auth_user_email', 'anggota@polines.ac.id')
            ->assertSessionHas('auth_verified_at');

        Http::assertSent(function ($request) {
            if (!str_ends_with($request->url(), '/api/login/google')) {
                return false;
            }

            return $request->hasHeader('X-Google-Bridge-Signature')
                && $request->hasHeader('X-Google-Bridge-Timestamp')
                && $request['email'] === 'anggota@polines.ac.id'
                && $request['name'] === 'Anggota Senat';
        });
    }

    public function test_google_callback_blocks_non_polines_domain_before_calling_backend(): void
    {
        config()->set('services.google.client_id', 'google-client-123');
        config()->set('services.google.client_secret', 'google-secret-456');
        config()->set('services.google.sso_secret', 'shared-bridge-secret');

        foreach (['asing@gmail.com', 'spoof@fakepolines.ac.id'] as $idx => $blockedEmail) {
            Http::fake([
                'https://oauth2.googleapis.com/token' => Http::response([
                    'access_token' => 'google-access-token-' . $idx,
                ], 200),
                'https://www.googleapis.com/oauth2/v3/userinfo' => Http::response([
                    'email' => $blockedEmail,
                    'email_verified' => true,
                ], 200),
            ]);

            $rejected = $this->withSession(['google_oauth_state' => 'valid-oauth-state-' . $idx])
                ->get(route('portal.login.google.callback', [
                    'state' => 'valid-oauth-state-' . $idx,
                    'code' => 'google-auth-code-' . $idx,
                ]));

            $rejected->assertRedirect(route('portal.login'))
                ->assertSessionHasErrors('login')
                ->assertSessionMissing('auth_user');

            Http::assertNotSent(fn ($request) => str_contains($request->url(), '/api/login/google'));
        }
    }

    public function test_session_requires_reverification_after_three_days(): void
    {
        Http::fake([
            '*/api/login' => Http::response([
                'status' => 'success',
                'token' => 'sanctum-token-3days',
                'user' => [
                    'id_user' => 8,
                    'nim_nip' => '198101012008011001',
                    'nama' => 'Dosen Polines',
                    'email' => 'dosen@polines.ac.id',
                    'role' => 'anggota',
                ],
            ], 200),
            '*/api/*' => Http::response(['status' => 'success', 'data' => []], 200),
        ]);

        $this->post(route('portal.login.submit'), [
            'identifier' => 'dosen@polines.ac.id',
            'password' => 'rahasia',
        ])->assertRedirect(route('portal.dashboard'))
            ->assertSessionHas('auth_verified_at');

        // 2 days 23 hours later -> still valid
        Carbon::setTestNow(now()->addDays(2)->addHours(23));
        $this->get(route('portal.dashboard'))
            ->assertOk();

        // After full 3 days (72 hours) -> forced back to login for re-verification
        Carbon::setTestNow(now()->addHours(1));
        $this->get(route('portal.dashboard'))
            ->assertRedirect(route('portal.login'))
            ->assertSessionHas('info')
            ->assertSessionMissing('auth_user')
            ->assertSessionMissing('auth_token')
            ->assertSessionMissing('auth_verified_at');
    }

    public function test_expired_session_visiting_login_page_is_cleared_without_redirect_loop(): void
    {
        $response = $this->withSession([
            'auth_token' => 'old-token',
            'auth_user' => ['id_user' => 8, 'nama' => 'Dosen', 'role' => 'anggota'],
            'auth_user_id' => 8,
            'auth_user_role' => 'anggota',
            'auth_verified_at' => now()->subDays(3)->subMinute()->timestamp,
        ])->get(route('portal.login'));

        $response->assertOk()
            ->assertSessionMissing('auth_user')
            ->assertSessionMissing('auth_token');
    }
}
