<nav
    x-data="{ mobileOpen: false }"
    class="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b border-slate-200/80"
>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            {{-- Logo & Brand --}}
            <a href="{{ route('public.home') }}" class="flex items-center gap-3 shrink-0 group">
                <div class="w-9 h-9 rounded-lg bg-polines-navy flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14v7" />
                    </svg>
                </div>
                <div class="leading-tight">
                    <p class="text-sm font-extrabold tracking-tight text-slate-900 group-hover:text-polines-navy transition-colors duration-150">
                        SENAT AKADEMIK
                    </p>
                    <p class="text-xs text-slate-500 font-medium">
                        Politeknik Negeri Semarang
                    </p>
                </div>
            </a>

            {{-- Desktop Menu --}}
            <div class="hidden md:flex items-center gap-1">
                <a
                    href="{{ route('public.home') }}"
                    class="px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-150 {{ request()->routeIs('public.home') ? 'text-polines-navy bg-blue-50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}"
                >
                    Beranda
                </a>
                <a
                    href="{{ route('public.profile') }}"
                    class="px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-150 {{ request()->routeIs('public.profile') ? 'text-polines-navy bg-blue-50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}"
                >
                    Profil &amp; Komisi
                </a>
                <a
                    href="{{ route('public.regulations') }}"
                    class="px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-150 {{ request()->routeIs('public.regulations') ? 'text-polines-navy bg-blue-50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}"
                >
                    Produk Hukum
                </a>
                <a
                    href="{{ route('public.aspirations') }}"
                    class="px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-150 {{ request()->routeIs('public.aspirations*') ? 'text-polines-navy bg-blue-50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}"
                >
                    Kanal Aspirasi
                </a>
                <a
                    href="{{ route('public.archives') }}"
                    class="px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-150 {{ request()->routeIs('public.archives') ? 'text-polines-navy bg-blue-50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}"
                >
                    Arsip
                </a>
            </div>

            {{-- Desktop Action --}}
            <div class="hidden md:flex items-center gap-3">
                <a
                    href="{{ route('portal.session') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white bg-polines-navy hover:bg-polines-orange transition-colors duration-150"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                    Portal Sidang
                </a>
            </div>

            {{-- Mobile Hamburger --}}
            <button
                @click="mobileOpen = !mobileOpen"
                class="md:hidden inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors duration-150"
                :aria-expanded="mobileOpen"
                aria-label="Toggle menu navigasi"
            >
                <svg x-show="!mobileOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg x-show="mobileOpen" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

        </div>
    </div>

    {{-- Mobile Menu --}}
    <div
        x-show="mobileOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        class="md:hidden border-t border-slate-200/80 bg-white"
    >
        <div class="px-4 pt-2 pb-4 space-y-1">
            <a
                href="{{ route('public.home') }}"
                class="block px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('public.home') ? 'text-polines-navy bg-blue-50' : 'text-slate-700 hover:bg-slate-100' }}"
            >
                Beranda
            </a>
            <a
                href="{{ route('public.profile') }}"
                class="block px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('public.profile') ? 'text-polines-navy bg-blue-50' : 'text-slate-700 hover:bg-slate-100' }}"
            >
                Profil &amp; Komisi
            </a>
            <a
                href="{{ route('public.regulations') }}"
                class="block px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('public.regulations') ? 'text-polines-navy bg-blue-50' : 'text-slate-700 hover:bg-slate-100' }}"
            >
                Produk Hukum
            </a>
            <a
                href="{{ route('public.aspirations') }}"
                class="block px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('public.aspirations*') ? 'text-polines-navy bg-blue-50' : 'text-slate-700 hover:bg-slate-100' }}"
            >
                Kanal Aspirasi
            </a>
            <a
                href="{{ route('public.archives') }}"
                class="block px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('public.archives') ? 'text-polines-navy bg-blue-50' : 'text-slate-700 hover:bg-slate-100' }}"
            >
                Arsip
            </a>

            <div class="pt-2 border-t border-slate-100">
                <a
                    href="{{ route('portal.session') }}"
                    class="flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-lg text-sm font-semibold text-white bg-polines-navy hover:bg-polines-orange transition-colors duration-150"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                    Portal Sidang
                </a>
            </div>
        </div>
    </div>
</nav>
