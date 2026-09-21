@extends('layouts.public')

@section('title', 'Kanal Aspirasi Civitas Akademika — Senat Akademik Polines')

@section('content')
<div class="bg-polines-navy text-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-300 mb-2">Transparansi &amp; Akuntabilitas</p>
        <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">Kanal Aspirasi Civitas Akademika</h1>
        <p class="mt-3 text-base text-blue-100/90 max-w-2xl">
            Sampaikan usulan, masukan kurikulum, kritik konstruktif, atau aspirasi fasilitas akademik langsung ke Komisi terkait Senat Akademik Polines.
        </p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        {{-- Form Aspirasi Interaktif --}}
        <div class="lg:col-span-8">
            <div class="bg-white rounded-2xl border border-slate-200/80 p-8 shadow-sm"
                 x-data="{
                     submitted: false,
                     ticket: '',
                     copied: false,
                     formData: {
                         category: 'Mahasiswa',
                         jurusan: 'Teknik Elektro',
                         committee: 'Komisi I — Bidang Pendidikan & Pengajaran',
                         subject: '',
                         description: ''
                     },
                     submitForm() {
                         if (!this.formData.subject || !this.formData.description) {
                             alert('Mohon lengkapi subjek dan deskripsi aspirasi Anda.');
                             return;
                         }
                         this.ticket = 'ASP-POLINES-2026-' + Math.floor(1000 + Math.random() * 9000);
                         this.submitted = true;
                     },
                     copyTicket() {
                         navigator.clipboard.writeText(this.ticket);
                         this.copied = true;
                         setTimeout(() => this.copied = false, 2500);
                     }
                 }">
                
                {{-- Template Ketika Belum Submit --}}
                <div x-show="!submitted">
                    <h2 class="text-xl font-bold text-slate-900 mb-2">Formulir Penyampaian Aspirasi</h2>
                    <p class="text-sm text-slate-500 mb-6">Aspirasi Anda akan diproses dan didisposisikan secara otomatis ke Komisi yang berwenang.</p>

                    <form @submit.prevent="submitForm" class="space-y-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-2">Kategori Pengirim</label>
                                <select x-model="formData.category" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-polines-blue focus:ring-1 focus:ring-polines-blue outline-none transition-all">
                                    <option value="Mahasiswa">Mahasiswa Aktif</option>
                                    <option value="Dosen">Dosen / Tenaga Pendidik</option>
                                    <option value="Tendik">Tenaga Kependidikan</option>
                                    <option value="Alumni">Alumni / Mitra Industri</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-2">Jurusan / Unit Kerja</label>
                                <select x-model="formData.jurusan" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-polines-blue focus:ring-1 focus:ring-polines-blue outline-none transition-all">
                                    <option value="Teknik Elektro">Teknik Elektro</option>
                                    <option value="Teknik Mesin">Teknik Mesin</option>
                                    <option value="Teknik Sipil">Teknik Sipil</option>
                                    <option value="Akuntansi">Akuntansi</option>
                                    <option value="Administrasi Bisnis">Administrasi Bisnis</option>
                                    <option value="Pusat / Unit Lainnya">Pusat / Unit Pelaksana Teknis</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-2">Komisi Tujuan</label>
                            <select x-model="formData.committee" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-polines-blue focus:ring-1 focus:ring-polines-blue outline-none transition-all">
                                <option value="Komisi I — Bidang Pendidikan & Pengajaran">Komisi I — Bidang Pendidikan & Pengajaran (Kurikulum, MBKM, Lab)</option>
                                <option value="Komisi II — Bidang Penelitian & Pengabdian Masyarakat">Komisi II — Bidang Riset & Pengabdian (Paten, Hilirisasi)</option>
                                <option value="Komisi III — Bidang Kelembagaan & Sumber Daya">Komisi III — Bidang Sarana & Pembukaan Prodi Baru</option>
                                <option value="Komisi IV — Bidang Kemahasiswaan & Alumni">Komisi IV — Bidang Kegiatan Ormawa & Jejaring Karir</option>
                                <option value="Komisi V — Bidang Penegakan Etika & Tata Tertib">Komisi V — Bidang Etika Akademik & Disiplin</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-2">Judul / Subjek Pokok Aspirasi</label>
                            <input type="text" x-model="formData.subject" placeholder="Contoh: Usulan Penambahan Jam Praktik Laboratorium PLC" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-polines-blue focus:ring-1 focus:ring-polines-blue outline-none transition-all">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-2">Uraian Rinci Aspirasi</label>
                            <textarea rows="5" x-model="formData.description" placeholder="Jelaskan latar belakang, kendala faktual, dan solusi yang diusulkan..." class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-polines-blue focus:ring-1 focus:ring-polines-blue outline-none transition-all"></textarea>
                        </div>

                        <div class="flex items-center justify-between pt-2">
                            <span class="text-xs text-slate-500">Identitas pengirim dilindungi dalam kerangka etik senat.</span>
                            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-polines-orange hover:bg-[#d95f14] px-6 py-3 text-sm font-bold text-white transition-colors duration-150">
                                Kirim Aspirasi
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Template Notifikasi Sukses --}}
                <div x-show="submitted" x-cloak class="text-center py-8">
                    <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-2">Aspirasi Berhasil Didaftarkan!</h3>
                    <p class="text-slate-600 text-sm max-w-md mx-auto mb-6">
                        Aspirasi Anda telah masuk ke sistem sekretariat dan dijadwalkan dalam agenda telaah Komisi terkait. Simpan kode tiket berikut:
                    </p>

                    <div class="inline-flex items-center gap-3 bg-slate-50 border border-slate-200 rounded-xl px-6 py-4 mb-6">
                        <span class="text-xl font-mono font-bold text-polines-navy tracking-wider tabular-nums" x-text="ticket"></span>
                        <button @click="copyTicket" class="text-xs bg-white border border-slate-200 hover:border-slate-300 text-slate-700 px-3 py-1.5 rounded-lg font-medium transition-all">
                            <span x-text="copied ? 'Tersalin! ✓' : 'Salin Tiket'"></span>
                        </button>
                    </div>

                    <div class="flex flex-wrap items-center justify-center gap-4">
                        <a :href="'{{ route('public.aspirations.track') }}?ticket=' + ticket" class="inline-flex items-center gap-2 rounded-lg bg-polines-navy hover:bg-polines-navyDark px-6 py-2.5 text-sm font-semibold text-white transition-colors">
                            Lacak Status Tiket Sekarang
                        </a>
                        <button @click="submitted = false; formData.subject = ''; formData.description = ''" class="text-sm font-semibold text-slate-600 hover:text-slate-900">
                            Kirim Aspirasi Baru
                        </button>
                    </div>
                </div>

            </div>
        </div>

        {{-- Sidebar Lacak Cepat & Informasi --}}
        <div class="lg:col-span-4 space-y-6">
            {{-- Kotak Lacak Cepat --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
                <h3 class="text-base font-bold text-slate-900 mb-2">Lacak Tiket Aspirasi</h3>
                <p class="text-xs text-slate-500 mb-4">Masukkan nomor tiket untuk memantau disposisi dan tanggapan resmi komisi.</p>
                
                <form action="{{ route('public.aspirations.track') }}" method="GET" class="space-y-3">
                    <input type="text" name="ticket" placeholder="Contoh: ASP-POLINES-2026-0042" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-polines-blue focus:ring-1 focus:ring-polines-blue outline-none transition-all font-mono tabular-nums">
                    <button type="submit" class="w-full rounded-lg bg-polines-navy hover:bg-polines-navyDark text-white py-2.5 text-sm font-semibold transition-colors">
                        Periksa Status
                    </button>
                </form>
            </div>

            {{-- Rekap Aspirasi Ditindaklanjuti --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
                <h3 class="text-base font-bold text-slate-900 mb-4">Aspirasi Terkini</h3>
                <div class="divide-y divide-slate-100">
                    @foreach($aspirations as $asp)
                        <div class="py-3.5 first:pt-0 last:pb-0">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="font-mono text-xs font-bold text-polines-navy tabular-nums">{{ $asp['ticket_code'] }}</span>
                                <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $asp['status'] === 'Ditindaklanjuti Komisi' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $asp['status'] }}
                                </span>
                            </div>
                            <p class="text-xs font-medium text-slate-800 line-clamp-2 leading-relaxed">{{ $asp['subject'] }}</p>
                            <p class="text-[11px] text-slate-400 mt-1">{{ $asp['committee_target'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
