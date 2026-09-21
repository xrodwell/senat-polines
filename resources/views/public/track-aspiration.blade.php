@extends('layouts.public')

@section('title', 'Pelacak Status Aspirasi — Senat Akademik Polines')

@section('content')
<div class="bg-polines-navy text-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-300 mb-2">Tracking &amp; Verifikasi</p>
        <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">Pelacak Status Aspirasi Akademik</h1>
        <p class="mt-3 text-base text-blue-100/90 max-w-2xl">
            Pantau transparansi penanganan aduan dan masukan Anda melalui nomor registrasi tiket resmi.
        </p>
    </div>
</div>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    {{-- Form Input Tiket --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 sm:p-8 shadow-sm mb-8">
        <form action="{{ route('public.aspirations.track') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-2">Masukkan Kode Tiket Aspirasi</label>
                <input type="text" name="ticket" value="{{ $ticket ?? '' }}" placeholder="Contoh: ASP-POLINES-2026-0042" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-polines-blue focus:ring-1 focus:ring-polines-blue outline-none transition-all font-mono tabular-nums">
            </div>
            <div class="sm:self-end">
                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-lg bg-polines-navy hover:bg-polines-navyDark text-white px-6 py-2.5 text-sm font-semibold transition-colors">
                    Cek Progres
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
            </div>
        </form>
    </div>

    @if ($ticket)
        @if ($aspiration)
            {{-- Hasil Tracking Ditemukan --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 sm:p-8 shadow-sm space-y-8">
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 pb-6">
                    <div>
                        <span class="text-xs font-semibold text-slate-400">Nomor Registrasi Tiket</span>
                        <h2 class="text-2xl font-mono font-extrabold text-polines-navy tabular-nums">{{ $aspiration['ticket_code'] }}</h2>
                    </div>
                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold {{ $aspiration['status'] === 'Ditindaklanjuti Komisi' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                        <span class="w-2 h-2 rounded-full {{ $aspiration['status'] === 'Ditindaklanjuti Komisi' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                        {{ $aspiration['status'] }}
                    </span>
                </div>

                {{-- Visual Progress Stepper --}}
                <div class="py-2">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-6">Tahapan Disposisi &amp; Musyawarah</p>
                    <div class="relative flex flex-col md:flex-row justify-between gap-6">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold text-xs shrink-0">✓</div>
                            <div>
                                <p class="text-xs font-bold text-slate-800">Tiket Diterima</p>
                                <p class="text-[11px] text-slate-400 tabular-nums">14 Sep 2026</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold text-xs shrink-0">✓</div>
                            <div>
                                <p class="text-xs font-bold text-slate-800">Disposisi Komisi</p>
                                <p class="text-[11px] text-slate-400">Komisi I Senat</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full {{ $aspiration['status'] === 'Ditindaklanjuti Komisi' ? 'bg-emerald-500 text-white' : 'bg-polines-navy text-white animate-pulse' }} flex items-center justify-center font-bold text-xs shrink-0">
                                {{ $aspiration['status'] === 'Ditindaklanjuti Komisi' ? '✓' : '3' }}
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-800">Rapat Pleno / Komisi</p>
                                <p class="text-[11px] text-slate-400">Penelaahan naskah</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full {{ $aspiration['status'] === 'Ditindaklanjuti Komisi' ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-500' }} flex items-center justify-center font-bold text-xs shrink-0">
                                {{ $aspiration['status'] === 'Ditindaklanjuti Komisi' ? '✓' : '4' }}
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-800">Rekomendasi / SK</p>
                                <p class="text-[11px] text-slate-400">Penyampaian ke Direktur</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Detail Aspirasi & Tanggapan Resmi --}}
                <div class="space-y-6 pt-4 border-t border-slate-100">
                    <div>
                        <span class="text-xs font-semibold text-slate-400">Pokok Bahasan</span>
                        <h3 class="text-lg font-bold text-slate-900 mt-1">{{ $aspiration['subject'] }}</h3>
                        <p class="text-sm text-slate-600 mt-2 leading-relaxed">{{ $aspiration['description'] }}</p>
                    </div>

                    <div class="rounded-xl border border-blue-200/80 bg-blue-50/50 p-5">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-5 h-5 text-polines-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-polines-navy">Tanggapan &amp; Rekomendasi Resmi Senat</h4>
                        </div>
                        <p class="text-sm text-slate-700 leading-relaxed">{{ $aspiration['response'] }}</p>
                    </div>
                </div>
            </div>
        @else
            {{-- Tiket Tidak Ditemukan --}}
            <div class="bg-white rounded-2xl border border-rose-200/80 p-8 text-center shadow-sm">
                <div class="w-12 h-12 bg-rose-100 text-rose-600 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-slate-900">Tiket Tidak Ditemukan</h3>
                <p class="text-sm text-slate-500 mt-1 max-w-sm mx-auto">
                    Nomor tiket <span class="font-mono font-bold text-slate-800">{{ $ticket }}</span> tidak terdaftar dalam pangkalan data aspirasi Senat Akademik.
                </p>
                <div class="mt-6">
                    <a href="{{ route('public.aspirations') }}" class="text-sm font-semibold text-polines-blue hover:underline">
                        ← Kembali ke formulir aspirasi
                    </a>
                </div>
            </div>
        @endif
    @endif
</div>
@endsection
