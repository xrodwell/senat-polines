<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Portal Internal Senat Akademik Polines')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        senat: {
                            hero: '#003c75',
                            primary: '#003366',
                            primaryHover: '#002850',
                            link: '#0963bd',
                            orange: '#f37021',
                            ink: '#13233a',
                            body: '#52637d',
                            muted: '#667085',
                            line: '#e2e8ee',
                            surface: '#f7f9fc',
                            sidebarLine: '#d9e2ef',
                            badgeBg: '#f2f9ff',
                            badgeLine: '#8cb8e3',
                            border: '#cbd5e1',
                        },
                    },
                },
            },
        };
    </script>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
    </style>

    @stack('head')
</head>
@php
    /**
     * Portal chrome — Figma nodes 2269:728 (Topbar), 2269:739 (Sidebar Anggota),
     * 2269:764 (Sidebar Sekretariat).
     */
    $sidebar = $sidebar ?? 'anggota';
    $navAnggota = [
        ['label' => 'Dashboard', 'route' => 'portal.dashboard', 'icon' => '▦'],
        ['label' => 'Agenda & Presensi', 'route' => 'portal.agenda', 'icon' => '◫'],
        ['label' => 'E-Voting', 'route' => 'portal.voting', 'icon' => '✓'],
        ['label' => 'Notulensi & Risalah', 'route' => 'portal.minutes', 'icon' => '≡'],
        ['label' => 'Aspirasi & RUU Kampus', 'route' => 'portal.aspirations', 'icon' => '◉'],
        ['label' => 'Dokumen & Arsip', 'route' => 'portal.documents', 'icon' => '▣'],
        ['label' => 'Profil Anggota', 'route' => 'portal.profile', 'icon' => '◎'],
    ];
    $navSekretariat = [
        ['label' => 'Dashboard', 'route' => 'portal.secretariat.dashboard', 'icon' => '▦'],
        ['label' => 'Sidang & Agenda', 'route' => 'portal.secretariat.sessions', 'icon' => '◫'],
        ['label' => 'Presensi & QR', 'route' => 'portal.secretariat.attendance', 'icon' => '▤'],
        ['label' => 'Notulensi & Tindak Lanjut', 'route' => 'portal.secretariat.minutes', 'icon' => '≡'],
        ['label' => 'E-Voting', 'route' => 'portal.secretariat.voting', 'icon' => '✓'],
        ['label' => 'Aspirasi Masuk', 'route' => 'portal.secretariat.aspirations', 'icon' => '◉'],
        ['label' => 'Produk, Arsip & Berita', 'route' => 'portal.secretariat.publications', 'icon' => '▣'],
    ];
    $navItems = $sidebar === 'sekretariat' ? $navSekretariat : $navAnggota;
    $roleLabel = $sidebar === 'sekretariat' ? 'Sekretariat Senat' : 'Portal Internal';
@endphp
<body class="bg-senat-surface font-sans text-senat-body antialiased">

    {{-- Topbar (2269:728) --}}
    <header class="relative flex h-16 items-center bg-senat-primary text-white">
        <div class="absolute right-0 top-0 hidden h-16 w-[540px] bg-[#2376c7] lg:block"></div>

        <div class="relative flex w-full items-center gap-3 px-6">
            <button type="button" class="text-[22px] leading-none text-white lg:hidden" aria-label="Buka menu">☰</button>
            <img src="{{ asset('images/logo-polines.png') }}" alt="Logo Polines" class="h-[38px] w-[38px] rounded-lg object-contain">
            <span class="leading-none">
                <span class="block text-[14px] font-bold">SENAT AKADEMIK</span>
                <span class="mt-[6px] block text-[11px] font-medium text-[#bfdbfe]">Politeknik Negeri Semarang</span>
            </span>

            <div class="relative ml-auto flex items-center gap-4">
                <a href="{{ route('public.home') }}" class="relative flex h-9 w-9 items-center justify-center rounded-full bg-white/10" aria-label="Notifikasi">
                    <span class="text-[15px]">🔔</span>
                    <span class="absolute right-1 top-1 h-2 w-2 rounded-full bg-senat-orange"></span>
                </a>
                <div class="relative" x-data="{ open: false }">
                    <button type="button" @click="open = !open"
                            class="flex h-10 w-[240px] items-center justify-between rounded-[20px] bg-[#2b72b8] px-4 text-[13px] font-medium">
                        <span>{{ $roleLabel }}</span>
                        <span class="text-[18px] font-bold leading-none">▾</span>
                    </button>
                    <div x-cloak x-show="open" @click.outside="open = false"
                         class="absolute right-0 top-full z-50 mt-2 w-[240px] overflow-hidden rounded-xl border border-senat-line bg-white shadow-lg">
                        <a href="{{ route('portal.dashboard') }}" class="block border-b border-senat-line px-4 py-3 text-[13px] font-medium text-senat-ink hover:bg-senat-surface">Dashboard Anggota</a>
                        <a href="{{ route('portal.secretariat.dashboard') }}" class="block border-b border-senat-line px-4 py-3 text-[13px] font-medium text-senat-ink hover:bg-senat-surface">Dashboard Sekretariat</a>
                        <a href="{{ route('portal.attendance') }}" class="block border-b border-senat-line px-4 py-3 text-[13px] font-medium text-senat-ink hover:bg-senat-surface">Presensi Cepat (QR)</a>
                        <a href="{{ route('public.home') }}" class="block px-4 py-3 text-[13px] font-medium text-senat-ink hover:bg-senat-surface">Keluar ke Portal Publik</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="flex min-h-[calc(100vh-64px)]">
        {{-- Sidebar (2269:739 / 2269:764) --}}
        <aside class="hidden w-[260px] shrink-0 border-r border-senat-sidebarLine bg-white lg:block">
            <p class="px-[23px] pt-[27px] text-[11px] font-semibold text-senat-muted">MENU UTAMA</p>
            <nav class="mt-[42px] space-y-[6px] px-[15px]" aria-label="Menu portal">
                @foreach ($navItems as $item)
                    @php $active = Route::has($item['route']) && request()->routeIs($item['route']); @endphp
                    <a href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}"
                       class="flex h-[42px] items-center gap-[13px] rounded-[9px] px-[13px] transition-colors {{ $active ? 'bg-[#eef4fb] ring-1 ring-senat-badgeLine' : 'hover:bg-senat-surface' }}">
                        <span class="w-5 text-[15px] font-semibold text-senat-muted" aria-hidden="true">{{ $item['icon'] }}</span>
                        <span class="text-[13px] font-medium {{ $active ? 'font-semibold text-senat-primary' : 'text-senat-ink' }}">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>
            <div class="mx-[23px] mt-6 h-px bg-senat-sidebarLine"></div>
            <p class="px-[23px] pt-4 text-[12px] text-senat-muted">Dukungan Sekretariat</p>
        </aside>

        <main class="min-w-0 flex-1 px-6 py-8 lg:px-10">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
