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
    <section class="senat-shell pt-12 pb-20 sm:pt-14 sm:pb-24 lg:pt-16 lg:pb-28">
        <div class="mx-auto max-w-4xl">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.1em] text-senat-orange">Wewenang Normatif</p>
                <h2 class="mt-3 text-2xl font-bold leading-snug text-senat-ink sm:text-[28px]">Fungsi Utama Senat Berdasarkan Statuta Polines</h2>
                <p class="mt-4 max-w-[68ch] text-base leading-7 text-senat-body">
                    Senat Akademik menjalankan wewenang kolegial dalam merumuskan arah kebijakan akademik, mengawal akuntabilitas penjaminan mutu, serta menjamin hak sivitas akademika.
                </p>
            </div>

            <ol class="mt-8 space-y-7 sm:mt-10 sm:space-y-8">
                @foreach ($kewenangan as $duty)
                    <li class="flex items-start gap-4 sm:gap-5">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-[10px] border border-senat-badgeLine bg-senat-badgeBg text-sm font-bold text-senat-link tabular-nums">
                            {{ $duty['no'] }}
                        </span>
                        <div class="min-w-0 pt-1.5">
                            <p class="text-base font-bold leading-6 text-senat-ink sm:text-lg sm:leading-7">{{ $duty['title'] }}</p>
                            <p class="mt-2 max-w-[68ch] text-sm leading-6 text-senat-body sm:text-base sm:leading-7">{{ $duty['note'] }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

@endsection
