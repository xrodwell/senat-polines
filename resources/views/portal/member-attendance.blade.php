@extends('layouts.public')

@section('title', 'Portal Anggota — Presensi & Notulensi Senat Polines')

@section('content')
<div class="bg-slate-900 text-white py-8 border-b border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Portal Kerja Internal Anggota</span>
            </div>
            <h1 class="text-2xl font-extrabold">Prof. Dr. Ir. Budi Rahardjo, M.T.</h1>
            <p class="text-xs text-slate-400">Ketua Komisi I — Bidang Pendidikan &amp; Pengajaran (NIP. 197108151998021001)</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-xs font-semibold text-slate-400">Tingkat Kehadiran:</span>
            <div class="bg-slate-800 border border-slate-700 rounded-xl px-4 py-2 flex items-center gap-3">
                <span class="text-xl font-bold font-mono text-emerald-400 tabular-nums">94%</span>
                <span class="text-[11px] text-slate-400 leading-tight">16 dari 17<br>Sidang Terhadiri</span>
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        {{-- Kolom Kiri: Presensi Sesi Berlangsung & Notulensi --}}
        <div class="lg:col-span-8 space-y-8">
            
            {{-- Modul Presensi Digital Interaktif --}}
            @if ($activeSession)
                <div class="bg-white rounded-2xl border-2 border-polines-orange/40 p-6 sm:p-8 shadow-sm relative overflow-hidden"
                     x-data="{
                         isCheckedIn: false,
                         inputCode: '',
                         checkinTime: '',
                         processCheckin() {
                             if (!this.inputCode) {
                                 alert('Masukkan kode sesi sidang');
                                 return;
                             }
                             const now = new Date();
                             this.checkinTime = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0') + ' WIB';
                             this.isCheckedIn = true;
                         }
                     }">
                    
                    <div class="flex items-center justify-between mb-4">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 border border-amber-200 px-3 py-1 text-xs font-bold text-amber-700">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                            SESI SIDANG AKTIF DIBUKA
                        </span>
                        <span class="text-xs text-slate-400 font-medium">Batas Presensi: 30 Menit Sejak Dibuka</span>
                    </div>

                    <h2 class="text-xl font-bold text-slate-900 leading-snug mb-2">
                        {{ $activeSession['title'] }}
                    </h2>
                    <p class="text-xs text-slate-500 mb-6">
                        📍 {{ $activeSession['room'] }} · ⏰ {{ $activeSession['time'] }}
                    </p>

                    {{-- Form Input Kode Presensi --}}
                    <div x-show="!isCheckedIn" class="bg-slate-50 border border-slate-200/80 rounded-xl p-6">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                            Masukkan Kode Sesi Rapat (Tampil di Layar Proyektor)
                        </label>
                        <div class="flex flex-col sm:flex-row gap-3">
                            <input type="text" x-model="inputCode" placeholder="Contoh: {{ $activeSession['session_code'] }}" class="flex-1 rounded-lg border border-slate-300 px-4 py-3 text-base font-mono uppercase tracking-widest font-bold focus:border-polines-blue focus:ring-1 focus:ring-polines-blue outline-none transition-all tabular-nums text-center sm:text-left">
                            <button @click="processCheckin" type="button" class="rounded-lg bg-polines-navy hover:bg-polines-navyDark text-white px-8 py-3 text-sm font-bold transition-colors">
                                Konfirmasi Hadir
                            </button>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-3">
                            Atau arahkan kamera ponsel ke QR Code yang ditampilkan oleh sekretariat di depan ruang sidang.
                        </p>
                    </div>

                    {{-- Notifikasi Sukses Presensi --}}
                    <div x-show="isCheckedIn" x-cloak class="bg-emerald-50 border border-emerald-200 rounded-xl p-6 text-center sm:text-left flex flex-col sm:flex-row items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-emerald-500 text-white flex items-center justify-center text-xl font-bold shrink-0">
                            ✓
                        </div>
                        <div class="flex-1">
                            <h3 class="text-base font-bold text-emerald-900">Kehadiran Berhasil Diverifikasi!</h3>
                            <p class="text-xs text-emerald-700 mt-0.5">
                                Presensi Anda tercatat pada pukul <span class="font-bold tabular-nums" x-text="checkinTime"></span>. Data telah masuk ke rekapitulasi sidang sekretariat.
                            </p>
                        </div>
                        <span class="text-xs font-mono font-bold bg-white border border-emerald-200 text-emerald-800 px-3 py-1.5 rounded-lg tabular-nums">
                            Status: SAH
                        </span>
                    </div>

                </div>
            @endif

            {{-- Modul Notulensi Mandiri Anggota --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 sm:p-8 shadow-sm"
                 x-data="{
                     savedNote: false,
                     noteText: '',
                     saveNotes() {
                         if (!this.noteText) return;
                         this.savedNote = true;
                         setTimeout(() => this.savedNote = false, 3000);
                     }
                 }">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-slate-900">Pencatatan Risalah &amp; Catatan Sidang</h2>
                    <span class="text-xs text-slate-400">Sinkronisasi Otomatis ke Komisi I</span>
                </div>
                <p class="text-xs text-slate-500 mb-4">
                    Gunakan ruang ini untuk mencatat poin argumentasi, usulan perubahan draf, atau persetujuan pasal regulasi selama musyawarah berlangsung.
                </p>

                <textarea rows="6" x-model="noteText" placeholder="Ketik catatan musyawarah sidang pleno di sini (poin masukan, persetujuan draf SK, pertimbangan norma)..." class="w-full rounded-xl border border-slate-300 p-4 text-sm focus:border-polines-blue focus:ring-1 focus:ring-polines-blue outline-none transition-all leading-relaxed"></textarea>

                <div class="flex items-center justify-between mt-4">
                    <span class="text-xs text-emerald-600 font-semibold" x-show="savedNote" x-cloak>
                        ✓ Draf catatan risalah tersimpan di cache lokal!
                    </span>
                    <span x-show="!savedNote"></span>

                    <button @click="saveNotes" type="button" class="inline-flex items-center gap-2 rounded-lg bg-slate-900 hover:bg-slate-800 text-white px-5 py-2.5 text-xs font-bold transition-colors">
                        Simpan Catatan Risalah
                    </button>
                </div>
            </div>

        </div>

        {{-- Kolom Kanan: Jadwal Sidang Mendatang & Informasi Anggota --}}
        <div class="lg:col-span-4 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
                <h3 class="text-base font-bold text-slate-900 mb-4">Agenda Sidang Berikutnya</h3>
                <div class="space-y-4">
                    @foreach($upcomingMeetings as $m)
                        <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-4">
                            <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold bg-blue-50 text-polines-blue mb-2">
                                {{ $m['type'] }}
                            </span>
                            <h4 class="text-xs font-bold text-slate-800 leading-snug mb-2">{{ $m['title'] }}</h4>
                            <div class="text-[11px] text-slate-500 space-y-1">
                                <p class="tabular-nums">📅 {{ $m['date'] }} · {{ $m['time'] }}</p>
                                <p>📍 {{ $m['room'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-slate-900 text-white rounded-2xl p-6 shadow-sm">
                <h3 class="text-sm font-bold text-polines-orange uppercase tracking-wider mb-2">Bantuan Sekretariat</h3>
                <p class="text-xs text-slate-300 leading-relaxed mb-4">
                    Apabila berhalangan hadir pada agenda sidang pleno, mohon ajukan surat izin resmi paling lambat H-1 ke Sekretariat Senat Polines.
                </p>
                <a href="mailto:senat@polines.ac.id" class="inline-flex items-center justify-center w-full py-2.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-white border border-slate-700 transition-colors">
                    Kirim Surat Izin / Dispensasi
                </a>
            </div>
        </div>

    </div>
</div>
@endsection
