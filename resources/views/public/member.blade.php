@extends('layouts.public')

@section('title', $member['name'].' — Profil Anggota Senat Akademik Politeknik Negeri Semarang')
@section('description', 'Profil jabatan '.$member['name'].' ('.$member['role'].') pada struktur Senat Akademik Politeknik Negeri Semarang periode '.$period.'.')

@php
    /**
     * 04 — Detail Anggota — Figma node 2061:2 (1440×2112).
     * Data dari PublicController@member: $member, $committee, $peers, $period.
     */
    $initialsOf = function (string $name): string {
        $parts = collect(explode(' ', preg_replace('/,.*$/', '', $name)))
            ->filter(fn ($part) => $part !== '' && ! str_contains($part, '.'))
            ->values();
        $out = $parts->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');

        return $out !== '' ? $out : mb_strtoupper(mb_substr($name, 0, 1));
    };

    $committeeLabel = isset($member['committee_code']) ? str_replace('KOM-', 'Komisi ', $member['committee_code']) : null;
    $isCommitteeMember = $committeeLabel !== null && $committee !== null;
    $badge = $committeeLabel ?? ($member['organ'] ?? 'Pimpinan Senat');
    $metaLine = $isCommitteeMember
        ? 'Anggota '.$committeeLabel.' • Masa Bakti '.$period
        : 'Anggota Pimpinan Senat • Masa Bakti '.$period;

    /** Pimpinan komisi (ketua & sekretaris) untuk kartu ringkasan komisi. */
    $komisiPimpinan = [];
    if ($isCommitteeMember) {
        foreach ([['label' => 'Ketua', 'name' => $committee['lead'] ?? null], ['label' => 'Sekretaris', 'name' => $committee['secretary'] ?? null]] as $slot) {
            if (empty($slot['name'])) {
                continue;
            }
            $person = collect($peers)->first(fn (array $peer) => $peer['name'] === $slot['name']);
            $komisiPimpinan[] = ['label' => $slot['label'], 'name' => $slot['name'], 'slug' => $person['slug'] ?? null];
        }
    }
@endphp

@section('content')

    {{-- Breadcrumb strip --}}
    <div class="border-b border-[#d9e3f0] bg-white">
        <div class="senat-shell py-4">
            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px]">
                <a href="{{ route('public.profile.structure') }}" class="font-bold text-senat-link hover:underline">‹&nbsp; Profil &amp; Struktur</a>
                <span class="text-senat-body">/</span>
                <span class="font-medium text-senat-body">Detail Anggota Senat</span>
            </div>
            <p class="mt-2 text-[10px] font-bold uppercase tracking-[0.08em] text-senat-link">Profil Anggota Senat</p>
        </div>
    </div>

    {{-- Hero Banner Asli (Figma 2061:2) --}}
    <section class="relative overflow-hidden bg-senat-hero">
        <div class="pointer-events-none absolute inset-y-0 right-0 hidden w-[502px] bg-senat-primary/50 lg:block" aria-hidden="true"></div>
        <div class="pointer-events-none absolute inset-y-0 right-0 hidden w-[360px] bg-senat-hero/70 lg:block" aria-hidden="true"></div>

        <div class="senat-shell relative z-10 py-12 pb-[180px] sm:py-14 sm:pb-[200px]">
            <p class="text-[11px] font-bold uppercase tracking-[0.1em] text-[#add4ff]">Direktori Senat Akademik</p>
            <h1 class="mt-3 text-[26px] font-bold leading-tight text-white sm:text-[34px]">Profil Anggota Senat</h1>
            <p class="mt-4 max-w-[650px] text-[13px] leading-relaxed text-senat-skySoft">
                Informasi jabatan, mandat, dan rekam kontribusi institusional yang diumumkan melalui Sekretariat Senat.
            </p>
        </div>
    </section>

    {{-- Portrait + identity card (Melayang di atas hero dengan z-index yang tepat) --}}
    <div class="senat-shell relative z-20 -mt-[140px] sm:-mt-[160px]">
        <div class="grid gap-5 lg:grid-cols-[286px_minmax(0,1fr)] lg:items-start">

            {{-- Kartu Foto Anggota --}}
            <div class="rounded-[18px] border border-[#d9e3f0] bg-white p-7 shadow-xl">
                <div class="mx-auto flex h-[210px] w-[210px] max-w-full items-center justify-center overflow-hidden rounded-full border-4 border-[#c7dcf0] bg-[#eef5fc] shadow-inner">
                    @if (! empty($member['photo']))
                        <img src="{{ asset($member['photo']) }}" alt="{{ $member['name'] }}" class="h-full w-full rounded-full object-cover object-top">
                    @else
                        <span class="text-[52px] font-extrabold tracking-[0.04em] text-senat-primary">{{ $initialsOf($member['name']) }}</span>
                    @endif
                </div>
                <div class="mt-4 text-center">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-0.5 text-[10px] font-bold text-emerald-700 border border-emerald-200">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Status: Aktif Menjabat
                    </span>
                </div>
                <p class="mt-3 text-center text-[10px] leading-relaxed text-senat-muted">
                    Foto resmi anggota dikelola Sekretariat Senat.
                </p>
            </div>

            {{-- Kartu Identitas & Gelar --}}
            <div class="rounded-[18px] border border-[#d9e3f0] bg-white p-6 sm:p-8 shadow-xl">
                <span class="inline-flex items-center rounded-full bg-senat-badgeBg px-3.5 py-1 text-[9px] font-bold uppercase tracking-[0.08em] text-senat-link border border-senat-badgeLine/40">
                    {{ $badge }}
                </span>

                <h1 class="mt-3 text-[22px] font-bold leading-tight text-senat-primary sm:text-[28px]">{{ $member['name'] }}</h1>
                <p class="mt-1.5 text-[15px] font-bold text-senat-orange">{{ $member['role'] }}</p>
                <p class="mt-1 text-[11px] text-senat-body">{{ $metaLine }}</p>

                @if (! empty($member['nip']))
                    <p class="mt-1 text-[11px] font-medium text-slate-500">NIP {{ $member['nip'] }}</p>
                @endif

                <div class="mt-6 h-px w-full bg-[#d9e3f0]" aria-hidden="true"></div>

                <dl class="mt-5 grid gap-4 sm:grid-cols-3">
                    <div class="rounded-xl bg-[#f8fafc] p-3.5 border border-[#eef2f6]">
                        <dt class="text-[9px] font-bold uppercase tracking-[0.08em] text-senat-muted">Kedudukan</dt>
                        <dd class="mt-1 text-[11px] font-bold leading-snug text-senat-ink">{{ $member['organ'] }}</dd>
                    </div>
                    <div class="rounded-xl bg-[#f8fafc] p-3.5 border border-[#eef2f6]">
                        <dt class="text-[9px] font-bold uppercase tracking-[0.08em] text-senat-muted">Afiliasi</dt>
                        <dd class="mt-1 text-[11px] font-bold leading-snug text-senat-ink">Politeknik Negeri Semarang</dd>
                    </div>
                    <div class="rounded-xl bg-[#f8fafc] p-3.5 border border-[#eef2f6]">
                        <dt class="text-[9px] font-bold uppercase tracking-[0.08em] text-senat-muted">Masa Bakti</dt>
                        <dd class="mt-1 text-[11px] font-bold leading-snug text-emerald-700">{{ $period }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    {{-- Back to structure + ringkasan jabatan --}}
    <div class="senat-shell mt-8">
        <div class="grid gap-6 lg:grid-cols-[286px_minmax(0,1fr)] lg:items-start">

            <div class="space-y-3">
                <a href="{{ route('public.profile.structure') }}"
                   class="block rounded-[14px] border border-senat-hero bg-senat-hero p-5 text-white shadow-sm transition-all duration-150 hover:bg-senat-primary hover:shadow-md">
                    <span class="block text-[9px] font-bold uppercase tracking-[0.08em] text-[#add4ff]">Bagan Organisasi</span>
                    <span class="mt-2 block text-[13px] font-bold text-white">‹&nbsp; Kembali ke Struktur</span>
                </a>
                <a href="{{ route('public.directory') }}"
                   class="block rounded-[14px] border border-[#d9e3f0] bg-white p-4 text-center text-xs font-semibold text-senat-body shadow-sm transition-colors hover:border-senat-link hover:text-senat-link">
                    Direktori Komisi &amp; Anggota ›
                </a>
            </div>

            <div>
                <div class="flex items-center gap-3">
                    <span class="h-[4px] w-[80px] rounded-[2px] bg-senat-orange" aria-hidden="true"></span>
                    <span class="h-px flex-1 bg-[#d1deed]" aria-hidden="true"></span>
                    <span class="text-[9px] font-bold uppercase tracking-[0.08em] text-senat-body">Data Jabatan</span>
                    <span class="h-px flex-1 bg-[#d1deed]" aria-hidden="true"></span>
                </div>

                <div class="mt-5 rounded-[16px] border border-[#d9e3f0] bg-white p-6 sm:p-8 shadow-sm">
                    <p class="text-[10px] font-bold uppercase tracking-[0.08em] text-senat-link">Ringkasan Peran</p>
                    <h3 class="mt-2 text-[20px] font-bold leading-snug text-senat-ink">{{ $member['role'] }}</h3>
                    <p class="mt-3 max-w-[826px] text-[13px] leading-relaxed text-senat-body">{{ $member['focus'] }}</p>
                    <p class="mt-5 text-[11px] font-semibold text-emerald-600">Status keanggotaan: Aktif Menjabat</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Mandat & tanggung jawab --}}
    <section class="senat-shell mt-12">
        <p class="text-[11px] font-bold uppercase tracking-[0.08em] text-senat-link">Mandat &amp; Tanggung Jawab</p>
        <h2 class="mt-2 text-[20px] font-bold text-senat-ink">Tugas Pokok &amp; Fungsi (TUPOKSI)</h2>
        <p class="mt-2 max-w-[1264px] text-[13px] leading-relaxed text-senat-muted">
            @if ($isCommitteeMember)
                Rincian tugas pokok, fungsi, dan wewenang {{ $committeeLabel }} — {{ $committee['name'] }}.
            @else
                Rincian kedudukan, jabatan, dan tata kerja normatif pada struktur Senat Akademik Politeknik Negeri Semarang.
            @endif
        </p>

        <div class="mt-6 grid gap-5 lg:grid-cols-3">
            @if ($isCommitteeMember)
                <div class="rounded-[14px] border border-[#e2e8f0] bg-white p-6 shadow-sm">
                    <span class="inline-flex items-center rounded-[6px] bg-senat-badgeBg px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.04em] text-senat-link">Fokus Komisi</span>
                    <p class="mt-4 text-[15px] font-bold text-[#0f172a]">Fokus Kerja {{ $committeeLabel }}</p>
                    <p class="mt-2 text-[12px] leading-relaxed text-slate-600">{{ $committee['focus'] }}</p>
                </div>

                <div class="rounded-[14px] border border-[#e2e8f0] bg-white p-6 shadow-sm">
                    <span class="inline-flex items-center rounded-[6px] bg-senat-badgeBg px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.04em] text-senat-link">Pimpinan Komisi</span>
                    <p class="mt-4 text-[15px] font-bold text-[#0f172a]">Ketua &amp; Sekretaris</p>
                    <dl class="mt-2 space-y-2.5 text-[12px] leading-relaxed text-slate-600">
                        @foreach ($komisiPimpinan as $slot)
                            <div>
                                <dt class="text-[10px] font-bold uppercase tracking-[0.06em] text-senat-muted">{{ $slot['label'] }}</dt>
                                <dd class="mt-0.5 font-semibold text-senat-ink">
                                    @if ($slot['slug'])
                                        <a href="{{ route('public.directory.member', $slot['slug']) }}" class="hover:text-senat-link hover:underline">{{ $slot['name'] }}</a>
                                    @else
                                        {{ $slot['name'] }}
                                    @endif
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                </div>

                <div class="rounded-[14px] border border-[#e2e8f0] bg-white p-6 shadow-sm lg:col-span-3">
                    <span class="inline-flex items-center rounded-[6px] bg-senat-badgeBg px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.04em] text-senat-link">Tupoksi Komisi</span>
                    <p class="mt-4 text-[15px] font-bold text-[#0f172a]">Tugas Pokok &amp; Fungsi {{ $committeeLabel }}</p>
                    <p class="mt-2 max-w-[1180px] text-[12.5px] leading-relaxed text-slate-600">
                        {{ $committee['tupoksi'] ?? 'Rincian tupoksi komisi ini belum tersedia pada dokumen Sekretariat Senat.' }}
                    </p>
                </div>
            @else
                <div class="rounded-[14px] border border-[#e2e8f0] bg-white p-6 shadow-sm">
                    <span class="inline-flex items-center rounded-[6px] bg-senat-badgeBg px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.04em] text-senat-link">Kedudukan</span>
                    <p class="mt-4 text-[15px] font-bold text-[#0f172a]">Organ &amp; Afiliasi</p>
                    <dl class="mt-2 space-y-2 text-[12px] leading-relaxed text-slate-600">
                        <div>
                            <dt class="text-[10px] font-bold uppercase tracking-[0.06em] text-senat-muted">Organ</dt>
                            <dd class="mt-0.5 font-semibold text-senat-ink">{{ $member['organ'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-bold uppercase tracking-[0.06em] text-senat-muted">Afiliasi</dt>
                            <dd class="mt-0.5 font-semibold text-senat-ink">Politeknik Negeri Semarang</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-bold uppercase tracking-[0.06em] text-senat-muted">Masa Bakti</dt>
                            <dd class="mt-0.5 font-semibold text-senat-ink">{{ $period }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="rounded-[14px] border border-[#e2e8f0] bg-white p-6 shadow-sm">
                    <span class="inline-flex items-center rounded-[6px] bg-senat-badgeBg px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.04em] text-senat-link">Jabatan</span>
                    <p class="mt-4 text-[15px] font-bold text-[#0f172a]">{{ $member['role'] }}</p>
                    <p class="mt-2 text-[12px] leading-relaxed text-slate-600">
                        Tercatat pada struktur {{ $member['organ'] }} Senat Akademik Politeknik Negeri Semarang.
                    </p>
                </div>

                <div class="rounded-[14px] border border-[#e2e8f0] bg-white p-6 shadow-sm">
                    <span class="inline-flex items-center rounded-[6px] bg-senat-badgeBg px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.04em] text-senat-link">Data Kepegawaian</span>
                    <p class="mt-4 text-[15px] font-bold text-[#0f172a]">NIP &amp; Status</p>
                    <p class="mt-2 text-[12px] leading-relaxed text-slate-600">
                        @if (! empty($member['nip']))
                            NIP {{ $member['nip'] }} &middot; Status: Aktif
                        @else
                            NIP tercatat pada pangkalan data Sekretariat Senat &middot; Status: Aktif
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </section>

    {{-- Rekan sekomisi / Rekan Pimpinan --}}
    @if (count($peers))
        <section class="senat-shell mt-12">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.08em] text-senat-link">
                        @if ($isCommitteeMember)
                            Rekan Sekomisi
                        @else
                            Rekan Pimpinan &amp; BPH
                        @endif
                    </p>
                    <h2 class="mt-2 text-[20px] font-bold text-senat-ink">
                        {{ $isCommitteeMember ? $committee['name'] : $member['organ'] }}
                    </h2>
                </div>
                <span class="text-[11px] font-medium text-senat-body">{{ count($peers) }} anggota lain</span>
            </div>

            <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($peers as $peer)
                    <a href="{{ route('public.directory.member', $peer['slug']) }}"
                       class="group flex items-start gap-4 rounded-[14px] border border-[#d6e0ed] bg-white p-5 shadow-sm transition-all duration-150 hover:border-senat-link hover:shadow-md">
                        <span class="flex h-[52px] w-[52px] shrink-0 items-center justify-center rounded-full border border-[#bdd4eb] bg-senat-badgeBg text-[14px] font-bold text-senat-link transition-colors group-hover:bg-senat-hero group-hover:text-white">
                            {{ $initialsOf($peer['name']) }}
                        </span>
                        <span class="min-w-0">
                            <span class="block text-[9px] font-bold uppercase tracking-[0.06em] text-senat-link">{{ strtoupper($peer['role']) }}</span>
                            <span class="mt-1 block text-[13px] font-bold leading-snug text-senat-ink group-hover:text-senat-link transition-colors">{{ $peer['name'] }}</span>
                            <span class="mt-1 block text-[10px] text-senat-body">{{ $peer['organ'] }}</span>
                        </span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Bottom bar --}}
    <div class="senat-shell mt-12 pb-10">
        <div class="flex flex-col gap-3 border-t border-senat-line pt-6 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-[11px] text-senat-body">Direktori anggota menampilkan informasi jabatan resmi yang dikelola Sekretariat Senat Akademik Polines.</p>
            <a href="{{ route('public.directory') }}" class="text-[11px] font-bold text-senat-link hover:underline">Kembali ke Direktori Komisi &nbsp;›</a>
        </div>
    </div>

@endsection
