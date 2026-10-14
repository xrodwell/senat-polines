@extends('layouts.portal')

@section('title', 'Profil Anggota — Portal Internal Senat Akademik Polines')

@section('content')
@php
    /**
     * 12G — Profil Anggota (Figma node 2281:1477)
     * Data yang tersedia: $stats, $leadership (chair, secretary), $committees.
     */
    $chair = $leadership['chair'] ?? [];
    $secretary = $leadership['secretary'] ?? [];
    $periode = \App\Support\SenatDirectory::PERIOD;
    $pimpinan = [
        ['label' => 'Ketua Senat', 'person' => $chair],
        ['label' => 'Sekretaris Senat', 'person' => $secretary],
    ];
@endphp

<div class="mx-auto w-full max-w-[1116px] space-y-6">

    {{-- ================= PAGE HEADER ================= --}}
    <header>
        <h1 class="text-[25px] font-bold leading-tight tracking-tight text-senat-ink">Profil Anggota</h1>
        <p class="mt-1.5 text-[13px] text-senat-muted">Identitas, periode keanggotaan, komisi, dan rekam aktivitas Senat.</p>
    </header>

    {{-- ================= PROFIL ================= --}}
    <section class="rounded-[14px] border border-senat-line bg-white p-5 shadow-[0_3px_12px_rgba(13,26,41,0.07)]">
        <div class="flex flex-wrap items-center gap-5">
            <span class="flex h-[84px] w-[84px] shrink-0 items-center justify-center rounded-[42px] bg-[#eaf3fe] text-[30px] font-bold text-senat-link" aria-hidden="true">◎</span>
            <div class="min-w-0">
                <h2 class="text-[22px] font-bold leading-snug text-senat-ink">{{ $chair['name'] ?? 'Data anggota internal' }}</h2>
                <div class="mt-2 flex flex-wrap items-center gap-2">
                    <x-senat.badge state="info" :dot="false">{{ $chair['role'] ?? 'Anggota Senat' }}</x-senat.badge>
                    <x-senat.badge state="neutral" :dot="false">Periode {{ $periode }}</x-senat.badge>
                </div>
                <p class="mt-2 text-[13px] text-senat-body">Politeknik Negeri Semarang</p>
            </div>
        </div>

        <dl class="mt-6 grid grid-cols-1 gap-x-8 gap-y-5 border-t border-senat-line pt-5 sm:grid-cols-2">
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-[0.06em] text-senat-body">NIP</dt>
                <dd class="mt-1.5 text-[14px] font-medium tabular-nums text-senat-ink">{{ $chair['nip'] ?? 'Data anggota internal' }}</dd>
            </div>
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-[0.06em] text-senat-body">Organ</dt>
                <dd class="mt-1.5 text-[14px] font-medium text-senat-ink">{{ $chair['organ'] ?? 'Pimpinan Senat' }}</dd>
            </div>
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-[0.06em] text-senat-body">Jabatan</dt>
                <dd class="mt-1.5 text-[14px] font-medium text-senat-ink">{{ $chair['role'] ?? 'Anggota Komisi' }}</dd>
            </div>
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-[0.06em] text-senat-body">Periode</dt>
                <dd class="mt-1.5 text-[14px] font-medium text-senat-ink">{{ $periode }}</dd>
            </div>
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-[0.06em] text-senat-body">Kehadiran</dt>
                <dd class="mt-1.5 text-[14px] font-medium tabular-nums text-senat-ink">{{ $stats['attendance_rate'] }}% periode berjalan</dd>
            </div>
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-[0.06em] text-senat-body">Laporan</dt>
                <dd class="mt-1.5 text-[14px] font-medium text-senat-ink">Belum ada LPJ individual</dd>
            </div>
        </dl>

        @if (! empty($chair['focus']))
            <p class="mt-5 border-t border-senat-line pt-5 text-[13px] leading-relaxed text-senat-muted">{{ $chair['focus'] }}</p>
        @endif
    </section>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[730px_1fr]">

        {{-- ================= KEANGGOTAAN KOMISI ================= --}}
        <section class="rounded-[14px] border border-senat-line bg-white p-5 shadow-[0_3px_12px_rgba(13,26,41,0.07)]">
            <h2 class="text-[18px] font-bold text-senat-ink">Keanggotaan Komisi</h2>
            <p class="mt-2 text-[13px] text-senat-muted">
                Lima komisi tetap Senat Akademik periode {{ $periode }}.
            </p>

            <ul class="mt-5 divide-y divide-senat-line">
                @forelse ($committees as $committee)
                    <li class="flex flex-wrap items-start gap-x-4 gap-y-1 py-4 first:pt-0 last:pb-0">
                        <span class="mt-1.5 h-[9px] w-[9px] shrink-0 rounded-[5px] bg-senat-link" aria-hidden="true"></span>
                        <div class="min-w-0 flex-1">
                            <p class="text-[14px] font-semibold text-senat-ink">{{ $committee['name'] }}</p>
                            <p class="mt-0.5 text-[12px] text-senat-muted">
                                Ketua: {{ $committee['lead'] }} · Sekretaris: {{ $committee['secretary'] }}
                            </p>
                        </div>
                        <span class="shrink-0 whitespace-nowrap text-[11px] font-medium tabular-nums text-senat-muted">
                            {{ $committee['members_count'] }} anggota
                        </span>
                    </li>
                @empty
                    <li class="py-4"><p class="text-[13px] text-senat-muted">Data komisi belum dimuat.</p></li>
                @endforelse
            </ul>
        </section>

        {{-- ================= KINERJA & TRANSPARANSI ================= --}}
        <section class="rounded-[14px] border border-senat-line bg-white p-5 shadow-[0_3px_12px_rgba(13,26,41,0.07)]">
            <h2 class="text-[18px] font-bold text-senat-ink">Kinerja &amp; Transparansi</h2>
            <p class="mt-3 text-[13px] leading-relaxed text-senat-body">
                Halaman ini menyiapkan area untuk rekam kehadiran, riwayat legislasi, dan laporan pertanggungjawaban berkala.
            </p>

            <div class="mt-5 flex items-center gap-4 rounded-[14px] bg-[#eaf8f0] px-5 py-5 shadow-[0_3px_12px_rgba(13,26,41,0.07)]">
                <span class="flex h-[38px] w-[38px] shrink-0 items-center justify-center rounded-[10px] bg-[#116a3c] text-[16px] font-bold text-white" aria-hidden="true">◉</span>
                <div>
                    <p class="text-[28px] font-bold leading-none tabular-nums text-senat-ink">{{ $stats['attendance_rate'] }}%</p>
                    <p class="mt-1.5 text-[12px] font-medium text-senat-body">Kehadiran</p>
                </div>
            </div>

            <dl class="mt-5 divide-y divide-senat-line border-t border-senat-line">
                <div class="flex items-center justify-between gap-4 py-3">
                    <dt class="text-[12px] text-senat-muted">Total agenda tercatat</dt>
                    <dd class="text-[13px] font-semibold tabular-nums text-senat-ink">{{ $stats['total'] }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4 py-3">
                    <dt class="text-[12px] text-senat-muted">Kehadiran tercatat</dt>
                    <dd class="text-[13px] font-semibold tabular-nums text-senat-ink">{{ $stats['present'] }}/{{ $stats['invited'] }}</dd>
                </div>
            </dl>

            <x-senat.button class="mt-5" variant="secondary" :href="route('portal.agenda')">Lihat Rekap Kehadiran</x-senat.button>
        </section>
    </div>

    {{-- ================= PIMPINAN SENAT ================= --}}
    <section class="rounded-[14px] border border-senat-line bg-white p-5 shadow-[0_3px_12px_rgba(13,26,41,0.07)]">
        <h2 class="text-[18px] font-bold text-senat-ink">Pimpinan Senat</h2>

        <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2">
            @foreach ($pimpinan as $role)
                @if (! empty($role['person']))
                    <article class="rounded-xl border border-senat-line bg-senat-surface p-5">
                        <p class="text-[11px] font-bold uppercase tracking-[0.06em] text-senat-muted">{{ $role['label'] }}</p>
                        <h3 class="mt-2 text-[15px] font-bold text-senat-ink">{{ $role['person']['name'] }}</h3>
                        <p class="mt-1 text-[12px] tabular-nums text-senat-muted">NIP {{ $role['person']['nip'] }}</p>
                        @if (! empty($role['person']['focus']))
                            <p class="mt-2 text-[12px] leading-relaxed text-senat-body">{{ $role['person']['focus'] }}</p>
                        @endif
                    </article>
                @endif
            @endforeach
        </div>
    </section>

</div>
@endsection
