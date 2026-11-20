<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk ke Portal Senat — Senat Akademik Politeknik Negeri Semarang</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('images/logo-polines.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo-polines.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])
    @livewireStyles

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="relative min-h-screen w-full bg-slate-50 font-sans text-slate-700 antialiased flex flex-col justify-center items-center py-12 px-4 sm:px-6 lg:px-8" x-data="{
    show: false,
    showResetNotice: false,
    identifierInput: @js(old('identifier', $lockoutIdentifier ?? '')),
    lockedIdentifier: @js($lockoutIdentifier ?? ''),
    lockoutRemaining: {{ (int) ($lockoutSeconds ?? 0) }},
    _lockTimer: null,
    init() {
        if (this.lockoutRemaining > 0) {
            this._lockTimer = setInterval(() => {
                if (this.lockoutRemaining > 0) {
                    this.lockoutRemaining--;
                }
                if (this.lockoutRemaining <= 0 && this._lockTimer) {
                    clearInterval(this._lockTimer);
                    this._lockTimer = null;
                }
            }, 1000);
        }
    },
    get isLockedOut() {
        if (this.lockoutRemaining <= 0) return false;
        const locked = (this.lockedIdentifier || '').trim().toLowerCase();
        const current = (this.identifierInput || '').trim().toLowerCase();
        return locked === '' || current === locked;
    },
    formatCountdown(seconds) {
        const s = Math.max(0, Number(seconds) || 0);
        const mins = Math.floor(s / 60);
        const secs = s % 60;
        if (mins > 0) {
            return `${mins} menit ${String(secs).padStart(2, '0')} detik`;
        }
        return `${secs} detik`;
    }
}">

    {{-- Background subtle ambient gradient --}}
    <div class="fixed inset-0 pointer-events-none -z-10 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-blue-100/50 via-slate-50 to-slate-100"></div>

    {{-- Main Container Card (Fluid, Center, Simple & Minimalist) --}}
    <main class="w-full max-w-[440px] my-auto">
        <div class="rounded-2xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-xl shadow-slate-200/60">
            {{-- Header & Branding --}}
            <div class="text-center">
                <a href="{{ route('public.home') }}" class="inline-flex items-center justify-center transition hover:scale-105">
                    <img src="{{ asset('images/logo-polines.png') }}" alt="Logo Polines" class="h-16 w-16 object-contain">
                </a>
                <h1 class="mt-4 text-xl sm:text-2xl font-bold tracking-tight text-slate-900">
                    Masuk ke Portal Senat
                </h1>
                <p class="mt-1 text-xs text-slate-500">
                    Masuk dengan akun institusi atau akun Google terdaftar
                </p>
            </div>

            {{-- Live Lockout Countdown Banner --}}
            <div
                x-cloak
                x-show="isLockedOut"
                class="mt-5 rounded-lg border border-amber-200 bg-amber-50 p-3 text-xs text-amber-900">
                <div class="font-semibold flex items-center justify-between gap-2">
                    <span>Jeda Keamanan Aktif</span>
                    <span class="font-mono font-bold tabular-nums text-amber-800" x-text="formatCountdown(lockoutRemaining)"></span>
                </div>
                <p class="mt-1 text-amber-800 leading-snug">
                    Terlalu banyak percobaan kata sandi salah. Silakan tunggu hingga hitung mundur selesai atau gunakan login Google.
                </p>
            </div>

            {{-- Error & Info Alerts --}}
            @if ($errors->any())
                <div x-show="!isLockedOut" class="mt-5 rounded-lg bg-rose-50 border border-rose-200/80 p-3 text-xs text-rose-800">
                    <div class="font-semibold">Autentikasi Gagal</div>
                    <p class="mt-0.5 text-rose-700 leading-snug">{{ $errors->first('login') ?: $errors->first() }}</p>
                </div>
            @endif

            @if (session('info'))
                <div class="mt-5 rounded-lg border border-blue-200 bg-blue-50 p-3 text-xs text-blue-800">
                    {{ session('info') }}
                </div>
            @endif

            {{-- Form Login --}}
            <form method="POST" action="{{ route('portal.login.submit') }}" class="mt-6 space-y-4">
                @csrf

                {{-- Email / NIP Input --}}
                <div>
                    <label for="identifier" class="block text-xs font-semibold text-slate-700">
                        Email Institusi atau NIP
                    </label>
                    <div class="mt-1.5">
                        <input
                            id="identifier"
                            name="identifier"
                            type="text"
                            required
                            autofocus
                            x-model="identifierInput"
                            autocomplete="username"
                            placeholder="nama@polines.ac.id atau NIP"
                            class="h-11 w-full rounded-lg border border-slate-300 bg-white px-3.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100 transition">
                    </div>
                </div>

                {{-- Password Input --}}
                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-700">
                        Kata Sandi
                    </label>
                    <div class="relative mt-1.5">
                        <input
                            id="password"
                            name="password"
                            required
                            :type="show ? 'text' : 'password'"
                            autocomplete="current-password"
                            placeholder="Masukkan kata sandi"
                            class="h-11 w-full rounded-lg border border-slate-300 bg-white pl-3.5 pr-11 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100 transition">
                        <button
                            type="button"
                            @click="show = !show"
                            :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                            class="absolute inset-y-0 right-0 flex w-10 items-center justify-center text-slate-400 hover:text-slate-600 transition">
                            <span x-show="!show" aria-hidden="true"><x-senat.icon name="eye" class="h-4 w-4" /></span>
                            <span x-cloak x-show="show" aria-hidden="true"><x-senat.icon name="eye-off" class="h-4 w-4" /></span>
                        </button>
                    </div>
                </div>

                {{-- Session Policy Notice & Forgot Password --}}
                <div class="flex items-center justify-between gap-2 text-xs pt-1">
                    <span class="text-[11px] text-slate-400">
                        Sesi aktif berlaku 3 hari sebelum verifikasi ulang
                    </span>
                    <button
                        type="button"
                        @click="showResetNotice = true"
                        class="font-medium text-blue-600 hover:text-blue-700 hover:underline cursor-pointer">
                        Lupa kata sandi?
                    </button>
                </div>

                {{-- Submit Button --}}
                <div class="pt-2">
                    <button
                        type="submit"
                        :disabled="isLockedOut"
                        :class="isLockedOut ? 'bg-slate-300 text-slate-500 cursor-not-allowed shadow-none' : 'bg-[#003366] hover:bg-[#00284d] text-white shadow-sm hover:shadow active:scale-[0.99] cursor-pointer'"
                        class="h-11 w-full rounded-lg text-sm font-semibold transition-all duration-150 flex items-center justify-center gap-2">
                        <span x-show="!isLockedOut">Masuk ke Portal</span>
                        <span x-cloak x-show="isLockedOut" x-text="'Coba lagi dalam ' + formatCountdown(lockoutRemaining)"></span>
                    </button>
                </div>
            </form>

            {{-- Divider --}}
            <div class="relative my-5">
                <div class="absolute inset-0 flex items-center" aria-hidden="true">
                    <div class="w-full border-t border-slate-200"></div>
                </div>
                <div class="relative flex justify-center text-[11px] font-medium uppercase tracking-wider">
                    <span class="bg-white px-3 text-slate-400">atau</span>
                </div>
            </div>

            {{-- Google Sign-In Option --}}
            <div>
                <a
                    href="{{ route('portal.login.google') }}"
                    class="h-11 w-full rounded-lg border border-slate-300 bg-white hover:bg-slate-50 hover:border-slate-400 text-slate-700 text-sm font-semibold transition-all duration-150 shadow-xs hover:shadow-sm active:scale-[0.99] flex items-center justify-center gap-2.5">
                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.665-5.17 3.665-9.17Z"/>
                        <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.11-6.72-4.96H1.29v3.14C3.26 21.3 7.31 24 12 24Z"/>
                        <path fill="#FBBC05" d="M5.28 14.24c-.24-.72-.38-1.49-.38-2.24s.14-1.52.38-2.24V6.62H1.29C.47 8.24 0 10.06 0 12s.47 3.76 1.29 5.38l3.99-3.14Z"/>
                        <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.31 0 3.26 2.7 1.29 6.62l3.99 3.14c.95-2.85 3.6-4.96 6.72-4.96Z"/>
                    </svg>
                    <span>Masuk dengan Google</span>
                </a>
                <p class="mt-2.5 text-center text-[10.5px] text-slate-400">
                    Terbuka untuk seluruh akun Google berdomain <span class="font-medium text-slate-500">*@polines.ac.id</span>.
                </p>
            </div>

            {{-- Back to Home Link --}}
            <div class="mt-3 pt-2.5 border-t border-slate-100 text-center">
                <a href="{{ route('public.home') }}" class="text-xs font-medium text-blue-600 hover:text-blue-700 hover:underline transition">
                    Kembali ke Halaman Utama
                </a>
            </div>
        </div>
    </main>

    {{-- Modal Pemberitahuan Reset Kata Sandi --}}
    <div
        x-cloak
        x-show="showResetNotice"
        @keydown.escape.window="showResetNotice = false"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
        role="dialog"
        aria-modal="true">
        {{-- Backdrop Overlay --}}
        <div
            x-show="showResetNotice"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="showResetNotice = false"
            class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs"></div>

        {{-- Dialog Box --}}
        <div
            x-show="showResetNotice"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            @click.outside="showResetNotice = false"
            class="relative w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl z-10">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-[#003366]">
                    <x-senat.icon name="lock" class="h-5 w-5" />
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Pemulihan Kata Sandi</h3>
                    <p class="text-[11px] text-slate-500">Kebijakan Akses Institusional</p>
                </div>
            </div>

            <div class="mt-4 text-xs text-slate-600 leading-relaxed space-y-3">
                <p>
                    Akun Portal Senat Akademik Polines diterbitkan dan dikelola secara terpusat untuk menjaga keamanan
                </p>
                <div class="rounded-xl bg-blue-50/70 border border-blue-200/80 p-3 text-slate-800">
                    <p class="font-semibold text-[11.5px] text-[#003366]">Cara Pemulihan Akun:</p>
                    <p class="mt-1 text-[11px] text-slate-600 leading-snug">
                        Silakan hubungi <strong>Admin Sekretariat Senat</strong> atau personel yang memiliki wewenang akses administratif untuk verifikasi dan penerbitan ulang kata sandi.
                    </p>
                </div>
            </div>

            <div class="mt-5 flex justify-end">
                <button
                    type="button"
                    @click="showResetNotice = false"
                    class="h-9 px-4 rounded-lg bg-[#003366] hover:bg-[#002244] text-white text-xs font-semibold transition shadow-xs cursor-pointer">
                    Saya Mengerti
                </button>
            </div>
        </div>
    </div>

    @livewireScripts
</body>
</html>
