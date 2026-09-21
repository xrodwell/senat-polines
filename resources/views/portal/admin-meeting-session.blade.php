@extends('layouts.public')

@section('title', 'Sekretariat Senat — Manajemen Sidang, Presensi & Pemungutan Suara')

@section('content')
{{-- ================= SUB-HEADER INSTITUSIONAL ================= --}}
<div class="bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex flex-wrap items-start justify-between gap-6">
            <div class="flex items-start gap-4 min-w-0">
                <div class="h-11 w-11 shrink-0 rounded border border-slate-200 bg-slate-50 flex items-center justify-center text-polines-navy">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M4 21V10l8-6 8 6v11M9 21v-6h6v6M8 12h.01M12 12h.01M16 12h.01" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <nav class="text-[11px] text-slate-500" aria-label="Breadcrumb">
                        <a href="{{ route('portal.session') }}" class="hover:text-polines-blue">Portal Sekretariat</a>
                        <span class="mx-1 text-slate-300">/</span>
                        <span class="text-slate-700 font-medium">Manajemen Sidang</span>
                    </nav>
                    <h1 class="text-xl font-bold text-polines-navy tracking-tight mt-1">Panel Kerja Sekretariat Senat Akademik</h1>
                    <p class="text-xs text-slate-500 mt-1">
                        Penjadwalan agenda sidang, presensi elektronik, pemungutan suara, dan pemantauan kuorum.
                    </p>
                </div>
            </div>

            <div class="shrink-0 text-right border-l-2 border-polines-orange pl-4">
                <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Periode Kepengurusan</div>
                <div class="text-sm font-bold text-slate-900 tabular-nums">2025 &ndash; 2028</div>
                <div class="text-[11px] text-slate-500 mt-1">Politeknik Negeri Semarang</div>
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

         totalVotes() {
             return (this.activeVote && this.activeVote.options ? this.activeVote.options : [])
                 .reduce((sum, opt) => sum + (opt.count || 0), 0);
         },

         votePercent(count) {
             const total = this.totalVotes();
             return total > 0 ? ((count / total) * 100).toFixed(1) : '0.0';
         },

         fractionLabel(id) {
             const map = {
                 setuju: 'Fraksi Pendukung',
                 tolak: 'Fraksi Peninjau',
                 abstain: 'Fraksi Netral'
             };
             return map[id] || 'Tidak Terklasifikasi';
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
        
        {{-- KOLOM KIRI: REKAPITULASI PUTUSAN, PRESENSI & KUORUM --}}
        <div class="lg:col-span-8 space-y-6">
            
            {{-- ================= MODUL 1: REKAPITULASI PUTUSAN SIDANG ================= --}}
            <section class="rounded-md border border-slate-200 bg-white overflow-hidden">
                {{-- Toolbar Aksi --}}
                <div class="border-b border-slate-200 px-5 py-3.5 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Lembar Rekapitulasi Putusan Sidang</h2>
                        <p class="text-[11px] text-slate-500 mt-0.5">Tayangan resmi bilik suara elektronik pada proyektor ruang sidang.</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded border px-2.5 py-1 text-[11px] font-semibold"
                              :class="activeVote && activeVote.isOpen
                                  ? 'border-emerald-200 bg-emerald-50 text-emerald-800'
                                  : 'border-slate-300 bg-slate-100 text-slate-600'">
                            <span class="h-1.5 w-1.5 rounded-full"
                                  :class="activeVote && activeVote.isOpen ? 'bg-emerald-600' : 'bg-slate-400'"></span>
                            <span x-text="activeVote && activeVote.isOpen ? 'Bilik Suara Terbuka' : 'Bilik Suara Terkunci'"></span>
                        </span>

                        <div class="inline-flex items-center rounded border border-slate-200 bg-slate-50 p-0.5">
                            <button @click="toggleVoteStatus" type="button"
                                    class="rounded px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-white transition-colors"
                                    :title="activeVote && activeVote.isOpen ? 'Bekukan hasil dan hentikan penerimaan suara' : 'Terima suara anggota kembali'">
                                <span x-text="activeVote && activeVote.isOpen ? 'Kunci &amp; Bekukan' : 'Buka Bilik Suara'"></span>
                            </button>
                            <button @click="showNewVoteModal = true" type="button"
                                    class="rounded bg-polines-navy px-3 py-1.5 text-xs font-semibold text-white hover:bg-polines-navyDark transition-colors">
                                Gelar Voting Baru
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Identitas Putusan --}}
                <div class="border-b border-slate-200 px-5 py-4 bg-slate-50/60">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="text-[11px] font-mono font-semibold uppercase tracking-wider text-polines-orange">
                                Nomor Putusan: <span class="tabular-nums" x-text="activeVote.id"></span>
                            </div>
                            <h3 class="text-base font-bold text-slate-900 mt-1 leading-snug" x-text="activeVote.title"></h3>
                            <p class="text-xs text-slate-500 mt-1 max-w-3xl leading-relaxed" x-text="activeVote.description"></p>
                        </div>
                        <dl class="shrink-0 text-right">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Suara Masuk</dt>
                            <dd class="font-mono text-xl font-bold text-slate-900 tabular-nums mt-0.5">
                                <span x-text="totalVotes()"></span><span class="text-xs font-normal text-slate-400"> / 34 anggota</span>
                            </dd>
                        </dl>
                    </div>
                </div>

                {{-- Tabel Rekapitulasi Formal --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm border-collapse">
                        <caption class="sr-only">Rekapitulasi perolehan suara putusan sidang</caption>
                        <thead>
                            <tr class="bg-slate-50 text-[11px] font-semibold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                                <th scope="col" class="px-5 py-2.5 text-left">Opsi Putusan</th>
                                <th scope="col" class="px-5 py-2.5 text-left">Klasifikasi Fraksi</th>
                                <th scope="col" class="px-5 py-2.5 text-right">Suara</th>
                                <th scope="col" class="px-5 py-2.5 text-right">Persentase</th>
                                <th scope="col" class="px-5 py-2.5 text-left w-52">Distribusi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="opt in activeVote.options" :key="opt.id">
                                <tr>
                                    <th scope="row" class="px-5 py-3 text-left font-semibold text-slate-900" x-text="opt.label"></th>
                                    <td class="px-5 py-3 text-slate-600" x-text="fractionLabel(opt.id)"></td>
                                    <td class="px-5 py-3 text-right font-mono text-slate-900 tabular-nums" x-text="opt.count"></td>
                                    <td class="px-5 py-3 text-right font-mono text-slate-900 tabular-nums" x-text="votePercent(opt.count) + '%'"></td>
                                    <td class="px-5 py-3">
                                        <div class="h-1.5 w-full border border-slate-200 bg-slate-100">
                                            <div class="h-full bg-polines-navy transition-all duration-300"
                                                 :style="'width: ' + votePercent(opt.count) + '%'"></div>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot>
                            <tr class="border-t border-slate-200 bg-slate-50 text-xs">
                                <th scope="row" class="px-5 py-2.5 text-left font-bold uppercase tracking-wider text-slate-700">Jumlah Suara Sah</th>
                                <td class="px-5 py-2.5 text-slate-500">Kuorum 34 anggota senat</td>
                                <td class="px-5 py-2.5 text-right font-mono font-bold text-slate-900 tabular-nums" x-text="totalVotes()"></td>
                                <td class="px-5 py-2.5 text-right font-mono font-bold text-slate-900 tabular-nums"
                                    x-text="totalVotes() > 0 ? '100.0%' : '0.0%'"></td>
                                <td class="px-5 py-2.5"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>

            {{-- ================= MODUL 2: PRESENSI ELEKTRONIK & PROYEKTOR ================= --}}
            <section class="rounded-md border border-slate-200 bg-white overflow-hidden">
                <div class="border-b border-slate-200 px-5 py-3.5 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Presensi Elektronik Sidang</h2>
                        <p class="text-[11px] text-slate-500 mt-0.5">Pencatatan kehadiran anggota melalui pemindaian token QR.</p>
                    </div>

                    <div class="inline-flex items-center rounded border border-slate-200 bg-slate-50 p-0.5">
                        <button @click="toggleSession" type="button"
                                class="rounded px-3 py-1.5 text-xs font-semibold transition-colors"
                                :class="sessionOpen ? 'text-rose-800 hover:bg-white' : 'text-emerald-800 hover:bg-white'">
                            <span x-text="sessionOpen ? 'Bekukan Presensi' : 'Buka Kembali Sesi Presensi'"></span>
                        </button>
                    </div>
                </div>

                {{-- Tampilan Monitor Proyektor --}}
                <div class="p-6 sm:p-8" x-show="sessionOpen">
                    <div class="border-b border-slate-200 pb-5 mb-6">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="text-[11px] font-mono font-semibold uppercase tracking-wider text-polines-navy">
                                Sidang Pleno Terbuka &bull; Agenda Evaluasi Akreditasi
                            </span>
                            <span class="text-xs text-slate-500 font-mono tabular-nums">ID: PLN-PLENO-202609</span>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 mt-1 leading-snug">
                            Sidang Pleno Evaluasi Akreditasi Program Studi Jurusan Teknik Sipil &amp; Elektro
                        </h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-center bg-slate-50 border border-slate-200 rounded-md p-6">
                        {{-- Token QR --}}
                        <div class="md:col-span-5 flex flex-col items-center">
                            <div class="bg-white p-4 rounded border border-slate-200">
                                <svg class="w-44 h-44 text-slate-900" viewBox="0 0 100 100" fill="currentColor" role="img" aria-label="Token QR presensi sidang">
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
                            <div class="mt-3 flex items-center gap-2 text-slate-600 text-[11px]">
                                <span>Token berikutnya dalam</span>
                                <span class="font-mono font-bold text-slate-900 bg-white border border-slate-200 px-2 py-0.5 tabular-nums"
                                      x-text="qrCountdown + ' detik'"></span>
                            </div>
                        </div>

                        {{-- Instruksi & Kode Sesi --}}
                        <div class="md:col-span-7 space-y-4">
                            <div>
                                <span class="text-[11px] font-mono uppercase tracking-wider text-slate-500">Kode Sesi Alternatif</span>
                                <div class="text-4xl font-mono font-bold text-polines-navy tracking-widest tabular-nums mt-1" x-text="sessionCode"></div>
                            </div>

                            <p class="text-xs text-slate-600 leading-relaxed">
                                Anggota Senat dapat memindai token QR pada layar proyektor atau membuka alamat
                                <strong class="font-mono text-polines-navy">/portal/attendance</strong> pada perangkat masing-masing untuk mencatat kehadiran.
                            </p>

                            <div class="border-t border-slate-200 pt-3 flex flex-wrap items-center justify-between gap-2 text-[11px] text-slate-500">
                                <span>Token ditinjau ulang setiap 30 detik</span>
                                <span class="font-mono">Pencatatan atas nama anggota terverifikasi</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Status Sesi Dibekukan --}}
                <div class="p-10 text-center bg-slate-50" x-show="!sessionOpen" x-cloak>
                    <h3 class="text-base font-bold text-slate-900">Sesi Presensi Telah Dikunci</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto leading-relaxed">
                        Pencatatan presensi elektronik dinonaktifkan sementara. Data kuorum pada saat penguncian
                        menjadi dasar penandatanganan berita acara oleh Pimpinan Senat.
                    </p>
                </div>
            </section>

            {{-- ================= MODUL 3: MONITOR KUORUM ================= --}}
            <section class="rounded-md border border-slate-200 bg-white p-6">
                <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Monitor Kuorum &amp; Keabsahan Sidang</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Syarat sah pengambilan keputusan: minimal 2/3 anggota hadir (23 dari 34 anggota).</p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 rounded border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-800">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                        Kuorum Terpenuhi (82,3%)
                    </span>
                </div>

                <div class="relative w-full h-4 border border-slate-200 bg-slate-100 rounded-sm overflow-hidden mb-3">
                    <div class="absolute inset-y-0 left-[66.6%] w-px bg-polines-orange z-10" title="Ambang batas kuorum 66,7%"></div>
                    <div class="h-full bg-polines-navy transition-all duration-500" style="width: 82.3%;"></div>
                </div>
                <div class="flex justify-between text-[11px] font-mono text-slate-500 mb-6 tabular-nums">
                    <span>0 anggota</span>
                    <span class="text-polines-orange font-semibold">Ambang sah: 23 anggota (66,7%)</span>
                    <span>Total: 34 anggota</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="border border-slate-200 rounded p-3 bg-slate-50">
                        <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Hadir di Ruang Sidang</div>
                        <div class="text-2xl font-bold font-mono text-slate-900 mt-1 tabular-nums" x-text="presentCount"></div>
                        <div class="text-[11px] text-slate-500 mt-0.5">Terverifikasi token QR / kode sesi</div>
                    </div>
                    <div class="border border-slate-200 rounded p-3 bg-slate-50">
                        <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Izin / Penugasan Luar</div>
                        <div class="text-2xl font-bold font-mono text-slate-900 mt-1 tabular-nums">3</div>
                        <div class="text-[11px] text-slate-500 mt-0.5">Surat dispensasi tercatat</div>
                    </div>
                    <div class="border border-slate-200 rounded p-3 bg-slate-50">
                        <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Belum Hadir</div>
                        <div class="text-2xl font-bold font-mono text-slate-900 mt-1 tabular-nums">3</div>
                        <div class="text-[11px] text-slate-500 mt-0.5">Menunggu konfirmasi</div>
                    </div>
                </div>
            </section>

        </div>

        {{-- KOLOM KANAN: AGENDA & DOKUMEN ================= --}}
        <div class="lg:col-span-4 space-y-6">

            {{-- Agenda Sidang Dinamis --}}
            <section class="rounded-md border border-slate-200 bg-white">
                <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                    <div>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700">Agenda Sidang</h2>
                        <p class="text-[11px] text-slate-500 mt-0.5 font-mono tabular-nums" x-text="meetingsList.length + ' agenda terjadwal'"></p>
                    </div>
                    <button @click="showAddMeetingModal = true" type="button"
                            class="inline-flex items-center gap-1.5 rounded border border-slate-300 px-2.5 py-1.5 text-xs font-semibold text-slate-800 hover:bg-slate-50 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Sidang Baru
                    </button>
                </div>

                <div class="divide-y divide-slate-100 max-h-[480px] overflow-y-auto">
                    <template x-for="mtg in meetingsList" :key="mtg.id">
                        <article class="px-4 py-3.5"
                                 :class="mtg.status === 'Sedang Berlangsung' ? 'bg-amber-50/40' : ''">
                            <div class="flex items-center justify-between gap-2 mb-1.5">
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-sm border uppercase tracking-wider"
                                      :class="mtg.status === 'Sedang Berlangsung'
                                          ? 'border-amber-200 bg-amber-100 text-amber-900'
                                          : 'border-slate-200 bg-slate-50 text-slate-600'"
                                      x-text="mtg.status">
                                </span>
                                <span class="font-mono text-xs font-semibold text-slate-600 tabular-nums" x-text="mtg.session_code"></span>
                            </div>
                            <h3 class="text-xs font-bold text-slate-900 leading-snug" x-text="mtg.title"></h3>
                            <dl class="text-[11px] text-slate-500 mt-2 space-y-0.5">
                                <div class="flex gap-1.5">
                                    <dt class="shrink-0">Jadwal:</dt>
                                    <dd class="font-mono tabular-nums"><span x-text="mtg.date"></span> &bull; <span x-text="mtg.time"></span></dd>
                                </div>
                                <div class="flex gap-1.5">
                                    <dt class="shrink-0">Ruangan:</dt>
                                    <dd x-text="mtg.room"></dd>
                                </div>
                            </dl>
                        </article>
                    </template>
                </div>
            </section>

            {{-- Ekspor Dokumen Resmi --}}
            <section class="rounded-md border border-slate-200 bg-white p-5">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700">Berita Acara &amp; Daftar Hadir</h2>
                <p class="text-xs text-slate-500 leading-relaxed mt-1 mb-4">
                    Penerbitan dokumen rekapitulasi kehadiran resmi untuk lampiran risalah sidang.
                </p>

                <div class="flex flex-col gap-2">
                    <button type="button" class="w-full rounded border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-800 hover:bg-slate-50 flex items-center justify-between transition-colors">
                        <span>Cetak Berita Acara Sidang</span>
                        <span class="text-[10px] font-mono text-slate-500">PDF</span>
                    </button>
                    <button type="button" class="w-full rounded border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-800 hover:bg-slate-50 flex items-center justify-between transition-colors">
                        <span>Ekspor Daftar Presensi</span>
                        <span class="text-[10px] font-mono text-slate-500">XLSX / CSV</span>
                    </button>
                </div>
            </section>
        </div>

    </div>

    {{-- ================= MODAL: TAMBAH JADWAL SIDANG BARU ================= --}}
    <div x-show="showAddMeetingModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 flex items-center justify-center p-4">
        <div class="bg-white rounded-md border border-slate-200 shadow-sm max-w-lg w-full p-6 space-y-4" @click.away="showAddMeetingModal = false">
            <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                <h2 class="text-base font-bold text-polines-navy">Jadwalkan Sidang Senat Baru</h2>
                <button @click="showAddMeetingModal = false" type="button" aria-label="Tutup" class="text-slate-400 hover:text-slate-700 text-sm font-bold">&#10005;</button>
            </div>

            <div class="space-y-3 text-xs">
                <div>
                    <label class="block font-semibold text-slate-600 uppercase tracking-wider mb-1">Judul / Agenda Sidang</label>
                    <input type="text" x-model="newMeeting.title" placeholder="Contoh: Rapat Pleno Penetapan Standar Kurikulum Vokasi 2026" class="w-full rounded border border-slate-300 px-3 py-2.5 outline-none focus:border-polines-blue focus:ring-1 focus:ring-polines-blue">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-600 uppercase tracking-wider mb-1">Jenis Sidang</label>
                        <select x-model="newMeeting.type" class="w-full rounded border border-slate-300 px-3 py-2.5 outline-none focus:border-polines-blue focus:ring-1 focus:ring-polines-blue bg-white">
                            <option value="Sidang Pleno">Sidang Pleno</option>
                            <option value="Rapat Komisi I">Rapat Komisi I (Akademik)</option>
                            <option value="Rapat Komisi II">Rapat Komisi II (Keuangan)</option>
                            <option value="Rapat Komisi III">Rapat Komisi III (Kemahasiswaan)</option>
                            <option value="Rapat Komisi IV">Rapat Komisi IV (SDM)</option>
                            <option value="Rapat Komisi V">Rapat Komisi V (Etika)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-600 uppercase tracking-wider mb-1">Tanggal</label>
                        <input type="date" x-model="newMeeting.date" class="w-full rounded border border-slate-300 px-3 py-2.5 outline-none focus:border-polines-blue focus:ring-1 focus:ring-polines-blue tabular-nums">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-600 uppercase tracking-wider mb-1">Waktu / Jam Sidang</label>
                    <input type="text" x-model="newMeeting.time" placeholder="Contoh: 09:00 - 12:00 WIB" class="w-full rounded border border-slate-300 px-3 py-2.5 outline-none focus:border-polines-blue focus:ring-1 focus:ring-polines-blue">
                </div>

                <div>
                    <label class="block font-semibold text-slate-600 uppercase tracking-wider mb-1">Ruangan / Tempat Sidang</label>
                    <input type="text" x-model="newMeeting.room" class="w-full rounded border border-slate-300 px-3 py-2.5 outline-none focus:border-polines-blue focus:ring-1 focus:ring-polines-blue">
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-4">
                <button @click="showAddMeetingModal = false" type="button" class="rounded border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                    Batal
                </button>
                <button @click="saveNewMeeting" type="button" class="rounded bg-polines-navy px-5 py-2 text-xs font-semibold text-white hover:bg-polines-navyDark">
                    Simpan Agenda Sidang
                </button>
            </div>
        </div>
    </div>

    {{-- ================= MODAL: GELAR VOTING BARU ================= --}}
    <div x-show="showNewVoteModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 flex items-center justify-center p-4">
        <div class="bg-white rounded-md border border-slate-200 shadow-sm max-w-lg w-full p-6 space-y-4" @click.away="showNewVoteModal = false">
            <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                <h2 class="text-base font-bold text-polines-navy">Gelar Sesi Pemungutan Suara Baru</h2>
                <button @click="showNewVoteModal = false" type="button" aria-label="Tutup" class="text-slate-400 hover:text-slate-700 text-sm font-bold">&#10005;</button>
            </div>

            <div class="space-y-3 text-xs">
                <div>
                    <label class="block font-semibold text-slate-600 uppercase tracking-wider mb-1">Pokok Putusan / Pertanyaan Voting</label>
                    <input type="text" x-model="newVoteForm.title" placeholder="Contoh: Pengesahan Draf Rencana Strategis (Renstra) Polines 2026-2030" class="w-full rounded border border-slate-300 px-3 py-2.5 outline-none focus:border-polines-blue focus:ring-1 focus:ring-polines-blue">
                </div>

                <div>
                    <label class="block font-semibold text-slate-600 uppercase tracking-wider mb-1">Deskripsi / Penjelasan Pasal</label>
                    <textarea rows="3" x-model="newVoteForm.description" placeholder="Jelaskan dasar pertimbangan putusan sidang..." class="w-full rounded border border-slate-300 px-3 py-2.5 outline-none focus:border-polines-blue focus:ring-1 focus:ring-polines-blue"></textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-4">
                <button @click="showNewVoteModal = false" type="button" class="rounded border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                    Batal
                </button>
                <button @click="deployNewVote" type="button" class="rounded bg-polines-navy px-5 py-2 text-xs font-semibold text-white hover:bg-polines-navyDark">
                    Luncurkan ke Bilik Suara
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
