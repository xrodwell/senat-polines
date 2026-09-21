@extends('layouts.public')

@section('title', 'Sekretariat Senat — Konsol Kendali Sidang & Presensi')

@section('content')
{{-- SUB-HEADER KONSOL OPERATOR --}}
<div class="bg-slate-900 text-white border-b border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="h-10 w-10 rounded-lg bg-polines-navy border border-slate-700 flex items-center justify-center font-mono font-bold text-polines-orange">
                OPS
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-mono font-semibold uppercase tracking-wider text-slate-400">Konsol Kendali Sekretariat</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono bg-emerald-950 text-emerald-300 border border-emerald-800">
                        ● SISTEM AKTIF
                    </span>
                </div>
                <h1 class="text-lg font-bold text-white tracking-tight">Manajemen Sesi Sidang &amp; Monitor Kuorum</h1>
            </div>
        </div>

        <div class="flex items-center gap-3 text-xs font-mono">
            <div class="bg-slate-950 border border-slate-800 px-3 py-1.5 rounded text-slate-300">
                HOST: <span class="text-white">PROYEKSI-R201</span>
            </div>
            <div class="bg-slate-950 border border-slate-800 px-3 py-1.5 rounded text-slate-300">
                WAKTU: <span class="text-amber-400 tabular-nums">09:42:15 WIB</span>
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
     x-data="{
         sessionOpen: true,
         sessionCode: 'PLN-892',
         presentCount: 28,
         totalInvited: 34,
         qrCountdown: 28,
         init() {
             setInterval(() => {
                 if (this.qrCountdown > 1) {
                     this.qrCountdown--;
                 } else {
                     this.qrCountdown = 30;
                 }
             }, 1000);
         },
         toggleSession() {
             this.sessionOpen = !this.sessionOpen;
         }
     }">
    
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        {{-- KOLOM KIRI: KENDALI PROYEKTOR & KUORUM SIDANG --}}
        <div class="lg:col-span-8 space-y-6">
            
            {{-- PANEL PROYEKTOR RUANG SIDANG (HIGH-END INSTITUTIONAL DISPLAY) --}}
            <div class="rounded-xl border border-slate-300 bg-white shadow-xs overflow-hidden">
                {{-- Bar Kontrol Operator --}}
                <div class="bg-slate-100 border-b border-slate-200 px-5 py-3 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Kontrol Presensi Digital</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <button @click="toggleSession" type="button" 
                                class="inline-flex items-center gap-2 rounded px-3.5 py-1.5 text-xs font-bold transition-all"
                                :class="sessionOpen ? 'bg-rose-700 hover:bg-rose-800 text-white' : 'bg-emerald-700 hover:bg-emerald-800 text-white'">
                            <span x-text="sessionOpen ? 'Bekukan Presensi (Kunci Kuorum)' : 'Buka Kembali Sesi Presensi'"></span>
                        </button>
                    </div>
                </div>

                {{-- Tampilan Monitor Proyektor --}}
                <div class="p-6 sm:p-8" x-show="sessionOpen">
                    <div class="border-b border-slate-200 pb-5 mb-6">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-mono font-bold text-polines-navy uppercase">
                                Sidang Pleno Terbuka &bull; Agenda Evaluasi Akreditasi
                            </span>
                            <span class="text-xs text-slate-500 font-mono">ID: PLN-PLENO-202609</span>
                        </div>
                        <h2 class="text-xl font-bold text-slate-950 mt-1 leading-snug">
                            Sidang Pleno Evaluasi Akreditasi Program Studi Jurusan Teknik Sipil &amp; Elektro
                        </h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-center bg-slate-50 border border-slate-200/90 rounded-xl p-6">
                        {{-- QR Matrix Autentik --}}
                        <div class="md:col-span-5 flex flex-col items-center">
                            <div class="bg-white p-4 rounded-lg border border-slate-300 shadow-xs">
                                <svg class="w-44 h-44 text-slate-950" viewBox="0 0 100 100" fill="currentColor">
                                    {{-- Position Markers Autentik --}}
                                    <path d="M4 4h26v26H4V4zm4 4v18h18V8H8zm4 4h10v10H12V12zM70 4h26v26H70V4zm4 4v18h18V8H74zm4 4h10v10H78V12zM4 70h26v26H4V70zm4 4v18h18V74H8zm4 4h10v10H12V78z" />
                                    {{-- Data Matrix Nodes --}}
                                    <rect x="36" y="6" width="6" height="6" />
                                    <rect x="46" y="10" width="6" height="6" />
                                    <rect x="56" y="6" width="6" height="6" />
                                    <rect x="36" y="20" width="6" height="6" />
                                    <rect x="50" y="22" width="6" height="6" />
                                    <rect x="40" y="34" width="6" height="6" />
                                    <rect x="52" y="36" width="6" height="6" />
                                    <rect x="10" y="38" width="6" height="6" />
                                    <rect x="22" y="44" width="6" height="6" />
                                    <rect x="6" y="52" width="6" height="6" />
                                    <rect x="34" y="50" width="6" height="6" />
                                    <rect x="46" y="48" width="6" height="6" />
                                    <rect x="60" y="46" width="6" height="6" />
                                    <rect x="74" y="38" width="6" height="6" />
                                    <rect x="88" y="44" width="6" height="6" />
                                    <rect x="70" y="54" width="6" height="6" />
                                    <rect x="84" y="58" width="6" height="6" />
                                    <rect x="38" y="68" width="6" height="6" />
                                    <rect x="52" y="66" width="6" height="6" />
                                    <rect x="44" y="78" width="6" height="6" />
                                    <rect x="58" y="82" width="6" height="6" />
                                    <rect x="38" y="90" width="6" height="6" />
                                    <rect x="70" y="74" width="6" height="6" />
                                    <rect x="84" y="72" width="6" height="6" />
                                    <rect x="76" y="86" width="6" height="6" />
                                    <rect x="90" y="90" width="6" height="6" />
                                </svg>
                            </div>
                            <div class="mt-3 flex items-center gap-2 text-slate-600 font-mono text-[11px]">
                                <span>Segarkan token QR:</span>
                                <span class="font-bold text-slate-900 bg-white border border-slate-200 px-2 py-0.5 rounded tabular-nums" x-text="qrCountdown + 's'"></span>
                            </div>
                        </div>

                        {{-- Instruksi & Kode Sesi --}}
                        <div class="md:col-span-7 space-y-4">
                            <div>
                                <span class="text-[11px] font-mono uppercase tracking-wider text-slate-500">Kode Sesi Alternatif:</span>
                                <div class="text-4xl font-mono font-extrabold text-slate-900 tracking-widest tabular-nums mt-1" x-text="sessionCode"></div>
                            </div>

                            <p class="text-xs text-slate-600 leading-relaxed">
                                Anggota Senat dapat memindai QR Code di layar atau membuka alamat 
                                <strong class="font-mono text-polines-navy">/portal/attendance</strong> pada perangkat masing-masing untuk mencatat kehadiran.
                            </p>

                            <div class="border-t border-slate-200 pt-3 flex items-center justify-between text-[11px] font-mono text-slate-500">
                                <span>Algoritma: SHA-256 Time-Token</span>
                                <span class="text-emerald-700 font-bold">● Presensi Terenkripsi</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Status Sesi Dibekukan --}}
                <div class="p-10 text-center bg-slate-50" x-show="!sessionOpen" x-cloak>
                    <div class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-rose-100 text-rose-700 mb-3 font-bold">
                        !
                    </div>
                    <h3 class="text-base font-bold text-slate-900">Sesi Presensi Telah Dikunci</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                        Pencatatan presensi digital dinonaktifkan sementara. Data kuorum saat ini dijadikan dasar penandatanganan berita acara sah oleh Pimpinan Senat.
                    </p>
                </div>
            </div>

            {{-- MONITOR KUORUM INSTITUSIONAL --}}
            <div class="rounded-xl border border-slate-300 bg-white p-6 shadow-xs">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                    <div>
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900">Monitor Kuorum &amp; Keabsahan Sidang</h3>
                        <p class="text-xs text-slate-500">Syarat sah pengambilan keputusan: Minimal 2/3 anggota hadir (23 dari 34 Anggota).</p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded bg-emerald-50 text-emerald-800 border border-emerald-300 text-xs font-bold">
                        <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                        KUORUM TERPENUHI (82.3%)
                    </span>
                </div>

                {{-- Indikator Batang Kuorum --}}
                <div class="relative w-full bg-slate-200 h-4 rounded overflow-hidden mb-4">
                    {{-- Ambang Batas 2/3 (66.6%) --}}
                    <div class="absolute top-0 bottom-0 left-[66.6%] w-0.5 bg-rose-500 z-10" title="Ambang Batas Kuorum (66.7%)"></div>
                    <div class="bg-emerald-600 h-full rounded transition-all duration-500" style="width: 82.3%;"></div>
                </div>
                <div class="flex justify-between text-[11px] font-mono text-slate-500 mb-6">
                    <span>0 Anggota</span>
                    <span class="text-rose-600 font-bold">Ambang Sah: 23 Anggota (66.7%)</span>
                    <span>Total: 34 Anggota</span>
                </div>

                {{-- Metrik Detail Kehadiran --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="border border-slate-200 rounded-lg p-3 bg-slate-50">
                        <div class="text-[11px] font-semibold text-slate-500 uppercase">Hadir di Ruang Sidang</div>
                        <div class="text-2xl font-bold font-mono text-emerald-700 mt-1 tabular-nums" x-text="presentCount"></div>
                        <div class="text-[11px] text-slate-500 mt-0.5">Terverifikasi QR / Kode</div>
                    </div>
                    <div class="border border-slate-200 rounded-lg p-3 bg-slate-50">
                        <div class="text-[11px] font-semibold text-slate-500 uppercase">Izin / Penugasan Luar</div>
                        <div class="text-2xl font-bold font-mono text-amber-700 mt-1 tabular-nums">3</div>
                        <div class="text-[11px] text-slate-500 mt-0.5">Surat Dispensasi Sah</div>
                    </div>
                    <div class="border border-slate-200 rounded-lg p-3 bg-slate-50">
                        <div class="text-[11px] font-semibold text-slate-500 uppercase">Belum Hadir</div>
                        <div class="text-2xl font-bold font-mono text-rose-700 mt-1 tabular-nums">3</div>
                        <div class="text-[11px] text-slate-500 mt-0.5">Menunggu Konfirmasi</div>
                    </div>
                </div>
            </div>

        </div>

        {{-- KOLOM KANAN: KALENDER MASTER & DOKUMEN BERITA ACARA --}}
        <div class="lg:col-span-4 space-y-6">
            {{-- Master Jadwal --}}
            <div class="rounded-xl border border-slate-300 bg-white p-5 shadow-xs">
                <div class="flex items-center justify-between border-b border-slate-200 pb-3 mb-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900">Jadwal Kalender Sidang</h3>
                    <span class="text-xs font-mono text-slate-500">{{ count($meetings) }} Agenda</span>
                </div>

                <div class="space-y-3">
                    @foreach($meetings as $mtg)
                        <div class="rounded-lg border p-3.5 {{ $mtg['status'] === 'Sedang Berlangsung' ? 'border-amber-300 bg-amber-50/50' : 'border-slate-200 bg-slate-50/70' }}">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded font-mono uppercase {{ $mtg['status'] === 'Sedang Berlangsung' ? 'bg-amber-200 text-amber-900' : 'bg-slate-200 text-slate-700' }}">
                                    {{ $mtg['status'] }}
                                </span>
                                <span class="font-mono text-xs font-bold text-slate-700 tabular-nums">{{ $mtg['session_code'] }}</span>
                            </div>
                            <h4 class="text-xs font-bold text-slate-900 leading-snug mt-1">{{ $mtg['title'] }}</h4>
                            <div class="text-[11px] text-slate-500 mt-2 space-y-0.5 font-mono">
                                <div>📅 {{ $mtg['date'] }} &bull; {{ $mtg['time'] }}</div>
                                <div>📍 {{ $mtg['room'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Ekspor Berita Acara Resmi --}}
            <div class="rounded-xl border border-slate-300 bg-white p-5 shadow-xs">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-1">Berita Acara &amp; Daftar Hadir</h3>
                <p class="text-xs text-slate-500 leading-relaxed mb-4">
                    Generate dokumen rekapitulasi kehadiran resmi bertandatangan digital untuk lampiran risalah sidang.
                </p>

                <div class="space-y-2">
                    <button type="button" class="w-full py-2 px-3 rounded border border-slate-300 hover:bg-slate-50 text-slate-800 text-xs font-bold flex items-center justify-between transition-colors">
                        <span>Cetak Berita Acara (PDF)</span>
                        <span class="text-[10px] font-mono text-slate-500">RESMI</span>
                    </button>
                    <button type="button" class="w-full py-2 px-3 rounded border border-slate-300 hover:bg-slate-50 text-slate-800 text-xs font-bold flex items-center justify-between transition-colors">
                        <span>Ekspor Presensi (Excel/CSV)</span>
                        <span class="text-[10px] font-mono text-slate-500">RAW DATA</span>
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
