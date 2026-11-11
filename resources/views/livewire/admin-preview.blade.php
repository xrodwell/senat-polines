@php
    $groups = \App\Livewire\AdminPreview::GROUPS;
    $currentGroup = null;

    foreach ($groups as $group => $items) {
        if (in_array($active, $items, true)) {
            $currentGroup = $group;
            break;
        }
    }
@endphp

<div class="min-h-screen bg-senat-surface text-senat-body antialiased"
     x-data="{
         sidebarCollapsed: localStorage.getItem('admin_sidebar_collapsed') === 'true',
         animated: false,
         toggleSidebar() {
             this.animated = true;
             this.sidebarCollapsed = !this.sidebarCollapsed;
             localStorage.setItem('admin_sidebar_collapsed', this.sidebarCollapsed);
         }
     }">

    {{-- Topbar khas Portal Senat (Figma 2269:728) --}}
    <header class="sticky top-0 z-40 flex h-[60px] items-center bg-senat-primary text-white lg:h-16 shadow-sm">
        <div class="relative flex w-full items-center gap-3 px-4 sm:px-5 lg:px-6">
            {{-- Tombol Toggle Sidebar --}}
            <button type="button"
                    @click="toggleSidebar()"
                    class="senat-tap flex h-9 w-9 items-center justify-center rounded-lg border border-white/20 bg-white/10 text-white hover:bg-white/20 transition cursor-pointer shrink-0"
                    :title="sidebarCollapsed ? 'Perluas Sidebar' : 'Ciutkan Sidebar'">
                <x-senat.icon name="menu" class="h-4 w-4" />
            </button>

            <img src="{{ asset('images/logo-polines.png') }}" alt="Logo Polines" class="h-9 w-9 rounded-lg object-contain lg:h-[38px] lg:w-[38px]">
            <span class="leading-none">
                <span class="block text-[14px] font-bold">SENAT<span class="hidden sm:inline"> AKADEMIK</span></span>
                <span class="mt-[6px] hidden text-[11px] font-medium text-[#bfdbfe] sm:block">Politeknik Negeri Semarang</span>
            </span>

            <div class="ml-auto flex items-center gap-2 lg:gap-4">
                {{-- Quick Impersonate Dropdown --}}
                <div class="relative hidden sm:block" x-data="{ openImpersonate: false }">
                    <button type="button" @click="openImpersonate = !openImpersonate" :aria-expanded="openImpersonate.toString()"
                            class="senat-tap flex h-9 items-center gap-1.5 rounded-lg border border-amber-400/40 bg-amber-500/20 px-3 text-[12px] font-semibold text-amber-200 transition hover:bg-amber-500/30 cursor-pointer"
                            title="Beralih akun ke anggota Senat">
                        <svg class="h-3.5 w-3.5 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span>Login Sebagai</span>
                        <x-senat.icon name="chevron-down" class="h-3 w-3 text-amber-200/70" />
                    </button>
                    <div x-cloak x-show="openImpersonate" @click.outside="openImpersonate = false" x-transition.opacity
                         class="absolute right-0 top-full z-50 mt-2 w-72 max-h-80 overflow-y-auto rounded-xl border border-slate-200 bg-white p-2 shadow-2xl text-slate-800">
                        <div class="px-2.5 py-1.5 border-b border-slate-100 text-[10.5px] font-bold text-slate-500 uppercase tracking-wider">
                            Pilih Anggota Senat
                        </div>
                        <div class="divide-y divide-slate-100">
                            @forelse ($allLecturers ?? [] as $lecturer)
                                @if (($lecturer['id_user'] ?? 0) != session('auth_user_id'))
                                    <form method="POST" action="{{ route('admin.impersonate', $lecturer['id_user']) }}" class="m-0">
                                        @csrf
                                        <button type="submit" class="w-full text-left px-2.5 py-2 rounded-lg hover:bg-slate-50 transition flex flex-col cursor-pointer">
                                            <span class="text-xs font-semibold text-slate-900 truncate">{{ $lecturer['nama'] }}</span>
                                            <span class="text-[11px] text-slate-500">{{ $lecturer['komisi'] ?? 'Komisi Senat' }} &middot; {{ $lecturer['nim_nip'] ?: '-' }}</span>
                                        </button>
                                    </form>
                                @endif
                            @empty
                                <div class="px-2.5 py-3 text-center text-xs text-slate-500">
                                    Tidak ada data anggota
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Badge Status Sistem --}}
                <div class="hidden items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-[11.5px] font-medium text-white sm:flex">
                    <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                    <span>Admin Sekretariat</span>
                </div>

                <a href="{{ route('portal.dashboard') }}"
                   class="senat-tap flex h-9 items-center gap-1.5 rounded-lg border border-white/20 bg-white/10 px-3 text-[12px] font-medium text-white transition hover:bg-white/20">
                    <x-senat.icon name="user" class="h-3.5 w-3.5 text-[#bfdbfe]" />
                    <span>Portal Anggota</span>
                </a>

                <a href="{{ route('public.home') }}"
                   class="senat-tap flex h-9 items-center gap-1.5 rounded-lg border border-white/20 bg-white/10 px-3 text-[12px] font-medium text-white transition hover:bg-white/20">
                    <x-senat.icon name="dashboard" class="h-3.5 w-3.5 text-[#bfdbfe]" />
                    <span class="hidden md:inline">Portal Publik</span>
                </a>
            </div>
        </div>
    </header>

    <div class="flex min-h-[calc(100vh-60px)] lg:min-h-[calc(100vh-64px)]">

        {{-- Sidebar (Figma 2269:764 style modern & collapsible) --}}
        <aside x-data="{
                   init() {
                       this.$nextTick(() => {
                           const saved = sessionStorage.getItem('admin_sidebar_scroll');
                           if (saved) {
                               $el.scrollTop = parseInt(saved, 10);
                           }
                       });
                   }
               }"
               @scroll.passive="sessionStorage.setItem('admin_sidebar_scroll', $el.scrollTop)"
               class="w-full shrink-0 border-b border-senat-sidebarLine bg-white lg:sticky lg:top-16 lg:h-[calc(100vh-64px)] lg:w-[280px] lg:overflow-y-auto lg:border-b-0 lg:border-r flex flex-col justify-between"
               :class="{
                   'transition-all duration-200 ease-in-out': animated,
                   'lg:!w-[72px]': sidebarCollapsed,
                   'lg:w-[280px]': !sidebarCollapsed
               }"
               aria-label="Navigasi Admin Sekretariat">
            
            <div>
                <div class="border-b border-slate-100"
                     :class="{
                         'transition-all duration-200': animated,
                         'p-3 text-center': sidebarCollapsed,
                         'px-5 pt-5 pb-4': !sidebarCollapsed
                     }">
                    <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : 'justify-between gap-3'">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#003366] text-white shadow-xs"
                                 :title="sidebarCollapsed ? 'Admin Sekretariat' : ''">
                                <x-senat.icon name="dashboard" class="h-4 w-4 text-amber-400" />
                            </div>
                            <div class="min-w-0" x-show="!sidebarCollapsed" x-transition.opacity>
                                <div class="flex items-center gap-1.5">
                                    <p class="text-[11px] font-bold uppercase tracking-[0.08em] text-slate-900">ADMIN SEKRETARIAT</p>
                                </div>
                                <p class="mt-0.5 text-[11px] text-slate-500 truncate">Tata Kelola Sidang &amp; Publikasi</p>
                            </div>
                        </div>

                        {{-- Tombol Toggle Inline --}}
                        <button type="button"
                                @click="toggleSidebar()"
                                x-show="!sidebarCollapsed"
                                class="hidden lg:flex h-7 w-7 items-center justify-center rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition cursor-pointer"
                                title="Ciutkan Sidebar">
                            <x-senat.icon name="chevron-left" class="h-4 w-4" />
                        </button>
                    </div>
                </div>

                <nav class="mt-4 space-y-6 pb-6"
                     :class="{
                         'transition-all duration-200': animated,
                         'px-2': sidebarCollapsed,
                         'px-3.5': !sidebarCollapsed
                     }"
                     aria-label="Menu Admin">
                    @php
                        $iconMap = [
                            'Dashboard Rapat' => 'dashboard',
                            'Jadwal & Agenda Sidang' => 'calendar',
                            'Presensi & Verifikasi QR' => 'scan',
                            'Kuorum & Kehadiran Anggota' => 'users',
                            'Risalah & Notulensi Sidang' => 'minutes',
                            'E-Voting & Pengambilan Keputusan' => 'vote',
                            'Pengaturan Informasi & Hero' => 'image',
                            'Profil, Tupoksi & Struktur' => 'folder',
                            'Direktori Komisi & Anggota' => 'user',
                            'Manajemen Berita & Siaran Pers' => 'file-text',
                            'Katalog Regulasi & Produk Hukum' => 'folder',
                            'Repositori Arsip & Dokumen' => 'inbox',
                            'Pusat Aspirasi & Tindak Lanjut' => 'aspiration',
                        ];
                    @endphp

                    @foreach ($groups as $group => $items)
                        <div wire:key="group-{{ \Illuminate\Support\Str::slug($group) }}" aria-labelledby="grup-{{ $loop->index }}">
                            <div x-show="!sidebarCollapsed" x-transition.opacity>
                                <p id="grup-{{ $loop->index }}" class="px-3 pb-2 text-[10px] font-bold uppercase tracking-[0.08em] text-slate-400">
                                    {{ $group }}
                                </p>
                            </div>
                            <div x-show="sidebarCollapsed" class="my-2 border-t border-slate-100"></div>

                            <div class="space-y-1">
                                @foreach ($items as $item)
                                    @php
                                        $isActive = ($active === $item);
                                        $itemSlug = \Illuminate\Support\Str::slug($item);
                                        $iconName = $iconMap[$item] ?? 'dot';
                                    @endphp
                                    <a href="{{ route('admin.preview', ['tab' => $item]) }}"
                                       wire:click.prevent="select('{{ $item }}')"
                                       wire:key="menu-link-{{ $itemSlug }}"
                                       @if ($isActive) aria-current="page" @endif
                                       :title="sidebarCollapsed ? '{{ $item }}' : ''"
                                       class="group relative flex min-h-[38px] w-full items-center rounded-xl transition-all duration-150 {{ $isActive ? 'bg-[#003366] text-white shadow-xs font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 font-medium' }}"
                                       :class="sidebarCollapsed ? 'justify-center p-2' : 'gap-3 px-3 py-2'">
                                        <span class="shrink-0 transition-transform duration-150 group-hover:scale-110 {{ $isActive ? 'text-amber-300' : 'text-slate-400 group-hover:text-slate-700' }}">
                                            <x-senat.icon :name="$iconName" class="h-4 w-4" />
                                        </span>
                                        <span class="truncate text-[12px] flex-1" x-show="!sidebarCollapsed" x-transition.opacity>{{ $item }}</span>
                                        @if ($isActive)
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-400 shrink-0" aria-hidden="true" x-show="!sidebarCollapsed"></span>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </nav>
            </div>

            <div class="mt-auto border-t border-slate-100 bg-slate-50/60"
                 :class="{
                     'transition-all duration-200': animated,
                     'p-2 text-center': sidebarCollapsed,
                     'p-3': !sidebarCollapsed
                 }">
                <div class="flex items-center rounded-xl bg-white border border-slate-200/80 shadow-2xs"
                     :class="sidebarCollapsed ? 'justify-center p-2' : 'justify-between p-2'">
                    <div class="flex items-center gap-2.5 min-w-0" :title="sidebarCollapsed ? 'Administrator (Sekretariat Senat)' : ''">
                        <div class="h-8 w-8 rounded-lg bg-[#003366]/10 text-[#003366] flex items-center justify-center text-xs font-bold shrink-0">
                            AS
                        </div>
                        <div class="min-w-0" x-show="!sidebarCollapsed" x-transition.opacity>
                            <p class="text-[11.5px] font-bold text-slate-900 truncate">Administrator</p>
                            <p class="text-[10px] text-emerald-600 font-medium flex items-center gap-1">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                Sekretariat Senat
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('public.home') }}"
                       x-show="!sidebarCollapsed"
                       title="Ke Portal Publik"
                       class="h-7 w-7 rounded-lg border border-slate-200 hover:border-slate-300 hover:bg-slate-50 flex items-center justify-center text-slate-500 hover:text-slate-800 transition">
                        <x-senat.icon name="external" class="h-3.5 w-3.5" />
                    </a>
                </div>
            </div>
        </aside>

        {{-- Area Konten Utama Menggunakan Persis Desain Portal Sekretariat --}}
        <main class="min-w-0 flex-1 px-5 pb-20 pt-6 lg:px-10 lg:pb-12 lg:pt-8">
            <div class="mx-auto max-w-[1116px] space-y-6">

                {{-- Alert Flash --}}
                @if ($flashMessage)
                    <div class="flex items-center justify-between rounded-[10px] bg-[#eaf8f0] px-4 py-3 text-[13px] text-[#116a3c] shadow-xs">
                        <div class="flex items-center gap-2">
                            <x-senat.icon name="check" class="h-4 w-4 shrink-0 text-[#116a3c]" />
                            <span>{{ $flashMessage }}</span>
                        </div>
                        <button type="button" wire:click="$set('flashMessage', '')" class="text-base font-bold text-[#116a3c]">×</button>
                    </div>
                @endif

                {{-- Header Layar --}}
                <header class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.08em] text-senat-orange">
                            <span>Admin Sekretariat</span>
                            <span>/</span>
                            <span>{{ $currentGroup ?? 'Umum' }}</span>
                        </div>
                        <h1 class="mt-1 text-[25px] font-bold leading-tight text-senat-ink">
                            {{ $active }}
                        </h1>
                    </div>

                    <div class="flex items-center gap-2">
                        @if (in_array($active, ['Presensi & Verifikasi QR', 'Kuorum & Kehadiran Anggota']))
                            <button type="button"
                                    wire:click="exportExcel"
                                    class="senat-tap inline-flex h-9 items-center justify-center rounded-md bg-[#116a3c] px-3.5 text-[12px] font-semibold text-white transition hover:bg-[#0d542f] shadow-sm"
                                    title="Unduh Rekap Kehadiran dan Status Kuorum ke Excel (.xls)">
                                <span>Ekspor Excel (.xls)</span>
                            </button>
                            <button type="button"
                                    wire:click="exportCsv"
                                    class="senat-tap inline-flex h-9 items-center justify-center rounded-md bg-[#116a3c] px-3.5 text-[12px] font-semibold text-white transition hover:bg-[#0d542f] shadow-sm"
                                    title="Unduh Rekap Kehadiran ke CSV (.csv)">
                                <span>Ekspor CSV</span>
                            </button>
                        @else
                            <button type="button"
                                    wire:click="saveAction(@js($active))"
                                    class="senat-tap inline-flex h-9 items-center gap-1.5 rounded-md bg-senat-primary px-4 text-[12px] font-semibold text-white transition hover:bg-senat-primaryHover">
                                <x-senat.icon name="check" class="h-3.5 w-3.5" />
                                <span>Simpan Perubahan</span>
                            </button>
                        @endif
                    </div>
                </header>



            {{-- 1. SCREEN: Dashboard Rapat --}}
            {{-- 1. SCREEN: Dashboard Rapat --}}
            @if ($active === 'Dashboard Rapat' || $active === 'Dashboard')
                @include('livewire.admin.tabs.dashboard-rapat')

            {{-- 2. SCREEN: Jadwal & Agenda Sidang (Figma 13B) --}}
            @elseif ($active === 'Jadwal & Agenda Sidang')
                @include('livewire.admin.tabs.jadwal-agenda')

            {{-- 3. SCREEN: Presensi & Verifikasi QR (Figma 13D) --}}
            @elseif ($active === 'Presensi & Verifikasi QR')
                @include('livewire.admin.tabs.presensi-qr')

            {{-- 4. SCREEN: Kuorum & Kehadiran Anggota --}}
            @elseif ($active === 'Kuorum & Kehadiran Anggota')
                @include('livewire.admin.tabs.kuorum-kehadiran')

            {{-- 5. SCREEN: Risalah & Notulensi Sidang (Figma 13C) --}}
            @elseif ($active === 'Risalah & Notulensi Sidang')
                @include('livewire.admin.tabs.risalah-notulensi')

            {{-- 6. SCREEN: E-VOTING & PENGAMBILAN KEPUTUSAN (Figma 13E) --}}
            @elseif ($active === 'E-Voting & Pengambilan Keputusan')
                @include('livewire.admin.tabs.evoting')

            {{-- 7. SCREEN: Pengaturan Informasi & Hero --}}
            @elseif ($active === 'Pengaturan Informasi & Hero')
                @include('livewire.admin.tabs.pengaturan-informasi')

            {{-- 8. SCREEN: Profil, Tupoksi & Struktur --}}
            @elseif ($active === 'Profil, Tupoksi & Struktur')
                @include('livewire.admin.tabs.profil-struktur')

            {{-- 9. SCREEN: Direktori Komisi & Anggota --}}
            @elseif ($active === 'Direktori Komisi & Anggota')
                @include('livewire.admin.tabs.direktori-komisi')

            {{-- 10. SCREEN: Manajemen Berita & Siaran Pers --}}
            @elseif ($active === 'Manajemen Berita & Siaran Pers')
                @include('livewire.admin.tabs.manajemen-berita')

            {{-- 11. SCREEN: Katalog Regulasi & Produk Hukum (Backend API) --}}
            @elseif ($active === 'Katalog Regulasi & Produk Hukum')
                @include('livewire.admin.tabs.katalog-regulasi')

            {{-- 12. SCREEN: Repositori Arsip & Dokumen --}}
            @elseif ($active === 'Repositori Arsip & Dokumen')
                @include('livewire.admin.tabs.repositori-arsip')

            {{-- 13. SCREEN: Pusat Aspirasi & Tindak Lanjut --}}
            @elseif ($active === 'Pusat Aspirasi & Tindak Lanjut')
                @include('livewire.admin.tabs.pusat-aspirasi')
            @else
                {{-- Fallback --}}
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-xs">
                    <p class="text-[13px] text-slate-600">Modul ini siap dikonfigurasi lebih lanjut.</p>
                </div>
            @endif

        </div>
    </main>
</div>
</div>{{-- /root komponen Livewire --}}
