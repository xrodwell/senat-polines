@extends('layouts.public')

@section('title', 'Tugas, Fungsi & Wewenang — Senat Akademik Politeknik Negeri Semarang')
@section('description', 'Tugas pokok, fungsi, dan wewenang normatif Senat Akademik Politeknik Negeri Semarang berdasarkan Statuta Polines.')

@section('content')

    {{-- 02B — Profil · Tugas & Wewenang (Figma node 2204:147) --}}
    <x-senat.page-hero
        eyebrow="Landasan Hukum & Tugas Pokok"
        title="Tugas, Fungsi & Wewenang Senat"
        subtitle="Badan normatif tertinggi di Politeknik Negeri Semarang dengan wewenang pengawasan kebijakan akademik, pertimbangan strategis, dan penegakan etika keilmuan."
    />

    @php
        $kewenangan = [
            [
                'no' => '01',
                'title' => 'Menetapkan norma, kebijakan, dan peraturan akademik.',
                'note' => 'Pelaksanaan dilakukan melalui forum, komisi, dan sidang resmi Senat Akademik.',
            ],
            [
                'no' => '02',
                'title' => 'Memberikan pertimbangan terhadap rencana strategis institusi.',
                'note' => 'Pelaksanaan dilakukan melalui forum, komisi, dan sidang resmi Senat Akademik.',
            ],
            [
                'no' => '03',
                'title' => 'Melakukan pengawasan mutu serta etika akademik.',
                'note' => 'Pelaksanaan dilakukan melalui forum, komisi, dan sidang resmi Senat Akademik.',
            ],
        ];
    @endphp

    {{-- ================= WEWENANG NORMATIF ================= --}}
    <section class="senat-shell pt-14 lg:pt-16">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.1em] text-senat-orange">Wewenang Normatif</p>
            <h2 class="mt-3 text-[28px] font-bold leading-[1.15] text-senat-ink">Fungsi Utama Senat Berdasarkan Statuta Polines</h2>
            <p class="mt-3 max-w-[1264px] text-[14px] leading-[1.45] text-senat-body">
                Senat Akademik menjalankan wewenang kolegial dalam merumuskan arah kebijakan akademik, mengawal akuntabilitas penjaminan mutu, serta menjamin hak sivitas akademika.
            </p>
        </div>

        <ol class="mt-14 space-y-9">
            @foreach ($kewenangan as $duty)
                <li class="flex gap-[21px]">
                    <span class="flex h-[45px] w-[45px] shrink-0 items-center justify-center rounded-[10px] border border-senat-badgeLine bg-senat-badgeBg text-[13px] font-bold text-senat-link tabular-nums">
                        {{ $duty['no'] }}
                    </span>
                    <div class="min-w-0 pt-[6px]">
                        <p class="text-[14px] font-bold leading-[1.25] text-senat-ink">{{ $duty['title'] }}</p>
                        <p class="mt-1.5 text-[11px] leading-[1.2] text-senat-body">{{ $duty['note'] }}</p>
                    </div>
                </li>
            @endforeach
        </ol>
    </section>

    {{-- ================= LIMA FITUR UNGGULAN SENAT ================= --}}
    <section class="mt-16 border-t border-senat-line bg-white">
        <div class="senat-shell py-14">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.1em] text-senat-orange">Peta Produk</p>
                    <h2 class="mt-3 text-[22px] font-bold leading-[1.2] text-[#0f172a]">Lima Fitur Unggulan SENAT</h2>
                    <p class="mt-3 max-w-[860px] text-[13.5px] leading-[1.5] text-senat-muted">
                        Modul kerja yang menopang pelaksanaan tugas dan wewenang Senat: penjaringan aspirasi, sidang digital, transparansi produk hukum, akuntabilitas anggota, dan kanal informasi civitas.
                    </p>
                </div>
                <x-senat.button variant="secondary" size="md" :href="route('public.aspirations')" trailing="›">
                    Kanal Aspirasi
                </x-senat.button>
            </div>

            <div class="mt-6 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                @forelse ($features as $feature)
                    <article class="flex flex-col rounded-[12px] border border-senat-line bg-white p-6 transition-colors hover:border-senat-badgeLine">
                        <div class="flex items-start gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-[10px] bg-senat-hero text-[13px] font-bold text-white tabular-nums">
                                {{ $feature['code'] }}
                            </span>
                            <div class="min-w-0">
                                <h3 class="text-[14px] font-bold leading-snug text-senat-ink">{{ $feature['title'] }}</h3>
                                <p class="mt-0.5 text-[11px] font-medium text-senat-link">{{ $feature['subtitle'] }}</p>
                            </div>
                        </div>

                        <ul class="mt-4 flex-1 space-y-2.5">
                            @foreach ($feature['points'] as $point)
                                <li class="text-[12px] leading-relaxed text-senat-body">
                                    <span class="font-semibold text-senat-ink">{{ $point['label'] }}.</span>
                                    {{ $point['detail'] }}
                                </li>
                            @endforeach
                        </ul>

                        @if (! empty($feature['flow']))
                            <div class="mt-4 flex flex-wrap items-center gap-1.5 border-t border-senat-line pt-4">
                                @foreach ($feature['flow'] as $stage)
                                    <span class="inline-flex items-center rounded-full border border-senat-badgeLine bg-senat-badgeBg px-2 py-[2px] text-[10px] font-bold uppercase tracking-[0.06em] text-senat-link">{{ $stage }}</span>
                                    @if (! $loop->last)
                                        <span class="shrink-0 text-[10px] text-senat-muted" aria-hidden="true">›</span>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </article>
                @empty
                    <div class="md:col-span-2 lg:col-span-3">
                        <x-senat.empty title="Data fitur belum dimuat"
                                       message="Peta fitur layanan Senat Akademik akan tampil setelah data tersedia." />
                    </div>
                @endforelse
            </div>
        </div>
    </section>

@endsection
