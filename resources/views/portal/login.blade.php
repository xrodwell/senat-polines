<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk ke Portal Senat — Senat Akademik Politeknik Negeri Semarang</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])
    @livewireStyles

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
{{-- 11 — Login Portal Senat · Full Screen Split Layout --}}
<body class="min-h-screen bg-white font-sans text-senat-body antialiased" x-data="{
    show: false,
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

    {{-- Full width & full height flex container --}}
    <div class="flex min-h-screen w-full flex-col lg:flex-row">

        {{-- ================= BRAND PANEL (LEFT) ================= --}}
        <aside class="relative isolate flex flex-col justify-between overflow-hidden bg-senat-hero text-white lg:w-[460px] xl:w-[520px] 2xl:w-[580px] lg:shrink-0 p-8 sm:p-12 lg:p-14 xl:p-16">
            {{-- Decorative pattern --}}
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="absolute inset-y-0 right-[25px] w-[245px] bg-[#05305c]"></div>
                <div class="absolute inset-y-0 right-[104px] w-[28px] bg-[#0a457d]"></div>
                <div class="absolute bottom-0 left-0 h-[310px] w-full bg-[#002447]"></div>
            </div>

            <div class="relative z-10 flex h-full min-h-full flex-col justify-between">
                <div>
                    {{-- Brand Header --}}
                    <div class="flex items-center gap-4">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-white shadow-xs">
                            <img src="{{ asset('images/logo-polines.png') }}" alt="Logo Polines" class="h-[38px] w-[38px] rounded-[18px] object-contain">
                        </span>
                        <span class="leading-none">
                            <span class="block text-[15px] font-bold tracking-tight text-white">SENAT AKADEMIK</span>
                            <span class="mt-1 block text-[12px] font-normal text-[#bad6f5]">Politeknik Negeri Semarang</span>
                        </span>
                    </div>

                    {{-- Headline Text --}}
                    <div class="mt-12 lg:mt-[64px]">
                        <div class="h-1 w-16 rounded-[2px] bg-senat-orange"></div>
                        <p class="mt-6 text-[11px] font-bold uppercase tracking-[0.1em] text-[#94c9ff]">Portal Terlindungi</p>
                        <h1 class="mt-3 max-w-[430px] text-[34px] font-bold leading-[1.08] text-white lg:text-[40px]">
                            Ruang kerja<br>Senat Akademik.
                        </h1>
                        <p class="mt-6 max-w-[400px] text-[14px] leading-[1.5] text-[#cce0ff]">
                            Akses terverifikasi untuk anggota Senat dan Sekretariat dalam mendukung persidangan, presensi, e-voting, risalah, serta pengelolaan agenda.
                        </p>
                    </div>
                </div>

                {{-- Footer notes --}}
                <div class="mt-12 pt-8">
                    <p class="text-[11px] font-medium text-[#a1c2e5]">Akses hanya untuk akun institusional yang terverifikasi.</p>
                    <p class="mt-4 text-[10px] font-bold uppercase tracking-[0.08em] text-[#669cd1]">Portal Senat • 2026</p>
                </div>
            </div>
        </aside>

        {{-- ================= LOGIN SURFACE (RIGHT) ================= --}}
        <main class="flex flex-1 flex-col justify-center bg-white px-8 py-10 sm:px-12 lg:px-16 xl:px-24">
            <div class="mx-auto w-full max-w-[620px] py-4">
                <a href="{{ route('public.home') }}" class="inline-flex w-fit items-center gap-2 text-[11px] font-bold text-senat-link hover:underline">
                    <span aria-hidden="true">←</span> Kembali ke laman publik
                </a>

                <div class="mt-8 w-full">
                    <p class="text-[10px] font-bold uppercase tracking-[0.1em] text-senat-orange">Akses Portal</p>
                    <h2 class="mt-2 text-[28px] font-bold leading-tight tracking-tight text-senat-ink lg:text-[32px]">Masuk ke Portal Senat</h2>
                    <p class="mt-2 text-[12px] leading-relaxed text-senat-body">
                        Gunakan akun institusional. Hak akses portal ditentukan otomatis oleh sistem.
                    </p>
                    <p class="mt-1 text-[12px] font-medium text-senat-body">
                        Setelah autentikasi, sistem mengarahkan Anda ke ruang kerja sesuai peran akun.
                    </p>

                    {{-- Feedback messages (Red, No Border) --}}
                    @if ($errors->any())
                        <div class="mt-6 rounded-xl p-4 shadow-xs"
                             style="background-color: #fee2e2 !important; border: none !important; outline: none !important;">
                            <div class="text-[12.5px] font-bold" style="color: #991b1b !important;">Autentikasi Gagal</div>
                            <p class="mt-1 text-[11.5px] leading-relaxed" style="color: #b91c1c !important;">{{ $errors->first('login') ?: $errors->first() }}</p>
                        </div>
                    @endif

                    @if (session('info'))
                        <div class="mt-6 rounded-xl border border-blue-200 bg-blue-50 p-3.5 text-[12px] text-blue-800 shadow-sm">
                            {{ session('info') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('portal.login.submit') }}" class="mt-6 space-y-4">
                        @csrf

                        {{-- Email institusi / NIP --}}
                        <div>
                            <label for="identifier" class="block text-[10px] font-bold uppercase tracking-[0.08em] text-senat-ink">
                                Email Institusi atau ID Anggota / NIP
                            </label>
                            <div class="mt-2">
                                <input
                                    id="identifier"
                                    name="identifier"
                                    type="text"
                                    required
                                    value="{{ old('identifier') }}"
                                    autocomplete="username"
                                    placeholder="contoh: nama@polines.ac.id atau NIP"
                                    class="h-12 w-full rounded-lg border border-[#d6e0ed] bg-white px-4 text-[12px] text-senat-ink placeholder:text-senat-body focus:border-senat-link focus:outline-none focus:ring-2 focus:ring-senat-badgeBg">
                            </div>
                        </div>

                        {{-- Kata sandi --}}
                        <div>
                            <label for="password" class="block text-[10px] font-bold uppercase tracking-[0.08em] text-senat-ink">
                                Kata Sandi
                            </label>
                            <div class="relative mt-2">
                                <input
                                    id="password"
                                    name="password"
                                    required
                                    :type="show ? 'text' : 'password'"
                                    autocomplete="current-password"
                                    placeholder="Masukkan kata sandi Anda"
                                    class="h-12 w-full rounded-lg border border-[#d6e0ed] bg-white px-4 pr-12 text-[12px] text-senat-ink placeholder:text-senat-body focus:border-senat-link focus:outline-none focus:ring-2 focus:ring-senat-badgeBg">
                                <button
                                    type="button"
                                    @click="show = !show"
                                    :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                                    class="absolute inset-y-0 right-0 flex w-12 items-center justify-center text-[13px] text-senat-body hover:text-senat-link">
                                    <span x-show="!show" aria-hidden="true"><x-senat.icon name="eye" class="h-[18px] w-[18px]" /></span>
                                    <span x-cloak x-show="show" aria-hidden="true"><x-senat.icon name="eye-off" class="h-[18px] w-[18px]" /></span>
                                </button>
                            </div>
                        </div>

                        {{-- Remember / forgot --}}
                        <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
                            <label class="flex cursor-pointer items-center gap-3 text-[11px] text-senat-body">
                                <input type="checkbox" name="remember" value="1"
                                       class="h-[18px] w-[18px] rounded border-[#d6e0ed] text-senat-link focus:ring-senat-link">
                                Ingat saya di perangkat ini
                            </label>
                            <a href="{{ route('public.home') }}" class="text-[11px] font-bold text-senat-link hover:underline">Lupa kata sandi?</a>
                        </div>

                        {{-- Submit --}}
                        <div class="pt-2">
                            <button type="submit"
                                    class="h-[46px] w-full rounded-lg bg-[#155ead] text-[12px] font-semibold text-white transition-colors hover:bg-senat-primaryHover shadow-sm">
                                Masuk ke Portal
                            </button>
                            <p class="mt-2.5 text-[10px] text-senat-muted">Hak akses ditentukan otomatis setelah autentikasi.</p>
                        </div>
                    </form>

                    {{-- Demo Quick Fill Section --}}
                    <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50/80 p-4">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-600">⚡ Akun Demo / Uji Coba:</span>
                            <span class="text-[10px] text-slate-500">Klik untuk isi otomatis</span>
                        </div>
                        <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <button
                                type="button"
                                @click="fillDemo('admin@senat.test', 'password')"
                                class="flex items-center justify-between rounded-lg border border-indigo-200 bg-white px-3 py-2 text-left text-[11px] font-medium text-slate-700 transition hover:border-indigo-400 hover:bg-indigo-50/50 shadow-xs">
                                <div>
                                    <span class="font-bold text-indigo-700 block">Sekretariat (Admin)</span>
                                    <span class="text-[10px] text-slate-500">admin@senat.test</span>
                                </div>
                                <span class="text-[10px] font-semibold text-indigo-600">Pilih →</span>
                            </button>
                            <button
                                type="button"
                                @click="fillDemo('197405191999031001', 'password')"
                                class="flex items-center justify-between rounded-lg border border-blue-200 bg-white px-3 py-2 text-left text-[11px] font-medium text-slate-700 transition hover:border-blue-400 hover:bg-blue-50/50 shadow-xs">
                                <div>
                                    <span class="font-bold text-blue-700 block">Anggota Senat</span>
                                    <span class="text-[10px] text-slate-500">NIP: 197405191999031001</span>
                                </div>
                                <span class="text-[10px] font-semibold text-blue-600">Pilih →</span>
                            </button>
                        </div>
                    </div>

                    {{-- Security line --}}
                    <p class="mt-6 text-center text-[10px] text-senat-body">
                        <span aria-hidden="true"><x-senat.icon name="folder" class="h-[18px] w-[18px]" /></span> Koneksi aman • Akses tercatat pada sistem Sekretariat Senat
                    </p>

                    <div class="mt-5 h-px w-full bg-[#d6e0ed]"></div>

                    {{-- Help --}}
                    <div class="mt-5 flex flex-wrap items-center justify-between gap-3">
                        <p class="text-[11px] text-senat-body">Butuh bantuan akses?</p>
                        <a href="{{ route('public.home') }}" class="inline-flex items-center gap-1 text-[11px] font-bold text-senat-link hover:underline">
                            Hubungi Sekretariat Senat <span aria-hidden="true">→</span>
                        </a>
                    </div>

                    {{-- Privacy note --}}
                    <div class="mt-6 rounded-lg bg-[#f2f9ff] px-4 py-2.5">
                        <p class="text-[10px] font-bold uppercase tracking-[0.06em] text-senat-hero">Keamanan akun</p>
                        <p class="mt-1 text-[9px] leading-relaxed text-senat-body">
                            Gunakan kredensial institusional. Jangan bagikan kata sandi atau kode verifikasi kepada siapa pun.
                        </p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    @livewireScripts
</body>
</html>