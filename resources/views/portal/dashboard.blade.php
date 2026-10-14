@extends('layouts.portal')

@section('title', 'Dashboard Anggota — Portal Internal Senat Akademik Polines')

@section('content')
@php
    /**
     * 12A — Dashboard Anggota · Ringkasan (Figma node 2021:2)
     * Topbar + sidebar disediakan layouts.portal; bagian ini hanya area konten 1116px.
     */
    $bulanId = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $tanggal = function (string $value) use ($bulanId): string {
        $ts = strtotime($value);
        return $ts ? date('j', $ts) . ' ' . $bulanId[(int) date('n', $ts)] . ' ' . date('Y', $ts) : $value;
    };

    $active = $stats['active'] ?? null;
    $upcoming = array_values($stats['upcoming'] ?? []);
    $perluKonfirmasi = count($upcoming) + ($active ? 1 : 0);
    $perhatian = array_slice($upcoming ?: $meetings, 0, 2);
    $agendaTerdekat = $active ?: ($upcoming[0] ?? ($meetings[0] ?? null));
@endphp

<div class="mx-auto w-full max-w-[1116px]">

    {{-- ================= PAGE HEADER ================= --}}
    <header class="mb-6">
        <h1 class="text-[25px] font-bold leading-tight tracking-tight text-senat-ink">Dashboard Anggota</h1>
        <p class="mt-1.5 text-[13px] text-senat-muted">Ringkasan aktivitas, sidang, dan keputusan yang membutuhkan perhatian.</p>
    </header>

    {{-- ================= NOTICE ================= --}}
    @if ($perluKonfirmasi > 0)
        <div class="mb-6 flex flex-wrap items-center gap-x-4 gap-y-3 rounded-[14px] bg-[#fff4df] px-5 py-3 shadow-[0_3px_12px_rgba(13,26,41,0.07)]">
            <span class="text-[18px] font-bold leading-none text-[#e88a00]" aria-hidden="true">!</span>
            <p class="min-w-0 flex-1 text-[13px] font-medium text-[#975c00]">
                {{ $perluKonfirmasi }} sidang membutuhkan konfirmasi presensi sebelum waktu dimulai.
            </p>
            <x-senat.button variant="secondary" size="sm" :href="route('portal.agenda')">Lihat Agenda</x-senat.button>
        </div>
    @endif

    {{-- ================= KPI ================= --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-senat.stat label="Agenda" :value="$stats['total']" :hint="count($upcoming) . ' sidang terjadwal'" icon="▣" />
        <x-senat.stat label="E-Voting" value="—" hint="Belum ada voting dibuka" icon="✓" />
        <x-senat.stat label="Kehadiran" :value="$stats['attendance_rate'] . '%'" hint="Periode berjalan" icon="◷" />
        <x-senat.stat label="Tindak lanjut" :value="count($aspirations)" hint="Aspirasi masuk komisi" icon="!" />
    </div>

    {{-- ================= PRIORITAS + AGENDA TERDEKAT ================= --}}
    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- Tindakan Prioritas --}}
        <section class="rounded-[14px] border border-senat-line bg-white p-5 shadow-[0_3px_12px_rgba(13,26,41,0.07)] lg:col-span-2">
            <div class="flex flex-wrap items-center gap-3">
                <h2 class="text-[18px] font-bold text-senat-ink">Tindakan Prioritas</h2>
                <x-senat.badge state="warning" :dot="false">Perlu respons</x-senat.badge>
            </div>

            <ul class="mt-5 divide-y divide-senat-line">
                @forelse ($perhatian as $meeting)
                    @php
                        $sedang = ($meeting['status'] ?? '') === 'Sedang Berlangsung';
                        $dot = $sedang ? 'bg-[#e88a00]' : 'bg-senat-link';
                    @endphp
                    <li class="flex items-start gap-4 py-4 first:pt-0 last:pb-0">
                        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ $dot }}" aria-hidden="true"></span>
                        <div class="min-w-0 flex-1">
                            <p class="text-[14px] font-semibold text-senat-ink">{{ $sedang ? 'Konfirmasi presensi' : 'Agenda menunggu' }}</p>
                            <p class="mt-0.5 text-[11px] text-senat-muted">
                                {{ $meeting['type'] ?? 'Agenda' }} · {{ $tanggal($meeting['date']) }}
                            </p>
                        </div>
                        <x-senat.badge :state="$sedang ? 'success' : 'info'" :dot="false">
                            {{ $meeting['status'] ?? 'Terjadwal' }}
                        </x-senat.badge>
                    </li>
                @empty
                    <li class="py-4">
                        <p class="text-[13px] text-senat-muted">Tidak ada tindakan yang menunggu respons.</p>
                    </li>
                @endforelse

                @foreach ($aspirations as $aspiration)
                    <li class="flex items-start gap-4 py-4 first:pt-0 last:pb-0">
                        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-[#7048e8]" aria-hidden="true"></span>
                        <div class="min-w-0 flex-1">
                            <p class="text-[14px] font-semibold text-senat-ink">Tinjau aspirasi konstituen</p>
                            <p class="mt-0.5 text-[11px] text-senat-muted">
                                {{ $aspiration['subject'] }} · {{ $aspiration['committee_target'] }}
                            </p>
                        </div>
                        <span class="shrink-0 whitespace-nowrap text-[11px] font-semibold text-senat-muted">
                            {{ $aspiration['status'] }}
                        </span>
                    </li>
                @endforeach
            </ul>
        </section>

        {{-- Agenda Terdekat --}}
        <section class="rounded-[14px] border border-senat-line bg-white p-5 shadow-[0_3px_12px_rgba(13,26,41,0.07)]">
            <h2 class="text-[18px] font-bold text-senat-ink">Agenda Terdekat</h2>

            @if ($agendaTerdekat)
                <div class="mt-4">
                    <x-senat.badge state="info" :dot="false">{{ $agendaTerdekat['type'] ?? 'Agenda' }}</x-senat.badge>
                    <h3 class="mt-3 text-[16px] font-semibold leading-snug text-senat-ink">{{ $agendaTerdekat['title'] }}</h3>
                    <p class="mt-2 text-[12px] leading-relaxed text-senat-muted">{{ $agendaTerdekat['room'] }}</p>
                    <div class="mt-4 space-y-1">
                        <p class="text-[12px] font-medium text-senat-muted">{{ $tanggal($agendaTerdekat['date']) }}</p>
                        <p class="text-[12px] font-semibold text-senat-ink">
                            {{ $agendaTerdekat['time'] }} · {{ $agendaTerdekat['session_code'] }}
                        </p>
                    </div>
                    <x-senat.button class="mt-5" :href="route('portal.agenda')">Buka Agenda</x-senat.button>
                </div>
            @else
                <p class="mt-4 text-[13px] text-senat-muted">Belum ada agenda terjadwal.</p>
            @endif
        </section>
    </div>

    {{-- ================= AKTIVITAS TERKINI ================= --}}
    <section class="mt-6 rounded-[14px] border border-senat-line bg-white p-5 shadow-[0_3px_12px_rgba(13,26,41,0.07)]">
        <h2 class="text-[18px] font-bold text-senat-ink">Aktivitas Terkini</h2>

        <ul class="mt-5 divide-y divide-senat-line">
            @forelse ($announcements as $announcement)
                <li class="flex flex-wrap items-start gap-x-4 gap-y-1 py-4 first:pt-0 last:pb-0">
                    <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-[5px] bg-senat-link" aria-hidden="true"></span>
                    <div class="min-w-0 flex-1">
                        <p class="text-[14px] font-semibold text-senat-ink">{{ $announcement['title'] }}</p>
                        <p class="mt-0.5 text-[12px] text-senat-muted">{{ $announcement['excerpt'] }}</p>
                    </div>
                    <span class="shrink-0 whitespace-nowrap text-[11px] font-medium text-senat-muted">
                        {{ $tanggal($announcement['date']) }}
                    </span>
                </li>
            @empty
                <li class="py-4"><p class="text-[13px] text-senat-muted">Belum ada aktivitas tercatat.</p></li>
            @endforelse
        </ul>
    </section>

    {{-- ================= WARTA & PENGUMUMAN ================= --}}
    <section class="mt-6">
        <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
            <h2 class="text-[18px] font-bold text-senat-ink">Warta &amp; Pengumuman</h2>
            <x-senat.button variant="ghost" size="sm" :trailing="'›'" :href="route('public.news')">Kanal berita publik</x-senat.button>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            @forelse ($announcements as $announcement)
                <article class="flex h-full flex-col rounded-[14px] border border-senat-line bg-white p-5 shadow-[0_3px_12px_rgba(13,26,41,0.06)]">
                    <x-senat.badge state="neutral" :dot="false">{{ $announcement['category'] }}</x-senat.badge>
                    <h3 class="mt-3 text-[14px] font-semibold leading-snug text-senat-ink">{{ $announcement['title'] }}</h3>
                    <p class="mt-2 line-clamp-3 text-[12px] leading-relaxed text-senat-muted">{{ $announcement['excerpt'] }}</p>
                    <p class="mt-auto pt-4 text-[11px] font-medium text-senat-muted">{{ $tanggal($announcement['date']) }}</p>
                </article>
            @empty
                <div class="md:col-span-2 xl:col-span-4">
                    <x-senat.empty title="Belum ada pengumuman" message="Warta terbaru Senat akan muncul di area ini." />
                </div>
            @endforelse
        </div>
    </section>

</div>
@endsection
