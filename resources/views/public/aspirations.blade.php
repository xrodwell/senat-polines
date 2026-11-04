@extends('layouts.public')

@section('title', 'Kanal Aspirasi — Senat Akademik Politeknik Negeri Semarang')
@section('description', 'Sampaikan saran, masukan, atau pengaduan terkait penyelenggaraan akademik Politeknik Negeri Semarang secara aman, tertib, dan dapat ditindaklanjuti melalui Kanal Aspirasi Senat Akademik.')

@section('content')

    {{-- ============ HERO — Figma 2019:225 ============ --}}
    <section class="bg-senat-hero">
        <div class="senat-shell py-14 lg:py-[62px]">
            <p class="text-[11px] font-bold uppercase tracking-[0.1em] text-senat-sky">
                Ruang Suara Civitas Akademika
            </p>
            <h1 class="mt-4 text-[34px] font-bold leading-[1.1] text-white sm:text-[43px]">
                Kanal Aspirasi
            </h1>
            <p class="mt-4 max-w-[670px] text-[15px] leading-[1.45] text-senat-skySoft">
                Sampaikan saran, masukan, atau pengaduan terkait penyelenggaraan akademik secara aman, tertib, dan dapat ditindaklanjuti.
            </p>
            <div class="mt-8 flex flex-wrap items-center gap-3">
                <a href="#form-aspirasi"
                   class="inline-flex h-11 items-center gap-2 rounded-lg bg-senat-orange px-5 text-[12px] font-bold text-senat-primary shadow-sm transition-all duration-150 hover:bg-senat-orangeHover">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span>Isi Formulir Aspirasi</span>
                </a>
                <a href="{{ route('public.aspirations.track') }}"
                   class="inline-flex h-11 items-center gap-2 rounded-lg border border-white/25 bg-white/10 px-5 text-[12px] font-semibold text-white backdrop-blur-sm transition-all duration-150 hover:bg-white/20">
                    <svg class="h-4 w-4 text-senat-sky" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <span>Lacak Status Tiket</span>
                </a>
            </div>
        </div>
    </section>

    {{-- ============ ALUR PENANGANAN ASPIRASI — Figma 2019:231/232 ============ --}}
    @php
        $processSteps = [
            ['index' => 1, 'title' => 'Kirim Aspirasi', 'detail' => 'Lengkapi data dan uraian yang jelas.'],
            ['index' => 2, 'title' => 'Verifikasi', 'detail' => 'Sekretariat memeriksa kelengkapan.'],
            ['index' => 3, 'title' => 'Telaah Komisi', 'detail' => 'Aspirasi diteruskan ke komisi terkait.'],
            ['index' => 4, 'title' => 'Tindak Lanjut', 'detail' => 'Status dapat dilacak dengan nomor tiket.'],
        ];
    @endphp

    <section class="senat-shell pt-16">
        <p class="text-[11px] font-bold uppercase tracking-[0.1em] text-senat-orange">
            Alur Penanganan Aspirasi
        </p>
        <h2 class="mt-4 max-w-[730px] text-[24px] font-bold leading-[1.15] text-senat-ink sm:text-[27px]">
            Setiap suara tercatat dan ditindaklanjuti
        </h2>

        <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($processSteps as $step)
                <div class="flex min-h-[160px] flex-col rounded-xl border border-[#d9e3f0] bg-white p-[18px]">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-senat-hero text-[12px] font-bold text-white tabular-nums">
                        {{ $step['index'] }}
                    </span>
                    <p class="mt-4 text-[15px] font-bold leading-[1.15] text-senat-ink">{{ $step['title'] }}</p>
                    <p class="mt-2 text-[11px] leading-[1.35] text-senat-body">{{ $step['detail'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ============ ASPIRASI TERKINI (data $aspirations) ============ --}}
    @if (! empty($aspirations))
        <section class="senat-shell pt-16">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.1em] text-senat-orange">Aspirasi Terkini</p>
                    <h2 class="mt-3 text-[22px] font-bold leading-[1.15] text-senat-ink">
                        Contoh aspirasi yang telah tercatat
                    </h2>
                </div>
                <a href="{{ route('public.aspirations.track') }}"
                   class="text-[12px] font-semibold text-senat-link hover:text-senat-primary">
                    Lacak status tiket →
                </a>
            </div>

            <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2">
                @foreach ($aspirations as $asp)
                    @php
                        $aspOk = str_contains($asp['status'] ?? '', 'Ditindaklanjuti');
                        $badgeClass = $aspOk
                            ? 'border-[#86efac] bg-[#f0fdf4] text-[#166534]'
                            : 'border-[#fcd34d] bg-[#fffbeb] text-[#92400e]';
                        $dotClass = $aspOk ? 'bg-[#16a34a]' : 'bg-[#f59e0b]';
                    @endphp
                    <article class="rounded-xl border border-[#d9e3f0] bg-white p-5">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <span class="font-mono text-[12px] font-bold tracking-wide text-senat-primary tabular-nums">
                                {{ $asp['ticket_code'] }}
                            </span>
                            <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-[3px] text-[10px] font-bold uppercase tracking-[0.06em] {{ $badgeClass }}">
                                <span class="h-1.5 w-1.5 rounded-full {{ $dotClass }}" aria-hidden="true"></span>
                                {{ $asp['status'] }}
                            </span>
                        </div>
                        <h3 class="mt-3 text-[13px] font-bold leading-snug text-senat-ink">
                            {{ $asp['subject'] }}
                        </h3>
                        <p class="mt-1.5 line-clamp-2 text-[11px] leading-relaxed text-senat-body">
                            {{ $asp['description'] }}
                        </p>
                        <p class="mt-3 border-t border-senat-line pt-3 text-[10.5px] font-medium text-senat-muted">
                            {{ $asp['committee_target'] }} · {{ $asp['submitted_at'] }} WIB
                        </p>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ============ FORM SECTION — Figma 2019:253 ============ --}}
    <section id="form-aspirasi" class="senat-shell py-16 scroll-mt-6">
        <div class="rounded-2xl border border-[#d9e3f0] bg-white p-6 sm:p-8"
             x-data="{
                 submitted: false,
                 loading: false,
                 errorMessage: '',
                 ticket: '',
                 copied: false,

                 // State Verifikasi OTP Email
                 emailVerified: false,
                 sendingOtp: false,
                 verifyingOtp: false,
                 otpSent: false,
                 otpDigits: ['', '', '', '', '', ''],
                 otpCooldown: 0,
                 otpTimer: null,
                 otpSuccessMessage: '',
                 otpErrorMessage: '',

                 get combinedOtp() {
                     return this.otpDigits.join('');
                 },

                 formData: {
                     name: '',
                     email: '',
                     category: 'Mahasiswa',
                     jurusan: 'Teknik Elektro',
                     committee: '',
                     subject: '',
                     description: ''
                 },

                 async sendOtp() {
                     if (!this.formData.email) {
                         this.otpErrorMessage = 'Masukkan alamat email institusi (@polines.ac.id) Anda terlebih dahulu.';
                         return;
                     }
                     this.sendingOtp = true;
                     this.errorMessage = '';
                     this.otpErrorMessage = '';
                     this.otpSuccessMessage = '';
                     this.otpDigits = ['', '', '', '', '', ''];
                     try {
                         const res = await fetch('{{ route('public.aspirations.send-otp') }}', {
                             method: 'POST',
                             headers: {
                                 'Content-Type': 'application/json',
                                 'Accept': 'application/json',
                                 'X-CSRF-TOKEN': '{{ csrf_token() }}'
                             },
                             body: JSON.stringify({ email: this.formData.email })
                         });
                         const data = await res.json();
                         if (res.ok && data.success) {
                             this.otpSent = true;
                             this.otpSuccessMessage = data.message;
                             this.otpCooldown = 60;
                             if (this.otpTimer) clearInterval(this.otpTimer);
                             this.otpTimer = setInterval(() => {
                                 if (this.otpCooldown > 0) {
                                     this.otpCooldown--;
                                 } else {
                                     clearInterval(this.otpTimer);
                                 }
                             }, 1000);
                             this.$nextTick(() => {
                                 const first = document.getElementById('otp-digit-0');
                                 if (first) first.focus();
                             });
                         } else {
                             this.otpErrorMessage = data.message || 'Gagal mengirim kode verifikasi ke email.';
                         }
                     } catch (err) {
                         this.otpErrorMessage = 'Terjadi gangguan jaringan saat mengirim kode verifikasi.';
                     } finally {
                         this.sendingOtp = false;
                     }
                 },

                 handleOtpInput(index, event) {
                     const val = event.target.value;
                     const digit = val.replace(/\D/g, '').slice(-1);
                     this.otpDigits[index] = digit;
                     event.target.value = digit;
                     this.otpErrorMessage = '';

                     if (digit && index < 5) {
                         this.$nextTick(() => {
                             const next = document.getElementById('otp-digit-' + (index + 1));
                             if (next) next.focus();
                         });
                     }

                     if (this.combinedOtp.length === 6) {
                         this.verifyOtp();
                     }
                 },

                 handleOtpKeyDown(index, event) {
                     if (event.key === 'Backspace' && !this.otpDigits[index] && index > 0) {
                         this.otpDigits[index - 1] = '';
                         const prev = document.getElementById('otp-digit-' + (index - 1));
                         if (prev) {
                             prev.focus();
                             prev.value = '';
                         }
                     } else if (event.key === 'ArrowLeft' && index > 0) {
                         const prev = document.getElementById('otp-digit-' + (index - 1));
                         if (prev) prev.focus();
                     } else if (event.key === 'ArrowRight' && index < 5) {
                         const next = document.getElementById('otp-digit-' + (index + 1));
                         if (next) next.focus();
                     }
                 },

                 handleOtpPaste(event) {
                     event.preventDefault();
                     const paste = (event.clipboardData || window.clipboardData).getData('text');
                     const clean = paste.replace(/\D/g, '').slice(0, 6);
                     if (!clean) return;
                     for (let i = 0; i < 6; i++) {
                         this.otpDigits[i] = clean[i] || '';
                         const el = document.getElementById('otp-digit-' + i);
                         if (el) el.value = clean[i] || '';
                     }
                     const targetIdx = Math.min(clean.length, 5);
                     const targetEl = document.getElementById('otp-digit-' + targetIdx);
                     if (targetEl) targetEl.focus();

                     if (clean.length === 6) {
                         this.verifyOtp();
                     }
                 },

                 async verifyOtp() {
                     const code = this.combinedOtp;
                     if (!code || code.length < 6) {
                         this.otpErrorMessage = 'Masukkan 6 digit kode verifikasi secara lengkap.';
                         return;
                     }
                     this.verifyingOtp = true;
                     this.otpErrorMessage = '';
                     try {
                         const res = await fetch('{{ route('public.aspirations.verify-otp') }}', {
                             method: 'POST',
                             headers: {
                                 'Content-Type': 'application/json',
                                 'Accept': 'application/json',
                                 'X-CSRF-TOKEN': '{{ csrf_token() }}'
                             },
                             body: JSON.stringify({
                                 email: this.formData.email,
                                 otp: code
                             })
                         });
                         const data = await res.json();
                         if (res.ok && data.success) {
                             this.emailVerified = true;
                             this.otpSuccessMessage = 'Email resmi institusi berhasil diverifikasi!';
                         } else {
                             this.otpErrorMessage = data.message || 'Kode verifikasi salah atau telah kedaluwarsa.';
                         }
                     } catch (err) {
                         this.otpErrorMessage = 'Terjadi gangguan jaringan saat memverifikasi kode verifikasi.';
                     } finally {
                         this.verifyingOtp = false;
                     }
                 },

                 resetVerification() {
                     this.emailVerified = false;
                     this.otpSent = false;
                     this.otpDigits = ['', '', '', '', '', ''];
                     this.otpSuccessMessage = '';
                     this.otpErrorMessage = '';
                     if (this.otpTimer) clearInterval(this.otpTimer);
                     this.otpCooldown = 0;
                 },

                 async submitForm() {
                     if (!this.emailVerified) {
                         this.errorMessage = 'Harap verifikasi email Anda dengan kode OTP terlebih dahulu sebelum mengirim aspirasi.';
                         return;
                     }
                     if (!this.formData.name || !this.formData.email || !this.formData.committee || !this.formData.subject || !this.formData.description) {
                         this.errorMessage = 'Mohon lengkapi semua kolom bertanda * sebelum mengirim.';
                         return;
                     }
                     this.loading = true;
                     this.errorMessage = '';
                     try {
                         const response = await fetch('{{ route('public.aspirations.submit') }}', {
                             method: 'POST',
                             headers: {
                                 'Content-Type': 'application/json',
                                 'Accept': 'application/json',
                                 'X-CSRF-TOKEN': '{{ csrf_token() }}'
                             },
                             body: JSON.stringify(this.formData)
                         });
                         const data = await response.json();
                         if (response.ok && data.success && data.ticket) {
                             this.ticket = data.ticket;
                             this.submitted = true;
                         } else {
                             this.errorMessage = data.message || 'Gagal mengirimkan aspirasi ke database. Silakan coba kembali.';
                         }
                     } catch (err) {
                         console.error('Submit aspirasi error:', err);
                         this.errorMessage = 'Terjadi kesalahan jaringan saat mengirimkan aspirasi.';
                     } finally {
                         this.loading = false;
                     }
                 },

                 copyTicket() {
                     navigator.clipboard.writeText(this.ticket);
                     this.copied = true;
                     setTimeout(() => this.copied = false, 2500);
                 }
             }">

            {{-- Template: Formulir --}}
            <div x-show="!submitted">
                <h2 class="text-[22px] font-bold leading-[1.15] text-[#003c75] sm:text-[25px]">
                    Sampaikan Aspirasi Anda
                </h2>
                <p class="mt-2 text-[11px] leading-[1.2] text-senat-body">
                    Isilah formulir berikut dengan informasi yang benar. Kolom bertanda * wajib diisi.
                </p>

                <div x-show="errorMessage" x-cloak class="mt-4 rounded-xl border border-red-200 bg-red-50 p-4 text-[12px] text-red-700">
                    <span x-text="errorMessage"></span>
                </div>

                <form @submit.prevent="submitForm" class="mt-10 space-y-8">

                    {{-- Baris 1 & 2: dua kolom --}}
                    <div class="grid grid-cols-1 gap-x-[42px] gap-y-8 sm:grid-cols-2">
                        <div>
                            <label for="asp-name" class="block text-[11px] font-bold leading-[1.1] text-senat-ink">Nama Lengkap *</label>
                            <input id="asp-name" name="name" type="text" required
                                   x-model="formData.name"
                                   placeholder="Masukkan nama lengkap Anda"
                                   class="mt-2 h-[46px] w-full rounded-lg border border-[#d9e3f0] bg-white px-3.5 text-[12px] text-senat-ink placeholder:text-senat-body focus:border-[#155ead] focus:outline-none focus:ring-1 focus:ring-[#155ead]">
                        </div>

                        <div>
                            <div class="flex items-center justify-between">
                                <label for="asp-email" class="block text-[11px] font-bold leading-[1.1] text-senat-ink">
                                    Email Institusi Polines *
                                </label>
                                <span x-show="emailVerified" x-cloak class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-[10.5px] font-bold text-emerald-700 border border-emerald-200">
                                    <svg class="h-3 w-3 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Email Terverifikasi
                                </span>
                            </div>
                            <div class="mt-2 flex gap-2">
                                <input id="asp-email" name="email" type="email" required
                                       x-model="formData.email"
                                       :readonly="emailVerified"
                                       placeholder="nama@polines.ac.id"
                                       :class="emailVerified ? 'bg-slate-50 text-slate-700 border-emerald-300' : 'bg-white text-senat-ink'"
                                       class="h-[46px] flex-1 rounded-lg border border-[#d9e3f0] px-3.5 text-[12px] placeholder:text-senat-body focus:border-[#155ead] focus:outline-none focus:ring-1 focus:ring-[#155ead]">
                                
                                <button type="button"
                                        x-show="!emailVerified"
                                        @click="sendOtp"
                                        :disabled="sendingOtp || otpCooldown > 0 || !formData.email"
                                        class="inline-flex h-[46px] items-center justify-center rounded-lg bg-[#155ead] px-4 text-[11.5px] font-bold text-white transition hover:bg-[#124e91] disabled:opacity-50 shrink-0">
                                    <span x-show="!sendingOtp && otpCooldown === 0">Kirim Kode OTP</span>
                                    <span x-show="sendingOtp">Mengirim ke Email...</span>
                                    <span x-show="otpCooldown > 0" x-text="'Tunggu ' + otpCooldown + 's'"></span>
                                </button>

                                <button type="button"
                                        x-show="emailVerified"
                                        @click="resetVerification"
                                        class="inline-flex h-[46px] items-center justify-center rounded-lg border border-slate-300 bg-white px-3 text-[11px] font-medium text-slate-600 hover:bg-slate-50 hover:text-red-600 shrink-0"
                                        title="Ganti Email">
                                    Ubah Email
                                </button>
                            </div>
                        </div>

                        {{-- Kotak Masukkan 6 Digit Kode OTP (Full Width Span 2 Kolom) --}}
                        <div x-show="otpSent && !emailVerified" x-cloak
                             class="sm:col-span-2 rounded-2xl border border-blue-100 bg-gradient-to-b from-blue-50/50 to-white p-6 shadow-xs transition-all">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-blue-100/70">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-[#155ead]">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="text-[13px] font-bold text-senat-ink">Verifikasi Email Institusi</h4>
                                        <p class="mt-0.5 text-[11px] text-slate-500">
                                            Kode 6 digit telah dikirim ke <span class="font-semibold text-senat-primary" x-text="formData.email"></span>. Masukkan kode untuk membuka formulir.
                                        </p>
                                    </div>
                                </div>
                                <span class="inline-flex items-center gap-1.5 self-start sm:self-auto rounded-full bg-white border border-blue-100 px-3 py-1 text-[10.5px] font-medium text-slate-600 shadow-2xs">
                                    <svg class="h-3.5 w-3.5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Berlaku 10 menit
                                </span>
                            </div>

                            {{-- 6 Input Digit Kotak Mandiri (Modern Enterprise Style) --}}
                            <div class="mt-6 flex flex-col items-center justify-center" @paste="handleOtpPaste($event)">
                                <div class="flex items-center justify-center gap-2 sm:gap-3">
                                    <template x-for="(digit, index) in otpDigits" :key="index">
                                        <div class="flex items-center">
                                            <input :id="'otp-digit-' + index"
                                                   type="text"
                                                   inputmode="numeric"
                                                   pattern="[0-9]*"
                                                   maxlength="1"
                                                   autocomplete="one-time-code"
                                                   :value="digit"
                                                   @input="handleOtpInput(index, $event)"
                                                   @keydown="handleOtpKeyDown(index, $event)"
                                                   class="h-12 w-10 sm:h-14 sm:w-12 rounded-xl border border-slate-300 bg-white text-center font-mono text-[22px] font-bold text-senat-ink shadow-xs transition-all focus:border-[#155ead] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#155ead]/20">
                                            <span x-show="index === 2" class="mx-1 text-slate-300 font-bold select-none text-xl" aria-hidden="true">—</span>
                                        </div>
                                    </template>
                                </div>

                                {{-- Pesan error / status --}}
                                <div x-show="otpErrorMessage" x-cloak class="mt-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-2 text-[11.5px] font-medium text-rose-700 flex items-center gap-2">
                                    <svg class="h-4 w-4 shrink-0 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span x-text="otpErrorMessage"></span>
                                </div>
                                <div x-show="otpSuccessMessage && !emailVerified" x-cloak class="mt-3 text-[11px] text-blue-700 font-medium" x-text="otpSuccessMessage"></div>

                                {{-- Status Kirim Ulang & Tombol Verifikasi --}}
                                <div class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-4 w-full pt-1">
                                    <button type="button"
                                            @click="verifyOtp"
                                            :disabled="verifyingOtp || combinedOtp.length < 6"
                                            class="inline-flex h-[42px] w-full sm:w-auto items-center justify-center gap-2 rounded-lg bg-[#155ead] px-8 text-[12px] font-bold text-white shadow-xs transition hover:bg-[#124e91] disabled:opacity-40">
                                        <svg x-show="verifyingOtp" class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                        </svg>
                                        <span x-text="verifyingOtp ? 'Memverifikasi...' : 'Verifikasi Kode OTP'"></span>
                                    </button>

                                    <div class="text-[11.5px] text-slate-500">
                                        <span>Belum menerima kode?</span>
                                        <button type="button"
                                                x-show="otpCooldown === 0"
                                                @click="sendOtp"
                                                class="ml-1 font-bold text-[#155ead] hover:underline">
                                            Kirim ulang
                                        </button>
                                        <span x-show="otpCooldown > 0" class="ml-1 font-medium text-slate-400">
                                            Kirim ulang dalam <strong class="tabular-nums" x-text="otpCooldown + 's'"></strong>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="asp-category" class="block text-[11px] font-bold leading-[1.1] text-senat-ink">Kategori Aspirasi *</label>
                            <select id="asp-category" name="sender_category" required
                                    x-model="formData.category"
                                    class="mt-2 h-[46px] w-full rounded-lg border border-[#d9e3f0] bg-white px-3.5 text-[12px] text-senat-ink focus:border-[#155ead] focus:outline-none focus:ring-1 focus:ring-[#155ead]">
                                <option value="Mahasiswa">Mahasiswa Aktif</option>
                                <option value="Dosen">Dosen / Tenaga Pendidik</option>
                                <option value="Tendik">Tenaga Kependidikan</option>
                                <option value="Alumni">Alumni / Mitra Industri</option>
                            </select>
                        </div>

                        <div>
                            <label for="asp-jurusan" class="block text-[11px] font-bold leading-[1.1] text-senat-ink">Unit/Program Studi Terkait</label>
                            <select id="asp-jurusan" name="jurusan"
                                    x-model="formData.jurusan"
                                    class="mt-2 h-[46px] w-full rounded-lg border border-[#d9e3f0] bg-white px-3.5 text-[12px] text-senat-ink focus:border-[#155ead] focus:outline-none focus:ring-1 focus:ring-[#155ead]">
                                <option value="Teknik Elektro">Teknik Elektro</option>
                                <option value="Teknik Mesin">Teknik Mesin</option>
                                <option value="Teknik Sipil">Teknik Sipil</option>
                                <option value="Akuntansi">Akuntansi</option>
                                <option value="Administrasi Bisnis">Administrasi Bisnis</option>
                                <option value="Pusat / Unit Lainnya">Pusat / Unit Pelaksana Teknis</option>
                            </select>
                        </div>
                    </div>

                    {{-- Komisi Tujuan — diisi dari $committees --}}
                    <div>
                        <label for="asp-committee" class="block text-[11px] font-bold leading-[1.1] text-senat-ink">Komisi Tujuan *</label>
                        <select id="asp-committee" name="committee" required
                                x-model="formData.committee"
                                class="mt-2 h-[46px] w-full rounded-lg border border-[#d9e3f0] bg-white px-3.5 text-[12px] text-senat-ink focus:border-[#155ead] focus:outline-none focus:ring-1 focus:ring-[#155ead]">
                            <option value="" disabled>Pilih komisi tujuan</option>
                            @foreach ($committees as $committee)
                                <option value="{{ $committee['name'] }}">{{ $committee['name'] }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1.5 text-[10.5px] text-senat-muted">
                            Aspirasi akan didisposisikan ke komisi yang berwenang menelaah substansinya.
                        </p>
                    </div>

                    {{-- Judul --}}
                    <div>
                        <label for="asp-subject" class="block text-[11px] font-bold leading-[1.1] text-senat-ink">Judul Aspirasi *</label>
                        <input id="asp-subject" name="subject" type="text" required
                               x-model="formData.subject"
                               placeholder="Ringkas pokok aspirasi Anda"
                               class="mt-2 h-[46px] w-full rounded-lg border border-[#d9e3f0] bg-white px-3.5 text-[12px] text-senat-ink placeholder:text-senat-body focus:border-[#155ead] focus:outline-none focus:ring-1 focus:ring-[#155ead]">
                    </div>

                    {{-- Uraian --}}
                    <div>
                        <label for="asp-description" class="block text-[11px] font-bold leading-[1.1] text-senat-ink">Uraian Aspirasi *</label>
                        <textarea id="asp-description" name="description" rows="6" required
                                  x-model="formData.description"
                                  placeholder="Jelaskan latar belakang, kondisi, dan harapan atau usulan perbaikan secara rinci."
                                  class="mt-2 h-[150px] w-full rounded-lg border border-[#d9e3f0] bg-white px-3.5 py-3 text-[12px] leading-relaxed text-senat-ink placeholder:text-senat-body focus:border-[#155ead] focus:outline-none focus:ring-1 focus:ring-[#155ead]"></textarea>
                    </div>

                    {{-- Lampiran --}}
                    <div>
                        <p class="text-[10px] font-medium text-senat-body">
                            Lampiran pendukung (opsional) &nbsp;•&nbsp; PDF, DOCX, JPG, PNG maks. 5 MB
                        </p>
                        <label class="mt-2 inline-flex h-[38px] cursor-pointer items-center gap-2 rounded-lg border border-[#a6c4e5] bg-senat-badgeBg px-3.5 text-[11px] font-semibold text-senat-link transition-colors hover:border-senat-link">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                            </svg>
                            Pilih Lampiran
                            <input type="file" name="attachment" class="hidden" accept=".pdf,.docx,.jpg,.jpeg,.png">
                        </label>
                    </div>

                    {{-- Panel Petunjuk & Kirim — Figma 2349:1264 --}}
                    <div class="rounded-2xl border border-slate-200/90 bg-slate-50/70 p-6 shadow-2xs">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-5">
                            <div class="space-y-1.5 max-w-xl">
                                <div x-show="!emailVerified" class="text-[12.5px] font-semibold text-amber-900">
                                    Lakukan verifikasi email dengan kode OTP untuk mengaktifkan tombol kirim.
                                </div>
                                <div x-show="emailVerified" x-cloak class="text-[12.5px] font-semibold text-emerald-800">
                                    Email terverifikasi. Formulir siap dikirimkan ke Sekretariat Senat Akademik.
                                </div>
                                <p class="text-[11.5px] leading-relaxed text-slate-500">
                                    Data aspirasi digunakan untuk proses telaah resmi komisi Senat Polines secara transparan dan akuntabel.
                                </p>
                            </div>

                            <div class="sm:shrink-0">
                                <button type="submit"
                                        :disabled="loading || !emailVerified"
                                        :class="!emailVerified
                                            ? 'bg-slate-200 text-slate-400 cursor-not-allowed border border-slate-300/50'
                                            : 'bg-[#155ead] text-white hover:bg-[#124e91] shadow-sm hover:shadow'"
                                        class="inline-flex h-11 w-full sm:w-auto items-center justify-center rounded-xl px-8 text-[13px] font-bold transition-all duration-150">
                                    <svg x-show="loading" class="mr-2 h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                    <span x-text="loading ? 'Mengirim aspirasi...' : 'Kirim Aspirasi'"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Template: Notifikasi Sukses --}}
            <div x-show="submitted" x-cloak class="py-6 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-[#f0fdf4] text-[#116a3c]">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h3 class="text-[22px] font-bold text-senat-ink">Aspirasi Berhasil Didaftarkan</h3>
                <p class="mx-auto mt-2 max-w-md text-[12px] leading-relaxed text-senat-body">
                    Aspirasi Anda telah masuk ke sistem sekretariat dan dijadwalkan dalam agenda telaah komisi terkait. Simpan nomor registrasi berikut untuk melacak status.
                </p>

                <div class="mx-auto mt-6 inline-flex flex-wrap items-center justify-center gap-3 rounded-xl border border-[#d9e3f0] bg-senat-surface px-6 py-4">
                    <span class="font-mono text-[18px] font-bold tracking-wider text-senat-primary tabular-nums" x-text="ticket"></span>
                    <button type="button" @click="copyTicket"
                            class="rounded-lg border border-[#d9e3f0] bg-white px-3 py-1.5 text-[11px] font-semibold text-senat-body transition-colors hover:border-senat-link hover:text-senat-link">
                        <span x-text="copied ? 'Tersalin' : 'Salin Tiket'"></span>
                    </button>
                </div>

                <div class="mt-6 flex flex-wrap items-center justify-center gap-4">
                    <a :href="'{{ route('public.aspirations.track') }}?ticket=' + ticket"
                       class="inline-flex h-10 items-center rounded-lg bg-senat-primary px-6 text-[12px] font-bold text-white transition-colors hover:bg-senat-primaryHover">
                        Lacak Status Tiket Sekarang
                    </a>
                    <button type="button"
                            @click="submitted = false; formData.subject = ''; formData.description = ''"
                            class="text-[12px] font-semibold text-senat-body hover:text-senat-ink">
                        Kirim Aspirasi Baru
                    </button>
                </div>
            </div>
        </div>
    </section>

@endsection
