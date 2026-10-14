@php
    /**
     * Header / Public — Figma node 2006:2
     * "Shared public navigation for all Senat Polines desktop screens."
     */
    $navItems = [
        ['label' => 'Beranda', 'route' => 'public.home', 'children' => []],
        [
            'label' => 'Profil Senat', 'route' => 'public.profile', 'children' => [
                ['label' => 'Visi, Misi & Tujuan', 'route' => 'public.profile'],
                ['label' => 'Tugas & Wewenang', 'route' => 'public.profile.tasks'],
                ['label' => 'Struktur Organisasi', 'route' => 'public.profile.structure'],
                ['label' => 'Direktori Komisi & Anggota', 'route' => 'public.directory'],
            ],
        ],
        [
            'label' => 'Produk Hukum', 'route' => 'public.regulations', 'children' => [
                ['label' => 'Peraturan Senat', 'route' => 'public.regulations'],
                ['label' => 'Keputusan Senat', 'route' => 'public.regulations.decrees'],
                ['label' => 'Standar & Pedoman Akademik', 'route' => 'public.regulations.standards'],
            ],
        ],
        [
            'label' => 'Kanal Aspirasi', 'route' => 'public.aspirations', 'children' => [
                ['label' => 'Sampaikan Aspirasi', 'route' => 'public.aspirations'],
                ['label' => 'Lacak Status Tiket', 'route' => 'public.aspirations.track'],
            ],
        ],
        [
            'label' => 'Arsip', 'route' => 'public.archives', 'children' => [
                ['label' => 'Risalah Sidang', 'route' => 'public.archives'],
                ['label' => 'Laporan Tahunan & LPJ', 'route' => 'public.archives.reports'],
                ['label' => 'Warta & Berita Senat', 'route' => 'public.news'],
            ],
        ],
    ];
@endphp

<header class="sticky top-0 z-40 border-b border-senat-line bg-white">
    <div class="senat-shell flex h-16 items-center">
        <a href="{{ route('public.home') }}" class="flex shrink-0 items-center gap-3">
            <img src="{{ asset('images/logo-polines.png') }}" alt="Logo Politeknik Negeri Semarang"
                 class="h-[38px] w-[38px] rounded-md object-contain">
            <span class="leading-none">
                <span class="block text-[13px] font-bold text-[#0f172a]">SENAT AKADEMIK</span>
                <span class="mt-1 block text-[10px] font-medium text-senat-muted">Politeknik Negeri Semarang</span>
            </span>
        </a>

        <nav class="ml-[142px] hidden items-center gap-0 lg:flex" aria-label="Navigasi utama">
            @foreach ($navItems as $item)
                @php $hasChildren = count($item['children']) > 0 && Route::has($item['children'][0]['route']); @endphp
                @if (! $hasChildren)
                    <a href="{{ route($item['route']) }}"
                       class="px-4 py-2 text-[12px] font-medium transition-colors {{ request()->routeIs($item['route']) ? 'text-senat-primary' : 'text-senat-muted hover:text-senat-primary' }}">
                        {{ $item['label'] }}
                    </a>
                @else
                    <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <button type="button" @click="open = !open" :aria-expanded="open.toString()"
                                class="flex items-center gap-1 px-4 py-2 text-[12px] font-medium text-senat-muted transition-colors hover:text-senat-primary">
                            {{ $item['label'] }}
                            <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.06l3.71-3.83a.75.75 0 111.08 1.04l-4.25 4.39a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <div x-cloak x-show="open" x-transition.opacity
                             class="absolute left-0 top-full z-50 w-[250px] overflow-hidden rounded-lg border border-senat-line bg-white shadow-lg">
                            @foreach ($item['children'] as $child)
                                @continue(! Route::has($child['route']))
                                <a href="{{ route($child['route']) }}"
                                   class="block border-b border-senat-line px-[18px] py-3 text-[12px] font-medium text-senat-ink last:border-b-0 hover:bg-senat-surface">
                                    {{ $child['label'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </nav>

        <div class="ml-auto flex shrink-0 items-center gap-3">
            <a href="{{ route('portal.attendance') }}"
               class="inline-flex h-9 items-center rounded-md border border-senat-border bg-[#f8fafc] px-4 text-[11.5px] font-semibold text-senat-primary transition-colors hover:border-senat-primary">
                🔒&nbsp; Masuk Portal
            </a>
            <a href="{{ route('public.aspirations') }}"
               class="inline-flex h-9 items-center rounded-md bg-senat-primary px-5 text-[12px] font-bold text-white transition-colors hover:bg-senat-primaryHover">
                Sampaikan Aspirasi ›
            </a>
        </div>
    </div>
</header>
