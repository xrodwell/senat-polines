@extends('layouts.public')

@section('title', 'Portal Anggota — Presensi, Notulensi & E-Voting Senat Polines')

@section('content')
{{-- ================= IDENTITAS JABATAN ANGGOTA ================= --}}
<div class="bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex flex-wrap items-start justify-between gap-6">
            <div class="flex items-start gap-4 min-w-0">
                <div class="h-11 w-11 shrink-0 rounded border border-slate-200 bg-slate-50 flex items-center justify-center text-polines-navy">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <nav class="text-[11px] text-slate-500" aria-label="Breadcrumb">
                        <span class="text-slate-700 font-medium">Portal Anggota</span>
                        <span class="mx-1 text-slate-300">/</span>
                        <span>Presensi, Notulensi &amp; E-Voting</span>
                    </nav>
                    <h1 class="text-xl font-bold text-polines-navy tracking-tight mt-1">Tri Yudantoro, S.Kom., M.Kom.</h1>
                    <p class="text-xs text-slate-500 mt-1">
                        Ketua Komisi I — Pendidikan dan Pengajaran &bull; <span class="font-mono tabular-nums">Masa Bakti 2026–2029</span>
                    </p>
                </div>
            </div>

            <div class="shrink-0 text-right border-l-2 border-polines-orange pl-4">
                <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Rekap Kehadiran Sidang 2026</div>
                <div class="text-sm font-bold text-slate-900 tabular-nums mt-0.5">16 dari 17 sidang &bull; 94%</div>
                <div class="text-[11px] text-slate-500 mt-1">Masa Bakti 2025 &ndash; 2028</div>
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
     x-data="memberAttendance">
     
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        {{-- Kolom Kiri: E-Voting Sidang, Presensi Sesi Berlangsung & Notulensi --}}
        <div class="lg:col-span-8 space-y-8">

            {{-- ================= MODUL 0: PEMILIH RAPAT (GERBANG AKSES) ================= --}}
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden">
                <div class="bg-slate-50 border-b border-slate-200 px-6 py-4 flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800">Langkah 1 &mdash; Pilih Rapat</h2>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-mono font-semibold uppercase tracking-wide px-2 py-0.5 rounded border"
                              :class="apiOnline ? 'bg-blue-50 text-polines-navy border-blue-300' : 'bg-slate-100 text-slate-500 border-slate-300'"
                              x-text="apiOnline ? 'TERHUBUNG SERVER' : 'MODE LOKAL'"></span>
                        <span class="text-[11px] font-mono font-semibold uppercase tracking-wide px-2 py-0.5 rounded border"
                              :class="selectedMeetingId ? 'bg-emerald-50 text-emerald-700 border-emerald-300' : 'bg-amber-50 text-amber-700 border-amber-300'"
                              x-text="selectedMeetingId ? 'RAPAT TERPILIH' : 'BELUM DIPILIH'"></span>
                    </div>
                </div>
                <div class="p-5 sm:p-6">
                    <p class="text-xs text-slate-500 leading-relaxed mb-4">
                        Catatan risalah, unggahan dokumen, dan hak suara e-voting terikat pada satu rapat.
                        Pilih rapat di bawah ini untuk membuka modul tersebut.
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <template x-for="m in meetings" :key="m.id">
                            <button type="button" @click="selectMeeting(m)"
                                    class="text-left rounded-xl border-2 p-4 transition-all"
                                    :class="selectedMeetingId === m.id ? 'border-polines-navy bg-blue-50/60 shadow-sm' : 'border-slate-200 bg-white hover:border-slate-300'">
                                <div class="flex items-center justify-between gap-2 mb-1.5">
                                    <span class="text-[10px] font-bold uppercase tracking-wide px-2 py-0.5 rounded bg-slate-100 text-slate-700" x-text="m.type"></span>
                                    <span class="text-[10px] font-semibold flex items-center gap-1"
                                          :class="m.status === 'Sedang Berlangsung' ? 'text-emerald-700' : 'text-slate-500'">
                                        <span class="w-1.5 h-1.5 rounded-full"
                                              :class="m.status === 'Sedang Berlangsung' ? 'bg-emerald-500' : 'bg-slate-400'"></span>
                                        <span x-text="m.status"></span>
                                    </span>
                                </div>
                                <h4 class="text-xs font-bold text-slate-900 leading-snug" x-text="m.title"></h4>
                                <p class="text-[11px] text-slate-500 mt-2 tabular-nums">
                                    <span x-text="m.date"></span> &bull; <span x-text="m.time"></span>
                                </p>
                                <p class="text-[11px] text-slate-500 mt-0.5" x-text="m.room"></p>
                                <p class="mt-2 text-[11px] font-bold"
                                   :class="selectedMeetingId === m.id ? 'text-polines-navy' : 'text-slate-400'"
                                   x-text="selectedMeetingId === m.id ? '✓ Rapat ini dipilih' : 'Pilih rapat ini →'"></p>
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            {{-- ================= MODUL 1: E-VOTING SIDANG PLENO (INTERAKTIF) ================= --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="bg-slate-50 border-b border-slate-200 px-6 py-4 flex flex-wrap items-center justify-between gap-4">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800">E-Voting Musyawarah &amp; Putusan Sidang</h2>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-slate-500">Status Bilik Suara:</span>
                        <span class="font-semibold px-2 py-0.5 rounded text-[11px] border"
                              :class="!selectedMeetingId ? 'bg-amber-50 text-amber-700 border-amber-300' : (activeVote && activeVote.isOpen ? 'bg-emerald-50 text-emerald-700 border-emerald-300' : 'bg-slate-100 text-emerald-800 border-slate-300')">
                            <span x-text="!selectedMeetingId ? 'TERKUNCI — PILIH RAPAT' : (activeVote && activeVote.isOpen ? 'DIBUKA UNTUK ANGGOTA' : 'DITUTUP')"></span>
                        </span>
                    </div>
                </div>

                {{-- Bilik suara terkunci sampai anggota memilih rapat --}}
                <div x-show="!selectedMeetingId" class="p-6 sm:p-8">
                    <div class="rounded-xl border border-dashed border-amber-300 bg-amber-50/60 p-6 text-center">
                        <div class="w-10 h-10 rounded-xl bg-white border border-amber-300 text-amber-700 flex items-center justify-center mx-auto mb-3 font-bold">!</div>
                        <p class="text-sm font-bold text-slate-900">Hak suara belum dibuka</p>
                        <p class="text-xs text-slate-600 mt-1 max-w-md mx-auto leading-relaxed">
                            Pilih rapat terlebih dahulu pada modul <strong>Langkah 1 &mdash; Pilih Rapat</strong> di atas.
                            Setelah rapat dipilih, bilik suara untuk rapat tersebut akan terbuka.
                        </p>
                    </div>
                </div>

                <div class="p-6 sm:p-8" x-show="selectedMeetingId && activeVote" x-cloak>
                    <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-5 sm:p-6 mb-6">
                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200 pb-3 mb-4">
                            <span class="text-[11px] font-mono font-semibold text-slate-500 uppercase" x-text="'Pokok Putusan: ' + activeVote.id"></span>
                            <span class="text-[11px] text-slate-500">Hak Suara: 1 Suara / Anggota</span>
                        </div>
                        <h3 class="text-base sm:text-lg font-bold text-slate-900 leading-snug" x-text="activeVote.title"></h3>
                        <p class="text-xs text-slate-600 mt-2 leading-relaxed" x-text="activeVote.description"></p>
                    </div>

                    {{-- Form Pilihan Hak Suara Anggota --}}
                    <div x-show="activeVote.isOpen && !voteSubmitted" class="space-y-3">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                            Tentukan Suara Anda (Sekali Pilih &bull; Terenkripsi Langsung ke Rekapitulasi Sidang):
                        </p>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <button @click="castVote('setuju')" type="button" class="p-4 rounded-xl border-2 border-emerald-500/50 hover:border-emerald-600 bg-emerald-50/40 hover:bg-emerald-50 text-left transition-all group">
                                <div class="text-base font-bold text-emerald-950 flex items-center justify-between">
                                    <span>Setuju</span>
                                    <span class="text-emerald-600 group-hover:scale-110 transition-transform">✓</span>
                                </div>
                                <p class="text-[11px] text-emerald-800 mt-1">Menerima dan menyetujui pengesahan draf norma.</p>
                            </button>

                            <button @click="castVote('tolak')" type="button" class="p-4 rounded-xl border-2 border-rose-500/50 hover:border-rose-600 bg-rose-50/40 hover:bg-rose-50 text-left transition-all group">
                                <div class="text-base font-bold text-rose-950 flex items-center justify-between">
                                    <span>Tolak</span>
                                    <span class="text-rose-600 group-hover:scale-110 transition-transform">✕</span>
                                </div>
                                <p class="text-[11px] text-rose-800 mt-1">Menolak isi draf untuk dikaji ulang komisi.</p>
                            </button>

                            <button @click="castVote('abstain')" type="button" class="p-4 rounded-xl border-2 border-slate-300 hover:border-slate-400 bg-slate-50 hover:bg-slate-100 text-left transition-all group">
                                <div class="text-base font-bold text-slate-900 flex items-center justify-between">
                                    <span>Abstain</span>
                                    <span class="text-slate-500 group-hover:scale-110 transition-transform">—</span>
                                </div>
                                <p class="text-[11px] text-slate-600 mt-1">Menyerahkan putusan akhir kepada mayoritas.</p>
                            </button>
                        </div>
                    </div>

                    {{-- Status Suara Telah Diserahkan --}}
                    <div x-show="voteSubmitted" x-cloak class="rounded-xl border border-emerald-300 bg-slate-50 p-5 flex flex-wrap items-start justify-between gap-4">
                        <div class="flex items-start gap-3 min-w-0">
                            <div class="w-9 h-9 rounded border border-emerald-300 bg-white text-emerald-700 flex items-center justify-center font-bold text-sm shrink-0">
                                ✓
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-sm font-bold text-slate-900">Suara Anda Berhasil Dicatat</h4>
                                <p class="text-xs text-slate-600 mt-0.5 leading-relaxed">
                                    Pilihan tercatat: <strong class="font-mono uppercase text-slate-900" x-text="myVote"></strong>. Rekapitulasi suara dinyatakan sah dan terakumulasi pada monitor kuorum pimpinan sidang.
                                </p>
                            </div>
                        </div>
                        <span class="text-[11px] font-mono text-slate-500 border border-slate-300 bg-white px-2.5 py-1 rounded shrink-0">
                            TERVERIFIKASI
                        </span>
                    </div>

                    {{-- Bilik Suara Ditutup Oleh Admin --}}
                    <div x-show="!activeVote.isOpen && !voteSubmitted" x-cloak class="rounded-xl border border-slate-200 bg-slate-50 p-6 text-center text-slate-600">
                        <p class="text-xs">Sesi pemungutan suara untuk agenda ini telah ditutup oleh Sekretariat Sidang.</p>
                    </div>
                </div>
            </div>
            
            {{-- ================= MODUL 2: PRESENSI DIGITAL ================= --}}
            @if ($activeSession)
                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden"
                     x-data="{
                         activeTab: 'qr',
                         isCheckedIn: false,
                         inputCode: '',
                         checkinTime: '',
                         scannerActive: false,
                         scannedSuccess: false,
                         stream: null,
                         
                         startCamera() {
                             this.scannerActive = true;
                             this.$nextTick(() => {
                                 const video = document.getElementById('camera-feed');
                                 if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                                     navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
                                         .then(s => {
                                             this.stream = s;
                                             if (video) video.srcObject = s;
                                         })
                                         .catch(err => {
                                             console.warn('Camera access denied/unavailable, fallback simulation ready');
                                         });
                                 }
                             });
                         },
                         stopCamera() {
                             if (this.stream) {
                                 this.stream.getTracks().forEach(t => t.stop());
                                 this.stream = null;
                             }
                             this.scannerActive = false;
                         },
                         simulateScan() {
                             this.stopCamera();
                             const now = new Date();
                             this.checkinTime = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0') + ' WIB';
                             this.isCheckedIn = true;
                         },
                         processCodeCheckin() {
                             if (!this.inputCode) {
                                 alert('Silakan masukkan 6 digit kode sesi sidang.');
                                 return;
                             }
                             const now = new Date();
                             this.checkinTime = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0') + ' WIB';
                             this.isCheckedIn = true;
                         }
                     }">
                    
                    {{-- Header Kartu Presensi --}}
                    <div class="bg-slate-50 border-b border-slate-200 px-6 py-4 flex flex-wrap items-center justify-between gap-4">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800">Sesi Presensi Sidang Aktif</h2>
                        <div class="flex items-center gap-2 text-xs text-slate-500">
                            <span>Sisa Waktu Kuorum:</span>
                            <span class="border border-slate-300 bg-white text-slate-700 font-semibold px-2 py-0.5 rounded tabular-nums">24:18</span>
                        </div>
                    </div>

                    <div class="p-6 sm:p-8">
                        {{-- Ringkasan Agenda Sidang --}}
                        <div class="border-b border-slate-100 pb-6 mb-6">
                            <span class="text-[11px] font-mono font-bold text-polines-navy bg-blue-50 px-2.5 py-1 rounded-md uppercase">
                                {{ $activeSession['type'] }}
                            </span>
                            <h3 class="text-xl font-extrabold text-slate-900 mt-2 leading-snug">
                                {{ $activeSession['title'] }}
                            </h3>
                            <div class="flex flex-wrap items-center gap-y-2 gap-x-6 text-xs text-slate-500 mt-3 font-medium">
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    {{ $activeSession['room'] }}
                                </span>
                                <span class="flex items-center gap-1.5 tabular-nums">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ $activeSession['time'] }}
                                </span>
                                <span class="flex items-center gap-1.5 font-mono text-slate-700">
                                    Kode Sesi: <strong class="text-polines-navy">{{ $activeSession['session_code'] }}</strong>
                                </span>
                            </div>
                        </div>

                        {{-- Panel Belum Presensi --}}
                        <div x-show="!isCheckedIn">
                            {{-- Tab Metode Presensi --}}
                            <div class="flex border-b border-slate-200 mb-6">
                                <button @click="activeTab = 'qr'; stopCamera()" type="button" class="pb-3 px-4 text-xs font-bold uppercase tracking-wider transition-all border-b-2"
                                        :class="activeTab === 'qr' ? 'border-polines-navy text-polines-navy' : 'border-transparent text-slate-400 hover:text-slate-700'">
                                    Scan Kamera QR Code
                                </button>
                                <button @click="activeTab = 'code'; stopCamera()" type="button" class="pb-3 px-4 text-xs font-bold uppercase tracking-wider transition-all border-b-2"
                                        :class="activeTab === 'code' ? 'border-polines-navy text-polines-navy' : 'border-transparent text-slate-400 hover:text-slate-700'">
                                    Input Kode 6-Digit
                                </button>
                            </div>

                            {{-- TAB 1: SCAN KAMERA QR CODE --}}
                            <div x-show="activeTab === 'qr'" class="space-y-4">
                                <div x-show="!scannerActive" class="rounded-xl border border-slate-200/90 bg-slate-50 p-8 text-center">
                                    <div class="w-14 h-14 rounded-2xl bg-white border border-slate-200 text-polines-navy flex items-center justify-center mx-auto mb-3 shadow-xs">
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </div>
                                    <h4 class="text-sm font-bold text-slate-900">Pindai QR Code di Layar Proyektor Sidang</h4>
                                    <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1 mb-5">
                                        Arahkan kamera perangkat Anda langsung ke QR Code dinamis yang diproyeksikan oleh sekretariat di depan ruang sidang.
                                    </p>
                                    <button @click="startCamera()" type="button" class="inline-flex items-center gap-2 rounded-lg bg-polines-navy hover:bg-polines-navyDark text-white px-6 py-2.5 text-xs font-bold transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                        </svg>
                                        Buka Kamera &amp; Pindai
                                    </button>
                                </div>

                                {{-- Jendela Bidik Kamera Interaktif --}}
                                <div x-show="scannerActive" x-cloak class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-center relative overflow-hidden">
                                    <div class="relative w-full max-w-xs mx-auto aspect-square bg-slate-900 rounded-lg overflow-hidden flex items-center justify-center border-2 border-dashed border-slate-300">
                                        <video id="camera-feed" autoplay playsinline class="w-full h-full object-cover"></video>
                                        <div class="absolute inset-0 pointer-events-none flex flex-col items-center justify-between p-4">
                                            <div class="w-full flex justify-between">
                                                <div class="w-6 h-6 border-t-2 border-l-2 border-white/80"></div>
                                                <div class="w-6 h-6 border-t-2 border-r-2 border-white/80"></div>
                                            </div>
                                            <div class="text-[11px] font-medium bg-white/85 border border-slate-300 px-3 py-1 rounded text-slate-700">
                                                Arahkan ke QR Code Proyektor
                                            </div>
                                            <div class="w-full flex justify-between">
                                                <div class="w-6 h-6 border-b-2 border-l-2 border-white/80"></div>
                                                <div class="w-6 h-6 border-b-2 border-r-2 border-white/80"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4 flex flex-wrap items-center justify-center gap-3">
                                        <button @click="simulateScan()" type="button" class="rounded-lg border border-slate-300 bg-white hover:bg-slate-100 text-slate-700 px-4 py-2 text-xs font-bold transition-colors">
                                            Simulasikan QR Terdeteksi
                                        </button>
                                        <button @click="stopCamera()" type="button" class="rounded-lg bg-polines-navy hover:bg-polines-navyDark text-white px-4 py-2 text-xs font-semibold transition-colors">
                                            Tutup Kamera
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- TAB 2: INPUT KODE 6-DIGIT --}}
                            <div x-show="activeTab === 'code'" x-cloak class="rounded-xl border border-slate-200/90 bg-slate-50 p-6 sm:p-8">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                                    Kode Presensi Rapat (Tertera di Layar Proyektor)
                                </label>
                                <div class="flex flex-col sm:flex-row gap-3">
                                    <input type="text" x-model="inputCode" placeholder="Contoh: {{ $activeSession['session_code'] }}" class="flex-1 rounded-lg border border-slate-300 px-4 py-3 text-base font-mono uppercase tracking-widest font-bold focus:border-polines-blue focus:ring-1 focus:ring-polines-blue outline-none transition-all tabular-nums text-center sm:text-left bg-white">
                                    <button @click="processCodeCheckin()" type="button" class="rounded-lg bg-polines-navy hover:bg-polines-navyDark text-white px-8 py-3 text-sm font-bold transition-colors">
                                        Verifikasi Hadir
                                    </button>
                                </div>
                                <p class="text-[11px] text-slate-500 mt-2">
                                    Gunakan opsi ini bila perangkat Anda tidak dilengkapi kamera atau koneksi terbatas.
                                </p>
                            </div>
                        </div>

                        {{-- Panel Status Kehadiran Sah --}}
                        <div x-show="isCheckedIn" x-cloak class="rounded-xl border border-emerald-200 bg-emerald-50/70 p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded border border-emerald-300 bg-white text-emerald-700 flex items-center justify-center text-lg font-bold shrink-0 shadow-xs">
                                    ✓
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-base font-extrabold text-emerald-950">Presensi Berhasil Diverifikasi</h4>
                                        <span class="text-[10px] font-mono font-semibold bg-white text-slate-700 px-2 py-0.5 rounded border border-slate-300">STATUS: SAH</span>
                                    </div>
                                    <p class="text-xs text-emerald-800 mt-1">
                                        Tercatat atas nama <strong>Tri Yudantoro, S.Kom., M.Kom.</strong> pada pukul <span class="font-bold tabular-nums" x-text="checkinTime"></span>.
                                    </p>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="text-[11px] text-emerald-700 font-mono">ID Log: POLINES-ATT-{{ rand(1000, 9999) }}</span>
                            </div>
                        </div>

                    </div>
                </div>
            @endif

            {{-- ================= MODUL 3: CATATAN & NOTULENSI (TEKS EDITOR + LAMPIRAN + EXPORT) ================= --}}
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden">
                <div class="bg-slate-50 border-b border-slate-200 px-6 py-4 flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800">Catatan &amp; Notulensi Rapat</h2>
                    <span class="text-[11px] text-slate-500 truncate max-w-full"
                          x-text="selectedMeeting ? selectedMeeting.type + ' — ' + selectedMeeting.title : 'Belum ada rapat dipilih'"></span>
                </div>

                {{-- Terkunci sampai rapat dipilih --}}
                <div x-show="!selectedMeetingId" class="p-6 sm:p-8">
                    <div class="rounded-xl border border-dashed border-amber-300 bg-amber-50/60 p-6 text-center">
                        <div class="w-10 h-10 rounded-xl bg-white border border-amber-300 text-amber-700 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75M6.75 10.5h10.5a1.5 1.5 0 011.5 1.5v7.5a1.5 1.5 0 01-1.5 1.5H6.75a1.5 1.5 0 01-1.5-1.5V12a1.5 1.5 0 011.5-1.5z" />
                            </svg>
                        </div>
                        <p class="text-sm font-bold text-slate-900">Catatan risalah belum dapat diisi</p>
                        <p class="text-xs text-slate-600 mt-1 max-w-md mx-auto leading-relaxed">
                            Pilih rapat terlebih dahulu pada modul <strong>Langkah 1 &mdash; Pilih Rapat</strong> di atas
                            agar catatan dan lampiran tersimpan pada rapat yang benar.
                        </p>
                    </div>
                </div>

                <div x-show="selectedMeetingId" x-cloak class="p-6 sm:p-8">
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900">Catatan Risalah Musyawarah</h3>
                            <p class="text-[11px] text-slate-500 mt-0.5">
                                Poin telaah, argumentasi pasal, atau pertimbangan norma yang Anda sampaikan selama sidang.
                            </p>
                        </div>
                        <span class="text-[10px] font-mono text-slate-400 uppercase"
                              x-text="selectedMeetingId ? 'ID RAPAT: ' + selectedMeetingId : ''"></span>
                    </div>

                    {{-- Toolbar editor teks --}}
                    <div class="flex flex-wrap items-center gap-1 rounded-t-xl border border-slate-300 bg-slate-100/70 px-2 py-1.5">
                        <template x-for="tool in editorTools" :key="tool.cmd + (tool.value || '')">
                            <button type="button"
                                    @mousedown.prevent="format(tool.cmd, tool.value)"
                                    :title="tool.title"
                                    class="h-7 min-w-7 px-2 rounded text-[11px] font-bold text-slate-700 border border-transparent hover:bg-white hover:text-polines-navy hover:border-slate-300 transition-colors"
                                    x-text="tool.label"></button>
                        </template>
                        <span class="ml-auto text-[10px] text-slate-400 font-mono pr-1" x-text="wordCount + ' kata'"></span>
                    </div>

                    <div x-ref="editor"
                         contenteditable="true"
                         role="textbox"
                         aria-multiline="true"
                         aria-label="Editor catatan risalah"
                         data-placeholder="Tulis poin telaah, argumentasi pasal, atau pertimbangan norma di sini..."
                         @input="syncNote()"
                         @paste="onPaste($event)"
                         class="min-h-[190px] rounded-b-xl border border-slate-300 bg-white px-4 py-3 text-sm leading-relaxed text-slate-800 outline-none transition-all focus:border-polines-blue focus:ring-1 focus:ring-polines-blue [&_ul]:list-disc [&_ul]:pl-6 [&_ol]:list-decimal [&_ol]:pl-6 [&_h3]:mt-2 [&_h3]:text-base [&_h3]:font-bold [&_blockquote]:border-l-2 [&_blockquote]:border-slate-300 [&_blockquote]:pl-3 [&_blockquote]:italic [&_blockquote]:text-slate-600 [&:empty]:before:content-[attr(data-placeholder)] [&:empty]:before:text-slate-400"></div>

                    {{-- Lampiran notulensi (unggah berkas) --}}
                    <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50/60 p-4">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700">Lampiran Notulensi</h4>
                                <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                                    Unggah draf risalah, daftar hadir, atau bukti dokumen pendukung (PDF, DOCX, XLSX, gambar &mdash; maks. 10 MB/berkas).
                                </p>
                            </div>
                            <label class="shrink-0 cursor-pointer inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-100 px-3.5 py-2 text-xs font-bold text-slate-700 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                Unggah Berkas
                                <input type="file" class="hidden" multiple
                                       accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.jpg,.jpeg,.png"
                                       @change="addAttachments($event)">
                            </label>
                        </div>

                        <p class="text-[11px] text-slate-400 mt-3" x-show="attachments.length === 0">
                            Belum ada berkas dilampirkan pada notulensi rapat ini.
                        </p>

                        <ul class="mt-3 space-y-2">
                            <template x-for="(f, i) in attachments" :key="f.id">
                                <li class="flex items-center justify-between gap-3 rounded-lg border border-slate-200 bg-white px-3 py-2">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <span class="shrink-0 w-7 h-7 rounded border border-slate-200 bg-slate-50 text-[9px] font-bold text-slate-600 flex items-center justify-center uppercase"
                                              x-text="extOf(f.name)"></span>
                                        <div class="min-w-0">
                                            <p class="text-xs font-semibold text-slate-800 truncate" x-text="f.name"></p>
                                            <p class="text-[10px] text-slate-500 font-mono" x-text="formatBytes(f.size) + ' · diunggah ' + f.time"></p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1 shrink-0">
                                        <button type="button" @click="downloadAttachment(i)" class="text-[11px] font-bold text-polines-navy hover:underline px-2 py-1">Unduh</button>
                                        <button type="button" @click="removeAttachment(i)" class="text-[11px] font-bold text-rose-600 hover:underline px-2 py-1">Hapus</button>
                                    </div>
                                </li>
                            </template>
                        </ul>
                    </div>

                    {{-- Aksi simpan & export --}}
                    <p class="text-[11px] text-rose-600 mt-4" x-show="noteError" x-cloak x-text="noteError"></p>
                    <div class="flex flex-wrap items-center justify-between gap-3 mt-5 pt-4 border-t border-slate-100">
                        <div class="flex items-center gap-3 text-[11px]">
                            <span class="text-slate-500" x-text="attachments.length + ' lampiran'"></span>
                            <span class="text-emerald-600 font-semibold" x-show="savedNote" x-cloak>&#10003; Tersimpan</span>
                            <span class="text-amber-600 font-semibold" x-show="noteDirty && !savedNote">&#9679; Belum disimpan</span>
                            <span class="text-polines-navy font-semibold" x-show="menyimpan" x-cloak>Mengirim&hellip;</span>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <button type="button" @click="saveNote()"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-polines-navy hover:bg-polines-navyDark text-white px-4 py-2.5 text-xs font-bold transition-colors">
                                Simpan Catatan
                            </button>
                            <button type="button" @click="exportNote('md')"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-100 text-slate-700 px-4 py-2.5 text-xs font-bold transition-colors">
                                Export .md
                            </button>
                            <button type="button" @click="exportNote('txt')"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-100 text-slate-700 px-4 py-2.5 text-xs font-bold transition-colors">
                                Export .txt
                            </button>
                            <button type="button" @click="printNote()"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-100 text-slate-700 px-4 py-2.5 text-xs font-bold transition-colors">
                                Cetak / PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= MODUL 4: BERBAGI DOKUMEN & TAUTAN ANTAR ANGGOTA ================= --}}
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden">
                <div class="bg-slate-50 border-b border-slate-200 px-6 py-4 flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800">Berbagi Dokumen &amp; Tautan</h2>
                    <span class="text-[10px] font-mono text-slate-500 uppercase"
                          x-text="selectedMeetingId ? sharedItems.length + ' item dibagikan' : 'TERKUNCI'"></span>
                </div>

                <div x-show="!selectedMeetingId" class="p-6 sm:p-8">
                    <div class="rounded-xl border border-dashed border-amber-300 bg-amber-50/60 p-6 text-center">
                        <p class="text-sm font-bold text-slate-900">Berbagi dokumen belum tersedia</p>
                        <p class="text-xs text-slate-600 mt-1 max-w-md mx-auto leading-relaxed">
                            Dokumen dan tautan dibagikan ke peserta satu rapat. Pilih rapat terlebih dahulu.
                        </p>
                    </div>
                </div>

                <div x-show="selectedMeetingId" x-cloak class="p-6 sm:p-8">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        {{-- Bagikan berkas --}}
                        <label class="cursor-pointer rounded-xl border-2 border-dashed border-slate-300 bg-slate-50/60 hover:border-polines-blue hover:bg-blue-50/40 px-4 py-5 text-center transition-colors">
                            <svg class="w-6 h-6 mx-auto text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                            </svg>
                            <span class="block text-xs font-bold text-slate-800 mt-1.5">Bagikan Berkas</span>
                            <span class="block text-[11px] text-slate-500 mt-0.5">PDF, DOCX, XLSX, gambar</span>
                            <input type="file" class="hidden" multiple
                                   accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.jpg,.jpeg,.png"
                                   @change="shareFiles($event)">
                        </label>

                        {{-- Bagikan tautan --}}
                        <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 mb-1">Bagikan Tautan</label>
                            <input type="url" x-model="shareUrl" placeholder="https://drive.polines.ac.id/..."
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-800 outline-none focus:border-polines-blue focus:ring-1 focus:ring-polines-blue">
                            <input type="text" x-model="shareTitle" placeholder="Judul tautan (opsional)"
                                   class="w-full mt-2 rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-800 outline-none focus:border-polines-blue focus:ring-1 focus:ring-polines-blue">
                            <button type="button" @click="addLink()"
                                    class="w-full mt-2 rounded-lg bg-polines-navy hover:bg-polines-navyDark text-white px-3 py-2 text-xs font-bold transition-colors">
                                Bagikan Tautan
                            </button>
                            <p class="text-[10px] text-rose-600 mt-1.5" x-show="shareError" x-cloak x-text="shareError"></p>
                        </div>
                    </div>

                    <div class="mt-5">
                        <h4 class="text-[11px] font-bold uppercase tracking-wider text-slate-700 mb-2">Dibagikan dalam Rapat Ini</h4>
                        <p class="text-[11px] text-slate-400" x-show="sharedItems.length === 0">Belum ada dokumen atau tautan dibagikan.</p>

                        <ul class="space-y-2">
                            <template x-for="(item, i) in sharedItems" :key="item.id">
                                <li class="flex items-start justify-between gap-3 rounded-lg border border-slate-200 bg-slate-50/50 px-3 py-2.5">
                                    <div class="flex items-start gap-2.5 min-w-0">
                                        <span class="shrink-0 w-7 h-7 rounded border flex items-center justify-center text-[10px] font-bold uppercase"
                                              :class="item.kind === 'link' ? 'border-blue-200 bg-blue-50 text-polines-navy' : 'border-slate-200 bg-white text-slate-600'"
                                              x-text="item.kind === 'link' ? 'URL' : extOf(item.name)"></span>
                                        <div class="min-w-0">
                                            <p class="text-xs font-semibold text-slate-800 truncate" x-text="item.name"></p>
                                            <p class="text-[10px] text-slate-500 font-mono">
                                                <span x-text="item.by"></span> · <span x-text="item.time"></span>
                                                <template x-if="item.kind === 'file'"><span x-text="' · ' + formatBytes(item.size)"></span></template>
                                            </p>
                                            <p class="text-[10px] text-slate-400 truncate" x-show="item.kind === 'link'" x-text="item.url"></p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1 shrink-0">
                                        <button type="button" x-show="item.kind === 'file'" @click="downloadShared(item)"
                                                class="text-[11px] font-bold text-polines-navy hover:underline px-2 py-1">Unduh</button>
                                        <button type="button" x-show="item.kind === 'link'" @click="openShared(item)"
                                                class="text-[11px] font-bold text-polines-navy hover:underline px-2 py-1">Buka</button>
                                        <button type="button" x-show="item.mine" @click="removeShared(i)"
                                                class="text-[11px] font-bold text-rose-600 hover:underline px-2 py-1">Hapus</button>
                                    </div>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>
            </div>

        </div>

        {{-- Kolom Kanan: Jadwal Sidang Mendatang & Layanan Sekretariat --}}
        <div class="lg:col-span-4 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-900">Agenda Sidang Berikutnya</h3>
                    <span class="text-[10px] font-mono text-slate-500 uppercase">Sekretariat Senat</span>
                </div>
                <div class="space-y-3">
                    @foreach($upcomingMeetings as $m)
                        <div class="rounded-xl border border-slate-200/70 bg-slate-50/50 p-4">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-polines-navy uppercase">
                                    {{ $m['type'] }}
                                </span>
                                <span class="text-[10px] font-mono text-slate-400 tabular-nums">No. {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}/SID/{{ date('Y') }}</span>
                            </div>
                            <h4 class="text-xs font-bold text-slate-900 leading-snug mb-2">{{ $m['title'] }}</h4>
                            <dl class="text-[11px] text-slate-500 space-y-1">
                                <div class="flex gap-2 tabular-nums">
                                    <dt class="shrink-0 w-14 font-semibold text-slate-600">Waktu</dt>
                                    <dd class="min-w-0">{{ $m['date'] }} &bull; {{ $m['time'] }}</dd>
                                </div>
                                <div class="flex gap-2">
                                    <dt class="shrink-0 w-14 font-semibold text-slate-600">Ruang</dt>
                                    <dd class="min-w-0">{{ $m['room'] }}</dd>
                                </div>
                            </dl>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-extrabold text-slate-900">Permohonan Izin Resmi</h3>
                    <span class="text-[10px] font-mono text-slate-500 uppercase">Form. SNT-IZ/02</span>
                </div>
                <p class="text-xs text-slate-500 leading-relaxed mb-4">
                    Bila anggota berhalangan hadir karena tugas institusi, sampaikan surat dispensasi kepada Sekretariat Senat sebelum sidang dibuka pimpinan.
                </p>
                <a href="mailto:senat@polines.ac.id" class="inline-flex items-center justify-center w-full py-2.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-100 text-xs font-bold text-slate-700 transition-colors">
                    Kirim Surat Dispensasi / Izin
                </a>
            </div>
        </div>

    </div>
</div>

<script>
window.__senatMeetings = @json($meetings ?? []);
window.__senatUserId = {{ (int) ($idUser ?? 2) }};
window.__senatBase = '{{ url('/') }}';
document.addEventListener('alpine:init', () => {
    Alpine.data('memberAttendance', () => ({
        /* ---------- Gerbang: rapat terpilih ---------- */
        meetings: [],
        selectedMeetingId: null,
        idUser: 2,
        apiOnline: false,
        menyimpan: false,

        /* ---------- Catatan & notulensi ---------- */
        noteText: '',
        noteDirty: false,
        savedNote: false,
        noteError: '',
        attachments: [],

        /* ---------- Berbagi dokumen & tautan ---------- */
        sharedItems: [],
        shareUrl: '',
        shareTitle: '',
        shareError: '',

        /* ---------- E-voting ---------- */
        activeVote: null,
        myVote: null,
        voteSubmitted: false,
        isNumericTitle: false,

        editorTools: [
            { cmd: 'bold', label: 'B', title: 'Tebal (Ctrl+B)' },
            { cmd: 'italic', label: 'I', title: 'Miring (Ctrl+I)' },
            { cmd: 'underline', label: 'U', title: 'Garis bawah (Ctrl+U)' },
            { cmd: 'insertUnorderedList', label: '• Daftar', title: 'Daftar butir' },
            { cmd: 'insertOrderedList', label: '1. Daftar', title: 'Daftar bernomor' },
            { cmd: 'formatBlock', value: 'h3', label: 'H', title: 'Subjudul' },
            { cmd: 'formatBlock', value: 'blockquote', label: '❝', title: 'Kutipan' },
            { cmd: 'formatBlock', value: 'p', label: '¶', title: 'Paragraf normal' },
            { cmd: 'removeFormat', label: '✕ fmt', title: 'Hapus format' }
        ],

        get selectedMeeting() {
            if (!this.selectedMeetingId) return null;
            return this.meetings.find(m => m.id === this.selectedMeetingId) || null;
        },

        get wordCount() {
            const t = (this.noteText || '').trim();
            return t ? t.split(/\s+/).length : 0;
        },

        init() {
            this.meetings = window.__senatMeetings || [];
            this.idUser = window.__senatUserId || 2;
            this.loadVoteState();
            window.addEventListener('storage', () => this.loadVoteState());

            const saved = parseInt(localStorage.getItem('polines_senat_meeting_selected') || '', 10);
            const found = this.meetings.find(m => m.id === saved);
            if (found) this.selectMeeting(found);
        },

        loadVoteState() {
            const stored = localStorage.getItem('polines_senat_voting');
            if (stored) {
                this.activeVote = JSON.parse(stored);
            } else {
                this.activeVote = {
                    isOpen: true,
                    id: 'VOTE-2026-001',
                    title: 'Persetujuan Pengesahan Perubahan Kurikulum MBKM Vokasi 2026/2027',
                    description: 'Apakah Sidang Pleno menyetujui draf revisi kurikulum vokasi berbasis industri untuk disahkan menjadi Peraturan Senat Akademik?',
                    options: [
                        { id: 'setuju', label: 'Setuju / Mufakat', count: 22 },
                        { id: 'tolak', label: 'Menolak / Keberatan', count: 2 },
                        { id: 'abstain', label: 'Abstain / Pikir-pikir', count: 4 }
                    ]
                };
                localStorage.setItem('polines_senat_voting', JSON.stringify(this.activeVote));
            }
            this.computeNumericTitle();

            const savedMyVote = localStorage.getItem('polines_member_my_vote');
            if (savedMyVote) {
                this.myVote = savedMyVote;
                this.voteSubmitted = true;
            }
        },

        castVote(optionId) {
            if (!this.selectedMeetingId) return;
            if (this.voteSubmitted || !this.activeVote || !this.activeVote.isOpen) return;
            this.myVote = optionId;
            this.voteSubmitted = true;
            localStorage.setItem('polines_member_my_vote', optionId);

            const opt = this.activeVote.options.find(o => o.id === optionId);
            if (opt) opt.count++;
            localStorage.setItem('polines_senat_voting', JSON.stringify(this.activeVote));
        },

        computeNumericTitle() {
            const t = (this.activeVote && this.activeVote.title ? this.activeVote.title : '').trim();
            this.isNumericTitle = t.length > 0 && t.length <= 12 && /^[\d\s.\-\/]+$/.test(t);
        },

        /* ================= PEMILIH RAPAT ================= */
        selectMeeting(m) {
            if (!m) return;
            this.selectedMeetingId = m.id;
            localStorage.setItem('polines_senat_meeting_selected', String(m.id));

            this.noteDirty = false;
            this.savedNote = false;
            this.shareError = '';

            // Tampilkan cache lokal dulu agar UI tidak kosong saat menunggu jaringan
            this.loadNote();
            this.loadAttachments();
            this.loadShared();

            this.muatWorkspace();
        },

        storageKey(kind) {
            return 'polines_senat_' + kind + '_' + this.selectedMeetingId;
        },

        baseUrl() {
            return (window.__senatBase || '') + '/portal/attendance/' + this.selectedMeetingId;
        },

        csrf() {
            const m = document.querySelector('meta[name="csrf-token"]');
            return m ? m.getAttribute('content') : '';
        },

        /** Ambil catatan + lampiran + berbagi dari backend (via proxy Laravel). */
        async muatWorkspace() {
            const idRapat = this.selectedMeetingId;
            if (!idRapat) return;

            try {
                const res = await fetch(this.baseUrl() + '/workspace', {
                    headers: { 'Accept': 'application/json' },
                });

                if (!res.ok) throw new Error('HTTP ' + res.status);
                const data = await res.json();
                if (this.selectedMeetingId !== idRapat) return; // rapat berganti saat menunggu

                this.apiOnline = true;

                if (data.catatan && typeof data.catatan.isi === 'string') {
                    const html = data.catatan.isi;
                    this.$nextTick(() => {
                        if (this.$refs.editor) this.$refs.editor.innerHTML = html;
                        this.noteText = this.htmlToText(html);
                    });
                    localStorage.setItem(this.storageKey('note'), html);
                } else {
                    this.$nextTick(() => { if (this.$refs.editor) this.$refs.editor.innerHTML = ''; this.noteText = ''; });
                }

                this.attachments = (data.lampiran || []).map(r => this.mapLampiran(r));
                this.sharedItems = (data.berbagi || []).map(r => this.mapBerbagi(r));
                this.persistAttachments();
                this.persistShared();
            } catch (err) {
                console.warn('Workspace API tidak tersedia, memakai penyimpanan lokal.', err);
                this.apiOnline = false;
            }
        },

        mapLampiran(r) {
            return {
                id: r.id_lampiran,
                name: r.nama,
                size: r.ukuran,
                url: r.url,
                mime: r.mime,
                by: r.user ? r.user.nama : 'Anggota',
                mine: Number(r.id_user) === Number(this.idUser),
                time: this.timeOf(r.created_at),
            };
        },

        mapBerbagi(r) {
            return {
                id: r.id_berbagi,
                kind: r.jenis === 'link' ? 'link' : 'file',
                name: r.nama,
                url: r.url,
                size: r.ukuran,
                by: r.user ? r.user.nama : 'Anggota',
                mine: Number(r.id_user) === Number(this.idUser),
                time: this.timeOf(r.created_at),
            };
        },

        timeOf(iso) {
            if (!iso) return '';
            const d = new Date(iso);
            if (isNaN(d.getTime())) return '';
            return String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
        },

        /* ================= CATATAN (TEKS EDITOR) ================= */
        format(cmd, value) {
            if (!this.$refs.editor) return;
            this.$refs.editor.focus();
            document.execCommand(cmd, false, value || null);
            this.syncNote();
        },

        onPaste(e) {
            e.preventDefault();
            const text = (e.clipboardData || window.clipboardData).getData('text/plain');
            document.execCommand('insertText', false, text);
            this.syncNote();
        },

        syncNote() {
            if (!this.$refs.editor) return;
            this.noteText = this.$refs.editor.innerText || '';
            this.noteDirty = true;
            this.savedNote = false;
        },

        loadNote() {
            const html = localStorage.getItem(this.storageKey('note')) || '';
            this.noteText = this.htmlToText(html);
            this.$nextTick(() => {
                if (this.$refs.editor) this.$refs.editor.innerHTML = html;
            });
        },

        async saveNote() {
            if (!this.selectedMeetingId || !this.$refs.editor) return;

            const html = this.$refs.editor.innerHTML;
            this.noteError = '';
            this.menyimpan = true;

            // Cache lokal selalu ditulis agar tidak ada catatan yang hilang
            localStorage.setItem(this.storageKey('note'), html);

            if (this.apiOnline) {
                try {
                    const res = await fetch(this.baseUrl() + '/catatan', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.csrf(),
                        },
                        body: JSON.stringify({ isi: html }),
                    });

                    if (!res.ok) {
                        const msg = await res.json().catch(() => ({}));
                        throw new Error(msg.message || ('HTTP ' + res.status));
                    }
                } catch (err) {
                    this.menyimpan = false;
                    this.noteError = 'Gagal menyimpan ke server: ' + err.message + ' (tersimpan lokal)';
                    return;
                }
            }

            this.menyimpan = false;
            this.noteText = this.$refs.editor.innerText || '';
            this.noteDirty = false;
            this.savedNote = true;
            setTimeout(() => { this.savedNote = false; }, 4000);
        },

        /* ================= LAMPIRAN NOTULENSI (UNGGAH BERKAS) ================= */
        async addAttachments(e) {
            const files = Array.from(e.target.files || []);
            const max = 10 * 1024 * 1024;
            this.noteError = '';

            for (const file of files) {
                if (file.size > max) {
                    alert('Berkas "' + file.name + '" melebihi 10 MB dan dilewati.');
                    continue;
                }

                if (this.apiOnline) {
                    try {
                        const form = new FormData();
                        form.append('file', file);

                        const res = await fetch(this.baseUrl() + '/lampiran', {
                            method: 'POST',
                            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf() },
                            body: form,
                        });

                        const data = await res.json().catch(() => ({}));
                        if (!res.ok) throw new Error(data.message || ('HTTP ' + res.status));

                        this.attachments.unshift(this.mapLampiran(data.lampiran));
                        this.persistAttachments();
                        continue;
                    } catch (err) {
                        this.noteError = 'Unggah gagal: ' + err.message + ' — disimpan lokal.';
                    }
                }

                // Fallback lokal
                await new Promise(resolve => {
                    const item = {
                        id: 'att-' + Date.now() + '-' + Math.random().toString(36).slice(2, 7),
                        name: file.name,
                        size: file.size,
                        time: this.nowLabel()
                    };
                    const reader = new FileReader();
                    reader.onload = () => {
                        item.dataUrl = reader.result;
                        this.attachments.unshift(item);
                        resolve();
                    };
                    reader.onerror = resolve;
                    reader.readAsDataURL(file);
                });
                this.persistAttachments();
            }

            e.target.value = '';
        },

        loadAttachments() {
            try {
                const raw = localStorage.getItem(this.storageKey('attach'));
                this.attachments = raw ? JSON.parse(raw) : [];
            } catch (err) {
                this.attachments = [];
            }
        },

        persistAttachments() {
            try {
                // Saat online, isi berkas ada di server — cukup simpan metadata
                const payload = this.apiOnline
                    ? this.attachments.map(({ dataUrl, ...rest }) => rest)
                    : this.attachments;
                localStorage.setItem(this.storageKey('attach'), JSON.stringify(payload));
            } catch (err) {
                console.warn('Lampiran terlalu besar untuk penyimpanan lokal; tetap tersedia di sesi ini.', err);
            }
        },

        async removeAttachment(i) {
            const f = this.attachments[i];
            if (!f) return;

            if (this.apiOnline && typeof f.id === 'number') {
                try {
                    const res = await fetch(this.baseUrl() + '/lampiran/' + f.id, {
                        method: 'DELETE',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf() },
                    });
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                } catch (err) {
                    this.noteError = 'Gagal menghapus lampiran: ' + err.message;
                    return;
                }
            }

            this.attachments.splice(i, 1);
            this.persistAttachments();
        },

        downloadAttachment(i) {
            const f = this.attachments[i];
            if (!f) return;
            if (f.url) { window.open(f.url, '_blank', 'noopener'); return; }
            if (!f.dataUrl) { alert('Isi berkas tidak tersedia pada sesi ini.'); return; }
            this.downloadFile(f.name, null, null, f.dataUrl);
        },

        /* ================= BERBAGI DOKUMEN & TAUTAN ================= */
        loadShared() {
            try {
                const raw = localStorage.getItem(this.storageKey('share'));
                this.sharedItems = raw ? JSON.parse(raw) : [];
            } catch (err) {
                this.sharedItems = [];
            }
        },

        persistShared() {
            try { localStorage.setItem(this.storageKey('share'), JSON.stringify(this.sharedItems)); } catch (err) {}
        },

        async shareFiles(e) {
            const files = Array.from(e.target.files || []);
            this.shareError = '';

            for (const file of files) {
                if (file.size > 10 * 1024 * 1024) {
                    alert('Berkas "' + file.name + '" melebihi 10 MB dan dilewati.');
                    continue;
                }

                if (this.apiOnline) {
                    try {
                        const form = new FormData();
                        form.append('file', file);

                        const res = await fetch(this.baseUrl() + '/berbagi', {
                            method: 'POST',
                            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf() },
                            body: form,
                        });

                        const data = await res.json().catch(() => ({}));
                        if (!res.ok) throw new Error(data.message || ('HTTP ' + res.status));

                        this.sharedItems.unshift(this.mapBerbagi(data.berbagi));
                        this.persistShared();
                        continue;
                    } catch (err) {
                        this.shareError = 'Gagal membagikan ' + file.name + ': ' + err.message;
                    }
                }

                await new Promise(resolve => {
                    const item = {
                        id: 'shr-' + Date.now() + '-' + Math.random().toString(36).slice(2, 7),
                        kind: 'file',
                        name: file.name,
                        size: file.size,
                        by: 'Anda',
                        time: this.nowLabel(),
                        mine: true
                    };
                    const reader = new FileReader();
                    reader.onload = () => {
                        item.dataUrl = reader.result;
                        this.sharedItems.unshift(item);
                        resolve();
                    };
                    reader.onerror = resolve;
                    reader.readAsDataURL(file);
                });
                this.persistShared();
            }

            e.target.value = '';
        },

        async addLink() {
            this.shareError = '';
            const url = (this.shareUrl || '').trim();

            if (!/^https?:\/\/[^\s.]+\.[^\s]+/i.test(url)) {
                this.shareError = 'Tautan harus diawali http:// atau https://';
                return;
            }

            const nama = (this.shareTitle || '').trim() || this.domainOf(url);

            if (this.apiOnline) {
                try {
                    const res = await fetch(this.baseUrl() + '/berbagi', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.csrf(),
                        },
                        body: JSON.stringify({ url: url, nama: nama, jenis: 'link' }),
                    });

                    const data = await res.json().catch(() => ({}));
                    if (!res.ok) throw new Error(data.message || ('HTTP ' + res.status));

                    this.sharedItems.unshift(this.mapBerbagi(data.berbagi));
                    this.shareUrl = '';
                    this.shareTitle = '';
                    this.persistShared();
                    return;
                } catch (err) {
                    this.shareError = err.message;
                    return;
                }
            }

            this.sharedItems.unshift({
                id: 'lnk-' + Date.now(),
                kind: 'link',
                name: nama,
                url: url,
                by: 'Anda',
                time: this.nowLabel(),
                mine: true
            });

            this.shareUrl = '';
            this.shareTitle = '';
            this.persistShared();
        },

        async removeShared(i) {
            const item = this.sharedItems[i];
            if (!item) return;

            if (this.apiOnline && typeof item.id === 'number') {
                try {
                    const res = await fetch(this.baseUrl() + '/berbagi/' + item.id, {
                        method: 'DELETE',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf() },
                    });
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                } catch (err) {
                    this.shareError = 'Gagal menghapus: ' + err.message;
                    return;
                }
            }

            this.sharedItems.splice(i, 1);
            this.persistShared();
        },

        openShared(item) {
            if (item.url) window.open(item.url, '_blank', 'noopener');
        },

        downloadShared(item) {
            if (item.url) { window.open(item.url, '_blank', 'noopener'); return; }
            if (!item.dataUrl) {
                alert('Berkas ini tidak memiliki tautan unduhan.');
                return;
            }
            this.downloadFile(item.name, null, null, item.dataUrl);
        },

        /* ================= EXPORT NOTULENSI ================= */
        buildDocument() {
            const m = this.selectedMeeting || {};
            const note = (this.noteText || '').trim();
            const doc = [];

            doc.push('# Notulensi Rapat — ' + (m.type || 'Rapat Senat Akademik'));
            doc.push('');
            doc.push(m.title || '-');
            doc.push('');
            doc.push('- Tanggal: ' + (m.date || '-'));
            doc.push('- Waktu: ' + (m.time || '-'));
            doc.push('- Ruang: ' + (m.room || '-'));
            doc.push('- Status: ' + (m.status || '-'));
            if (m.session_code) doc.push('- Kode Sesi: ' + m.session_code);
            doc.push('');
            doc.push('## Catatan Anggota');
            doc.push('');
            doc.push(note || '_Belum ada catatan._');
            doc.push('');
            doc.push('## Lampiran Notulensi');
            doc.push('');

            if (this.attachments.length) {
                this.attachments.forEach(f => doc.push('- ' + f.name + ' (' + this.formatBytes(f.size) + ')'));
            } else {
                doc.push('_Tidak ada lampiran._');
            }

            doc.push('');
            doc.push('## Dokumen & Tautan Dibagikan');
            doc.push('');

            if (this.sharedItems.length) {
                this.sharedItems.forEach(it => {
                    doc.push(it.kind === 'link'
                        ? '- [' + it.name + '](' + it.url + ') — ' + it.by
                        : '- ' + it.name + ' (' + this.formatBytes(it.size) + ') — ' + it.by);
                });
            } else {
                doc.push('_Tidak ada._');
            }

            doc.push('');
            doc.push('---');
            doc.push('Diekspor ' + new Date().toLocaleString('id-ID') + ' · Portal Anggota Senat Akademik Polines');

            return doc.join('\n');
        },

        exportNote(fmt) {
            if (!this.selectedMeetingId) return;
            this.syncNote();

            let body = this.buildDocument();
            if (fmt === 'txt') {
                body = body.replace(/^#{1,3}\s+/gm, '').replace(/\*\*/g, '').replace(/_([^_]+)_/g, '$1');
            }

            const title = this.selectedMeeting ? this.selectedMeeting.title : 'rapat';
            const name = 'notulensi-' + this.slug(title) + '.' + fmt;
            const mime = fmt === 'md' ? 'text/markdown;charset=utf-8' : 'text/plain;charset=utf-8';

            this.downloadFile(name, body, mime);
        },

        printNote() {
            if (!this.selectedMeetingId) return;
            this.syncNote();

            const m = this.selectedMeeting || {};
            const win = window.open('', '_blank');
            if (!win) { alert('Popup diblokir browser. Izinkan popup untuk mencetak / simpan PDF.'); return; }

            const notesHtml = this.$refs.editor ? this.$refs.editor.innerHTML : '';
            const files = this.attachments
                .map(f => '<li>' + this.escapeHtml(f.name) + ' <span>(' + this.formatBytes(f.size) + ')</span></li>')
                .join('');

            win.document.write(
                '<!doctype html><html lang="id"><head><meta charset="utf-8">' +
                '<title>Notulensi — ' + this.escapeHtml(m.title || 'Rapat') + '</title>' +
                '<style>' +
                'body{font-family:Georgia,serif;color:#172133;margin:36px;line-height:1.6}' +
                'h1{font-size:19px;margin:0 0 4px}' +
                '.meta{font-size:12px;color:#52637d;margin-bottom:18px}' +
                'h2{font-size:13px;text-transform:uppercase;letter-spacing:.06em;border-bottom:1px solid #e2e8ee;padding-bottom:4px;margin-top:24px}' +
                '.note{border:1px solid #e2e8ee;padding:14px;min-height:80px}' +
                'ul{padding-left:20px} li span{color:#637082;font-size:11px}' +
                'footer{margin-top:28px;font-size:10px;color:#637082;border-top:1px solid #e2e8ee;padding-top:8px}' +
                '</style></head><body>' +
                '<h1>Notulensi ' + this.escapeHtml(m.type || 'Rapat') + '</h1>' +
                '<div class="meta">' + this.escapeHtml(m.title || '') + '<br>' +
                this.escapeHtml(m.date || '') + ' · ' + this.escapeHtml(m.time || '') + ' · ' + this.escapeHtml(m.room || '') + '</div>' +
                '<h2>Catatan Anggota</h2><div class="note">' + (notesHtml || '<em>Belum ada catatan.</em>') + '</div>' +
                '<h2>Lampiran Notulensi</h2>' + (files ? '<ul>' + files + '</ul>' : '<p><em>Tidak ada lampiran.</em></p>') +
                '<footer>Diekspor ' + new Date().toLocaleString('id-ID') + ' · Portal Anggota Senat Akademik Polines</footer>' +
                '</body></html>'
            );

            win.document.close();
            win.focus();
            setTimeout(() => win.print(), 300);
        },

        /* ================= UTIL ================= */
        downloadFile(name, content, mime, href) {
            const link = document.createElement('a');
            link.download = name;

            if (href) {
                link.href = href;
            } else {
                const blob = new Blob([content], { type: mime || 'application/octet-stream' });
                link.href = URL.createObjectURL(blob);
                setTimeout(() => URL.revokeObjectURL(link.href), 2000);
            }

            document.body.appendChild(link);
            link.click();
            link.remove();
        },

        htmlToText(html) {
            if (!html) return '';
            const d = document.createElement('div');
            d.innerHTML = html;
            return (d.innerText || d.textContent || '').trim();
        },

        formatBytes(n) {
            if (n === null || n === undefined) return '-';
            if (n < 1024) return n + ' B';
            if (n < 1048576) return (n / 1024).toFixed(1).replace('.0', '') + ' KB';
            return (n / 1048576).toFixed(1).replace('.0', '') + ' MB';
        },

        extOf(name) {
            const parts = String(name || '').split('.');
            return parts.length > 1 ? parts.pop().slice(0, 4).toUpperCase() : 'FILE';
        },

        slug(s) {
            return String(s || 'rapat')
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-|-$/g, '')
                .slice(0, 60);
        },

        domainOf(url) {
            try { return new URL(url).hostname.replace(/^www\./, ''); }
            catch (e) { return url; }
        },

        escapeHtml(s) {
            const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
            return String(s === null || s === undefined ? '' : s).replace(/[&<>"']/g, c => map[c]);
        },

        nowLabel() {
            const d = new Date();
            return String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
        },

        voteLabel(id) {
            const map = { setuju: 'Setuju', tolak: 'Tolak', abstain: 'Abstain' };
            return map[id] || id;
        }
    }));
});
</script>
@endsection
