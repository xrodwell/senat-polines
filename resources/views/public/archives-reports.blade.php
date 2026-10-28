@extends('layouts.public')

@section('title', 'Arsip · Laporan Tahunan & LPJ — Senat Akademik Politeknik Negeri Semarang')

@section('content')

    {{-- ===================== HERO (Figma 2236:368) ===================== --}}
    <x-senat.page-hero
        eyebrow="Dokumentasi & Memori Kolektif"
        title="Laporan Tahunan & Akuntabilitas (LPJ)"
        subtitle="Laporan pertanggungjawaban berkala kinerja pimpinan, memori serah terima jabatan pimpinan senat, dan laporan evaluasi tahunan."
        height="h-[300px]"
    />

    @php
        $reportYears = collect($archives)->pluck('year')->filter()->unique()->sortDesc()->values()->all();
    @endphp

    {{-- ============ ARSIP LAPORAN + FILTER TAHUN ============ --}}
    <section class="senat-shell py-14" x-data="{ year: '' }">

        <div class="mb-8">
            <p class="text-[11px] font-bold uppercase tracking-[0.1em] text-senat-orange">Dokumen Resmi</p>
            <h2 class="mt-2 text-[27px] font-bold leading-tight tracking-tight text-senat-ink">Arsip Kegiatan Senat Akademik</h2>
            <p class="mt-3 max-w-[720px] text-[13px] leading-relaxed text-senat-body">
                Kumpulan laporan tahunan, laporan pertanggungjawaban (LPJ), memori jabatan, dan laporan evaluasi kinerja Senat Akademik Polines.
            </p>
        </div>

        @if (count($archives) > 0)

            {{-- Filter tahun (client-side, Alpine) --}}
            <div class="mb-6 flex flex-wrap gap-2" role="tablist" aria-label="Filter tahun laporan">
                <button type="button" data-year="" @click="year = ''"
                        :class="year === '' ? 'border-senat-primary bg-senat-primary text-white' : 'border-slate-200 bg-white text-slate-600 hover:border-senat-primary/50'"
                        class="inline-flex h-8 items-center rounded-md border px-3.5 text-[12px] font-semibold transition-colors duration-150">
                    Semua Tahun
                </button>
                @foreach ($reportYears as $reportYear)
                    <button type="button" data-year="{{ $reportYear }}" @click="year = $el.dataset.year"
                            :class="year === $el.dataset.year ? 'border-senat-primary bg-senat-primary text-white' : 'border-slate-200 bg-white text-slate-600 hover:border-senat-primary/50'"
                            class="inline-flex h-8 items-center rounded-md border px-3.5 text-[12px] font-semibold transition-colors duration-150">
                        {{ $reportYear }}
                    </button>
                @endforeach
            </div>

            {{-- Grid dokumen laporan (Figma 2236:374 Archive card) --}}
            <div class="grid gap-5 md:grid-cols-2">
                @foreach ($archives as $item)
                    <article
                        data-report-card
                        data-year="{{ $item['year'] }}"
                        x-show="year === '' || year === $el.dataset.year"
                        x-transition.opacity.duration.150ms
                        class="flex flex-col rounded-[15px] border border-[#d9e3f0] bg-white p-6 transition-colors duration-150 hover:border-senat-primary/40"
                    >
                        <div class="flex items-center justify-between gap-4">
                            <span class="inline-flex items-center rounded-full bg-senat-badgeBg px-3 py-1 text-[10px] font-bold uppercase tracking-[0.06em] text-senat-link">
                                {{ $item['category'] }}
                            </span>
                            <span class="text-[12px] font-bold text-senat-body tabular-nums">Tahun {{ $item['year'] }}</span>
                        </div>

                        <h3 class="mt-4 text-[16px] font-bold leading-snug tracking-tight text-senat-ink">{{ $item['title'] }}</h3>

                        <p class="mt-2 flex-1 text-[11px] leading-relaxed text-senat-body">
                            Laporan resmi kinerja, capaian program kerja, dan akuntabilitas Senat Akademik Politeknik Negeri Semarang.
                        </p>

                        <div class="mt-5 flex items-center justify-between gap-4 border-t border-slate-100 pt-4">
                            <span class="text-[10px] font-medium text-senat-body">Ukuran Berkas: {{ $item['file_size'] }}</span>
                            <a href="{{ $item['download_url'] }}"
                               class="inline-flex h-[34px] items-center gap-1.5 rounded-[7px] bg-senat-badgeBg px-4 text-[10px] font-bold text-senat-ink transition-colors duration-150 hover:bg-senat-skySoft">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Unduh Berkas PDF
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- Empty state saat filter tahun tidak menemukan dokumen --}}
            <div
                x-show="year !== '' && !Array.from($root.querySelectorAll('[data-report-card]')).some(el => el.dataset.year === year)"
                x-cloak
                class="rounded-xl border border-dashed border-senat-border bg-white px-6 py-10 text-center"
            >
                <p class="text-sm text-slate-500">Tidak ada laporan pada tahun ini.</p>
            </div>

        @else
            <x-senat.empty title="Belum ada laporan tahunan" message="Laporan tahunan dan LPJ akan ditampilkan di sini setelah dipublikasikan oleh Sekretariat Senat." />
        @endif
    </section>

@endsection
