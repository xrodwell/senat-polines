<?php

namespace Tests\Feature;

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Tests\TestCase;

class CsrfProtectionTest extends TestCase
{
    public function test_aspirations_and_presensi_routes_are_not_exempt_from_csrf(): void
    {
        $middleware = app(PreventRequestForgery::class);

        $method = new \ReflectionMethod(PreventRequestForgery::class, 'inExceptArray');
        $method->setAccessible(true);

        $aspirasiRequest = Request::create('/aspirasi/kirim', 'POST');
        $otpRequest = Request::create('/aspirasi/send-otp', 'POST');
        $presensiRequest = Request::create('/portal/presensi/submit', 'POST');
        $chatbotRequest = Request::create('/api/chatbot/send', 'POST');

        // Web routes must NOT be in the exempt array
        $this->assertFalse($method->invoke($middleware, $aspirasiRequest), '/aspirasi/kirim should not be exempt from CSRF.');
        $this->assertFalse($method->invoke($middleware, $otpRequest), '/aspirasi/send-otp should not be exempt from CSRF.');
        $this->assertFalse($method->invoke($middleware, $presensiRequest), '/portal/presensi/submit should not be exempt from CSRF.');

        // Stateless API route should remain exempt
        $this->assertTrue($method->invoke($middleware, $chatbotRequest), '/api/chatbot/send should be exempt from CSRF.');
    }

    public function test_csrf_middleware_enforces_token_verification(): void
    {
        // Simulate production request by overriding runningUnitTests()
        $middleware = new class(app(), app('encrypter')) extends PreventRequestForgery {
            protected function runningUnitTests()
            {
                return false;
            }
        };

        $requestWithoutToken = Request::create('/aspirasi/send-otp', 'POST', [
            'email' => 'mahasiswa@polines.ac.id',
        ]);
        $session = app('session.store');
        $session->put('_token', 'valid-secret-token');
        $requestWithoutToken->setLaravelSession($session);

        $this->expectException(TokenMismatchException::class);
        $middleware->handle($requestWithoutToken, fn () => response('OK'));
    }

    public function test_csrf_middleware_passes_with_valid_token(): void
    {
        $middleware = new class(app(), app('encrypter')) extends PreventRequestForgery {
            protected function runningUnitTests()
            {
                return false;
            }
        };

        $token = 'valid-secret-token';
        $requestWithToken = Request::create('/aspirasi/send-otp', 'POST', [
            'email' => 'mahasiswa@polines.ac.id',
        ]);
        $requestWithToken->headers->set('X-CSRF-TOKEN', $token);

        $session = app('session.store');
        $session->put('_token', $token);
        $requestWithToken->setLaravelSession($session);

        $response = $middleware->handle($requestWithToken, fn () => response('OK'));
        $this->assertEquals('OK', $response->getContent());
    }
}
