@extends('layouts.public')

@section('title', 'Arsip & Laporan Pertanggungjawaban — Senat Akademik Polines')

@section('content')
<div class="bg-polines-navy text-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-300 mb-2">Dokumentasi &amp; Memori Kolektif</p>
        <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">Arsip Kegiatan &amp; Laporan Pertanggungjawaban (LPJ)</h1>
        <p class="mt-3 text-base text-blue-100/90 max-w-2xl">
            Repositori resmi risalah musyawarah, memori serah terima jabatan pimpinan, dan laporan evaluasi tahunan kinerja Senat Akademik Polines.
        </p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($archives as $item)
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 sm:p-8 shadow-sm flex flex-col justify-between hover:border-slate-300 transition-all">
                <div>
                    <div class="flex items-center justify-between gap-4 mb-4">
                        <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-polines-blue">
                            {{ $item['category'] }}
                        </span>
                        <span class="font-mono text-sm font-bold text-slate-400 tabular-nums">
                            Tahun {{ $item['year'] }}
                        </span>
                    </div>

                    <h3 class="text-lg font-bold text-slate-900 leading-snug mb-3">
                        {{ $item['title'] }}
                    </h3>

                    <p class="text-xs text-slate-500 mb-6">
                        Dokumen resmi yang telah disahkan melalui Sidang Pleno Terbuka Senat Akademik Politeknik Negeri Semarang.
                    </p>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-400 tabular-nums">Ukuran Berkas: {{ $item['file_size'] }}</span>
                    <a href="{{ $item['download_url'] }}" class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 px-4 py-2 text-xs font-bold transition-colors">
                        <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Unduh Berkas PDF
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
