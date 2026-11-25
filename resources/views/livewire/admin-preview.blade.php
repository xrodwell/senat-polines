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
             document.documentElement.classList.remove('sb-collapsed');
             this.animated = true;
             this.sidebarCollapsed = !this.sidebarCollapsed;
             localStorage.setItem('admin_sidebar_collapsed', this.sidebarCollapsed);
         }
     }">
    <script>
        if (localStorage.getItem('admin_sidebar_collapsed') === 'true') {
            document.documentElement.classList.add('sb-collapsed');
        }
    </script>
    <style>
        @media (min-width: 1024px) {
            html.sb-collapsed .admin-sidebar {
                width: 80px;
            }
            html.sb-collapsed .admin-sidebar-label {
                max-width: 0;
                width: 0;
                opacity: 0;
            }
            html.sb-collapsed .admin-sidebar-group {
                max-height: 0;
                opacity: 0;
            }
            html.sb-collapsed .admin-sidebar-divider {
                margin-top: 0.5rem;
                margin-bottom: 0.5rem;
                opacity: 1;
                height: auto;
                border-top-width: 1px;
            }
            html.sb-collapsed .admin-sidebar-link {
                width: 2.25rem;
                justify-content: center;
                padding-left: 0;
                padding-right: 0;
                gap: 0;
            }
            html.sb-collapsed .admin-sidebar-ext {
                width: 0;
                opacity: 0;
                border-width: 0;
                pointer-events: none;
            }
            html.sb-collapsed .admin-sb-icon-menu {
                display: flex !important;
            }
            html.sb-collapsed .admin-sb-icon-chevron {
                display: none !important;
            }
        }
    </style>

    {{-- Topbar khas Portal Senat (Figma 2269:728) --}}
    <header class="sticky top-0 z-40 flex h-[60px] items-center bg-senat-primary text-white lg:h-16 shadow-sm">
        <div class="relative flex w-full items-center gap-3 px-4 sm:px-5 lg:px-[18px]">
            <img src="{{ asset('images/logo-polines.png') }}" alt="Logo Polines" class="h-9 w-9 rounded-lg object-contain">
            <span class="leading-none">
                <span class="block text-[14px] font-bold">SENAT<span class="hidden sm:inline"> AKADEMIK</span></span>
                <span class="mt-[6px] hidden text-[11px] font-medium text-[#bfdbfe] sm:block">Politeknik Negeri Semarang</span>
            </span>

            <div class="ml-auto flex items-center gap-2 lg:gap-4">
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
               class="admin-sidebar w-full shrink-0 border-b border-senat-sidebarLine bg-white lg:sticky lg:top-16 lg:h-[calc(100vh-64px)] lg:w-[280px] lg:overflow-y-auto lg:overflow-x-hidden lg:border-b-0 lg:border-r flex flex-col justify-between"
               :class="{
                   'transition-[width] duration-300 ease-[cubic-bezier(0.4,0,0.2,1)]': animated,
                   '[&_*]:!transition-none': !animated,
                   'lg:w-[80px]': sidebarCollapsed,
                   'lg:w-[280px]': !sidebarCollapsed
               }"
               aria-label="Navigasi Admin Sekretariat">
            
            <div>
                <div class="border-b border-slate-100 px-[18px] py-4">
                    <div class="flex items-center gap-3 min-w-0">
                        <button type="button"
                                @click="toggleSidebar()"
                                class="senat-tap flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#003366] text-white shadow-xs transition hover:bg-[#00264d] cursor-pointer"
                                :title="sidebarCollapsed ? 'Perluas Sidebar' : 'Ciutkan Sidebar'">
                            <span x-show="sidebarCollapsed" x-cloak class="admin-sb-icon-menu flex items-center justify-center">
                                <x-senat.icon name="menu" class="h-4 w-4 text-white" />
                            </span>
                            <span x-show="!sidebarCollapsed" class="admin-sb-icon-chevron flex items-center justify-center">
                                <x-senat.icon name="chevron-left" class="h-4 w-4 text-white" />
                            </span>
                        </button>
                        <div class="admin-sidebar-label min-w-0 overflow-hidden whitespace-nowrap transition-all duration-200 ease-in-out"
                             :class="sidebarCollapsed ? 'max-w-0 opacity-0' : 'max-w-[200px] opacity-100'">
                            <div class="flex items-center gap-1.5">
                                <p class="text-[11px] font-bold uppercase tracking-[0.08em] text-slate-900">ADMIN SEKRETARIAT</p>
                            </div>
                            <p class="mt-0.5 text-[11px] text-slate-500 truncate">Tata Kelola Sidang &amp; Publikasi</p>
                        </div>
                    </div>
                </div>

                <nav class="mt-4 space-y-6 px-[18px] pb-6"
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
                            <div class="admin-sidebar-group overflow-hidden whitespace-nowrap transition-all duration-200 ease-in-out"
                                 :class="sidebarCollapsed ? 'max-h-0 opacity-0' : 'max-h-8 opacity-100'">
                                <p id="grup-{{ $loop->index }}" class="px-2.5 pb-2 text-[10px] font-bold uppercase tracking-[0.08em] text-slate-400">
                                    {{ $group }}
                                </p>
                            </div>
                            <div class="admin-sidebar-divider border-t border-slate-100 transition-all duration-200 ease-in-out"
                                 :class="sidebarCollapsed ? 'my-2 opacity-100' : 'my-0 opacity-0 h-0 border-t-0'"></div>

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
                                       class="admin-sidebar-link group relative flex h-9 items-center rounded-xl transition-all duration-200 ease-in-out {{ $isActive ? 'bg-[#003366] text-white shadow-xs font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 font-medium' }}"
                                       :class="sidebarCollapsed ? 'w-9 justify-center px-0 gap-0' : 'w-full px-2.5 gap-3'">
                                        <span class="shrink-0 transition-transform duration-150 group-hover:scale-110 {{ $isActive ? 'text-amber-300' : 'text-slate-400 group-hover:text-slate-700' }}">
                                            <x-senat.icon :name="$iconName" class="h-4 w-4" />
                                        </span>
                                        <span class="admin-sidebar-label truncate text-[12px] whitespace-nowrap overflow-hidden transition-all duration-200 ease-in-out"
                                              :class="sidebarCollapsed ? 'w-0 max-w-0 opacity-0' : 'max-w-[200px] opacity-100 flex-1'">{{ $item }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </nav>
            </div>

            <div class="mt-auto border-t border-slate-100 bg-slate-50/60 p-3">
                <div class="flex items-center justify-between rounded-xl bg-white border border-slate-200/80 p-2 shadow-2xs overflow-hidden">
                    <div class="flex items-center min-w-0 transition-all duration-200 ease-in-out"
                         :class="sidebarCollapsed ? 'gap-0' : 'gap-2.5'"
                         :title="sidebarCollapsed ? 'Administrator (Sekretariat Senat)' : ''">
                        <div class="h-8 w-8 rounded-lg bg-[#003366]/10 text-[#003366] flex items-center justify-center text-xs font-bold shrink-0">
                            AS
                        </div>
                        <div class="admin-sidebar-label min-w-0 overflow-hidden whitespace-nowrap transition-all duration-200 ease-in-out"
                             :class="sidebarCollapsed ? 'max-w-0 opacity-0' : 'max-w-[160px] opacity-100'">
                            <p class="text-[11.5px] font-bold text-slate-900 truncate">Administrator</p>
                            <p class="text-[10px] text-emerald-600 font-medium flex items-center gap-1">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                Sekretariat Senat
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('public.home') }}"
                       title="Ke Portal Publik"
                       class="admin-sidebar-ext shrink-0 h-7 rounded-lg border border-slate-200 hover:border-slate-300 hover:bg-slate-50 flex items-center justify-center text-slate-500 hover:text-slate-800 overflow-hidden transition-all duration-200 ease-in-out"
                       :class="sidebarCollapsed ? 'w-0 opacity-0 border-0 pointer-events-none' : 'w-7 opacity-100'">
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
                    @php
                        $isUnavailable = preg_match('/\AFitur .+ belum tersedia\. Tidak ada perubahan yang disimpan\.\z/s', $flashMessage) === 1;
                        $flashColor = $isUnavailable ? 'text-amber-800' : 'text-[#116a3c]';
                    @endphp
                    <div class="flex items-center justify-between rounded-[10px] {{ $isUnavailable ? 'bg-amber-50' : 'bg-[#eaf8f0]' }} px-4 py-3 text-[13px] {{ $flashColor }} shadow-xs">
                        <div class="flex items-center gap-2">
                            <x-senat.icon :name="$isUnavailable ? 'warning' : 'check'" class="h-4 w-4 shrink-0 {{ $flashColor }}" />
                            <span>{{ $flashMessage }}</span>
                        </div>
                        <button type="button" wire:click="$set('flashMessage', '')" class="text-base font-bold {{ $flashColor }}">×</button>
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
