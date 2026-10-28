@extends('layouts.public')

@section('title', 'Arsip · Risalah Sidang — Senat Akademik Politeknik Negeri Semarang')

@section('content')

    {{-- ===================== HERO (Figma 2020:161) ===================== --}}
    <x-senat.page-hero
        eyebrow="Dokumentasi & Memori Kolektif"
        title="Risalah Sidang & Notulensi Pleno"
        subtitle="Dokumentasi resmi risalah musyawarah komisi, catatan persidangan pleno terbuka, dan berita acara rapat senat."
        height="h-[300px]"
    />

    @php
        $archiveCategories = collect($archives)->pluck('category')->filter()->unique()->values()->all();
    @endphp

    {{-- ============ ARSIP DOKUMEN + TAB KATEGORI RISALAH ============ --}}
    <section class="senat-shell py-14" x-data="{ cat: '' }">

        <div class="mb-8">
            <p class="text-[11px] font-bold uppercase tracking-[0.1em] text-senat-orange">Dokumen Resmi</p>
            <h2 class="mt-2 text-[27px] font-bold leading-tight tracking-tight text-senat-ink">Arsip Kegiatan Senat Akademik</h2>
            <p class="mt-3 max-w-[720px] text-[13px] leading-relaxed text-senat-body">
                Telusuri risalah sidang pleno, berita acara rapat, dan notulensi komisi berdasarkan kategori risalah resmi Sekretariat Senat.
            </p>
        </div>

        @if (count($archives) > 0)

            {{-- Tab / filter kategori risalah (client-side, Alpine) --}}
            <div class="mb-6 flex flex-wrap gap-2" role="tablist" aria-label="Filter kategori risalah">
                <button type="button" role="tab" data-cat="" @click="cat = ''"
                        :class="cat === '' ? 'border-senat-primary bg-senat-primary text-white' : 'border-slate-200 bg-white text-slate-600 hover:border-senat-primary/50'"
                        class="inline-flex h-8 items-center rounded-md border px-3.5 text-[12px] font-semibold transition-colors duration-150">
                    Semua Kategori
                </button>
                @foreach ($archiveCategories as $category)
                    <button type="button" role="tab" data-cat="{{ $category }}" @click="cat = $el.dataset.cat"
                            :class="cat === $el.dataset.cat ? 'border-senat-primary bg-senat-primary text-white' : 'border-slate-200 bg-white text-slate-600 hover:border-senat-primary/50'"
                            class="inline-flex h-8 items-center rounded-md border px-3.5 text-[12px] font-semibold transition-colors duration-150">
                        {{ $category }}
                    </button>
                @endforeach
            </div>

            {{-- Grid dokumen arsip (Figma 2020:167 Archive card) --}}
            <div class="grid gap-5 md:grid-cols-2">
                @foreach ($archives as $item)
                    <article
                        data-archive-card
                        data-cat="{{ $item['category'] }}"
                        x-show="cat === '' || cat === $el.dataset.cat"
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
                            Dokumen resmi yang telah disahkan melalui Sidang Pleno Terbuka Senat Akademik Politeknik Negeri Semarang.
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

            {{-- Empty state saat filter tidak menemukan dokumen --}}
            <div
                x-show="cat !== '' && !Array.from($root.querySelectorAll('[data-archive-card]')).some(el => el.dataset.cat === cat)"
                x-cloak
                class="rounded-xl border border-dashed border-senat-border bg-white px-6 py-10 text-center"
            >
                <p class="text-sm text-slate-500">Tidak ada dokumen pada kategori risalah ini.</p>
            </div>

        @else
            <x-senat.empty title="Belum ada arsip dokumen" message="Dokumen risalah sidang akan ditampilkan di sini setelah dipublikasikan oleh Sekretariat Senat." />
        @endif
    </section>

    {{-- ============ DAFTAR SIDANG ($meetings) ============ --}}
    <section class="border-t border-senat-line bg-white">
        <div class="senat-shell py-14">
            <div class="mb-8">
                <p class="text-[11px] font-bold uppercase tracking-[0.1em] text-senat-orange">Agenda Terdokumentasi</p>
                <h2 class="mt-2 text-[27px] font-bold leading-tight tracking-tight text-senat-ink">Daftar Sidang &amp; Rapat Senat</h2>
                <p class="mt-3 max-w-[720px] text-[13px] leading-relaxed text-senat-body">
                    Rekapitulasi persidangan pleno dan rapat komisi Senat Akademik Polines beserta status forum dan tingkat kehadiran anggota.
                </p>
            </div>

            <div class="space-y-4">
                @forelse ($meetings as $meeting)
                    @php
                        $state = match ($meeting['status']) {
                            'Sedang Berlangsung' => 'success',
                            'Terjadwal' => 'info',
                            default => 'neutral',
                        };
                    @endphp
                    <article class="rounded-xl border border-senat-line bg-senat-surface/60 p-5 transition-colors duration-150 hover:border-senat-primary/30 sm:p-6">
                        <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <x-senat.badge :state="$state">{{ $meeting['status'] }}</x-senat.badge>
                                    <span class="inline-flex items-center rounded-full border border-senat-line bg-white px-2.5 py-[3px] text-[10px] font-bold uppercase tracking-[0.06em] text-senat-body">
                                        {{ $meeting['type'] }}
                                    </span>
                                    <span class="font-mono text-[11px] font-semibold text-senat-muted">#{{ $meeting['session_code'] }}</span>
                                </div>

                                <h3 class="mt-3 text-[15px] font-bold leading-snug tracking-tight text-senat-ink">{{ $meeting['title'] }}</h3>

                                <div class="mt-3 flex flex-wrap gap-x-6 gap-y-2 text-[12px] text-senat-body">
                                    <span class="inline-flex items-center gap-1.5">
                                        <x-senat.icon name="calendar" class="h-3.5 w-3.5" />
                                        {{ \Carbon\Carbon::parse($meeting['date'])->translatedFormat('d F Y') }}
                                    </span>
                                    <span class="inline-flex items-center gap-1.5">
                                        <span aria-hidden="true">🕘</span>
                                        {{ $meeting['time'] }}
                                    </span>
                                    <span class="inline-flex items-start gap-1.5">
                                        <span aria-hidden="true">📍</span>
                                        {{ $meeting['room'] }}
                                    </span>
                                </div>
                            </div>

                            <div class="shrink-0 rounded-lg border border-senat-line bg-white px-5 py-3 text-center">
                                <p class="text-[10px] font-bold uppercase tracking-[0.06em] text-senat-muted">Kehadiran</p>
                                <p class="mt-1 text-[20px] font-extrabold leading-none text-senat-primary tabular-nums">
                                    {{ $meeting['present_count'] }}<span class="text-[12px] font-semibold text-senat-muted">/{{ $meeting['total_invited'] }}</span>
                                </p>
                                <p class="mt-1 text-[10px] font-medium text-senat-muted">anggota</p>
                            </div>
                        </div>
                    </article>
                @empty
                    <x-senat.empty title="Belum ada agenda sidang" message="Jadwal persidangan Senat Akademik akan tampil di sini." />
                @endforelse
            </div>
        </div>
    </section>

@endsection
