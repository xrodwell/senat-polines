<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#003366">
    <title>@yield('title', 'Portal Internal Senat Akademik Polines')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])

    @include('components.senat.styles')

    @stack('head')
</head>
@php
    /**
     * Portal chrome — Figma 2269:728 (Topbar), 2269:739 (Sidebar Anggota), 2269:764 (Sidebar Sekretariat).
     * Responsif (2352:1306 tablet / 2352:1335 mobile): sidebar → drawer 320px dari kanan (<1024),
     * bottom navigation di mobile, KPI 2 kolom, tap target ≥44px.
     */
    $sidebar = $sidebar ?? 'anggota';
    $navAnggota = [
        ['label' => 'Dashboard', 'route' => 'portal.dashboard', 'icon' => 'dashboard'],
        ['label' => 'Agenda & Presensi', 'route' => 'portal.agenda', 'icon' => 'calendar'],
        ['label' => 'E-Voting', 'route' => 'portal.voting', 'icon' => 'vote'],
        ['label' => 'Notulensi & Risalah', 'route' => 'portal.minutes', 'icon' => 'minutes'],
        ['label' => 'Aspirasi & RUU Kampus', 'route' => 'portal.aspirations', 'icon' => 'aspiration'],
        ['label' => 'Dokumen & Arsip', 'route' => 'portal.documents', 'icon' => 'folder'],
        ['label' => 'Profil Anggota', 'route' => 'portal.profile', 'icon' => 'user'],
    ];
    $navSekretariat = [
        ['label' => 'Dashboard', 'route' => 'portal.secretariat.dashboard', 'icon' => 'dashboard'],
        ['label' => 'Sidang & Agenda', 'route' => 'portal.secretariat.sessions', 'icon' => 'calendar'],
        ['label' => 'Presensi & QR', 'route' => 'portal.secretariat.attendance', 'icon' => 'scan'],
        ['label' => 'Notulensi & Tindak Lanjut', 'route' => 'portal.secretariat.minutes', 'icon' => 'minutes'],
        ['label' => 'E-Voting', 'route' => 'portal.secretariat.voting', 'icon' => 'vote'],
        ['label' => 'Aspirasi Masuk', 'route' => 'portal.secretariat.aspirations', 'icon' => 'aspiration'],
        ['label' => 'Produk, Arsip & Berita', 'route' => 'portal.secretariat.publications', 'icon' => 'folder'],
    ];
    $navItems = $sidebar === 'sekretariat' ? $navSekretariat : $navAnggota;
    $roleLabel = $sidebar === 'sekretariat' ? 'Sekretariat Senat' : 'Portal Internal';
    $bottomNav = [
        ['label' => 'Dashboard', 'route' => $navItems[0]['route'], 'icon' => 'dashboard'],
        ['label' => 'Agenda', 'route' => $navItems[1]['route'], 'icon' => 'calendar'],
        ['label' => 'Voting', 'route' => $navItems[4]['route'], 'icon' => 'vote'],
        ['label' => 'Aspirasi', 'route' => $navItems[5]['route'], 'icon' => 'aspiration'],
        ['label' => 'Profil', 'route' => 'portal.profile', 'icon' => 'user'],
    ];
@endphp
<body x-data="{ nav: false }" @keydown.escape.window="nav = false"
      class="bg-senat-surface font-sans text-senat-body antialiased">

    {{-- Topbar (2269:728) — 60px mobile, 64px ≥1024 --}}
    <header class="sticky top-0 z-40 flex h-[60px] items-center bg-senat-primary text-white lg:h-16">
        <div class="relative flex w-full items-center gap-3 px-5 lg:px-6">
            <img src="{{ asset('images/logo-polines.png') }}" alt="Logo Polines" class="h-9 w-9 rounded-lg object-contain lg:h-[38px] lg:w-[38px]">
            <span class="leading-none">
                <span class="block text-[14px] font-bold">SENAT<span class="hidden sm:inline"> AKADEMIK</span></span>
                <span class="mt-[6px] hidden text-[11px] font-medium text-[#bfdbfe] sm:block">Politeknik Negeri Semarang</span>
            </span>

            <div class="ml-auto flex items-center gap-2 lg:gap-4">
                <a href="{{ route('public.home') }}" class="senat-tap relative flex h-11 w-11 items-center justify-center rounded-full hover:bg-white/10" aria-label="Notifikasi">
                    <x-senat.icon name="bell" class="h-[19px] w-[19px]" />
                    <span class="absolute right-2.5 top-2.5 h-2 w-2 rounded-full bg-senat-orange"></span>
                </a>

                {{-- Role switcher (desktop) --}}
                <div class="relative hidden lg:block" x-data="{ open: false }">
                    <button type="button" @click="open = !open" :aria-expanded="open.toString()"
                            class="senat-tap flex h-10 w-[240px] items-center justify-between rounded-[20px] bg-[#2b72b8] px-4 text-[13px] font-medium">
                        <span>{{ $roleLabel }}</span>
                        <x-senat.icon name="chevron-down" class="h-4 w-4" />
                    </button>
                    <div x-cloak x-show="open" @click.outside="open = false" x-transition.opacity
                         class="absolute right-0 top-full z-50 mt-2 w-[240px] overflow-hidden rounded-xl border border-senat-line bg-white shadow-lg">
                        <a href="{{ route('portal.dashboard') }}" class="flex min-h-[44px] items-center border-b border-senat-line px-4 text-[13px] font-medium text-senat-ink hover:bg-senat-surface">Dashboard Anggota</a>
                        <a href="{{ route('portal.secretariat.dashboard') }}" class="flex min-h-[44px] items-center border-b border-senat-line px-4 text-[13px] font-medium text-senat-ink hover:bg-senat-surface">Dashboard Sekretariat</a>
                        <a href="{{ route('portal.attendance') }}" class="flex min-h-[44px] items-center border-b border-senat-line px-4 text-[13px] font-medium text-senat-ink hover:bg-senat-surface">Presensi Cepat (QR)</a>
                        <a href="{{ route('public.home') }}" class="flex min-h-[44px] items-center px-4 text-[13px] font-medium text-senat-ink hover:bg-senat-surface">Keluar ke Portal Publik</a>
                    </div>
                </div>

                {{-- Hamburger (<1024) --}}
                <button type="button" @click="nav = true" :aria-expanded="nav.toString()"
                        aria-controls="senat-portal-drawer" aria-label="Buka menu navigasi"
                        class="senat-tap -mr-2 flex h-11 w-11 items-center justify-center rounded-md text-white lg:hidden">
                    <x-senat.icon name="menu" class="h-[22px] w-[22px]" />
                </button>
            </div>
        </div>
    </header>

    <div class="flex min-h-[calc(100vh-60px)] lg:min-h-[calc(100vh-64px)]">
        {{-- Sidebar (2269:739 / 2269:764) — desktop ≥1024 --}}
        <aside class="hidden w-[260px] shrink-0 border-r border-senat-sidebarLine bg-white lg:block">
            <p class="px-[23px] pt-[27px] text-[11px] font-semibold text-senat-muted">MENU UTAMA</p>
            <nav class="mt-[42px] space-y-[6px] px-[15px]" aria-label="Menu portal">
                @foreach ($navItems as $item)
                    @php $active = Route::has($item['route']) && request()->routeIs($item['route']); @endphp
                    <a href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}"
                       @if ($active) aria-current="page" @endif
                       class="senat-tap flex h-[42px] items-center gap-[13px] rounded-[9px] px-[13px] transition-colors {{ $active ? 'bg-[#eef4fb] ring-1 ring-senat-badgeLine' : 'hover:bg-senat-surface' }}">
                        <x-senat.icon :name="$item['icon']" class="h-5 w-5 shrink-0 text-senat-muted" />
                        <span class="truncate text-[13px] font-medium {{ $active ? 'font-semibold text-senat-primary' : 'text-senat-ink' }}">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>
            <div class="mx-[23px] mt-6 h-px bg-senat-sidebarLine"></div>
            <p class="px-[23px] pt-4 text-[12px] text-senat-muted">Dukungan Sekretariat</p>
        </aside>

        <main class="min-w-0 flex-1 px-5 pb-28 pt-6 lg:px-10 lg:pb-10 lg:pt-8">
            @yield('content')
        </main>
    </div>

    {{-- Drawer navigasi (320px dari kanan, backdrop 40%) --}}
    <div x-cloak x-show="nav" class="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true" aria-label="Menu portal">
        <div class="senat-drawer-backdrop absolute inset-0" @click="nav = false"></div>
        <aside id="senat-portal-drawer"
               x-transition:enter="transition ease-out duration-200"
               x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
               x-transition:leave="transition ease-in duration-150"
               x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
               class="absolute right-0 top-0 flex h-full w-[320px] max-w-[86vw] flex-col bg-white shadow-2xl">
            <div class="flex h-[60px] shrink-0 items-center justify-between border-b border-senat-sidebarLine px-5">
                <span class="leading-none">
                    <span class="block text-[13px] font-bold text-senat-ink">SENAT AKADEMIK</span>
                    <span class="mt-1 block text-[10px] font-medium text-senat-muted">{{ $roleLabel }}</span>
                </span>
                <button type="button" @click="nav = false" aria-label="Tutup menu"
                        class="senat-tap -mr-2 flex h-11 w-11 items-center justify-center rounded-md text-senat-muted">
                    <x-senat.icon name="close" class="h-5 w-5" />
                </button>
            </div>
            <nav class="flex-1 overflow-y-auto overscroll-contain px-3 py-3" aria-label="Menu portal (drawer)">
                @foreach ($navItems as $item)
                    @php $active = Route::has($item['route']) && request()->routeIs($item['route']); @endphp
                    <a href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}"
                       @if ($active) aria-current="page" @endif
                       class="senat-tap flex min-h-[48px] items-center gap-3 rounded-lg px-3 text-[14px] font-semibold {{ $active ? 'bg-[#eef4fb] text-senat-primary' : 'text-senat-ink' }}">
                        <x-senat.icon :name="$item['icon']" class="h-5 w-5 shrink-0 text-senat-muted" />
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>
            <div class="shrink-0 border-t border-senat-sidebarLine p-4">
                <a href="{{ route('public.home') }}"
                   class="senat-tap flex min-h-[48px] w-full items-center justify-center gap-2 rounded-lg border border-senat-border bg-[#f8fafc] text-[13px] font-semibold text-senat-primary">
                    <x-senat.icon name="external" class="h-4 w-4" />
                    Keluar ke Portal Publik
                </a>
            </div>
        </aside>
    </div>

    {{-- Bottom navigation (mobile, 2352:1335) --}}
    @include('components.senat.bottom-nav', ['items' => $bottomNav])

    @stack('scripts')
</body>
</html>
