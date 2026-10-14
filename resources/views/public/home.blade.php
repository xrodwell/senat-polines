@extends('layouts.public')

@section('title', 'Beranda — Senat Akademik Politeknik Negeri Semarang')
@section('description', 'Portal resmi Senat Akademik Politeknik Negeri Semarang — agenda sidang, lima komisi, fitur unggulan, dan warta keputusan akademik terbaru.')

@section('content')

@php
    /**
     * 01 — Beranda · Figma node 2013:3 (1440x2255)
     * Konversi React+Tailwind absolut → Blade + Tailwind responsif (flex/grid).
     * Urutan desain: Hero → Agenda Sidang Terdekat → Lima Komisi → (Fitur Unggulan) → Warta.
     */
    $period        = $direktori['period'] ?? '2026–2029';
    $memberCount   = $direktori['members'] ?? null;
    $committeeCount = $direktori['committees'] ?? count($committees);
    $releaseCount  = count($posts);
    $hasActive     = (bool) $activeSession;

    $bulan = ['JAN','FEB','MAR','APR','MEI','JUN','JUL','AGU','SEP','OKT','NOV','DES'];
@endphp

{{-- ============================ HERO (Figma 2013:16) ============================ --}}
<section class="relative overflow-hidden bg-senat-hero">
    {{-- Latar foto gedung dengan lapisan biru pekat sesuai desain (bg #003c75) --}}
    <div class="absolute inset-0 z-0 select-none overflow-hidden">
        <img src="{{ asset('images/gedung-polines.jpg') }}" alt=""
             class="h-full w-full object-cover object-center" loading="eager">
        <div class="absolute inset-0 bg-gradient-to-r from-senat-hero via-senat-hero/95 to-senat-hero/85"></div>
    </div>

    <div class="senat-shell relative z-10 pb-4 pt-[72px]">

        {{-- Info Sidang (2013:17 / 2013:18) --}}
        @if ($hasActive)
            <div class="inline-flex h-[38px] max-w-full items-center gap-2 rounded-[12px] border border-[#cc9421] bg-[#1c334d] px-4">
                <span class="h-[7px] w-[7px] shrink-0 rounded-full bg-[#ffd66b]" aria-hidden="true"></span>
                <p class="truncate text-[11px] font-bold leading-none text-[#ffd66b]">
                    INFO SIDANG: Sedang berlangsung {{ $activeSession['title'] }}
                </p>
            </div>
        @endif

        <div class="max-w-[700px]">
            {{-- Eyebrow (2013:19) --}}
            <p class="text-[11px] font-bold uppercase leading-[1.1] tracking-[0.02em] text-senat-sky {{ $hasActive ? 'mt-[38px]' : '' }}">
                Organ Tertinggi Normatif Akademik
            </p>

            {{-- Judul (2013:20 / 2013:21) --}}
            <h1 class="mt-[14px] text-[34px] font-bold leading-[1.05] tracking-tight text-white sm:text-[42px] lg:text-[50px]">
                Senat Akademik<br>
                <span class="text-senat-orange">Politeknik Negeri Semarang</span>
            </h1>

            {{-- Deskripsi (2013:22) --}}
            <p class="mt-[26px] max-w-[650px] text-[15px] leading-[1.45] text-senat-skySoft">
                Perumus kebijakan akademik, penjaga mutu pendidikan vokasi, dan penyalur aspirasi civitas akademika
                — memastikan tata kelola Polines berjalan transparan, akuntabel, dan berintegritas.
            </p>

            {{-- CTA (2013:23 / 2013:25) --}}
            <div class="mt-10 flex flex-wrap items-center gap-[18px]">
                <a href="{{ route('public.profile') }}"
                   class="inline-flex h-10 items-center gap-1.5 rounded-lg bg-senat-orange px-5 text-[12px] font-bold text-white transition-colors duration-150 hover:bg-senat-orangeHover">
                    Profil &amp; Komisi
                    <span aria-hidden="true">›</span>
                </a>
                <a href="{{ route('public.regulations') }}"
                   class="inline-flex h-10 items-center rounded-lg bg-[#1f3859] px-6 text-[12px] font-bold text-white transition-colors duration-150 hover:bg-[#274568]">
                    Repositori Produk Hukum
                </a>
            </div>
        </div>

        {{-- Statistik (2013:27) --}}
        <dl class="mt-[46px] grid grid-cols-2 rounded-[14px] border border-[#33577a] bg-[#05264d] lg:grid-cols-4">
            @php
                $stats = [
                    ['label' => 'ANGGOTA SENAT',   'value' => $memberCount,     'hint' => 'Guru Besar & Pendidik'],
                    ['label' => 'KOMISI AKTIF',    'value' => $committeeCount,  'hint' => 'Komisi I s.d. V'],
                    ['label' => 'WARTA TERBIT',    'value' => $releaseCount,    'hint' => 'Rilis & keputusan senat'],
                    ['label' => 'TAHUN PERIODE',   'value' => $period,          'hint' => 'Senat Akademik'],
                ];
            @endphp
            @foreach ($stats as $i => $stat)
                <div class="px-[22px] py-[21px] border-[#33577a]
                            {{ $i % 2 === 1 ? 'border-l' : '' }}
                            lg:border-l lg:first:border-l-0">
                    <dt class="text-[10px] font-bold uppercase leading-[1.1] tracking-[0.04em] text-senat-sky">{{ $stat['label'] }}</dt>
                    <dd class="mt-[6px] text-[27px] font-bold leading-none text-white tabular-nums">{{ $stat['value'] }}</dd>
                    <dd class="mt-[7px] text-[10px] leading-[1.1] text-[#adcff0]">{{ $stat['hint'] }}</dd>
                </div>
            @endforeach
        </dl>
    </div>
</section>

{{-- ==================== AGENDA SIDANG TERDEKAT (2013:40 → 2013:56) ==================== --}}
<section class="pt-[58px]">
    <div class="senat-shell">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-[11px] font-bold uppercase leading-[1.1] text-senat-orange">Kalender Senat</p>
                <h2 class="mt-2 text-[20px] font-bold leading-[1.15] text-senat-ink sm:text-[24px] lg:text-[27px]">
                    Agenda Sidang Terdekat
                </h2>
            </div>
            <a href="{{ route('portal.session') }}"
               class="shrink-0 text-[12px] font-bold text-senat-link transition-colors hover:text-senat-primary">
                Lihat Portal Sidang ›
            </a>
        </div>

        <div class="mt-[31px] space-y-5">
            @forelse ($upcomingMeetings as $meeting)
                @php
                    $d = \Carbon\Carbon::parse($meeting['date']);
                @endphp
                <article class="flex items-center gap-[18px] rounded-[14px] border border-[#d9e3f0] bg-white p-[18px]">
                    {{-- Blok tanggal (2013:44) --}}
                    <div class="flex h-[58px] w-[58px] shrink-0 flex-col items-center justify-center rounded-[10px] bg-senat-hero text-white">
                        <span class="text-[19px] font-bold leading-none tabular-nums">{{ $d->format('d') }}</span>
                        <span class="mt-1 text-[10px] font-bold uppercase leading-none text-[#bfdeff]">{{ $bulan[(int) $d->format('n') - 1] }}</span>
                    </div>

                    {{-- Detail agenda --}}
                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] font-bold uppercase leading-[1.1] tracking-[0.04em] text-senat-link">
                            {{ $meeting['type'] }}
                        </p>
                        <h3 class="mt-[6px] text-[14px] font-bold leading-snug text-senat-ink">{{ $meeting['title'] }}</h3>
                        <p class="mt-[8px] text-[11px] leading-snug text-senat-body">
                            {{ $meeting['time'] }} • {{ $meeting['room'] }}
                        </p>
                    </div>
                </article>
            @empty
                <div class="rounded-[14px] border border-dashed border-senat-border bg-white px-6 py-10 text-center">
                    <p class="text-[13px] text-senat-muted">Belum ada agenda sidang terjadwal dalam waktu dekat.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

{{-- ==================== LIMA KOMISI SENAT (2013:57 → 2013:88) ==================== --}}
<section class="pt-[60px]">
    <div class="senat-shell">
        <div>
            <p class="text-[11px] font-bold uppercase leading-[1.1] text-senat-orange">Struktur Kerja</p>
            <h2 class="mt-2 text-[20px] font-bold leading-[1.15] text-senat-ink sm:text-[24px] lg:text-[27px]">
                Lima Komisi Senat Polines
            </h2>
        </div>

        <div class="mt-[33px] grid grid-cols-1 gap-[19px] sm:grid-cols-2 lg:grid-cols-5">
            @forelse ($committees as $committee)
                @php
                    $roman = \Illuminate\Support\Str::after($committee['code'], 'KOM-');
                @endphp
                <a href="{{ route('public.profile.structure') }}"
                   class="group flex min-h-[195px] flex-col rounded-[14px] border border-[#d9e3f0] bg-white p-[18px] transition-colors duration-150 hover:border-senat-primary/50">
                    <span class="flex h-[34px] w-[34px] shrink-0 items-center justify-center rounded-[8px] bg-senat-hero text-[12px] font-bold text-white">
                        {{ $roman }}
                    </span>
                    <p class="mt-3 text-[10px] font-bold uppercase leading-[1.1] tracking-[0.04em] text-senat-body">
                        KOMISI {{ $roman }}
                    </p>
                    <h3 class="mt-[6px] text-[14px] font-bold leading-snug text-senat-ink group-hover:text-senat-primary">
                        {{ \Illuminate\Support\Str::after($committee['name'], 'Komisi ') }}
                    </h3>
                    <p class="mt-[14px] text-[11px] leading-[1.3] text-senat-body line-clamp-4">
                        {{ $committee['focus'] }}
                    </p>
                </a>
            @empty
                <div class="rounded-[14px] border border-dashed border-senat-border bg-white px-6 py-10 text-center sm:col-span-2 lg:col-span-5">
                    <p class="text-[13px] text-senat-muted">Data komisi belum dimuat.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

{{-- ==================== LIMA FITUR UNGGULAN (dipertahankan dari beranda sebelumnya) ==================== --}}
<section class="pt-[68px]">
    <div class="senat-shell">
        <div class="max-w-[760px]">
            <p class="text-[11px] font-bold uppercase leading-[1.1] text-senat-orange">Peta Produk</p>
            <h2 class="mt-2 text-[20px] font-bold leading-[1.15] text-senat-ink sm:text-[24px] lg:text-[27px]">
                Lima Fitur Unggulan SENAT
            </h2>
            <p class="mt-3 text-[13px] leading-relaxed text-senat-body">
                Modul inti sistem informasi Senat: dari penjaringan aspirasi, sidang digital, hingga transparansi kinerja
                dan anggaran lembaga.
            </p>
        </div>

        <div class="mt-[31px] grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            @forelse ($features as $feature)
                <article class="flex flex-col rounded-[14px] border border-[#d9e3f0] bg-white p-[22px]">
                    <div class="flex items-start gap-3">
                        <span class="flex h-[34px] w-[34px] shrink-0 items-center justify-center rounded-[8px] bg-senat-hero text-[12px] font-bold text-white tabular-nums">
                            {{ $feature['code'] }}
                        </span>
                        <div class="min-w-0">
                            <h3 class="text-[14px] font-bold leading-snug text-senat-ink">{{ $feature['title'] }}</h3>
                            <p class="mt-1 text-[10px] font-bold uppercase leading-[1.2] tracking-[0.03em] text-senat-link">
                                {{ $feature['subtitle'] }}
                            </p>
                        </div>
                    </div>

                    <ul class="mt-4 flex-1 space-y-[10px]">
                        @foreach ($feature['points'] as $point)
                            <li class="text-[11px] leading-[1.45] text-senat-body">
                                <span class="font-bold text-senat-ink">{{ $point['label'] }}</span> — {{ $point['detail'] }}
                            </li>
                        @endforeach
                    </ul>

                    @if (! empty($feature['flow']))
                        <div class="mt-4 flex flex-wrap items-center gap-[6px] border-t border-senat-line pt-[14px]">
                            @foreach ($feature['flow'] as $stage)
                                <span class="inline-flex items-center rounded-full border border-senat-badgeLine bg-senat-badgeBg px-[9px] py-[3px] text-[10px] font-bold uppercase tracking-[0.04em] text-senat-link">
                                    {{ $stage }}
                                </span>
                                @if (! $loop->last)
                                    <span class="text-[10px] text-senat-muted" aria-hidden="true">›</span>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </article>
            @empty
                <div class="rounded-[14px] border border-dashed border-senat-border bg-white px-6 py-10 text-center md:col-span-2 lg:col-span-3">
                    <p class="text-[13px] text-senat-muted">Data fitur unggulan belum dimuat.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

{{-- ==================== WARTA & KEPUTUSAN SENAT (2013:89 → 2013:110) ==================== --}}
<section class="pb-[50px] pt-[68px]">
    <div class="senat-shell">
        <div>
            <p class="text-[11px] font-bold uppercase leading-[1.1] text-senat-orange">Siaran Pers</p>
            <h2 class="mt-2 text-[20px] font-bold leading-[1.15] text-senat-ink sm:text-[24px] lg:text-[27px]">
                Warta &amp; Keputusan Senat Terbaru
            </h2>
        </div>

        <div class="mt-[30px] grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @forelse ($posts as $post)
                <a href="{{ route('public.news.show', $post['slug']) }}"
                   class="group flex flex-col overflow-hidden rounded-[14px] border border-[#d9e3f0] bg-white transition-colors duration-150 hover:border-senat-primary/50">
                    {{-- Thumbnail / placeholder (2013:92) --}}
                    <div class="h-[125px] w-full overflow-hidden bg-[#d4dee5]">
                        @if (! empty($post['thumbnail']))
                            <img src="{{ $post['thumbnail'] }}" alt="{{ $post['title'] }}" loading="lazy"
                                 class="h-full w-full object-cover transition-transform duration-300 ease-out group-hover:scale-105">
                        @endif
                    </div>

                    <div class="flex flex-1 flex-col p-[14px]">
                        <p class="text-[10px] font-bold uppercase leading-[1.1] tracking-[0.04em] text-senat-link">
                            {{ $post['category'] }}
                        </p>
                        <h3 class="mt-[6px] text-[13px] font-bold leading-snug text-senat-ink line-clamp-3 group-hover:text-senat-primary">
                            {{ $post['title'] }}
                        </h3>
                        <p class="mt-2 text-[11px] leading-[1.3] text-senat-body line-clamp-2">
                            {{ $post['excerpt'] }}
                        </p>
                    </div>
                </a>
            @empty
                <div class="rounded-[14px] border border-dashed border-senat-border bg-white px-6 py-10 text-center sm:col-span-2 lg:col-span-4">
                    <p class="text-[13px] text-senat-muted">Belum ada warta yang diterbitkan.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

@endsection
