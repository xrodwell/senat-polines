<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PortalGuestMiddleware
{
    /**
     * Prevent already authenticated users (with active <3 day session) from accessing login page again.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (session()->has('auth_user')) {
            if (PortalAuthMiddleware::isSessionVerificationExpired()) {
                PortalAuthMiddleware::clearAuthSession($request);
                session()->flash('info', 'Sesi login Anda telah melewati 3 hari. Silakan masuk kembali untuk verifikasi ulang akun.');

                return $next($request);
            }

            if (session('auth_user_role') === 'admin') {
                return redirect()->route('admin.preview');
            }

            return redirect()->route('portal.dashboard');
        }

        return $next($request);
    }
}
