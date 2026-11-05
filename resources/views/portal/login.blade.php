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
    fillDemo(ident, pass) {
        const elId = document.getElementById('identifier');
        const elPass = document.getElementById('password');
        if (elId) {
            elId.value = ident;
            elId.dispatchEvent(new Event('input', { bubbles: true }));
        }
        if (elPass) {
            elPass.value = pass;
            elPass.dispatchEvent(new Event('input', { bubbles: true }));
        }
    }
}">

    {{-- Background subtle ambient gradient --}}
    <div class="fixed inset-0 pointer-events-none -z-10 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-blue-100/50 via-slate-50 to-slate-100"></div>

    {{-- Top Navigation: Back to Home (Clean Modern Pill in Top Left) --}}
    <header class="absolute top-0 inset-x-0 p-4 sm:p-6 lg:p-8 flex items-center justify-between pointer-events-none z-10">
        <a href="{{ route('public.home') }}" class="pointer-events-auto inline-flex items-center gap-2 rounded-full border border-slate-200/90 bg-white/90 px-3.5 py-1.5 text-xs font-medium text-slate-600 shadow-xs backdrop-blur transition-all hover:bg-white hover:text-slate-900 hover:border-slate-300 hover:shadow group">
            <span class="text-slate-400 group-hover:-translate-x-0.5 group-hover:text-slate-700 transition-transform">←</span>
            <span>Kembali ke Halaman Utama</span>
        </a>
    </header>

    {{-- Main Container Card (Fluid, Center, Simple & Minimalist) --}}
    <main class="w-full max-w-[440px] my-auto">
        <div class="rounded-2xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-xl shadow-slate-200/60">
            {{-- Header & Branding --}}
            <div class="text-center">
                <a href="{{ route('public.home') }}" class="inline-flex items-center justify-center h-14 w-14 rounded-2xl bg-slate-50 border border-slate-100 p-2 shadow-xs transition hover:scale-105">
                    <img src="{{ asset('images/logo-polines.png') }}" alt="Logo Polines" class="h-full w-full object-contain">
                </a>
                <h1 class="mt-4 text-xl sm:text-2xl font-bold tracking-tight text-slate-900">
                    Masuk ke Portal Senat
                </h1>
                <p class="mt-1 text-xs text-slate-500">
                    Gunakan kredensial institusional untuk mengakses akun
                </p>
            </div>

            {{-- Error & Info Alerts --}}
            @if ($errors->any())
                <div class="mt-5 rounded-lg bg-rose-50 border border-rose-200/80 p-3 text-xs text-rose-800">
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
                            value="{{ old('identifier') }}"
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

                {{-- Remember Me & Forgot Password --}}
                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 text-slate-600 cursor-pointer select-none">
                        <input
                            type="checkbox"
                            name="remember"
                            value="1"
                            class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        Ingat saya
                    </label>
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
                        class="h-11 w-full rounded-lg bg-[#003366] hover:bg-[#00284d] text-white text-sm font-semibold transition-all duration-150 shadow-sm hover:shadow active:scale-[0.99] flex items-center justify-center gap-2">
                        Masuk ke Portal
                    </button>
                    <p class="mt-2 text-center text-[10.5px] text-slate-400">
                        Hak akses diarahkan otomatis sesuai peran akun
                    </p>
                </div>
            </form>

            {{-- Compact Quick Fill Demo --}}
            <div class="mt-6 pt-5 border-t border-slate-100">
                <div class="flex items-center justify-between mb-2.5">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Akun Uji Coba:</span>
                    <span class="text-[10px] text-slate-400">Klik untuk isi cepat</span>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <button
                        type="button"
                        @click="fillDemo('admin@senat.test', 'password')"
                        class="rounded-lg border border-slate-200 bg-slate-50/80 px-2.5 py-2 text-left transition hover:bg-blue-50 hover:border-blue-300">
                        <span class="block text-[11px] font-bold text-slate-800">Sekretariat</span>
                        <span class="block text-[10px] text-slate-500 truncate">admin@senat.test</span>
                    </button>
                    <button
                        type="button"
                        @click="fillDemo('197405191999031001', 'password')"
                        class="rounded-lg border border-slate-200 bg-slate-50/80 px-2.5 py-2 text-left transition hover:bg-blue-50 hover:border-blue-300">
                        <span class="block text-[11px] font-bold text-slate-800">Anggota Senat</span>
                        <span class="block text-[10px] text-slate-500 truncate">197405191999031001</span>
                    </button>
                </div>
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
                    Akun Portal Senat Akademik Polines diterbitkan dan dikelola secara terpusat untuk menjaga keamanan hak suara dan dokumen sidang.
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
