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
            <a href="{{ route('public.aspirations.track') }}"
               class="mt-8 inline-flex h-10 items-center gap-1.5 rounded-lg bg-senat-orange px-5 text-[12px] font-bold text-white transition-colors duration-150 hover:bg-[#d95f14]">
                Lacak Status Tiket
                <span aria-hidden="true">›</span>
            </a>
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
    <section class="senat-shell py-16">
        <div class="rounded-2xl border border-[#d9e3f0] bg-white p-6 sm:p-8"
             x-data="{
                 submitted: false,
                 ticket: '',
                 copied: false,
                 formData: {
                     name: '',
                     email: '',
                     category: 'Mahasiswa',
                     jurusan: 'Teknik Elektro',
                     committee: '',
                     subject: '',
                     description: ''
                 },
                 submitForm() {
                     if (!this.formData.name || !this.formData.email || !this.formData.committee || !this.formData.subject || !this.formData.description) {
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

            {{-- Template: Formulir --}}
            <div x-show="!submitted">
                <h2 class="text-[22px] font-bold leading-[1.15] text-[#003c75] sm:text-[25px]">
                    Sampaikan Aspirasi Anda
                </h2>
                <p class="mt-2 text-[11px] leading-[1.2] text-senat-body">
                    Isilah formulir berikut dengan informasi yang benar. Kolom bertanda * wajib diisi.
                </p>

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
                            <label for="asp-email" class="block text-[11px] font-bold leading-[1.1] text-senat-ink">Email Institusi *</label>
                            <input id="asp-email" name="email" type="email" required
                                   x-model="formData.email"
                                   placeholder="nama@polines.ac.id"
                                   class="mt-2 h-[46px] w-full rounded-lg border border-[#d9e3f0] bg-white px-3.5 text-[12px] text-senat-ink placeholder:text-senat-body focus:border-[#155ead] focus:outline-none focus:ring-1 focus:ring-[#155ead]">
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
                        <label class="mt-2 inline-flex h-[38px] cursor-pointer items-center gap-2 rounded-[7px] border border-[#a6c4e5] bg-senat-badgeBg px-3.5 text-[11px] font-bold text-senat-link transition-colors hover:border-senat-link">
                            ↑&nbsp; Pilih Lampiran
                            <input type="file" name="attachment" class="hidden" accept=".pdf,.docx,.jpg,.jpeg,.png">
                        </label>
                    </div>

                    {{-- Panel Petunjuk & Kirim — Figma 2349:1264 --}}
                    <div class="rounded-[10px] border border-[#d9e2ef] bg-[#f5f8fc] p-5">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <p class="max-w-[740px] text-[12px] leading-[1.35] text-[#4b5565]">
                                Setelah dikirim, alamat email akan diverifikasi sebelum nomor tiket diterbitkan.
                            </p>
                            <button type="submit"
                                    class="inline-flex h-[38px] w-full items-center justify-center gap-1.5 rounded-lg bg-[#155ead] px-5 text-[12px] font-semibold text-white transition-colors duration-150 hover:bg-[#124e91] sm:w-[244px] sm:shrink-0">
                                Kirim Aspirasi
                                <span aria-hidden="true">›</span>
                            </button>
                        </div>
                        <p class="mt-4 text-[11px] leading-[1.3] text-[#667085]">
                            Data digunakan untuk proses verifikasi dan tindak lanjut aspirasi.
                        </p>
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
                        <span x-text="copied ? 'Tersalin! ✓' : 'Salin Tiket'"></span>
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
