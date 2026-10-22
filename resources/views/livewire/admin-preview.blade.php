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

<div class="min-h-screen lg:flex">

    {{-- Sidebar: desktop tetap, mobile jadi kolom biasa di atas konten --}}
    <aside class="w-full shrink-0 border-b border-senat-sidebarLine bg-white lg:sticky lg:top-0 lg:h-screen lg:w-[288px] lg:overflow-y-auto lg:border-b-0 lg:border-r"
           aria-label="Navigasi Admin Sekretariat">
        <div class="flex items-center gap-3 border-b border-senat-sidebarLine px-5 py-4">
            <img src="{{ asset('images/logo-polines.png') }}" alt="Logo Polines" class="h-9 w-9 rounded-lg object-contain">
            <div class="leading-tight">
                <p class="text-[13px] font-bold text-senat-ink">Admin Sekretariat</p>
                <p class="text-[11px] font-medium text-senat-muted">Senat Akademik Polines</p>
            </div>
        </div>

        <nav class="px-3 py-4" aria-label="Daftar layar admin">
            <button type="button"
                    wire:click="select('Dashboard')"
                    @if ($active === 'Dashboard') aria-current="page" @endif
                    class="mb-4 flex min-h-[44px] w-full items-center gap-3 rounded-lg px-3 text-left text-[13px] font-semibold transition-colors {{ $active === 'Dashboard' ? 'bg-senat-badgeBg text-senat-primary ring-1 ring-senat-badgeLine' : 'text-senat-ink hover:bg-senat-surface' }}">
                <x-senat.icon name="dashboard" class="h-5 w-5 shrink-0 text-senat-muted" />
                <span class="flex-1">Dashboard</span>
                @if ($active === 'Dashboard')
                    <span class="text-[10px] font-bold uppercase tracking-wide text-senat-link">Aktif</span>
                @endif
            </button>

            @foreach ($groups as $group => $items)
                <section class="mb-4" aria-labelledby="grup-{{ $loop->index }}">
                    <h2 id="grup-{{ $loop->index }}" class="px-3 pb-2 text-[11px] font-bold uppercase tracking-[0.08em] text-senat-muted">
                        {{ $group }}
                    </h2>
                    <ul class="space-y-1">
                        @foreach ($items as $item)
                            <li>
                                <button type="button"
                                        wire:click="select(@js($item))"
                                        @if ($active === $item) aria-current="page" @endif
                                        class="flex min-h-[44px] w-full items-center gap-3 rounded-lg px-3 text-left text-[13px] font-medium transition-colors {{ $active === $item ? 'bg-senat-badgeBg font-semibold text-senat-primary ring-1 ring-senat-badgeLine' : 'text-senat-ink hover:bg-senat-surface' }}">
                                    <span class="flex-1">{{ $item }}</span>
                                    @if ($active === $item)
                                        <span class="text-[10px] font-bold uppercase tracking-wide text-senat-link">Aktif</span>
                                    @endif
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
        </nav>
    </aside>

    <main class="min-w-0 flex-1 px-5 py-6 lg:px-10 lg:py-8">
        <div class="mx-auto max-w-4xl">

            <p class="text-[11px] font-bold uppercase tracking-[0.1em] text-senat-orange">
                {{ $currentGroup ?? 'Ringkasan' }}
            </p>

            <div class="mt-2 flex flex-wrap items-center gap-3">
                <h1 class="text-[26px] font-bold leading-tight tracking-tight text-senat-ink lg:text-[30px]">
                    {{ $active }}
                </h1>

                <span class="inline-flex items-center gap-1.5 rounded-full border border-senat-badgeLine bg-white px-3 py-1 text-[11px] font-semibold text-senat-link">
                    <span aria-hidden="true" class="h-1.5 w-1.5 rounded-full bg-senat-link"></span>
                    Preview visual
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full border border-senat-border bg-white px-3 py-1 text-[11px] font-semibold text-senat-muted">
                    <span aria-hidden="true" class="h-1.5 w-1.5 rounded-full bg-senat-orange"></span>
                    Segera dihubungkan
                </span>
            </div>

            <p class="mt-3 max-w-2xl text-[13px] leading-relaxed text-senat-body">
                Tampilan ini adalah pratinjau proposal UI. Belum ada autentikasi, basis data, penyimpanan, unggahan, atau aksi tulis yang aktif.
            </p>

            <div class="mt-6 rounded-xl border border-senat-line bg-white p-6 lg:p-8" aria-live="polite">
                <h2 class="text-[16px] font-bold text-senat-ink">Segera dihubungkan</h2>
                <p class="mt-2 max-w-xl text-[13px] leading-relaxed text-senat-body">
                    Sumber data dan aksi untuk layar ini belum tersedia. Tidak ada data pengganti yang ditampilkan.
                </p>

                @if ($active === 'Dashboard')
                    <dl class="mt-6 grid gap-3 sm:grid-cols-2">
                        @foreach ($groups as $group => $items)
                            <div class="rounded-lg border border-senat-line bg-senat-surface p-4">
                                <dt class="text-[12px] font-bold text-senat-ink">{{ $group }}</dt>
                                <dd class="mt-1 text-[12px] text-senat-body">{{ count($items) }} layar · belum tersambung</dd>
                            </div>
                        @endforeach
                    </dl>
                @else
                    <p class="mt-6 text-[11px] font-semibold uppercase tracking-[0.08em] text-senat-muted">
                        Aksi buat, edit, unggah, publikasi, scan, dan vote tidak dijalankan pada pratinjau ini.
                    </p>
                @endif
            </div>

        </div>
    </main>
</div>
