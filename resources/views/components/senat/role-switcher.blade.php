{{-- Floating Quick Role Switcher untuk Demo, Review & Presentasi PBL (Hanya Aktif di Mode Dev / Local) --}}
@env(['local', 'testing'])
<aside x-data="{ expanded: true }" class="fixed bottom-6 right-24 z-40 flex items-center font-sans">
    <div class="flex items-center gap-1 rounded-full border border-slate-700/80 bg-slate-900/95 p-1 text-white shadow-2xl backdrop-blur-md transition-all">
        <button 
            type="button" 
            @click="expanded = !expanded" 
            class="flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold text-slate-300 transition-colors hover:text-white"
            title="Klik untuk menyembunyikan / menampilkan role switcher (Hanya muncul di local dev)"
            aria-label="Toggle role switcher"
        >
            <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span class="font-mono text-[11px] font-semibold uppercase tracking-wider text-slate-400 hidden sm:inline">Dev Role:</span>
            <span x-show="!expanded" class="text-[11px] text-slate-400">Pilih Role ▾</span>
        </button>

        <div x-show="expanded" class="flex items-center gap-1 pr-1" x-transition:enter="transition ease-out duration-150" x-transition:leave="transition ease-in duration-100">
            {{-- Role 1: Publik --}}
            <a href="{{ route('public.home') }}"
               class="flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold transition-all {{ request()->routeIs('public.*') && !request()->routeIs('admin.*') ? 'bg-[#003c75] text-white shadow-sm ring-1 ring-white/20' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <span>🌐</span>
                <span class="hidden sm:inline">Publik</span>
            </a>

            {{-- Role 2: Anggota Senat --}}
            <a href="{{ route('portal.dashboard') }}"
               class="flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold transition-all {{ request()->routeIs('portal.dashboard') || (request()->routeIs('portal.*') && !request()->routeIs('portal.secretariat.*')) ? 'bg-emerald-600 text-white shadow-sm ring-1 ring-white/20' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <span>👤</span>
                <span class="hidden sm:inline">Anggota Senat</span>
            </a>

            {{-- Role 3: Admin Sekretariat --}}
            <a href="{{ route('admin.preview') }}"
               class="flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold transition-all {{ request()->routeIs('admin.*') || request()->routeIs('portal.secretariat.*') ? 'bg-[#f37021] text-white shadow-sm ring-1 ring-white/20' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <span>⚙️</span>
                <span class="hidden sm:inline">Sekretariat</span>
            </a>
        </div>
    </div>
</aside>
@endenv
