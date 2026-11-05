<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PortalRoleMiddleware
{
    /**
     * Ensure authenticated user possesses required portal roles.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $userRole = session('auth_user_role', 'guest');

        if (!empty($roles) && !in_array($userRole, $roles, true)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Forbidden. Anda tidak memiliki izin untuk tindakan ini.',
                ], 403);
            }

            if ($userRole === 'anggota') {
                return redirect()->route('portal.dashboard')
                    ->with('error', 'Akses ditolak. Halaman tersebut khusus untuk Sekretariat/Administrator Senat.');
            }

            return redirect()->route('portal.login')
                ->withErrors(['login' => 'Akses ditolak. Silakan login dengan akun yang memiliki hak akses.']);
        }

        return $next($request);
    }
}
