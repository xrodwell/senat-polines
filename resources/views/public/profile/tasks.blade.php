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

@endsection
