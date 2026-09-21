@extends('layouts.public')

@section('title', 'Panel Kontrol Sekretariat — Manajemen Sesi Sidang & Presensi')

@section('content')
<div class="bg-polines-navy text-white py-8 border-b border-polines-navyDark">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="w-2 h-2 rounded-full bg-polines-orange"></span>
                <span class="text-xs font-semibold text-blue-200 uppercase tracking-wider">Panel Sekretariat Senat Akademik</span>
            </div>
            <h1 class="text-2xl font-extrabold tracking-tight">Manajemen Sesi Sidang &amp; Presensi Digital</h1>
            <p class="text-xs text-blue-200/80">Kontrol proyektor layar ruang sidang, pembukaan sesi absensi, dan rekapitulasi kuorum.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-xs bg-white/10 border border-white/20 px-3 py-1.5 rounded-lg text-white font-mono tabular-nums">
                Mode: Kontrol Sekretariat
            </span>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10"
     x-data="{
         sessionOpen: true,
         sessionCode: 'PLN-892',
         presentCount: 28,
         totalInvited: 34,
         toggleSession() {
             this.sessionOpen = !this.sessionOpen;
         }
     }">
    
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        {{-- Kolom Kiri: Layar Sesi Sidang Aktif (Layar Proyektor) --}}
        <div class="lg:col-span-8 space-y-8">
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 sm:p-8 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 pb-5 mb-6">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Status Sesi Rapat Saat Ini</span>
                        <h2 class="text-xl font-extrabold text-slate-900 mt-0.5">
                            Sidang Pleno Evaluasi Akreditasi Program Studi Vokasi
                        </h2>
                    </div>

                    <div>
                        <button @click="toggleSession" type="button" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-bold transition-all"
                                :class="sessionOpen ? 'bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100' : 'bg-emerald-600 text-white hover:bg-emerald-700'">
                            <span class="w-2 h-2 rounded-full" :class="sessionOpen ? 'bg-rose-500' : 'bg-white'"></span>
                            <span x-text="sessionOpen ? 'Tutup Sesi Presensi' : 'Buka Kembali Sesi Presensi'"></span>
                        </button>
                    </div>
                </div>

                {{-- Area Tampilan Proyektor Ruang Rapat --}}
                <div class="bg-slate-950 text-white rounded-2xl p-6 sm:p-8 border border-slate-800 relative overflow-hidden" x-show="sessionOpen">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                        {{-- QR Code Display --}}
                        <div class="flex flex-col items-center justify-center p-6 bg-white rounded-xl shadow-lg">
                            {{-- Simulasi SVG QR Code Modern --}}
                            <svg class="w-48 h-48 text-slate-900" viewBox="0 0 100 100" fill="currentColor">
                                <rect width="28" height="28" x="6" y="6" rx="4" fill="#003366"/>
                                <rect width="16" height="16" x="12" y="12" fill="white"/>
                                <rect width="8" height="8" x="16" y="16" fill="#F37021"/>

                                <rect width="28" height="28" x="66" y="6" rx="4" fill="#003366"/>
                                <rect width="16" height="16" x="72" y="12" fill="white"/>
                                <rect width="8" height="8" x="76" y="16" fill="#F37021"/>

                                <rect width="28" height="28" x="6" y="66" rx="4" fill="#003366"/>
                                <rect width="16" height="16" x="12" y="72" fill="white"/>
                                <rect width="8" height="8" x="16" y="76" fill="#F37021"/>

                                {{-- Data Matrix Nodes --}}
                                <circle cx="44" cy="18" r="3" fill="#003366"/>
                                <circle cx="56" cy="18" r="3" fill="#003366"/>
                                <circle cx="44" cy="28" r="3" fill="#003366"/>
                                <circle cx="50" cy="40" r="4" fill="#F37021"/>
                                <circle cx="40" cy="50" r="3" fill="#003366"/>
                                <circle cx="60" cy="50" r="3" fill="#003366"/>
                                <circle cx="50" cy="60" r="4" fill="#F37021"/>
                                <circle cx="40" cy="75" r="3" fill="#003366"/>
                                <circle cx="56" cy="75" r="3" fill="#003366"/>
                                <circle cx="75" cy="44" r="3" fill="#003366"/>
                                <circle cx="85" cy="55" r="3" fill="#003366"/>
                                <circle cx="75" cy="65" r="3" fill="#003366"/>
                                <circle cx="85" cy="75" r="3" fill="#003366"/>
                            </svg>
                            <span class="text-[11px] font-mono text-slate-500 mt-3 font-semibold uppercase tracking-wider">
                                Scan via Smartphone Anggota
                            </span>
                        </div>

                        {{-- Kode Sesi & Instruksi Proyektor --}}
                        <div class="space-y-5 text-center md:text-left">
                            <div>
                                <span class="text-xs font-bold uppercase tracking-[0.2em] text-polines-orange">
                                    Kode Sesi Presensi
                                </span>
                                <div class="text-4xl sm:text-5xl font-mono font-extrabold text-white tracking-widest mt-1 tabular-nums" x-text="sessionCode"></div>
                            </div>

                            <p class="text-xs text-slate-400 leading-relaxed">
                                Anggota Senat dapat melakukan presensi melalui tautan <span class="text-blue-300 font-mono">/portal/attendance</span> dengan memasukkan kode sesi di atas atau memindai QR Code.
                            </p>

                            <div class="pt-2 border-t border-slate-800 flex items-center justify-between text-xs text-slate-400 font-mono">
                                <span>Token TTL: 30 Detik (Auto-Refresh)</span>
                                <span class="text-emerald-400">● Dynamic Security On</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Status Sesi Ditutup --}}
                <div class="bg-slate-100 rounded-2xl p-12 text-center border border-slate-200" x-show="!sessionOpen" x-cloak>
                    <div class="w-12 h-12 bg-slate-200 text-slate-600 rounded-full flex items-center justify-center mx-auto mb-3 text-lg font-bold">
                        🔒
                    </div>
                    <h3 class="text-lg font-bold text-slate-800">Sesi Presensi Telah Ditutup</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                        Presensi mandiri tidak lagi menerima input dari anggota. Data kuorum kehadiran telah dibekukan untuk berita acara sidang.
                    </p>
                </div>
            </div>

            {{-- Rekapitulasi Kuorum Sidang --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 sm:p-8 shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Rekapitulasi Kehadiran (Kuorum)</h3>
                        <p class="text-xs text-slate-500">Batas minimal kuorum sidang pleno adalah 2/3 dari 34 Anggota (23 Anggota).</p>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 border border-emerald-200">
                        ✓ Kuorum Terpenuhi (82%)
                    </span>
                </div>

                {{-- Progress Bar Kuorum --}}
                <div class="w-full bg-slate-100 rounded-full h-3 mb-6 overflow-hidden">
                    <div class="bg-emerald-500 h-3 rounded-full transition-all duration-500" style="width: 82%;"></div>
                </div>

                <div class="grid grid-cols-3 gap-4 text-center">
                    <div class="bg-slate-50 border border-slate-100 rounded-xl p-4">
                        <span class="text-xs font-semibold text-slate-500">Hadir di Ruangan</span>
                        <div class="text-2xl font-bold text-emerald-600 font-mono tabular-nums mt-1" x-text="presentCount"></div>
                    </div>
                    <div class="bg-slate-50 border border-slate-100 rounded-xl p-4">
                        <span class="text-xs font-semibold text-slate-500">Izin Resmi</span>
                        <div class="text-2xl font-bold text-amber-600 font-mono tabular-nums mt-1">3</div>
                    </div>
                    <div class="bg-slate-50 border border-slate-100 rounded-xl p-4">
                        <span class="text-xs font-semibold text-slate-500">Belum Presensi</span>
                        <div class="text-2xl font-bold text-rose-600 font-mono tabular-nums mt-1">3</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Kalender Sidang Seluruh Komisi --}}
        <div class="lg:col-span-4 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-slate-900">Master Kalender Sidang</h3>
                    <span class="text-xs font-mono text-slate-400">Total: {{ count($meetings) }}</span>
                </div>

                <div class="space-y-4">
                    @foreach($meetings as $mtg)
                        <div class="rounded-xl border p-4 {{ $mtg['status'] === 'Sedang Berlangsung' ? 'border-amber-300 bg-amber-50/40' : 'border-slate-200 bg-slate-50/70' }}">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-[11px] font-bold px-2 py-0.5 rounded {{ $mtg['status'] === 'Sedang Berlangsung' ? 'bg-amber-100 text-amber-800' : 'bg-slate-200 text-slate-700' }}">
                                    {{ $mtg['status'] }}
                                </span>
                                <span class="font-mono text-xs font-bold text-slate-600 tabular-nums">{{ $mtg['session_code'] }}</span>
                            </div>
                            <h4 class="text-xs font-bold text-slate-900 leading-snug mb-2">{{ $mtg['title'] }}</h4>
                            <div class="text-[11px] text-slate-500 space-y-0.5">
                                <p class="tabular-nums">📅 {{ $mtg['date'] }} · {{ $mtg['time'] }}</p>
                                <p>📍 {{ $mtg['room'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
                <h3 class="text-sm font-bold text-slate-900 mb-2">Ekspor Berita Acara</h3>
                <p class="text-xs text-slate-500 mb-4">
                    Cetak rekapan daftar hadir anggota dan notulensi ke dalam format resmi PDF/Excel untuk arsip LPJ.
                </p>
                <button type="button" class="w-full py-2.5 rounded-lg bg-polines-navy hover:bg-polines-navyDark text-white text-xs font-bold transition-colors">
                    Unduh Berita Acara (PDF)
                </button>
            </div>
        </div>

    </div>
</div>
@endsection
