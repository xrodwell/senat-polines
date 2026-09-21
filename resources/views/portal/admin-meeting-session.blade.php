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
                <h1 class="text-lg font-bold text-white tracking-tight">Manajemen Sesi Sidang, E-Voting &amp; Monitor Kuorum</h1>
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
         
         // Modal Tambah Sidang
         showAddMeetingModal: false,
         newMeeting: {
             title: '',
             type: 'Sidang Pleno',
             date: '',
             time: '',
             room: 'Ruang Sidang Utama Lantai 2 Gedung Direktorat Polines'
         },
         
         // List Jadwal Sidang (LocalStorage Sync)
         meetingsList: [],
         
         // Fitur Gelar E-Voting Sidang (Sync LocalStorage)
         activeVote: {
             isOpen: true,
             id: 'VOTE-2026-001',
             title: 'Persetujuan Pengesahan Perubahan Kurikulum MBKM Vokasi 2026/2027',
             description: 'Apakah Sidang Pleno menyetujui draf revisi kurikulum vokasi berbasis industri untuk disahkan menjadi Peraturan Senat Akademik?',
             options: [
                 { id: 'setuju', label: 'Setuju / Mufakat', count: 22 },
                 { id: 'tolak', label: 'Menolak / Keberatan', count: 2 },
                 { id: 'abstain', label: 'Abstain / Pikir-pikir', count: 4 }
             ]
         },
         
         showNewVoteModal: false,
         newVoteForm: {
             title: '',
             description: ''
         },

         init() {
             // Init Meetings
             const storedMeetings = localStorage.getItem('polines_senat_meetings');
             if (storedMeetings) {
                 this.meetingsList = JSON.parse(storedMeetings);
             } else {
                 this.meetingsList = {{ Js::from($meetings) }};
                 localStorage.setItem('polines_senat_meetings', JSON.stringify(this.meetingsList));
             }

             // Init Voting
             const storedVote = localStorage.getItem('polines_senat_voting');
             if (storedVote) {
                 this.activeVote = JSON.parse(storedVote);
             } else {
                 localStorage.setItem('polines_senat_voting', JSON.stringify(this.activeVote));
             }

             // Event listener untuk perubahan storage dari browser anggota
             window.addEventListener('storage', () => {
                 const updatedVote = localStorage.getItem('polines_senat_voting');
                 if (updatedVote) this.activeVote = JSON.parse(updatedVote);
             });

             // QR Countdown
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
         },

         saveNewMeeting() {
             if (!this.newMeeting.title || !this.newMeeting.date || !this.newMeeting.time) {
                 alert('Mohon lengkapi judul, tanggal, dan jam sidang.');
                 return;
             }
             const idNum = Math.floor(100 + Math.random() * 900);
             const code = 'PLN-' + idNum;
             const newItem = {
                 id: Date.now(),
                 title: this.newMeeting.title,
                 type: this.newMeeting.type,
                 date: this.newMeeting.date,
                 time: this.newMeeting.time,
                 room: this.newMeeting.room,
                 status: 'Terjadwal',
                 session_code: code,
                 qr_token: 'token-' + code.toLowerCase(),
                 total_invited: 34,
                 present_count: 0
             };
             this.meetingsList.unshift(newItem);
             localStorage.setItem('polines_senat_meetings', JSON.stringify(this.meetingsList));
             this.showAddMeetingModal = false;
             this.newMeeting = { title: '', type: 'Sidang Pleno', date: '', time: '', room: 'Ruang Sidang Utama Lantai 2 Gedung Direktorat Polines' };
         },

         toggleVoteStatus() {
             this.activeVote.isOpen = !this.activeVote.isOpen;
             localStorage.setItem('polines_senat_voting', JSON.stringify(this.activeVote));
         },

         deployNewVote() {
             if (!this.newVoteForm.title) {
                 alert('Ketikkan pokok putusan voting terlebih dahulu.');
                 return;
             }
             const num = Math.floor(100 + Math.random() * 900);
             this.activeVote = {
                 isOpen: true,
                 id: 'VOTE-2026-' + num,
                 title: this.newVoteForm.title,
                 description: this.newVoteForm.description || 'Musyawarah mufakat & pemungutan suara resmi sidang Senat Akademik Polines.',
                 options: [
                     { id: 'setuju', label: 'Setuju / Mufakat', count: 0 },
                     { id: 'tolak', label: 'Menolak / Keberatan', count: 0 },
                     { id: 'abstain', label: 'Abstain / Pikir-pikir', count: 0 }
                 ]
             };
             localStorage.setItem('polines_senat_voting', JSON.stringify(this.activeVote));
             localStorage.removeItem('polines_member_my_vote'); // Reset suara anggota
             this.showNewVoteModal = false;
             this.newVoteForm = { title: '', description: '' };
         }
     }">
    
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        {{-- KOLOM KIRI: KENDALI PROYEKTOR, E-VOTING & KUORUM SIDANG --}}
        <div class="lg:col-span-8 space-y-6">
            
            {{-- ================= MODUL 1: MONITOR & KENDALI E-VOTING SIDANG ================= --}}
            <div class="rounded-xl border border-slate-300 bg-white shadow-xs overflow-hidden">
                <div class="bg-slate-900 text-white px-5 py-3.5 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full" :class="activeVote && activeVote.isOpen ? 'bg-amber-400 animate-pulse' : 'bg-slate-500'"></span>
                        <span class="text-xs font-bold uppercase tracking-wider">Layar Rekapitulasi E-Voting Sidang (Proyektor)</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <button @click="toggleVoteStatus" type="button" 
                                class="rounded px-3 py-1 text-xs font-bold transition-all"
                                :class="activeVote && activeVote.isOpen ? 'bg-amber-600 hover:bg-amber-700 text-white' : 'bg-emerald-600 hover:bg-emerald-700 text-white'">
                            <span x-text="activeVote && activeVote.isOpen ? 'Kunci & Bekukan Voting' : 'Buka Kembali Bilik Suara'"></span>
                        </button>
                        <button @click="showNewVoteModal = true" type="button" class="rounded bg-polines-navy hover:bg-polines-navyDark text-white border border-slate-700 px-3 py-1 text-xs font-bold">
                            + Gelar Voting Baru
                        </button>
                    </div>
                </div>

                <div class="p-6">
                    <div class="border-b border-slate-200 pb-4 mb-5 flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <span class="text-[11px] font-mono font-bold text-polines-orange uppercase" x-text="'ID: ' + activeVote.id"></span>
                            <h3 class="text-base font-extrabold text-slate-950 mt-0.5" x-text="activeVote.title"></h3>
                            <p class="text-xs text-slate-500 mt-1" x-text="activeVote.description"></p>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-mono text-slate-400">Total Suara Masuk:</span>
                            <div class="text-2xl font-bold font-mono text-slate-900 tabular-nums">
                                <span x-text="(activeVote.options[0].count + activeVote.options[1].count + activeVote.options[2].count)"></span>
                                <span class="text-xs text-slate-400 font-normal">/ 34 Anggota</span>
                            </div>
                        </div>
                    </div>

                    {{-- Bar Hasil Suara Realtime --}}
                    <div class="space-y-4">
                        {{-- Setuju --}}
                        <div>
                            <div class="flex justify-between text-xs font-bold mb-1">
                                <span class="text-emerald-900">Setuju / Mufakat</span>
                                <span class="font-mono text-emerald-800" x-text="activeVote.options[0].count + ' Suara'"></span>
                            </div>
                            <div class="w-full bg-slate-100 h-3.5 rounded-full overflow-hidden">
                                <div class="bg-emerald-600 h-full rounded-full transition-all duration-300"
                                     :style="'width: ' + ((activeVote.options[0].count / 34) * 100) + '%'"></div>
                            </div>
                        </div>

                        {{-- Tolak --}}
                        <div>
                            <div class="flex justify-between text-xs font-bold mb-1">
                                <span class="text-rose-900">Menolak / Keberatan</span>
                                <span class="font-mono text-rose-800" x-text="activeVote.options[1].count + ' Suara'"></span>
                            </div>
                            <div class="w-full bg-slate-100 h-3.5 rounded-full overflow-hidden">
                                <div class="bg-rose-600 h-full rounded-full transition-all duration-300"
                                     :style="'width: ' + ((activeVote.options[1].count / 34) * 100) + '%'"></div>
                            </div>
                        </div>

                        {{-- Abstain --}}
                        <div>
                            <div class="flex justify-between text-xs font-bold mb-1">
                                <span class="text-slate-700">Abstain / Pikir-pikir</span>
                                <span class="font-mono text-slate-700" x-text="activeVote.options[2].count + ' Suara'"></span>
                            </div>
                            <div class="w-full bg-slate-100 h-3.5 rounded-full overflow-hidden">
                                <div class="bg-slate-400 h-full rounded-full transition-all duration-300"
                                     :style="'width: ' + ((activeVote.options[2].count / 34) * 100) + '%'"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= MODUL 2: PANEL PRESENSI DIGITAL & PROYEKTOR ================= --}}
            <div class="rounded-xl border border-slate-300 bg-white shadow-xs overflow-hidden">
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
                                    <path d="M4 4h26v26H4V4zm4 4v18h18V8H8zm4 4h10v10H12V12zM70 4h26v26H70V4zm4 4v18h18V8H74zm4 4h10v10H78V12zM4 70h26v26H4V70zm4 4v18h18V74H8zm4 4h10v10H12V78z" />
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

            {{-- ================= MODUL 3: MONITOR KUORUM INSTITUSIONAL ================= --}}
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

                <div class="relative w-full bg-slate-200 h-4 rounded overflow-hidden mb-4">
                    <div class="absolute top-0 bottom-0 left-[66.6%] w-0.5 bg-rose-500 z-10" title="Ambang Batas Kuorum (66.7%)"></div>
                    <div class="bg-emerald-600 h-full rounded transition-all duration-500" style="width: 82.3%;"></div>
                </div>
                <div class="flex justify-between text-[11px] font-mono text-slate-500 mb-6">
                    <span>0 Anggota</span>
                    <span class="text-rose-600 font-bold">Ambang Sah: 23 Anggota (66.7%)</span>
                    <span>Total: 34 Anggota</span>
                </div>

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

        {{-- KOLOM KANAN: TAMBAH SIDANG & KALENDER MASTER --}}
        <div class="lg:col-span-4 space-y-6">
            
            {{-- Tombol Aksi Tambah Sidang --}}
            <button @click="showAddMeetingModal = true" type="button" class="w-full py-3 px-4 rounded-xl bg-polines-orange hover:bg-orange-600 text-white font-bold text-sm shadow-sm flex items-center justify-center gap-2 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Jadwal Sidang Baru
            </button>

            {{-- Master Jadwal Dinamis --}}
            <div class="rounded-xl border border-slate-300 bg-white p-5 shadow-xs">
                <div class="flex items-center justify-between border-b border-slate-200 pb-3 mb-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900">Jadwal Kalender Sidang</h3>
                    <span class="text-xs font-mono text-slate-500" x-text="meetingsList.length + ' Agenda'"></span>
                </div>

                <div class="space-y-3 max-h-[480px] overflow-y-auto pr-1">
                    <template x-for="mtg in meetingsList" :key="mtg.id">
                        <div class="rounded-lg border p-3.5"
                             :class="mtg.status === 'Sedang Berlangsung' ? 'border-amber-300 bg-amber-50/50' : 'border-slate-200 bg-slate-50/70'">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded font-mono uppercase"
                                      :class="mtg.status === 'Sedang Berlangsung' ? 'bg-amber-200 text-amber-900' : 'bg-slate-200 text-slate-700'"
                                      x-text="mtg.status">
                                </span>
                                <span class="font-mono text-xs font-bold text-slate-700 tabular-nums" x-text="mtg.session_code"></span>
                            </div>
                            <h4 class="text-xs font-bold text-slate-900 leading-snug mt-1" x-text="mtg.title"></h4>
                            <div class="text-[11px] text-slate-500 mt-2 space-y-0.5 font-mono">
                                <div>📅 <span x-text="mtg.date"></span> &bull; <span x-text="mtg.time"></span></div>
                                <div>📍 <span x-text="mtg.room"></span></div>
                            </div>
                        </div>
                    </template>
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

    {{-- ================= MODAL: TAMBAH JADWAL SIDANG BARU ================= --}}
    <div x-show="showAddMeetingModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl border border-slate-300 shadow-2xl max-w-lg w-full p-6 space-y-4" @click.away="showAddMeetingModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-extrabold text-slate-900">Jadwalkan Sidang Senat Baru</h3>
                <button @click="showAddMeetingModal = false" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
            </div>

            <div class="space-y-3 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Judul / Agenda Sidang</label>
                    <input type="text" x-model="newMeeting.title" placeholder="Contoh: Rapat Pleno Penetapan Standar Kurikulum Vokasi 2026" class="w-full rounded-lg border border-slate-300 p-2.5 outline-none focus:border-polines-blue">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Jenis Sidang</label>
                        <select x-model="newMeeting.type" class="w-full rounded-lg border border-slate-300 p-2.5 outline-none focus:border-polines-blue bg-white">
                            <option value="Sidang Pleno">Sidang Pleno</option>
                            <option value="Rapat Komisi I">Rapat Komisi I (Akademik)</option>
                            <option value="Rapat Komisi II">Rapat Komisi II (Keuangan)</option>
                            <option value="Rapat Komisi III">Rapat Komisi III (Kemahasiswaan)</option>
                            <option value="Rapat Komisi IV">Rapat Komisi IV (SDM)</option>
                            <option value="Rapat Komisi V">Rapat Komisi V (Etika)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Tanggal</label>
                        <input type="date" x-model="newMeeting.date" class="w-full rounded-lg border border-slate-300 p-2.5 outline-none focus:border-polines-blue">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Waktu / Jam Sidang</label>
                    <input type="text" x-model="newMeeting.time" placeholder="Contoh: 09:00 - 12:00 WIB" class="w-full rounded-lg border border-slate-300 p-2.5 outline-none focus:border-polines-blue">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Ruangan / Tempat Sidang</label>
                    <input type="text" x-model="newMeeting.room" class="w-full rounded-lg border border-slate-300 p-2.5 outline-none focus:border-polines-blue">
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                <button @click="showAddMeetingModal = false" type="button" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 text-xs font-bold hover:bg-slate-50">
                    Batal
                </button>
                <button @click="saveNewMeeting" type="button" class="px-5 py-2 rounded-lg bg-polines-navy text-white text-xs font-bold hover:bg-polines-navyDark">
                    Simpan Agenda Sidang
                </button>
            </div>
        </div>
    </div>

    {{-- ================= MODAL: GELAR VOTING BARU ================= --}}
    <div x-show="showNewVoteModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl border border-slate-300 shadow-2xl max-w-lg w-full p-6 space-y-4" @click.away="showNewVoteModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-extrabold text-slate-900">Gelar Sesi E-Voting Sidang Baru</h3>
                <button @click="showNewVoteModal = false" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
            </div>

            <div class="space-y-3 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Pokok Putusan / Pertanyaan Voting</label>
                    <input type="text" x-model="newVoteForm.title" placeholder="Contoh: Pengesahan Draf Rencana Strategis (Renstra) Polines 2026-2030" class="w-full rounded-lg border border-slate-300 p-2.5 outline-none focus:border-polines-blue">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Deskripsi / Penjelasan Pasal</label>
                    <textarea rows="3" x-model="newVoteForm.description" placeholder="Jelaskan dasar pertimbangan putusan sidang..." class="w-full rounded-lg border border-slate-300 p-2.5 outline-none focus:border-polines-blue"></textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                <button @click="showNewVoteModal = false" type="button" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 text-xs font-bold hover:bg-slate-50">
                    Batal
                </button>
                <button @click="deployNewVote" type="button" class="px-5 py-2 rounded-lg bg-polines-orange text-white text-xs font-bold hover:bg-orange-600">
                    Luncurkan ke Bilik Suara Anggota
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
