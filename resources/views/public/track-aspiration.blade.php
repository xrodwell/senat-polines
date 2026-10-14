@extends('layouts.public')

@section('title', 'Pelacak Status Aspirasi — Senat Akademik Politeknik Negeri Semarang')
@section('description', 'Pantau transparansi penanganan aduan dan masukan Anda melalui nomor registrasi tiket resmi Senat Akademik Politeknik Negeri Semarang.')

@section('content')

    @php
        // Figma 2020:2 + 2350:1303 — PelacakTiketInline (Ready | Loading | Found | Not found)
        $asp = $aspiration ?? [
            'ticket_code' => $ticket ?: 'ASP-POLINES-2026-0042',
            'sender_category' => 'Mahasiswa',
            'jurusan' => 'Teknik Elektro (D4 TRK)',
            'committee_target' => 'Komisi Pendidikan dan Pengajaran',
            'subject' => 'Usulan Penambahan Jam Praktikum Mandiri di Laboratorium',
            'description' => 'Data rinci aspirasi belum tersedia pada pratinjau ini.',
            'status' => 'Sedang Ditelaah',
            'submitted_at' => '-',
            'response' => 'Menunggu pembaruan dari Sekretariat Senat.',
        ];

        // State awal ditentukan oleh server (rute /aspirasi/track dan /aspirasi/track/{ticket})
        $initialState = $ticket && $aspiration ? 'found' : ($ticket ? 'notfound' : 'ready');

        // Empat tahap disposisi (timeline status)
        $timeline = [
            ['title' => 'Tiket Diterima', 'detail' => 'Sekretariat mencatat aspirasi dan menerbitkan nomor registrasi tiket.'],
            ['title' => 'Verifikasi', 'detail' => 'Kelengkapan data dan identitas pengirim diperiksa oleh Sekretariat.'],
            ['title' => 'Telaah Komisi', 'detail' => 'Aspirasi diteruskan ke komisi tujuan untuk telaah substansi.'],
            ['title' => 'Tindak Lanjut', 'detail' => 'Rekomendasi dan tanggapan resmi disampaikan kepada pengirim.'],
        ];

        $statusText = $asp['status'] ?? '';
        $stage = str_contains($statusText, 'Ditindaklanjuti') ? 3
            : (str_contains($statusText, 'Telaah') || str_contains($statusText, 'Ditelaah') ? 3 : 2);
        $progress = (int) round($stage / 4 * 100);

        $statusOk = str_contains($statusText, 'Ditindaklanjuti');
        $badgeClass = $statusOk
            ? 'border-[#86efac] bg-[#f0fdf4] text-[#166534]'
            : 'border-[#fcd34d] bg-[#fffbeb] text-[#92400e]';
        $badgeDot = $statusOk ? 'bg-[#16a34a]' : 'bg-[#f59e0b]';

        $steps = [
            ['no' => '01', 'title' => 'Kirim aspirasi melalui formulir Kanal Aspirasi.', 'detail' => 'Status diperbarui oleh Sekretariat Senat sesuai proses musyawarah.'],
            ['no' => '02', 'title' => 'Simpan nomor registrasi tiket yang muncul setelah pengiriman.', 'detail' => 'Nomor registrasi menjadi kunci pelacakan tahapan disposisi.'],
            ['no' => '03', 'title' => 'Masukkan kode tiket untuk melihat tahapan disposisi dan tindak lanjut.', 'detail' => 'Pembaruan status tercatat pada riwayat penanganan komisi.'],
        ];
    @endphp

    {{-- ============ HERO — Figma 2020:16 ============ --}}
    <section class="bg-senat-hero">
        <div class="senat-shell py-12 lg:py-[56px]">
            <p class="text-[11px] font-bold uppercase tracking-[0.1em] text-senat-sky">
                Tracking &amp; Verifikasi
            </p>
            <h1 class="mt-5 max-w-[820px] text-[30px] font-bold leading-[1.1] text-white sm:text-[39px]">
                Pelacak Status Aspirasi Akademik
            </h1>
            <p class="mt-4 max-w-[760px] text-[14px] leading-[1.4] text-[#d1e3ff]">
                Pantau transparansi penanganan aduan dan masukan Anda melalui nomor registrasi tiket resmi.
            </p>
        </div>
    </section>

    {{-- ============ SEARCH CARD / PELACAK TIKET — Figma 2020:20 + 2350:1303 ============ --}}
    <section class="senat-shell pt-14">
        <div class="mx-auto max-w-[1008px]">
            <div class="rounded-2xl border border-[#d9e3f0] bg-white p-6 shadow-[0px_3px_8px_0px_rgba(13,26,41,0.04)] sm:p-8"
                 x-data="{
                     state: @js($initialState),
                     query: @js($ticket ?? ''),
                     submit() {
                         if (! this.query.trim()) { this.state = 'ready'; return; }
                         this.state = 'loading';
                         window.setTimeout(() => this.$refs.form.submit(), 650);
                     }
                 }">

                <form x-ref="form" action="{{ route('public.aspirations.track') }}" method="GET"
                      @submit.prevent="submit">
                    <label for="ticket-input" class="block text-[10px] font-bold uppercase tracking-[0.08em] text-senat-body">
                        Masukkan kode tiket aspirasi
                    </label>

                    <div class="mt-3 flex flex-col gap-3 sm:flex-row">
                        <input id="ticket-input" name="ticket" type="text" x-model="query"
                               autocomplete="off" spellcheck="false"
                               placeholder="Contoh: ASP-POLINES-2026-0042"
                               class="h-[46px] flex-1 rounded-lg border border-[#9fb3c8] bg-white px-3.5 font-mono text-[12px] text-senat-ink placeholder:font-sans placeholder:text-[#667085] focus:border-[#155ead] focus:outline-none focus:ring-1 focus:ring-[#155ead]">

                        <button type="submit"
                                :class="state === 'found'
                                    ? 'bg-white border border-[#9fb3c8] text-[#155ead]'
                                    : 'bg-[#155ead] text-white hover:bg-[#124e91]'"
                                class="inline-flex h-[38px] w-full items-center justify-center rounded-lg text-[12px] font-semibold transition-colors duration-150 sm:w-[232px] sm:shrink-0 sm:self-center">
                            <span x-text="state === 'notfound' ? 'Cek lagi'
                                        : (state === 'found' ? 'Periksa ulang'
                                        : (state === 'loading' ? 'Memeriksa…' : 'Cek Progres'))"></span>
                        </button>
                    </div>
                </form>

                {{-- Petunjuk (Ready | Loading) --}}
                <p x-show="state === 'ready'"
                   class="mt-4 text-[11px] font-normal text-[#667085]">
                    Masukkan nomor registrasi untuk melihat tahapan disposisi dan tindak lanjut.
                </p>
                <p x-show="state === 'loading'" x-cloak
                   class="mt-4 text-[12px] font-medium text-[#155ead]">
                    ◌&nbsp; Memeriksa nomor registrasi dan pembaruan terakhir…
                </p>

                {{-- STATE: TIKET DITEMUKAN — Figma 2350:1289 + data $aspiration --}}
                <div x-show="state === 'found'" x-cloak class="mt-8">
                    <div class="mx-auto max-w-[767px] rounded-xl border border-[#d9e2ef] bg-white p-5">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.06em] text-[#116a3c]">Tiket Ditemukan</p>
                        <p class="mt-1 font-mono text-[18px] font-bold tracking-wide text-[#13233a] tabular-nums">
                            {{ $asp['ticket_code'] }}
                        </p>
                        <p class="mt-1 text-[13px] font-semibold text-[#155ead]">
                            Tahap saat ini: {{ $timeline[$stage - 1]['title'] }}
                        </p>
                        <p class="mt-2 max-w-[650px] text-[12px] leading-relaxed text-[#4b5565]">
                            Aspirasi telah diverifikasi Sekretariat dan diteruskan untuk telaah substansi oleh komisi tujuan.
                        </p>
                        <p class="mt-3 text-[11px] text-[#667085]">
                            Pembaruan terakhir: {{ $asp['submitted_at'] }}{{ $asp['submitted_at'] !== '-' ? ' WIB' : '' }}
                        </p>
                        <div class="mt-3 h-[5px] w-full max-w-[620px] overflow-hidden rounded-[3px] bg-[#eaf3fe]">
                            <div class="h-full rounded-[3px] bg-[#155ead]" style="width: {{ $progress }}%"></div>
                        </div>
                    </div>

                    {{-- Detail aspirasi lengkap + timeline status --}}
                    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
                        <div class="rounded-xl border border-[#d9e2ef] bg-white p-6 lg:col-span-2">
                            <h3 class="text-[13px] font-bold uppercase tracking-[0.06em] text-senat-ink">
                                Rincian Aspirasi
                            </h3>

                            <dl class="mt-4 grid grid-cols-1 gap-x-8 gap-y-4 sm:grid-cols-2">
                                <div>
                                    <dt class="text-[10px] font-semibold uppercase tracking-[0.06em] text-senat-muted">Kategori Pengirim</dt>
                                    <dd class="mt-1 text-[12px] font-medium text-senat-ink">{{ $asp['sender_category'] }}</dd>
                                </div>
                                <div>
                                    <dt class="text-[10px] font-semibold uppercase tracking-[0.06em] text-senat-muted">Unit/Program Studi</dt>
                                    <dd class="mt-1 text-[12px] font-medium text-senat-ink">{{ $asp['jurusan'] }}</dd>
                                </div>
                                <div class="sm:col-span-2">
                                    <dt class="text-[10px] font-semibold uppercase tracking-[0.06em] text-senat-muted">Komisi Tujuan</dt>
                                    <dd class="mt-1 text-[12px] font-medium text-senat-ink">{{ $asp['committee_target'] }}</dd>
                                </div>
                                <div class="sm:col-span-2">
                                    <dt class="text-[10px] font-semibold uppercase tracking-[0.06em] text-senat-muted">Judul Aspirasi</dt>
                                    <dd class="mt-1 text-[13px] font-bold leading-snug text-senat-ink">{{ $asp['subject'] }}</dd>
                                </div>
                                <div class="sm:col-span-2">
                                    <dt class="text-[10px] font-semibold uppercase tracking-[0.06em] text-senat-muted">Uraian</dt>
                                    <dd class="mt-1 text-[12px] leading-relaxed text-senat-body">{{ $asp['description'] }}</dd>
                                </div>
                                <div>
                                    <dt class="text-[10px] font-semibold uppercase tracking-[0.06em] text-senat-muted">Status</dt>
                                    <dd class="mt-1.5">
                                        <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-[3px] text-[10px] font-bold uppercase tracking-[0.06em] {{ $badgeClass }}">
                                            <span class="h-1.5 w-1.5 rounded-full {{ $badgeDot }}" aria-hidden="true"></span>
                                            {{ $asp['status'] }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-[10px] font-semibold uppercase tracking-[0.06em] text-senat-muted">Waktu Pengajuan</dt>
                                    <dd class="mt-1 text-[12px] font-medium text-senat-ink tabular-nums">{{ $asp['submitted_at'] }}</dd>
                                </div>
                            </dl>

                            <div class="mt-6 rounded-xl border border-[#a6c4e5] bg-senat-badgeBg p-5">
                                <h4 class="text-[10px] font-bold uppercase tracking-[0.08em] text-senat-primary">
                                    Tanggapan &amp; Rekomendasi Resmi Senat
                                </h4>
                                <p class="mt-2 text-[12px] leading-relaxed text-senat-body">{{ $asp['response'] }}</p>
                            </div>
                        </div>

                        {{-- Timeline status --}}
                        <div class="rounded-xl border border-[#d9e2ef] bg-white p-6">
                            <h3 class="text-[13px] font-bold uppercase tracking-[0.06em] text-senat-ink">
                                Timeline Status
                            </h3>
                            <ol class="mt-5 space-y-5">
                                @foreach ($timeline as $i => $item)
                                    @php
                                        $n = $i + 1;
                                        $isDone = $n < $stage;
                                        $isCurrent = $n === $stage;
                                        $dot = $isDone
                                            ? 'bg-[#116a3c] text-white'
                                            : ($isCurrent ? 'bg-[#155ead] text-white' : 'border border-senat-line bg-senat-surface text-senat-muted');
                                    @endphp
                                    <li class="flex gap-3">
                                        <span class="mt-0.5 inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-[11px] font-bold {{ $dot }}">
                                            {{ $isDone ? '✓' : $n }}
                                        </span>
                                        <div class="min-w-0">
                                            <p class="text-[12px] font-bold {{ $isCurrent ? 'text-[#155ead]' : 'text-senat-ink' }}">
                                                {{ $item['title'] }}
                                            </p>
                                            <p class="mt-0.5 text-[11px] leading-snug text-senat-body">{{ $item['detail'] }}</p>
                                        </div>
                                    </li>
                                @endforeach
                            </ol>
                        </div>
                    </div>
                </div>

                {{-- STATE: TIKET TIDAK DITEMUKAN — Figma 2350:1299 --}}
                <div x-show="state === 'notfound'" x-cloak
                     class="mt-8 rounded-[10px] border border-[#f7c8c3] bg-[#fff7f6] p-4">
                    <div class="flex gap-3">
                        <span class="text-[14px] font-bold leading-none text-[#b4233b]" aria-hidden="true">!</span>
                        <div>
                            <p class="text-[13px] font-semibold text-[#13233a]">Kode tiket tidak ditemukan</p>
                            <p class="mt-1 text-[11px] leading-relaxed text-[#4b5565]">
                                Periksa kembali format nomor registrasi atau kirim aspirasi melalui kanal resmi.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Pratinjau 4 state (simulasi Alpine) — Figma 2350:1303 --}}
                <div class="mt-8 flex flex-wrap items-center gap-2 border-t border-senat-line pt-5">
                    <span class="mr-1 text-[10px] font-bold uppercase tracking-[0.08em] text-senat-muted">Pratinjau status</span>
                    @foreach ([['ready', 'Form siap'], ['loading', 'Loading'], ['found', 'Ditemukan'], ['notfound', 'Tidak ditemukan']] as [$key, $label])
                        <button type="button" @click="state = '{{ $key }}'"
                                :class="state === '{{ $key }}' ? 'bg-senat-primary text-white' : 'border border-senat-border bg-white text-senat-body hover:border-senat-primary'"
                                class="inline-flex h-8 items-center rounded-lg px-3 text-[11px] font-semibold transition-colors">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ============ CARA MELACAK ASPIRASI — Figma 2020:26 ============ --}}
    <section class="senat-shell pt-16">
        <div class="mx-auto max-w-[1008px]">
            <p class="text-[11px] font-bold uppercase tracking-[0.1em] text-senat-orange">
                Cara Melacak Aspirasi
            </p>
            <h2 class="mt-4 max-w-[760px] text-[22px] font-bold leading-[1.15] text-senat-ink sm:text-[26px]">
                Masukkan kode yang diterima setelah aspirasi dikirim
            </h2>

            <ol class="mt-8 space-y-6">
                @foreach ($steps as $step)
                    <li class="flex gap-4">
                        <span class="mt-0.5 inline-flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-full bg-senat-hero text-[12px] font-bold text-white tabular-nums">
                            {{ $step['no'] }}
                        </span>
                        <div class="min-w-0">
                            <p class="text-[14px] font-bold leading-[1.2] text-senat-ink">{{ $step['title'] }}</p>
                            <p class="mt-1 text-[11px] leading-[1.2] text-senat-body">{{ $step['detail'] }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- ============ HELP CARD — Figma 2020:40 ============ --}}
    <section class="senat-shell py-16">
        <div class="mx-auto max-w-[1008px] rounded-[15px] border border-[#adc9e5] bg-senat-badgeBg p-6 sm:p-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-[700px]">
                    <h3 class="text-[16px] font-bold leading-[1.1] text-[#003c75]">Belum memiliki nomor tiket?</h3>
                    <p class="mt-3 text-[12px] leading-[1.4] text-senat-body">
                        Sampaikan aspirasi Anda melalui kanal resmi. Nomor registrasi otomatis dikirim setelah formulir berhasil diproses.
                    </p>
                </div>
                <a href="{{ route('public.aspirations') }}"
                   class="inline-flex h-10 w-full items-center justify-center gap-1.5 rounded-lg border border-[#a6c4e5] bg-white px-5 text-[11px] font-bold text-senat-link transition-colors duration-150 hover:border-senat-link lg:w-[244px] lg:shrink-0">
                    Buka Kanal Aspirasi
                    <span aria-hidden="true">›</span>
                </a>
            </div>
        </div>
    </section>

@endsection
