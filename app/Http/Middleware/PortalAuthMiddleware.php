<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PortalAuthMiddleware
{
    /**
     * Ensure user is authenticated into the senate portal session.
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

        return $next($request);
    }
}
