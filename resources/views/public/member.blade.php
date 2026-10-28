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
        : 'Anggota ex officio • Masa Bakti '.$period;

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
                <a href="{{ route('public.profile') }}" class="font-bold text-senat-link hover:underline">‹&nbsp; Profil &amp; Komisi</a>
                <span class="text-senat-body">/</span>
                <span class="font-medium text-senat-body">Detail Anggota Senat</span>
            </div>
            <p class="mt-2 text-[10px] font-bold uppercase tracking-[0.08em] text-senat-orange">Profil Anggota Senat</p>
        </div>
    </div>

    {{-- Hero --}}
    <section class="relative overflow-hidden bg-senat-hero">
        <div class="pointer-events-none absolute inset-y-0 right-0 hidden w-[502px] bg-[#052e57] lg:block" aria-hidden="true"></div>
        <div class="pointer-events-none absolute inset-y-0 right-0 hidden w-[360px] bg-[#0a4073] lg:block" aria-hidden="true"></div>

        <div class="senat-shell relative py-14 pb-[190px]">
            <p class="text-[11px] font-bold uppercase tracking-[0.1em] text-[#99ccff]">Direktori Senat Akademik</p>
            <h1 class="mt-3 text-[26px] font-bold leading-tight text-white sm:text-[34px]">Profil Anggota Senat</h1>
            <p class="mt-4 max-w-[650px] text-[13px] leading-relaxed text-senat-skySoft">
                Informasi jabatan, mandat, dan rekam kontribusi institusional yang diumumkan melalui Sekretariat Senat.
            </p>
        </div>
    </section>

    {{-- Portrait + identity card (overlapping hero) --}}
    <div class="senat-shell -mt-[170px]">
        <div class="grid gap-5 lg:grid-cols-[286px_minmax(0,1fr)] lg:items-start">

            <div class="rounded-[18px] border border-[#d9e3f0] bg-white p-7">
                <div class="mx-auto flex h-[228px] w-[228px] max-w-full items-center justify-center rounded-full border border-[#a3c2e0] bg-[#eef5fc]">
                    <span class="text-[48px] font-bold tracking-[0.04em] text-senat-primary">{{ $initialsOf($member['name']) }}</span>
                </div>
                <p class="mt-5 text-center text-[10px] leading-relaxed text-senat-muted">
                    Foto resmi anggota dikelola Sekretariat Senat.
                </p>
            </div>

            <div class="rounded-[18px] border border-[#d9e3f0] bg-white p-6 sm:p-8">
                <span class="inline-flex items-center rounded-full bg-senat-badgeBg px-3.5 py-1 text-[9px] font-bold uppercase tracking-[0.08em] text-senat-hero">
                    {{ $badge }}
                </span>

                <h1 class="mt-4 text-[24px] font-bold leading-tight text-senat-primary sm:text-[30px]">{{ $member['name'] }}</h1>
                <p class="mt-2 text-[15px] font-bold text-senat-ink">{{ $member['role'] }}</p>
                <p class="mt-2 text-[11px] text-senat-body">{{ $metaLine }}</p>

                @if (! empty($member['nip']))
                    <p class="mt-1 text-[11px] text-senat-muted">NIP {{ $member['nip'] }}</p>
                @endif

                <div class="mt-6 h-px w-full bg-[#d9e3f0]" aria-hidden="true"></div>

                <dl class="mt-5 grid gap-5 sm:grid-cols-3">
                    <div>
                        <dt class="text-[9px] font-bold uppercase tracking-[0.08em] text-senat-muted">Kedudukan</dt>
                        <dd class="mt-1.5 text-[11px] font-bold leading-snug text-senat-ink">{{ $member['organ'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-[9px] font-bold uppercase tracking-[0.08em] text-senat-muted">Afiliasi</dt>
                        <dd class="mt-1.5 text-[11px] font-bold leading-snug text-senat-ink">Politeknik Negeri Semarang</dd>
                    </div>
                    <div>
                        <dt class="text-[9px] font-bold uppercase tracking-[0.08em] text-senat-muted">Status Keanggotaan</dt>
                        <dd class="mt-1.5 text-[11px] font-bold leading-snug text-senat-ink">Aktif</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    {{-- Back to structure + ringkasan jabatan --}}
    <div class="senat-shell mt-8">
        <div class="grid gap-6 lg:grid-cols-[286px_minmax(0,1fr)] lg:items-start">

            <a href="{{ route('public.profile.structure') }}"
               class="block rounded-[12px] border border-[#3d78b0] bg-[#0a4073] p-5 transition-colors duration-150 hover:bg-[#083458]">
                <span class="block text-[9px] font-bold uppercase tracking-[0.08em] text-[#a8cfff]">Bagan Organisasi</span>
                <span class="mt-2 block text-[12px] font-bold text-white">‹&nbsp; Kembali ke Struktur</span>
            </a>

            <div>
                <div class="flex items-center gap-3">
                    <span class="h-[4px] w-[80px] rounded-[2px] bg-senat-orange" aria-hidden="true"></span>
                    <span class="h-px flex-1 bg-[#d1deed]" aria-hidden="true"></span>
                    <span class="text-[9px] font-bold uppercase tracking-[0.08em] text-senat-body">Data Jabatan</span>
                    <span class="h-px flex-1 bg-[#d1deed]" aria-hidden="true"></span>
                </div>

                <p class="mt-6 text-[10px] font-bold uppercase tracking-[0.08em] text-senat-orange">Informasi Jabatan</p>
                <h2 class="mt-2 text-[24px] font-bold leading-tight text-senat-ink">Profil Ringkas Anggota</h2>
                <p class="mt-2 text-[11px] text-senat-body">Informasi jabatan yang relevan pada struktur Senat Akademik.</p>

                <div class="mt-6 grid gap-4 sm:grid-cols-3">
                    <x-senat.stat label="Masa Jabatan" :value="$period" hint="Periode keanggotaan Senat" />
                    <x-senat.stat label="Jabatan" :value="$member['role']" hint="Jabatan pada struktur Senat" />
                    <x-senat.stat label="Afiliasi" value="Politeknik Negeri Semarang" hint="Institusi asal" />
                </div>

                <div class="mt-5 rounded-[14px] border border-[#d9e3f0] bg-white p-6 sm:p-8">
                    <p class="text-[10px] font-bold uppercase tracking-[0.08em] text-senat-orange">Ringkasan Peran</p>
                    <h3 class="mt-3 text-[20px] font-bold leading-snug text-senat-ink">{{ $member['role'] }}</h3>
                    <p class="mt-3 max-w-[826px] text-[12px] leading-relaxed text-senat-body">{{ $member['focus'] }}</p>
                    <p class="mt-5 text-[10px] font-medium text-senat-link">Status keanggotaan: Aktif</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Mandat & tanggung jawab --}}
    <section class="senat-shell mt-14">
        <p class="text-[11px] font-bold uppercase tracking-[0.08em] text-senat-primary">Mandat &amp; Tanggung Jawab</p>
        <h2 class="mt-2 text-[20px] font-bold text-senat-ink">Tugas Pokok &amp; Fungsi (TUPOKSI)</h2>
        <p class="mt-3 max-w-[1264px] text-[13.5px] leading-relaxed text-senat-muted">
            @if ($isCommitteeMember)
                Rincian tugas pokok, fungsi, dan wewenang {{ $committeeLabel }} — {{ $committee['name'] }}.
            @else
                Rincian kedudukan, jabatan, dan data kepegawaian pada struktur Senat Akademik berdasarkan Statuta Polines.
            @endif
        </p>

        <div class="mt-6 grid gap-5 lg:grid-cols-3">
            @if ($isCommitteeMember)
                <div class="rounded-[8px] border border-[#e2e8f0] bg-white p-6">
                    <span class="inline-flex items-center rounded-[4px] bg-senat-badgeBg px-2.5 py-1 text-[10.5px] font-bold uppercase tracking-[0.04em] text-senat-hero">Fokus Komisi</span>
                    <p class="mt-4 text-[16px] font-bold text-[#0f172a]">Fokus Kerja {{ $committeeLabel }}</p>
                    <p class="mt-3 text-[12.5px] leading-relaxed text-[#475569]">{{ $committee['focus'] }}</p>
                </div>

                <div class="rounded-[8px] border border-[#e2e8f0] bg-white p-6">
                    <span class="inline-flex items-center rounded-[4px] bg-senat-badgeBg px-2.5 py-1 text-[10.5px] font-bold uppercase tracking-[0.04em] text-senat-hero">Pimpinan Komisi</span>
                    <p class="mt-4 text-[16px] font-bold text-[#0f172a]">Ketua &amp; Sekretaris</p>
                    <dl class="mt-3 space-y-3 text-[12.5px] leading-relaxed text-[#475569]">
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

                <div class="rounded-[8px] border border-[#e2e8f0] bg-white p-6 lg:col-span-3">
                    <span class="inline-flex items-center rounded-[4px] bg-senat-badgeBg px-2.5 py-1 text-[10.5px] font-bold uppercase tracking-[0.04em] text-senat-hero">Tupoksi Komisi</span>
                    <p class="mt-4 text-[16px] font-bold text-[#0f172a]">Tugas Pokok &amp; Fungsi {{ $committeeLabel }}</p>
                    <p class="mt-3 max-w-[1180px] text-[12.5px] leading-relaxed text-[#475569]">
                        {{ $committee['tupoksi'] ?? 'Rincian tupoksi komisi ini belum tersedia pada dokumen Sekretariat Senat.' }}
                    </p>
                </div>
            @else
                <div class="rounded-[8px] border border-[#e2e8f0] bg-white p-6">
                    <span class="inline-flex items-center rounded-[4px] bg-senat-badgeBg px-2.5 py-1 text-[10.5px] font-bold uppercase tracking-[0.04em] text-senat-hero">Kedudukan</span>
                    <p class="mt-4 text-[16px] font-bold text-[#0f172a]">Organ &amp; Afiliasi</p>
                    <dl class="mt-3 space-y-3 text-[12.5px] leading-relaxed text-[#475569]">
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

                <div class="rounded-[8px] border border-[#e2e8f0] bg-white p-6">
                    <span class="inline-flex items-center rounded-[4px] bg-senat-badgeBg px-2.5 py-1 text-[10.5px] font-bold uppercase tracking-[0.04em] text-senat-hero">Jabatan</span>
                    <p class="mt-4 text-[16px] font-bold text-[#0f172a]">{{ $member['role'] }}</p>
                    <p class="mt-3 text-[12.5px] leading-relaxed text-[#475569]">
                        Tercatat pada struktur {{ $member['organ'] }} Senat Akademik Politeknik Negeri Semarang.
                    </p>
                </div>

                <div class="rounded-[8px] border border-[#e2e8f0] bg-white p-6">
                    <span class="inline-flex items-center rounded-[4px] bg-senat-badgeBg px-2.5 py-1 text-[10.5px] font-bold uppercase tracking-[0.04em] text-senat-hero">Data Kepegawaian</span>
                    <p class="mt-4 text-[16px] font-bold text-[#0f172a]">NIP &amp; Status</p>
                    <p class="mt-3 text-[12.5px] leading-relaxed text-[#475569]">
                        @if (! empty($member['nip']))
                            NIP {{ $member['nip'] }} &middot; Status keanggotaan: Aktif
                        @else
                            NIP belum tersedia pada dokumen Sekretariat. Status keanggotaan: Aktif
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </section>

    {{-- Rekan sekomisi --}}
    @if (count($peers))
        <section class="senat-shell mt-14">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.08em] text-senat-orange">
                        @if ($isCommitteeMember)
                            Rekan Sekomisi
                        @else
                            Rekan Pimpinan &amp; BPH
                        @endif
                    </p>
                    <h2 class="mt-2 text-[21px] font-bold text-senat-ink">
                        {{ $isCommitteeMember ? $committee['name'] : $member['organ'] }}
                    </h2>
                </div>
                <span class="text-[11px] font-medium text-senat-body">{{ count($peers) }} anggota lain</span>
            </div>

            <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($peers as $peer)
                    <a href="{{ route('public.directory.member', $peer['slug']) }}"
                       class="group flex items-start gap-4 rounded-[13px] border border-[#d6e0ed] bg-white p-5 transition-colors duration-150 hover:border-senat-link">
                        <span class="flex h-[54px] w-[54px] shrink-0 items-center justify-center rounded-full border border-[#bdd4eb] bg-senat-badgeBg text-[14px] font-bold text-senat-link">
                            {{ $initialsOf($peer['name']) }}
                        </span>
                        <span class="min-w-0">
                            <span class="block text-[9px] font-bold uppercase tracking-[0.06em] text-senat-link">{{ strtoupper($peer['role']) }}</span>
                            <span class="mt-1 block text-[13px] font-bold leading-snug text-senat-ink group-hover:text-senat-link">{{ $peer['name'] }}</span>
                            <span class="mt-1 block text-[10px] text-senat-body">{{ $peer['organ'] }}</span>
                        </span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Bottom bar --}}
    <div class="senat-shell mt-14">
        <div class="flex flex-col gap-3 border-t border-senat-line pt-6 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-[11px] text-senat-body">Direktori anggota menampilkan informasi jabatan yang dikelola Sekretariat Senat.</p>
            <a href="{{ route('public.directory') }}" class="text-[11px] font-bold text-senat-link hover:underline">Kembali ke Direktori Komisi &nbsp;›</a>
        </div>
    </div>
    <div class="pb-4"></div>
@endsection
