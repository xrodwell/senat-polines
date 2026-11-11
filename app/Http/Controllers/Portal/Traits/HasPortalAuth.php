<?php

namespace App\Http\Controllers\Portal\Traits;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

trait HasPortalAuth
{
    /**
     * 11 — Login Portal Senat
     */
    public function login(): View|RedirectResponse
    {
        if (session()->has('auth_user')) {
            if (session('auth_user_role') === 'admin') {
                return redirect()->route('admin.preview');
            }
            return redirect()->route('portal.dashboard');
        }

        return view('portal.login');
    }

    /**
     * Proses Autentikasi Login Portal
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

        try {
            $response = Http::timeout(5)->post('http://127.0.0.1:8001/api/login', [
                'identifier' => $credentials['identifier'],
                'password'   => $credentials['password'],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $user = $data['user'] ?? [];

                // Rotate the session id to prevent session fixation.
                $request->session()->regenerate();

                // Simpan ke session Laravel frontend
                session([
                    'auth_token'     => $data['token'] ?? null,
                    'auth_user'      => $user,
                    'auth_user_id'   => $user['id_user'] ?? null,
                    'auth_user_name' => $user['nama'] ?? 'Anggota Senat',
                    'auth_user_role' => $user['role'] ?? 'anggota',
                    'auth_user_nip'  => $user['nim_nip'] ?? '',
                    'auth_user_email'=> $user['email'] ?? '',
                ]);

                // Redirect sesuai role
                if (($user['role'] ?? '') === 'admin') {
                    return redirect()->route('admin.preview')->with('success', 'Selamat datang, ' . ($user['nama'] ?? 'Admin') . '!');
                }

                return redirect()->route('portal.dashboard')->with('success', 'Selamat datang, ' . ($user['nama'] ?? 'Anggota Senat') . '!');
            }

            $errorMessage = $response->json('message') ?? 'NIP/Email atau kata sandi tidak valid.';
            return back()->withInput($request->only('identifier'))->withErrors(['login' => $errorMessage]);
        } catch (\Throwable $e) {
            Log::error('Portal login failed: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            return back()->withInput($request->only('identifier'))
                ->withErrors(['login' => 'Layanan autentikasi sedang tidak tersedia. Silakan coba beberapa saat lagi.']);
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
                    ->post('http://127.0.0.1:8001/api/logout');
            } catch (\Throwable) {
                // Ignore failure if backend is unreachable
            }
        }

        session()->forget(['auth_token', 'auth_user', 'auth_user_id', 'auth_user_name', 'auth_user_role', 'auth_user_nip', 'auth_user_email']);
        session()->flush();

        return redirect()->route('portal.login')->with('info', 'Anda telah berhasil keluar dari akun.');
    }
}

