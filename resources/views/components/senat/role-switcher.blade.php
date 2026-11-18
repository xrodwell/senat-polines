{{-- Floating Quick Role Switcher untuk Demo, Review & Presentasi PBL (Hanya Aktif di Mode Dev / Local) --}}
@env(['local', 'testing'])
<aside x-data="{ expanded: true }" class="fixed bottom-6 right-24 z-40 flex items-center font-sans">
    <div class="flex items-center gap-1 rounded-full border border-slate-700/80 bg-slate-900/95 p-1 text-white shadow-2xl backdrop-blur-md transition-all">
        <button 
            type="button" 
            @click="expanded = !expanded" 
            class="flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold text-slate-300 transition-colors hover:text-white cursor-pointer"
            title="Klik untuk menyembunyikan / menampilkan role switcher (Hanya muncul di local dev)"
            aria-label="Toggle role switcher"
        >
            <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span class="font-mono text-[11px] font-semibold uppercase tracking-wider text-slate-400 hidden sm:inline">Dev Role:</span>
            <span x-show="!expanded" class="text-[11px] text-slate-400">Pilih Role &blacktriangledown;</span>
        </button>

        <div x-show="expanded" class="flex items-center gap-1 pr-1" x-transition:enter="transition ease-out duration-150" x-transition:leave="transition ease-in duration-100">
            {{-- Role 1: Publik --}}
            <a href="{{ route('public.home') }}"
               class="flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold transition-all {{ request()->routeIs('public.*') && !request()->routeIs('admin.*') ? 'bg-[#003c75] text-white shadow-sm ring-1 ring-white/20' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
                <span class="hidden sm:inline">Publik</span>
            </a>

            {{-- Role 2: Anggota Senat --}}
            <a href="{{ route('portal.dashboard') }}"
               class="flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold transition-all {{ request()->routeIs('portal.dashboard') || (request()->routeIs('portal.*') && !request()->routeIs('portal.secretariat.*')) ? 'bg-emerald-600 text-white shadow-sm ring-1 ring-white/20' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                <span class="hidden sm:inline">Anggota Senat</span>
            </a>

            {{-- Logout Button --}}
            <a href="{{ route('portal.logout') }}"
               class="flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold text-rose-300 transition-all hover:bg-rose-950/80 hover:text-white" title="Keluar dari sesi login">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                <span class="hidden sm:inline">Logout</span>
            </a>

            {{-- Role 3: Admin Sekretariat --}}
            <a href="{{ route('admin.preview') }}"
               class="flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold transition-all {{ request()->routeIs('admin.*') || request()->routeIs('portal.secretariat.*') ? 'bg-[#f37021] text-white shadow-sm ring-1 ring-white/20' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                <span class="hidden sm:inline">Sekretariat</span>
            </a>

            {{-- Impersonation Return Button (Active only when impersonating) --}}
            @if (session()->has('impersonator'))
                <form method="POST" action="{{ route('portal.leave-impersonate') }}" class="m-0 inline">
                    @csrf
                    <button type="submit" class="flex items-center gap-1.5 rounded-full bg-amber-400 px-2.5 py-1 text-xs font-bold text-slate-900 transition-all hover:bg-amber-300 cursor-pointer shadow-xs" title="Keluar mode impersonasi dan kembali ke sesi Admin">
                        <svg class="h-3.5 w-3.5 text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z" /></svg>
                        <span class="hidden sm:inline">Kembali Admin</span>
                    </button>
                </form>
            @endif
        </div>
    </div>
</aside>
@endenv