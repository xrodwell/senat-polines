@extends('layouts.public')

@section('title', 'Direktori Komisi & Anggota — Senat Akademik Politeknik Negeri Semarang')
@section('description', 'Direktori komisi kerja dan anggota Senat Akademik Politeknik Negeri Semarang periode '.$period.' — pimpinan komisi, sekretaris, anggota komisi, fokus kerja, dan tupoksi.')

@php
    /**
     * 03 — Direktori Komisi & Anggota — Figma node 2075:2 (1440×2532).
     * Data dari PublicController@directory: $committees, $members, $leadership, $period, $direktori.
     */
    $komisiLabel = fn (string $code): string => str_replace('KOM-', 'Komisi ', $code);
    $komisiShort = fn (string $name): string => preg_replace('/^Komisi\s+/', '', $name);
    $initialsOf = function (string $name): string {
        $parts = collect(explode(' ', preg_replace('/,.*$/', '', $name)))
            ->filter(fn ($part) => $part !== '' && ! str_contains($part, '.'))
            ->values();
        $out = $parts->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');

        return $out !== '' ? $out : mb_strtoupper(mb_substr($name, 0, 1));
    };

    $memberGroups = collect($members)->groupBy(fn (array $person) => $person['committee_code'] ?? '__senat');

    $panels = [];
    foreach ($committees as $committee) {
        $rows = collect($memberGroups[$committee['code']] ?? []);

        $panels[] = [
            'code' => $committee['code'],
            'label' => $komisiLabel($committee['code']),
            'short' => $komisiShort($committee['name']),
            'name' => $committee['name'],
            'focus' => $committee['focus'],
            'tupoksi' => $committee['tupoksi'] ?? null,
            'total' => $committee['members_count'],
            'lead' => $rows->first(fn (array $person) => $person['name'] === $committee['lead']),
            'secretary' => $rows->first(fn (array $person) => $person['name'] === $committee['secretary']),
            'anggota' => $rows->filter(fn (array $person) => str_starts_with($person['role'], 'Anggota '))->values()->all(),
        ];
    }

    $heroPanels = array_map(fn (array $panel) => [
        'title' => $panel['label'].' · '.$panel['short'],
        'focus' => $panel['focus'],
        'badge' => $panel['total'].' Anggota Komisi',
        'composition' => '1 Ketua • 1 Sekretaris • '.count($panel['anggota']).' Anggota',
        'count' => count($panel['anggota']),
    ], $panels);

    $leaderRows = collect([$leadership['chair'] ?? null, $leadership['secretary'] ?? null])
        ->merge($leadership['bph'] ?? [])
        ->filter()
        ->values();
@endphp

@section('content')
    <div x-data="{ active: 0, panels: @js($heroPanels) }">

        {{-- Breadcrumb bar --}}
        <div class="border-b border-[#d6e0ed] bg-white">
            <div class="senat-shell flex h-[62px] flex-wrap items-center gap-x-3 gap-y-1 text-[11px]">
                <a href="{{ route('public.profile') }}" class="font-bold text-senat-link hover:underline">‹&nbsp; Profil &amp; Komisi</a>
                <span class="font-medium text-senat-body">/</span>
                <span class="font-medium text-senat-body">Direktori Komisi</span>
            </div>
        </div>

        {{-- Hero --}}
        <section class="relative overflow-hidden bg-senat-hero">
            <div class="pointer-events-none absolute inset-y-0 right-0 hidden w-[32%] bg-[#052e59] lg:block" aria-hidden="true"></div>
            <div class="pointer-events-none absolute inset-y-0 right-0 hidden w-[26px] bg-senat-orange lg:block" aria-hidden="true"></div>

            <div class="senat-shell relative flex min-h-[288px] flex-col justify-center py-10">
                <div class="lg:flex lg:items-center lg:gap-10">
                    <div class="lg:max-w-[730px]">
                        <p class="text-[11px] font-bold uppercase tracking-[0.1em] text-senat-sky">Direktori Anggota Senat</p>
                        <h1 class="mt-3 text-[26px] font-bold leading-[1.08] text-white sm:text-[35px]"
                            x-text="panels[active].title">{{ $panels[0]['label'] ?? 'Direktori Komisi' }}@if (isset($panels[0])) · {{ $panels[0]['short'] }}@endif</h1>
                        <p class="mt-4 max-w-[690px] text-[13px] leading-relaxed text-senat-skySoft"
                           x-text="panels[active].focus">{{ $panels[0]['focus'] ?? $period }}</p>
                    </div>

                    <div class="mt-8 lg:mt-0 lg:ml-auto lg:w-[330px]">
                        <span class="inline-flex items-center rounded-full bg-[#e8fcf5] px-3.5 py-1 text-[9px] font-bold uppercase tracking-[0.06em] text-[#008c66]"
                              x-text="panels[active].badge">{{ $panels[0]['total'] ?? 0 }} Anggota Komisi</span>
                        <p class="mt-5 text-[10px] font-bold uppercase tracking-[0.08em] text-[#94c4f5]">Masa Bakti</p>
                        <p class="mt-1 text-[18px] font-bold text-white">{{ $period }}</p>
                        <p class="mt-2 text-[10px] text-[#b8d6f5]">Struktur dapat diperbarui Sekretariat</p>
                    </div>
                </div>
            </div>
        </section>

        <div class="senat-shell pb-16 pt-10">

            {{-- Selector surface: pilih komisi --}}
            <section class="rounded-[13px] border border-[#d6e0ed] bg-white p-5 sm:p-6" aria-label="Pilih komisi">
                <p class="text-[10px] font-bold uppercase tracking-[0.08em] text-senat-body">Pilih Komisi</p>

                @if (count($panels))
                    <div class="mt-4 flex flex-wrap gap-3">
                        @foreach ($panels as $i => $panel)
                            <button type="button"
                                    @click="active = {{ $i }}"
                                    :class="active === {{ $i }} ? 'border-senat-hero bg-senat-hero' : 'border-[#d6e0ed] bg-white hover:border-senat-border'"
                                    :aria-pressed="(active === {{ $i }}).toString()"
                                    class="flex min-w-[150px] flex-1 flex-col items-center justify-center gap-1 rounded-lg border px-4 py-2.5 text-center transition-colors duration-150">
                                <span class="text-[9px] font-bold uppercase tracking-[0.08em]"
                                      :class="active === {{ $i }} ? 'text-[#abd1ff]' : 'text-senat-body'">{{ strtoupper($panel['label']) }}</span>
                                <span class="text-[10px] font-bold leading-snug"
                                      :class="active === {{ $i }} ? 'text-white' : 'text-senat-ink'">{{ $panel['short'] }}</span>
                            </button>
                        @endforeach
                    </div>
                @else
                    <x-senat.empty class="mt-4"
                                   title="Data komisi belum tersedia"
                                   message="Struktur komisi Senat Akademik akan ditampilkan setelah Sekretariat memperbarui data keanggotaan." />
                @endif
            </section>

            {{-- Panel per komisi --}}
            @foreach ($panels as $i => $panel)
                <div @if ($i > 0) x-cloak @endif x-show="active === {{ $i }}" class="mt-12 space-y-8">

                    {{-- Susunan keanggotaan --}}
                    <section>
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-[0.08em] text-senat-orange">Susunan Keanggotaan</p>
                                <h2 class="mt-2 text-[26px] font-bold leading-tight text-senat-ink">Anggota {{ $panel['label'] }}</h2>
                                <p class="mt-2 max-w-[800px] text-[11px] leading-relaxed text-senat-body">
                                    Pilih kartu anggota untuk membuka profil jabatan. Nama atau data yang belum tersedia ditandai
                                    sebagai data Sekretariat.
                                </p>
                            </div>
                            <span class="shrink-0 self-start rounded-full bg-senat-badgeBg px-3.5 py-1 text-[9px] font-bold uppercase tracking-[0.06em] text-senat-hero lg:self-end"
                                  x-text="panels[{{ $i }}].composition">1 Ketua • 1 Sekretaris • {{ count($panel['anggota']) }} Anggota</span>
                        </div>
                    </section>

                    {{-- Leadership zone: pimpinan komisi --}}
                    <section class="rounded-[15px] border border-[#d6e0ed] bg-white p-6 sm:p-8">
                        <p class="text-[10px] font-bold uppercase tracking-[0.08em] text-senat-orange">Pimpinan Komisi</p>

                        <div class="mt-6 grid gap-5 lg:grid-cols-2">
                            @if (! empty($panel['lead']))
                                <a href="{{ route('public.directory.member', $panel['lead']['slug']) }}"
                                   class="group flex items-center gap-5 rounded-[12px] border border-[#a3c2e3] bg-senat-badgeBg p-6 transition-colors duration-150 hover:border-senat-link">
                                    <span class="relative flex h-[82px] w-[82px] shrink-0 items-center justify-center rounded-full border border-[#9ebfe0] bg-[#e5f5ff] text-[16px] font-bold text-senat-link">
                                        <span class="flex h-[74px] w-[74px] items-center justify-center rounded-full border border-[#bdd4eb] bg-senat-badgeBg">{{ $initialsOf($panel['lead']['name']) }}</span>
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-[10px] font-bold uppercase tracking-[0.06em] text-senat-link">Ketua {{ $panel['label'] }}</span>
                                        <span class="mt-1.5 block text-[16px] font-bold leading-snug text-senat-ink group-hover:text-senat-link">{{ $panel['lead']['name'] }}</span>
                                        <span class="mt-1.5 block text-[11px] text-senat-body">{{ $panel['lead']['organ'] }}</span>
                                        <span class="mt-3 block text-[11px] font-bold text-senat-link">Lihat profil anggota &nbsp;›</span>
                                    </span>
                                </a>
                            @else
                                <div class="rounded-[12px] border border-dashed border-senat-border bg-white p-6 text-[11px] text-senat-muted">
                                    Data ketua {{ $panel['label'] }} belum diisi pada dokumen Sekretariat.
                                </div>
                            @endif

                            @if (! empty($panel['secretary']))
                                <a href="{{ route('public.directory.member', $panel['secretary']['slug']) }}"
                                   class="group flex items-center gap-5 rounded-[12px] border border-[#d6e0ed] bg-white p-6 transition-colors duration-150 hover:border-senat-link">
                                    <span class="relative flex h-[82px] w-[82px] shrink-0 items-center justify-center rounded-full border border-[#9ebfe0] bg-[#e5f5ff] text-[16px] font-bold text-senat-link">
                                        <span class="flex h-[74px] w-[74px] items-center justify-center rounded-full border border-[#bdd4eb] bg-senat-badgeBg">{{ $initialsOf($panel['secretary']['name']) }}</span>
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-[10px] font-bold uppercase tracking-[0.06em] text-senat-link">Sekretaris {{ $panel['label'] }}</span>
                                        <span class="mt-1.5 block text-[16px] font-bold leading-snug text-senat-ink group-hover:text-senat-link">{{ $panel['secretary']['name'] }}</span>
                                        <span class="mt-1.5 block text-[11px] text-senat-body">{{ $panel['secretary']['organ'] }}</span>
                                        <span class="mt-3 block text-[11px] font-bold text-senat-link">Lihat profil anggota &nbsp;›</span>
                                    </span>
                                </a>
                            @else
                                <div class="rounded-[12px] border border-[#d6e0ed] bg-white p-6">
                                    <p class="text-[10px] font-bold uppercase tracking-[0.06em] text-senat-link">Sekretaris {{ $panel['label'] }}</p>
                                    <p class="mt-1.5 text-[16px] font-bold leading-snug text-senat-ink">Nama Sekretaris Komisi</p>
                                    <p class="mt-1.5 text-[11px] text-senat-body">Data Sekretariat belum diisi</p>
                                    <span class="mt-3 inline-flex items-center rounded-full bg-senat-badgeBg px-3 py-1 text-[9px] font-bold uppercase tracking-[0.06em] text-senat-orange">Data menunggu pembaruan</span>
                                </div>
                            @endif
                        </div>
                    </section>

                    {{-- Daftar anggota komisi --}}
                    <section>
                        <div class="flex flex-wrap items-end justify-between gap-3">
                            <h2 class="text-[21px] font-bold text-senat-ink">Daftar Anggota Komisi</h2>
                            <span class="text-[11px] font-medium text-senat-body"
                                  x-text="panels[{{ $i }}].count + ' anggota komisi'">{{ count($panel['anggota']) }} anggota komisi</span>
                        </div>

                        @if (count($panel['anggota']))
                            <div class="mt-5 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                                @foreach ($panel['anggota'] as $person)
                                    <article class="flex flex-col rounded-[13px] border border-[#d6e0ed] bg-white p-6">
                                        <div class="flex items-start gap-4">
                                            <span class="relative flex h-[70px] w-[70px] shrink-0 items-center justify-center rounded-full border border-[#9ebfe0] bg-[#e5f5ff] text-[14px] font-bold text-senat-link">
                                                <span class="flex h-[62px] w-[62px] items-center justify-center rounded-full border border-[#bdd4eb] bg-senat-badgeBg">{{ $initialsOf($person['name']) }}</span>
                                            </span>
                                            <div class="min-w-0">
                                                <p class="text-[10px] font-bold uppercase tracking-[0.06em] text-senat-link">{{ strtoupper($person['role']) }}</p>
                                                <p class="mt-1.5 text-[15px] font-bold leading-snug text-senat-ink">{{ $person['name'] }}</p>
                                                <p class="mt-1.5 text-[10px] text-senat-body">{{ $person['organ'] }}</p>
                                            </div>
                                        </div>

                                        <p class="mt-4 text-[10px] leading-relaxed text-senat-body">Data jabatan dikelola Sekretariat Senat.</p>

                                        <div class="mt-auto pt-4">
                                            <a href="{{ route('public.directory.member', $person['slug']) }}"
                                               class="flex h-[30px] w-full items-center justify-center rounded-[7px] border border-[#bdd1e5] bg-senat-badgeBg text-[9px] font-bold uppercase tracking-[0.06em] text-senat-link transition-colors duration-150 hover:border-senat-link">
                                                Lihat profil anggota &nbsp;›
                                            </a>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        @else
                            <x-senat.empty class="mt-5"
                                           title="Data anggota belum diisi"
                                           message="Nama anggota {{ $panel['label'] }} akan ditampilkan setelah Sekretariat Senat memperbarui data keanggotaan." />
                        @endif
                    </section>

                    {{-- Tupoksi komisi --}}
                    <section class="rounded-[8px] border border-[#e2e8f0] bg-[#f8fafc] p-6 sm:p-7">
                        <p class="text-[11px] font-bold uppercase tracking-[0.06em] text-senat-hero">Panduan Kinerja Komisi</p>
                        <h2 class="mt-2 text-[18px] font-bold text-[#0f172a]">Tugas Pokok &amp; Fungsi (TUPOKSI) {{ $panel['label'] }}</h2>

                        <div class="mt-5 grid gap-5 lg:grid-cols-2">
                            <div class="rounded-[6px] border border-[#e2e8f0] bg-white p-5">
                                <p class="text-[11px] font-bold uppercase tracking-[0.06em] text-senat-orange">Fokus Kerja Komisi</p>
                                <p class="mt-3 text-[12.5px] leading-relaxed text-[#475569]">{{ $panel['focus'] }}</p>
                            </div>

                            <div class="rounded-[6px] border border-[#e2e8f0] bg-white p-5">
                                <p class="text-[11px] font-bold uppercase tracking-[0.06em] text-senat-orange">Pimpinan Komisi</p>
                                <dl class="mt-3 space-y-3 text-[12.5px] leading-relaxed text-[#475569]">
                                    <div>
                                        <dt class="text-[10px] font-bold uppercase tracking-[0.06em] text-senat-muted">Ketua</dt>
                                        <dd class="mt-0.5 font-semibold text-senat-ink">{{ $panel['lead']['name'] ?? 'Belum tersedia' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-[10px] font-bold uppercase tracking-[0.06em] text-senat-muted">Sekretaris</dt>
                                        <dd class="mt-0.5 font-semibold text-senat-ink">{{ $panel['secretary']['name'] ?? 'Belum tersedia' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-[10px] font-bold uppercase tracking-[0.06em] text-senat-muted">Jumlah Anggota</dt>
                                        <dd class="mt-0.5 font-semibold text-senat-ink">{{ $panel['total'] }} orang &middot; {{ count($panel['anggota']) }} anggota komisi</dd>
                                    </div>
                                </dl>
                            </div>

                            <div class="rounded-[6px] border border-[#e2e8f0] bg-white p-5 lg:col-span-2">
                                <p class="text-[11px] font-bold uppercase tracking-[0.06em] text-senat-orange">Tupoksi Komisi</p>
                                <p class="mt-3 max-w-[1100px] text-[12.5px] leading-relaxed text-[#475569]">
                                    {{ $panel['tupoksi'] ?: 'Rincian tugas pokok dan fungsi komisi ini belum tersedia pada dokumen Sekretariat Senat.' }}
                                </p>
                            </div>
                        </div>
                    </section>
                </div>
            @endforeach

            {{-- Pimpinan Senat & BPH --}}
            @if ($leaderRows->isNotEmpty())
                <section class="mt-14 rounded-[15px] border border-[#d6e0ed] bg-white p-6 sm:p-8">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.08em] text-senat-orange">Pimpinan Senat &amp; BPH</p>
                            <h2 class="mt-2 text-[26px] font-bold leading-tight text-senat-ink">Pimpinan Senat Akademik</h2>
                            <p class="mt-2 text-[11px] leading-relaxed text-senat-body">
                                Ketua, Sekretaris, dan Badan Pekerja Harian Senat Akademik periode {{ $period }}.
                            </p>
                        </div>
                        <span class="shrink-0 self-start rounded-full bg-senat-badgeBg px-3.5 py-1 text-[9px] font-bold uppercase tracking-[0.06em] text-senat-hero lg:self-end">
                            Masa Bakti {{ $period }}
                        </span>
                    </div>

                    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        @foreach ($leaderRows as $leader)
                            <a href="{{ route('public.directory.member', $leader['slug']) }}"
                               class="group rounded-[12px] border border-[#d6e0ed] bg-white p-5 transition-colors duration-150 hover:border-senat-link">
                                <span class="flex items-center gap-3">
                                    <span class="flex h-[44px] w-[44px] shrink-0 items-center justify-center rounded-full border border-[#bdd4eb] bg-senat-badgeBg text-[12px] font-bold text-senat-link">{{ $initialsOf($leader['name']) }}</span>
                                    <span class="min-w-0">
                                        <span class="block text-[9px] font-bold uppercase tracking-[0.06em] text-senat-link">{{ $leader['role'] }}</span>
                                        <span class="mt-1 block text-[13px] font-bold leading-snug text-senat-ink group-hover:text-senat-link">{{ $leader['name'] }}</span>
                                    </span>
                                </span>
                                <span class="mt-3 block text-[10px] text-senat-body">{{ $leader['organ'] }}</span>
                            </a>
                        @endforeach
                    </div>

                    <div class="mt-6 grid gap-5 sm:grid-cols-3">
                        <x-senat.stat label="Total Anggota" :value="$direktori['members']" hint="Pimpinan, BPH, dan anggota komisi" icon="👥" />
                        <x-senat.stat label="Komisi Kerja" :value="$direktori['committees']" hint="Komisi I–V Senat Akademik" icon="▦" />
                        <x-senat.stat label="Masa Bakti" :value="$direktori['period']" hint="Periode kepengurusan Senat" icon="🗓" />
                    </div>
                </section>
            @endif

            {{-- Bottom bar --}}
            <div class="mt-14 flex flex-col gap-3 border-t border-senat-line pt-6 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-[11px] text-senat-body">Direktori keanggotaan Senat Akademik &bull; Masa Bakti {{ $period }}</p>
                <a href="{{ route('public.profile') }}" class="text-[11px] font-bold text-senat-link hover:underline">Kembali ke Profil &amp; Komisi &nbsp;›</a>
            </div>
        </div>
    </div>
@endsection
