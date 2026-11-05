<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PortalGuestMiddleware
{
    /**
     * Prevent already authenticated users from accessing login page again.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (session()->has('auth_user')) {
            if (session('auth_user_role') === 'admin') {
                return redirect()->route('admin.preview');
            }

            return redirect()->route('portal.dashboard');
        }

        return $next($request);
    }
}
