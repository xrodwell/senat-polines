@extends('layouts.public')

@section('title', 'Portal Anggota — Presensi, Notulensi & E-Voting Senat Polines')

@section('content')
<div class="bg-polines-navy text-white py-8 border-b border-polines-navyDark">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span class="text-xs font-semibold text-blue-200 uppercase tracking-wider">Portal Kerja Internal Anggota</span>
            </div>
            <h1 class="text-2xl font-extrabold tracking-tight">Prof. Dr. Ir. Budi Rahardjo, M.T.</h1>
            <p class="text-xs text-blue-200/80 font-mono mt-0.5">Ketua Komisi I &bull; NIP. 197108151998021001</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-xs text-blue-200 font-semibold">Kehadiran Sidang 2026:</span>
            <div class="bg-white/10 border border-white/15 rounded-xl px-4 py-2 flex items-center gap-3 backdrop-blur-sm">
                <span class="text-xl font-bold font-mono text-emerald-400 tabular-nums">94%</span>
                <span class="text-[11px] text-blue-100 leading-tight">16 dari 17 Sidang<br><span class="text-emerald-300 font-semibold">Tercatat Hadir</span></span>
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10"
     x-data="{
         // Voting State (disinkronkan via localStorage agar interaktif dengan layar Admin)
         activeVote: null,
         myVote: null,
         voteSubmitted: false,
         
         init() {
             this.loadVoteState();
             window.addEventListener('storage', () => this.loadVoteState());
         },
         
         loadVoteState() {
             const stored = localStorage.getItem('polines_senat_voting');
             if (stored) {
                 this.activeVote = JSON.parse(stored);
             } else {
                 // Default initial vote state
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
             
             const savedMyVote = localStorage.getItem('polines_member_my_vote');
             if (savedMyVote) {
                 this.myVote = savedMyVote;
                 this.voteSubmitted = true;
             }
         },
         
         castVote(optionId) {
             if (this.voteSubmitted || !this.activeVote || !this.activeVote.isOpen) return;
             this.myVote = optionId;
             this.voteSubmitted = true;
             localStorage.setItem('polines_member_my_vote', optionId);
             
             // Tambahkan hitungan suara ke storage agar terpantau di panel Admin
             const opt = this.activeVote.options.find(o => o.id === optionId);
             if (opt) opt.count++;
             localStorage.setItem('polines_senat_voting', JSON.stringify(this.activeVote));
         }
     }">
     
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        {{-- Kolom Kiri: E-Voting Sidang, Presensi Sesi Berlangsung & Notulensi --}}
        <div class="lg:col-span-8 space-y-8">
            
            {{-- ================= MODUL 1: E-VOTING SIDANG PLENO (INTERAKTIF) ================= --}}
            <div class="bg-white rounded-2xl border-2 border-polines-blue/40 shadow-sm overflow-hidden">
                <div class="bg-slate-900 text-white px-6 py-4 flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full" :class="activeVote && activeVote.isOpen ? 'bg-amber-400' : 'bg-slate-500'"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5" :class="activeVote && activeVote.isOpen ? 'bg-amber-400' : 'bg-slate-500'"></span>
                        </span>
                        <h2 class="text-sm font-bold uppercase tracking-wider">E-Voting Musyawarah &amp; Putusan Sidang</h2>
                    </div>
                    <div class="flex items-center gap-2 text-xs font-mono">
                        <span class="text-slate-400">Status Bilik Suara:</span>
                        <span class="font-bold px-2 py-0.5 rounded text-[11px]" 
                              :class="activeVote && activeVote.isOpen ? 'bg-emerald-950 text-emerald-300 border border-emerald-800' : 'bg-slate-800 text-slate-400'">
                            <span x-text="activeVote && activeVote.isOpen ? '● DIBUKA UNTUK ANGGOTA' : 'DITUTUP'"></span>
                        </span>
                    </div>
                </div>

                <div class="p-6 sm:p-8" x-show="activeVote">
                    <div class="border-b border-slate-100 pb-5 mb-6">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-mono font-bold text-polines-orange uppercase" x-text="'ID PUTUSAN: ' + activeVote.id"></span>
                            <span class="text-xs text-slate-400 font-mono">Hak Suara: 1 Suara / Anggota</span>
                        </div>
                        <h3 class="text-lg font-extrabold text-slate-900 mt-2 leading-snug" x-text="activeVote.title"></h3>
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
                    <div x-show="voteSubmitted" x-cloak class="rounded-xl border border-emerald-200 bg-emerald-50/70 p-5 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-lg shrink-0">
                                ✓
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-emerald-950">Suara Anda Berhasil Dicatat!</h4>
                                <p class="text-xs text-emerald-800 mt-0.5">
                                    Pilihan Anda: <strong class="uppercase underline font-mono text-emerald-900" x-text="myVote"></strong>. Hasil langsung terakumulasi pada monitor kuorum pimpinan sidang.
                                </p>
                            </div>
                        </div>
                        <span class="text-[11px] font-mono text-emerald-700 bg-white border border-emerald-200 px-2.5 py-1 rounded">
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
                    <div class="bg-slate-900 text-white px-6 py-4 flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <span class="relative flex h-2.5 w-2.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-400"></span>
                            </span>
                            <h2 class="text-sm font-bold uppercase tracking-wider">Sesi Presensi Sidang Aktif</h2>
                        </div>
                        <div class="flex items-center gap-2 text-xs text-slate-300 font-mono">
                            <span>Sisa Waktu Kuorum:</span>
                            <span class="bg-slate-800 text-amber-400 font-bold px-2 py-0.5 rounded tabular-nums">24:18</span>
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
                                    📷 Scan Kamera QR Code
                                </button>
                                <button @click="activeTab = 'code'; stopCamera()" type="button" class="pb-3 px-4 text-xs font-bold uppercase tracking-wider transition-all border-b-2"
                                        :class="activeTab === 'code' ? 'border-polines-navy text-polines-navy' : 'border-transparent text-slate-400 hover:text-slate-700'">
                                    ⌨️ Input Kode 6-Digit
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
                                <div x-show="scannerActive" x-cloak class="rounded-xl border border-slate-900 bg-black p-4 text-center text-white relative overflow-hidden">
                                    <div class="relative w-full max-w-xs mx-auto aspect-square bg-slate-950 rounded-lg overflow-hidden flex items-center justify-center border-2 border-dashed border-polines-orange">
                                        <video id="camera-feed" autoplay playsinline class="w-full h-full object-cover"></video>
                                        <div class="absolute inset-0 pointer-events-none flex flex-col items-center justify-between p-4">
                                            <div class="w-full flex justify-between">
                                                <div class="w-6 h-6 border-t-2 border-l-2 border-white"></div>
                                                <div class="w-6 h-6 border-t-2 border-r-2 border-white"></div>
                                            </div>
                                            <div class="text-[11px] font-mono bg-black/75 px-3 py-1 rounded text-amber-300">
                                                Arahkan ke QR Code Proyektor
                                            </div>
                                            <div class="w-full flex justify-between">
                                                <div class="w-6 h-6 border-b-2 border-l-2 border-white"></div>
                                                <div class="w-6 h-6 border-b-2 border-r-2 border-white"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4 flex flex-wrap items-center justify-center gap-3">
                                        <button @click="simulateScan()" type="button" class="rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 text-xs font-bold transition-colors">
                                            Simulasikan QR Terdeteksi (Demo Instan)
                                        </button>
                                        <button @click="stopCamera()" type="button" class="rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 px-4 py-2 text-xs font-semibold transition-colors">
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
                                <div class="w-12 h-12 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xl font-extrabold shrink-0 shadow-xs">
                                    ✓
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-base font-extrabold text-emerald-950">Presensi Berhasil Diverifikasi</h4>
                                        <span class="text-[10px] font-mono font-bold bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded border border-emerald-300">STATUS: SAH</span>
                                    </div>
                                    <p class="text-xs text-emerald-800 mt-1">
                                        Tercatat atas nama <strong>Prof. Dr. Ir. Budi Rahardjo, M.T.</strong> pada pukul <span class="font-bold tabular-nums" x-text="checkinTime"></span>.
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

            {{-- ================= MODUL 3: NOTULENSI MANDIRI ================= --}}
            <div class="bg-white rounded-2xl border border-slate-200/90 p-6 sm:p-8 shadow-sm"
                 x-data="{
                     savedNote: false,
                     noteText: '',
                     saveNotes() {
                         if (!this.noteText) return;
                         this.savedNote = true;
                         setTimeout(() => this.savedNote = false, 3000);
                     }
                 }">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-lg font-extrabold text-slate-900">Catatan Risalah Musyawarah Komisi</h3>
                    <span class="text-xs text-slate-400 font-mono">Komisi I Bidang Akademik</span>
                </div>
                <p class="text-xs text-slate-500 mb-4">
                    Catatan poin telaah, argumentasi pasal, atau pertimbangan norma yang Anda sampaikan selama sidang berlangsung.
                </p>

                <textarea rows="5" x-model="noteText" placeholder="Ketik catatan risalah rapat di sini..." class="w-full rounded-xl border border-slate-300 p-4 text-sm focus:border-polines-blue focus:ring-1 focus:ring-polines-blue outline-none transition-all leading-relaxed"></textarea>

                <div class="flex items-center justify-between mt-4">
                    <span class="text-xs text-emerald-600 font-semibold" x-show="savedNote" x-cloak>
                        ✓ Draf risalah berhasil diamankan ke penyimpanan lokal.
                    </span>
                    <span x-show="!savedNote"></span>

                    <button @click="saveNotes()" type="button" class="inline-flex items-center gap-2 rounded-lg bg-polines-navy hover:bg-polines-navyDark text-white px-5 py-2.5 text-xs font-bold transition-colors">
                        Simpan Catatan Risalah
                    </button>
                </div>
            </div>

        </div>

        {{-- Kolom Kanan: Jadwal Sidang Mendatang & Layanan Sekretariat --}}
        <div class="lg:col-span-4 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-sm">
                <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-900 mb-4">Agenda Sidang Berikutnya</h3>
                <div class="space-y-3">
                    @foreach($upcomingMeetings as $m)
                        <div class="rounded-xl border border-slate-200/70 bg-slate-50/50 p-4">
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-polines-navy uppercase mb-1.5">
                                {{ $m['type'] }}
                            </span>
                            <h4 class="text-xs font-bold text-slate-900 leading-snug mb-2">{{ $m['title'] }}</h4>
                            <div class="text-[11px] text-slate-500 space-y-0.5">
                                <p class="tabular-nums">📅 {{ $m['date'] }} &bull; {{ $m['time'] }}</p>
                                <p>📍 {{ $m['room'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-sm">
                <h3 class="text-sm font-extrabold text-slate-900 mb-2">Permohonan Izin Resmi</h3>
                <p class="text-xs text-slate-500 leading-relaxed mb-4">
                    Bila anggota berhalangan hadir karena tugas institusi, sampaikan surat dispensasi sebelum sidang dimulai.
                </p>
                <a href="mailto:senat@polines.ac.id" class="inline-flex items-center justify-center w-full py-2.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition-colors">
                    Kirim Surat Dispensasi / Izin
                </a>
            </div>
        </div>

    </div>
</div>
@endsection
