@php
    /**
     * Header / Public — Figma node 2006:2 (desktop ≥1024) + 2352:1279 (mobile 390: logo + ☰, tinggi 60px).
     * Di bawah 1024 nav menjadi drawer 320px dari kanan (spec 2352:1334 / 2354:1090),
     * tutup via X, backdrop, Escape, atau pilih menu; tap target ≥44px; focus ring 2px #155EAD.
     */
    $navItems = [
        ['label' => 'Beranda', 'route' => 'public.home', 'icon' => 'dashboard', 'children' => []],
        [
            'label' => 'Profil Senat', 'route' => 'public.profile', 'icon' => 'user', 'children' => [
                ['label' => 'Visi, Misi & Tujuan', 'route' => 'public.profile'],
                ['label' => 'Tugas & Wewenang', 'route' => 'public.profile.tasks'],
                ['label' => 'Struktur Organisasi', 'route' => 'public.profile.structure'],
                ['label' => 'Direktori Komisi & Anggota', 'route' => 'public.directory'],
            ],
        ],
        [
            'label' => 'Produk Hukum', 'route' => 'public.regulations', 'icon' => 'minutes', 'children' => [
                ['label' => 'Peraturan Senat', 'route' => 'public.regulations'],
                ['label' => 'Keputusan Senat', 'route' => 'public.regulations.decrees'],
                ['label' => 'Standar & Pedoman Akademik', 'route' => 'public.regulations.standards'],
            ],
        ],
        [
            'label' => 'Kanal Aspirasi', 'route' => 'public.aspirations', 'icon' => 'aspiration', 'children' => [
                ['label' => 'Sampaikan Aspirasi', 'route' => 'public.aspirations'],
                ['label' => 'Lacak Status Tiket', 'route' => 'public.aspirations.track'],
            ],
        ],
        [
            'label' => 'Arsip', 'route' => 'public.archives', 'icon' => 'folder', 'children' => [
                ['label' => 'Risalah Sidang', 'route' => 'public.archives'],
                ['label' => 'Laporan Tahunan & LPJ', 'route' => 'public.archives.reports'],
                ['label' => 'Warta & Berita Senat', 'route' => 'public.news'],
            ],
        ],
    ];
@endphp

<header x-data="{ open: false }" @keydown.escape.window="open = false" class="sticky top-0 z-40 border-b border-senat-line bg-white">
    <div class="senat-shell flex h-[60px] items-center lg:h-16">
        <a href="{{ route('public.home') }}" class="senat-tap flex shrink-0 items-center gap-3">
            <img src="{{ asset('images/logo-polines.png') }}" alt="Logo Politeknik Negeri Semarang"
                 class="h-9 w-9 rounded-md object-contain lg:h-[38px] lg:w-[38px]">
            <span class="leading-none">
                <span class="block text-[13px] font-bold text-[#0f172a]">SENAT AKADEMIK</span>
                <span class="mt-1 hidden text-[10px] font-medium text-senat-muted sm:block">Politeknik Negeri Semarang</span>
            </span>
        </a>

        {{-- Desktop nav (≥1024) --}}
        <nav class="ml-auto hidden items-center lg:flex xl:ml-[142px]" aria-label="Navigasi utama">
            @foreach ($navItems as $item)
                @php $hasChildren = count($item['children']) > 0 && Route::has($item['children'][0]['route']); @endphp
                @if (! $hasChildren)
                    <a href="{{ route($item['route']) }}"
                       class="senat-tap flex items-center px-4 py-2 text-[12px] font-medium transition-colors {{ request()->routeIs($item['route']) ? 'text-senat-primary' : 'text-senat-muted hover:text-senat-primary' }}">
                        {{ $item['label'] }}
                    </a>
                @else
                    <div class="relative" x-data="{ sub: false }" @mouseenter="sub = true" @mouseleave="sub = false">
                        <button type="button" @click="sub = !sub" :aria-expanded="sub.toString()"
                                class="senat-tap flex items-center gap-1 px-4 py-2 text-[12px] font-medium text-senat-muted transition-colors hover:text-senat-primary">
                            {{ $item['label'] }}
                            <x-senat.icon name="chevron-down" class="h-3 w-3" />
                        </button>
                        <div x-cloak x-show="sub" x-transition.opacity
                             class="absolute left-0 top-full z-50 w-[250px] overflow-hidden rounded-lg border border-senat-line bg-white shadow-lg">
                            @foreach ($item['children'] as $child)
                                @continue(! Route::has($child['route']))
                                <a href="{{ route($child['route']) }}"
                                   class="flex min-h-[44px] items-center border-b border-senat-line px-[18px] text-[12px] font-medium text-senat-ink last:border-b-0 hover:bg-senat-surface">
                                    {{ $child['label'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </nav>

        <div class="ml-auto flex shrink-0 items-center gap-2 lg:ml-6 lg:gap-3">
            <a href="{{ route('admin.preview') }}"
               class="senat-tap hidden h-9 items-center gap-1.5 rounded-md bg-senat-primary px-3 text-[11.5px] font-semibold text-white shadow-sm transition-colors hover:bg-senat-primaryHover sm:inline-flex">
                <span class="inline-block h-2 w-2 rounded-full bg-emerald-400"></span>
                Panel Admin
            </a>
            <a href="{{ route('portal.login') }}"
               class="senat-tap hidden h-9 items-center gap-1.5 rounded-md border border-senat-border bg-[#f8fafc] px-4 text-[11.5px] font-semibold text-senat-primary transition-colors hover:border-senat-primary md:inline-flex">
                <x-senat.icon name="lock" class="h-[14px] w-[14px]" />
                Masuk Portal
            </a>
            <a href="{{ route('public.aspirations') }}"
               class="senat-tap hidden h-9 items-center rounded-md bg-senat-primary px-5 text-[12px] font-bold text-white transition-colors hover:bg-senat-primaryHover md:inline-flex">
                Sampaikan Aspirasi
            </a>

            <button type="button" @click="open = true" :aria-expanded="open.toString()"
                    aria-controls="senat-public-drawer" aria-label="Buka menu navigasi"
                    class="senat-tap -mr-2 flex h-11 w-11 items-center justify-center rounded-md text-senat-primary lg:hidden">
                <x-senat.icon name="menu" class="h-[22px] w-[22px]" />
            </button>
        </div>
    </div>

    {{-- Fallback tanpa JavaScript --}}
    <noscript>
        <div class="border-b border-senat-line bg-white lg:hidden">
            <nav class="senat-shell flex flex-wrap gap-x-4 gap-y-2 py-3 text-[12px] font-medium text-senat-muted">
                @foreach ($navItems as $item)
                    @continue(! Route::has($item['route']))
                    <a href="{{ route($item['route']) }}">{{ $item['label'] }}</a>
                @endforeach
                <a href="{{ route('portal.login') }}">Masuk Portal</a>
            </nav>
        </div>
    </noscript>

    {{-- Drawer nav (320px dari kanan, backdrop 40%) --}}
    <div x-cloak x-show="open" class="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true" aria-label="Navigasi">
        <div class="senat-drawer-backdrop absolute inset-0" @click="open = false"></div>
        <aside id="senat-public-drawer"
               x-transition:enter="transition ease-out duration-200"
               x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
               x-transition:leave="transition ease-in duration-150"
               x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
               class="absolute right-0 top-0 flex h-full w-[320px] max-w-[86vw] flex-col bg-white shadow-2xl">
            <div class="flex h-[60px] shrink-0 items-center justify-between border-b border-senat-line px-5">
                <span class="text-[13px] font-bold text-senat-ink">SENAT AKADEMIK</span>
                <button type="button" @click="open = false" aria-label="Tutup menu"
                        class="senat-tap -mr-2 flex h-11 w-11 items-center justify-center rounded-md text-senat-muted">
                    <x-senat.icon name="close" class="h-5 w-5" />
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto overscroll-contain px-3 py-3" aria-label="Navigasi utama (drawer)">
                @foreach ($navItems as $item)
                    @php $hasChildren = count($item['children']) > 0 && Route::has($item['children'][0]['route']); @endphp
                    @if (! $hasChildren)
                        <a href="{{ route($item['route']) }}"
                           class="senat-tap flex min-h-[48px] items-center gap-3 rounded-lg px-3 text-[14px] font-semibold {{ request()->routeIs($item['route']) ? 'bg-[#eef4fb] text-senat-primary' : 'text-senat-ink' }}">
                            <x-senat.icon :name="$item['icon']" class="h-5 w-5 text-senat-muted" />
                            {{ $item['label'] }}
                        </a>
                    @else
                        @php $subOpen = request()->routeIs($item['route']) || collect($item['children'])->contains(fn ($c) => request()->routeIs($c['route'])); @endphp
                        <div x-data="{ sub: {{ $subOpen ? 'true' : 'false' }} }">
                            <button type="button" @click="sub = !sub" :aria-expanded="sub.toString()"
                                    class="senat-tap flex min-h-[48px] w-full items-center gap-3 rounded-lg px-3 text-[14px] font-semibold text-senat-ink">
                                <x-senat.icon :name="$item['icon']" class="h-5 w-5 text-senat-muted" />
                                <span class="flex-1 text-left">{{ $item['label'] }}</span>
                                <x-senat.icon name="chevron-down" class="h-4 w-4 text-senat-muted transition-transform" x-bind:class="sub && 'rotate-180'" />
                            </button>
                            <div x-show="sub" x-cloak class="pb-1">
                                @foreach ($item['children'] as $child)
                                    @continue(! Route::has($child['route']))
                                    <a href="{{ route($child['route']) }}"
                                       class="senat-tap flex min-h-[44px] items-center rounded-lg pl-[44px] pr-3 text-[13px] font-medium {{ request()->routeIs($child['route']) ? 'text-senat-primary' : 'text-senat-body' }}">
                                        {{ $child['label'] }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </nav>

            <div class="shrink-0 space-y-2 border-t border-senat-line p-4">
                <a href="{{ route('admin.preview') }}"
                   class="senat-tap flex min-h-[48px] w-full items-center justify-center gap-2 rounded-lg bg-senat-primary text-[13px] font-bold text-white shadow-sm">
                    <span class="inline-block h-2 w-2 rounded-full bg-emerald-400"></span>
                    Masuk Panel Admin (Sekretariat)
                </a>
                <a href="{{ route('public.aspirations') }}"
                   class="senat-tap flex min-h-[48px] w-full items-center justify-center rounded-lg border border-senat-border bg-white text-[13px] font-bold text-senat-primary">
                    Sampaikan Aspirasi
                </a>
                <a href="{{ route('portal.login') }}"
                   class="senat-tap flex min-h-[48px] w-full items-center justify-center gap-2 rounded-lg border border-senat-border bg-[#f8fafc] text-[13px] font-semibold text-senat-primary">
                    <x-senat.icon name="lock" class="h-4 w-4" />
                    Masuk Portal
                </a>
            </div>
        </aside>
    </div>
</header>
