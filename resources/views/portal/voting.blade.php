@extends('layouts.portal')

@section('title', 'E-Voting — Portal Internal Senat Akademik Polines')

@section('content')
@php
    /**
     * 12D — E-Voting Anggota Senat
     */
    $active = $activeVoting ?? null;
    $votingDibuka = $active && in_array(strtolower($active['status'] ?? ''), ['dibuka', 'aktif', 'active', 'open'], true);

    $votingId = $active['id'] ?? $active['id_voting'] ?? null;
    $judulVoting = $active['judul'] ?? $active['judul_voting'] ?? 'Sesi Pemungutan Suara';
    $deskripsiVoting = $active['deskripsi'] ?? 'Pilihlah salah satu opsi di bawah ini sesuai pertimbangan keanggotaan Senat Akademik.';

    $opsiList = $active['opsi'] ?? [];
    $totalSuara = (int) ($active['total_suara'] ?? collect($opsiList)->sum(fn ($o) => (int) ($o['total'] ?? $o['jumlah_suara'] ?? 0)));
    $activeInvited = (int) ($active['kuorum']['total'] ?? 0);
    $activePresent = (int) ($active['kuorum']['hadir'] ?? 0);
    $kuorum = $activeInvited > 0
        ? "{$activePresent}/{$activeInvited} anggota terundang hadir"
        : (($stats['active']['total_invited'] ?? 0) > 0
            ? ($stats['active']['present_count'] ?? 0) . '/' . ($stats['active']['total_invited'] ?? 0) . ' anggota terundang hadir'
            : ($totalSuara > 0 ? "{$totalSuara} suara masuk" : 'Menunggu presensi sidang'));

    $historyList = $historyVotings ?? [];
    $votedCount = collect($historyList)->where('user_voted', true)->count();
    $unvotedCount = collect($historyList)->where('user_voted', false)->count();
@endphp

<div class="mx-auto w-full max-w-[1116px] space-y-8" x-data="{
    pilihan: null,
    pilihanNama: '',
    historyTab: 'all'
}">

    {{-- ================= FLASH ALERTS ================= --}}
    @if (session('success'))
        <div class="rounded-xl bg-[#eaf8f0] border border-[#147a4a]/25 px-4 py-3 text-[13px] font-semibold text-[#147a4a] flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-[#147a4a] text-white text-xs">✓</span>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif
    @if (session('error'))
        <div class="rounded-xl bg-[#fdecef] border border-[#c63b50]/25 px-4 py-3 text-[13px] font-semibold text-[#c63b50] flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-[#c63b50] text-white text-xs font-bold">!</span>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    {{-- ================= PAGE HEADER ================= --}}
    <header class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between border-b border-senat-line pb-5">
        <div>
            <h1 class="text-[25px] font-bold leading-tight tracking-tight text-senat-ink">E-Voting Sidang Senat</h1>
            <p class="mt-1 text-[13px] text-senat-muted">
                Partisipasi pengambilan keputusan dan peninjauan rekapitulasi hasil pemungutan suara Senat Akademik Polines.
            </p>
        </div>
        <div class="flex items-center gap-2 pt-2 sm:pt-0">
            @if ($votingDibuka)
                <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 border border-emerald-200 px-3 py-1 text-xs font-semibold text-emerald-700">
                    <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Sesi Aktif Terbuka
                </span>
            @else
                <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 border border-slate-200 px-3 py-1 text-xs font-medium text-slate-600">
                    <span class="h-2 w-2 rounded-full bg-slate-400"></span>
                    Tidak Ada Sesi Aktif
                </span>
            @endif
        </div>
    </header>

    {{-- ================= SECTION 1: SESI VOTING / EMPTY STATE & KETENTUAN ================= --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[730px_1fr]">

        {{-- KOLOM KIRI: SESI AKTIF ATAU EMPTY STATE --}}
        <section class="rounded-[16px] border border-senat-line bg-white p-6 shadow-[0_3px_12px_rgba(13,26,41,0.06)] flex flex-col justify-between">
            @if ($votingDibuka)
                {{-- KARTU VOTING AKTIF --}}
                <div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider text-emerald-800">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-600 animate-ping"></span>
                            Voting Sedang Dibuka
                        </span>
                        @if (!empty($active['rapat']['kode']))
                            <span class="text-[11px] font-medium text-senat-muted bg-slate-100 px-2 py-0.5 rounded">
                                {{ $active['rapat']['kode'] }}
                            </span>
                        @endif
                    </div>

                    <h2 class="mt-4 text-[20px] font-bold leading-snug text-senat-ink">
                        {{ $judulVoting }}
                    </h2>
                    <p class="mt-2 text-[13px] leading-relaxed text-senat-muted">
                        {{ $deskripsiVoting }}
                    </p>

                    @if (!empty($active['has_voted']))
                        {{-- STATE ANGGOTA SUDAH MEMILIH --}}
                        <div class="mt-5 border-t border-senat-line pt-5 space-y-4">
                            <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-4 flex items-start gap-3.5">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-600 text-white font-bold text-sm shrink-0">✓</span>
                                <div>
                                    <h3 class="text-[14px] font-bold text-emerald-900">Hak Suara Anda Telah Tercatat &amp; Dikunci</h3>
                                    <p class="mt-0.5 text-[12px] text-emerald-700 leading-relaxed">
                                        Pilihan Anda: <strong class="text-emerald-900 uppercase tracking-wide">{{ $active['my_choice']['nama_opsi'] ?? 'Suara Sah' }}</strong>. Sesuai tata tertib Senat Akademik Polines, hak suara Anda telah dienkripsi dan diamankan ke buku induk putusan sidang.
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-3 pt-2">
                                <p class="text-[13px] font-bold text-senat-ink">Rekapitulasi Suara Sidang Sementara:</p>
                                <div class="space-y-2.5">
                                    @foreach ($opsiList as $opsi)
                                        @php
                                            $opsiNama = $opsi['nama_opsi'] ?? $opsi['label'] ?? '';
                                            $totalVote = (int) ($opsi['total'] ?? $opsi['jumlah_suara'] ?? 0);
                                            $persenVote = $opsi['persentase'] ?? 0;
                                            $isMyPick = ($active['my_choice']['id_opsi'] ?? null) == ($opsi['id_opsi'] ?? $opsi['id'] ?? null);
                                        @endphp
                                        <div class="rounded-xl border p-3.5 transition {{ $isMyPick ? 'border-emerald-300 bg-emerald-50/40 ring-1 ring-emerald-300' : 'border-slate-200 bg-slate-50/60' }}">
                                            <div class="flex items-center justify-between text-[13px]">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-bold text-slate-800">{{ $opsiNama }}</span>
                                                    @if ($isMyPick)
                                                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800">Pilihan Anda</span>
                                                    @endif
                                                </div>
                                                <span class="font-bold text-slate-700">{{ $totalVote }} suara ({{ $persenVote }}%)</span>
                                            </div>
                                            <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-slate-200">
                                                <div class="h-full rounded-full {{ $isMyPick ? 'bg-emerald-600' : 'bg-[#003366]' }}" style="width: {{ $persenVote }}%"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mt-4 rounded-xl border border-senat-line bg-slate-50/70 p-4">
                                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <dt class="text-[11px] font-medium text-senat-muted">Status Partisipasi</dt>
                                        <dd class="mt-0.5 text-[13px] font-bold text-emerald-700 flex items-center gap-1.5">
                                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                            Suara Masuk &amp; Sah
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-[11px] font-medium text-senat-muted">Total Partisipasi Sidang</dt>
                                        <dd class="mt-0.5 text-[13px] font-semibold text-senat-ink">{{ $totalSuara }} dari 39 anggota senat</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    @else
                        {{-- FORM PILIH SUARA JIKA BELUM MEMILIH --}}
                        <form method="POST" action="{{ route('portal.voting.submit') }}" class="mt-5 border-t border-senat-line pt-5">
                            @csrf
                            <input type="hidden" name="id_voting" value="{{ $votingId }}">
                            <input type="hidden" name="id_opsi" :value="pilihan">

                            <div class="flex items-center justify-between">
                                <p class="text-[14px] font-bold text-senat-ink">Pilih Suara Anda</p>
                                <span class="text-[11px] text-senat-muted">Satu anggota satu suara</span>
                            </div>

                            <div class="mt-3.5 grid grid-cols-1 gap-3 sm:grid-cols-3">
                                @foreach ($opsiList as $opsi)
                                    @php
                                        $opsiId = $opsi['id_opsi'] ?? $opsi['id'] ?? $loop->iteration;
                                        $opsiNama = $opsi['nama_opsi'] ?? $opsi['label'] ?? '';
                                        $opsiKey = strtolower(str_replace(' ', '-', $opsiNama));
                                        $totalVote = (int) ($opsi['total'] ?? $opsi['jumlah_suara'] ?? 0);
                                        $persenVote = $opsi['persentase'] ?? 0;

                                        $style = match (true) {
                                            str_contains($opsiKey, 'tidak') || str_contains($opsiKey, 'tolak') => ['icon' => '✕', 'bg' => 'bg-[#fdecef]', 'border' => 'border-[#c63b50]/30', 'fg' => 'text-[#c63b50]'],
                                            str_contains($opsiKey, 'setuju') => ['icon' => '✓', 'bg' => 'bg-[#eaf8f0]', 'border' => 'border-[#147a4a]/30', 'fg' => 'text-[#147a4a]'],
                                            default => ['icon' => '—', 'bg' => 'bg-[#eaf3fe]', 'border' => 'border-senat-link/30', 'fg' => 'text-senat-link'],
                                        };
                                    @endphp
                                    <button type="button"
                                            @click="pilihan = {{ $opsiId }}; pilihanNama = '{{ $opsiNama }}'"
                                            :aria-pressed="pilihan === {{ $opsiId }}"
                                            :class="pilihan === {{ $opsiId }} ? 'ring-2 ring-senat-link ring-offset-2 border-senat-link shadow-sm' : 'border-transparent hover:border-slate-300'"
                                            class="{{ $style['bg'] }} rounded-[12px] border p-4 text-left transition-all duration-150 cursor-pointer">
                                        <span class="block text-[18px] font-black {{ $style['fg'] }}" aria-hidden="true">{{ $style['icon'] }}</span>
                                        <span class="mt-2 block text-[14px] font-bold text-senat-ink">{{ $opsiNama }}</span>
                                        <span class="mt-1 block text-[11px] text-senat-muted">
                                            {{ $totalVote > 0 ? $totalVote . ' suara (' . $persenVote . '%)' : 'Belum ada suara' }}
                                        </span>
                                    </button>
                                @endforeach
                            </div>

                            <div class="mt-5 rounded-xl border border-senat-line bg-slate-50/70 p-4">
                                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <dt class="text-[11px] font-medium text-senat-muted">Status Sidang</dt>
                                        <dd class="mt-0.5 text-[13px] font-bold text-emerald-700 flex items-center gap-1.5">
                                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                            Sesi voting sedang dibuka
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-[11px] font-medium text-senat-muted">Kuorum &amp; Kehadiran</dt>
                                        <dd class="mt-0.5 text-[13px] font-semibold text-senat-ink">{{ $kuorum }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <div class="mt-4 flex items-start gap-3 rounded-xl bg-blue-50/60 border border-blue-100 p-3.5">
                                <span class="text-[14px] font-bold text-blue-700 mt-0.5" aria-hidden="true">ℹ</span>
                                <p class="min-w-0 flex-1 text-[12px] text-slate-700 leading-snug">
                                    <span x-show="!pilihan">Silakan tentukan pilihan Anda pada opsi di atas, lalu tekan tombol <strong>Kirim &amp; Kunci Suara</strong>.</span>
                                    <span x-cloak x-show="pilihan">Pilihan Anda: <strong class="text-senat-ink" x-text="pilihanNama"></strong>. Suara yang dikirim bersifat final dan tidak dapat ditarik kembali.</span>
                                </p>
                            </div>

                            <div class="mt-5">
                                <button type="submit"
                                        :disabled="!pilihan"
                                        class="inline-flex items-center justify-center rounded-[10px] bg-senat-primary px-6 py-3 text-[13px] font-semibold text-white transition hover:bg-senat-primaryHover disabled:opacity-50 disabled:cursor-not-allowed shadow-xs cursor-pointer">
                                    <span x-show="!pilihan">Pilih Salah Satu Suara Terlebih Dahulu</span>
                                    <span x-cloak x-show="pilihan">Kirim &amp; Kunci Suara Sekarang</span>
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            @else
                {{-- EMPTY STATE: TIDAK ADA VOTING AKTIF --}}
                <div class="py-10 px-4 text-center my-auto">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-50 border border-blue-100 text-[#003366] shadow-xs">
                        <x-senat.icon name="vote" class="h-8 w-8 text-[#003366]" />
                    </div>

                    <span class="mt-5 inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                        Tidak Ada Sesi Aktif
                    </span>

                    <h2 class="mt-3 text-[20px] font-bold tracking-tight text-slate-900">
                        Belum Ada Pemungutan Suara yang Sedang Dibuka
                    </h2>

                    <p class="mx-auto mt-2 max-w-[500px] text-[13px] leading-relaxed text-slate-500">
                        Sesi E-Voting hanya dibuka oleh pimpinan sidang / Sekretariat Senat saat sidang pleno atau komisi memasuki tahap pengambilan keputusan.
                    </p>

                    <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                        <a href="{{ route('portal.agenda') }}"
                           class="inline-flex items-center gap-2 rounded-lg bg-senat-primary px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-senat-primaryHover shadow-xs">
                            <x-senat.icon name="calendar" class="h-4 w-4" />
                            <span>Lihat Jadwal Agenda &amp; Presensi</span>
                        </a>
                        <a href="{{ route('portal.minutes') }}"
                           class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                            <x-senat.icon name="minutes" class="h-4 w-4" />
                            <span>Buka Notulensi &amp; Risalah</span>
                        </a>
                    </div>
                </div>
            @endif
        </section>

        {{-- KOLOM KANAN: KETENTUAN VOTING --}}
        <section class="rounded-[16px] border border-senat-line bg-white p-6 shadow-[0_3px_12px_rgba(13,26,41,0.06)]">
            <h2 class="text-[17px] font-bold text-senat-ink">Ketentuan E-Voting</h2>
            <p class="mt-1 text-xs text-senat-muted">Pedoman tata tertib sidang pengambilan keputusan.</p>

            @php
                $ketentuan = [
                    ['no' => '01', 'title' => 'Hak Suara Anggota', 'text' => 'Hanya anggota yang terdaftar hadir dalam presensi sidang yang berhak memberikan suara.'],
                    ['no' => '02', 'title' => 'Satu Suara Final', 'text' => 'Pilihan suara bersifat rahasia institusional dan tidak dapat diubah setelah dikirim.'],
                    ['no' => '03', 'title' => 'Kuorum Persidangan', 'text' => 'Keputusan dinyatakan sah apabila kuorum kehadiran sidang memenuhi ketentuan statuta.'],
                    ['no' => '04', 'title' => 'Transparansi Rekap', 'text' => 'Rekapitulasi perolehan suara terbuka dan otomatis tercatat pada risalah sidang.'],
                ];
            @endphp

            <ul class="mt-5 space-y-4">
                @foreach ($ketentuan as $item)
                    <li class="flex items-start gap-3.5">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-[11px] font-bold text-[#003366] tabular-nums">
                            {{ $item['no'] }}
                        </span>
                        <div class="min-w-0">
                            <p class="text-[13px] font-bold text-senat-ink">{{ $item['title'] }}</p>
                            <p class="mt-0.5 text-[12px] leading-relaxed text-senat-muted">{{ $item['text'] }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    </div>

    {{-- ================= SECTION 2: RIWAYAT HASIL PEMUNGUTAN SUARA ================= --}}
    <section class="rounded-[16px] border border-senat-line bg-white p-6 shadow-[0_3px_12px_rgba(13,26,41,0.06)] space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b border-senat-line pb-4">
            <div>
                <h2 class="text-[18px] font-bold text-senat-ink">Riwayat Pemungutan Suara Sidang</h2>
                <p class="mt-0.5 text-[12px] text-senat-muted">
                    Arsip perolehan suara sidang terdahulu dan catatan partisipasi Anda.
                </p>
            </div>

            {{-- FILTER TABS --}}
            <div class="flex items-center gap-1.5 rounded-xl bg-slate-100 p-1">
                <button type="button"
                        @click="historyTab = 'all'"
                        :class="historyTab === 'all' ? 'bg-white text-senat-ink shadow-xs font-bold' : 'text-slate-500 hover:text-slate-800 font-medium'"
                        class="rounded-lg px-3 py-1.5 text-xs transition cursor-pointer">
                    Semua ({{ count($historyList) }})
                </button>
                <button type="button"
                        @click="historyTab = 'voted'"
                        :class="historyTab === 'voted' ? 'bg-white text-emerald-800 shadow-xs font-bold' : 'text-slate-500 hover:text-slate-800 font-medium'"
                        class="rounded-lg px-3 py-1.5 text-xs transition cursor-pointer">
                    Saya Ikuti ({{ $votedCount }})
                </button>
                <button type="button"
                        @click="historyTab = 'unvoted'"
                        :class="historyTab === 'unvoted' ? 'bg-white text-slate-800 shadow-xs font-bold' : 'text-slate-500 hover:text-slate-800 font-medium'"
                        class="rounded-lg px-3 py-1.5 text-xs transition cursor-pointer">
                    Tidak Ikut / Absen ({{ $unvotedCount }})
                </button>
            </div>
        </div>

        {{-- DAFTAR KARTU RIWAYAT --}}
        @if (empty($historyList))
            <div class="py-12 text-center text-senat-muted">
                <p class="text-[13px]">Belum ada riwayat pemungutan suara yang tercatat dalam sistem.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($historyList as $item)
                    @php
                        $userVoted = $item['user_voted'] ?? false;
                        $statusPartisipasi = $item['status_partisipasi'] ?? ($userVoted ? 'voted' : 'absent');
                        $userPilihan = $item['user_pilihan_nama'] ?? 'Setuju';

                        $rapatInfo = $item['rapat'] ?? [];
                        $namaRapat = $rapatInfo['nama_rapat'] ?? $rapatInfo['title'] ?? 'Sidang Senat Akademik';
                        $kodeRapat = $rapatInfo['kode'] ?? 'SIDANG';
                        $tanggalRapat = $rapatInfo['tanggal'] ?? ($item['waktu_mulai'] ? date('d M Y', strtotime($item['waktu_mulai'])) : '—');

                        $hasilKeputusan = $item['hasil_keputusan'] ?? 'Disetujui';
                        $badgeState = $item['badge_state'] ?? 'success';

                        $opsiRekap = $item['opsi'] ?? [];
                        $totalVoteItem = (int) ($item['total_suara'] ?? collect($opsiRekap)->sum(fn ($o) => (int) ($o['total'] ?? $o['jumlah_suara'] ?? 0)));
                    @endphp

                    <div x-show="historyTab === 'all' || (historyTab === 'voted' && {{ $userVoted ? 'true' : 'false' }}) || (historyTab === 'unvoted' && {{ !$userVoted ? 'true' : 'false' }})"
                         x-transition
                         class="rounded-[14px] border border-senat-line bg-slate-50/50 p-5 hover:bg-white hover:border-slate-300 transition-all duration-150 shadow-2xs">

                        {{-- Baris 1: Rapat & Tanggal & Badge Putusan --}}
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <span class="rounded-md bg-white border border-slate-200 px-2 py-0.5 text-[10.5px] font-bold text-slate-700">
                                    {{ $kodeRapat }}
                                </span>
                                <span class="text-xs font-semibold text-slate-800">{{ $namaRapat }}</span>
                                <span class="text-slate-300">•</span>
                                <span class="text-[11.5px] text-slate-500">{{ $tanggalRapat }}</span>
                            </div>

                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider
                                {{ $badgeState === 'success' ? 'bg-emerald-100 text-emerald-800' : ($badgeState === 'danger' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                {{ $hasilKeputusan }}
                            </span>
                        </div>

                        {{-- Baris 2: Judul Voting --}}
                        <h3 class="mt-2.5 text-[15px] sm:text-[16px] font-bold text-senat-ink">
                            {{ $item['judul'] ?? $item['judul_voting'] ?? 'Voting Senat' }}
                        </h3>

                        {{-- Baris 3: Status Partisipasi Anggota --}}
                        <div class="mt-2.5 flex flex-wrap items-center gap-2">
                            <span class="text-[11px] font-medium text-slate-500">Status Partisipasi Anda:</span>
                            @if ($statusPartisipasi === 'voted')
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 border border-emerald-200/90 px-2.5 py-0.5 text-[11px] font-bold text-emerald-800">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                                    Anda Memberikan Suara: {{ $userPilihan }}
                                </span>
                            @elseif ($statusPartisipasi === 'present_not_voted')
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 border border-amber-200 px-2.5 py-0.5 text-[11px] font-semibold text-amber-800">
                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                    Hadir dalam Sidang (Tidak Memilih)
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 border border-slate-200 px-2.5 py-0.5 text-[11px] font-medium text-slate-600">
                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                    Tidak Hadir dalam Sidang
                                </span>
                            @endif
                        </div>

                        {{-- Baris 4: Visual Progress Bar Rekapitulasi Suara --}}
                        <div class="mt-4 pt-3.5 border-t border-slate-200/80">
                            {{-- Mini Bar Bertumpuk --}}
                            <div class="h-2 w-full overflow-hidden rounded-full bg-slate-200 flex">
                                @foreach ($opsiRekap as $opsi)
                                    @php
                                        $oNama = strtolower($opsi['nama_opsi'] ?? $opsi['label'] ?? '');
                                        $oPersen = (float) ($opsi['persentase'] ?? 0);
                                        $barColor = match (true) {
                                            str_contains($oNama, 'tidak') || str_contains($oNama, 'tolak') => 'bg-rose-500',
                                            str_contains($oNama, 'setuju') => 'bg-emerald-500',
                                            default => 'bg-slate-400',
                                        };
                                    @endphp
                                    <div class="{{ $barColor }}" style="width: {{ $oPersen }}%" title="{{ $opsi['nama_opsi'] }}: {{ $oPersen }}%"></div>
                                @endforeach
                            </div>

                            {{-- Angka Detail & Kuorum --}}
                            <div class="mt-2.5 flex flex-wrap items-center justify-between gap-2 text-xs">
                                <div class="flex flex-wrap items-center gap-3">
                                    @foreach ($opsiRekap as $opsi)
                                        @php
                                            $oNama = strtolower($opsi['nama_opsi'] ?? $opsi['label'] ?? '');
                                            $oTotal = (int) ($opsi['total'] ?? $opsi['jumlah_suara'] ?? 0);
                                            $oPersen = $opsi['persentase'] ?? 0;
                                            $dotColor = match (true) {
                                                str_contains($oNama, 'tidak') || str_contains($oNama, 'tolak') => 'bg-rose-500',
                                                str_contains($oNama, 'setuju') => 'bg-emerald-500',
                                                default => 'bg-slate-400',
                                            };
                                        @endphp
                                        <div class="flex items-center gap-1.5">
                                            <span class="h-2 w-2 rounded-full {{ $dotColor }}"></span>
                                            <span class="font-medium text-slate-700">{{ $opsi['nama_opsi'] ?? $opsi['label'] }}:</span>
                                            <span class="font-bold text-slate-900">{{ $oTotal }} ({{ $oPersen }}%)</span>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="text-[11px] font-medium text-slate-500">
                                    Total: <strong class="text-slate-800">{{ $totalVoteItem }} suara</strong>
                                    @if (!empty($item['kuorum']['total']))
                                        • Kehadiran: {{ $item['kuorum']['hadir'] ?? $totalVoteItem }}/{{ $item['kuorum']['total'] }} (Kuorum Terpenuhi)
                                    @endif
                                </div>
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>
        @endif
    </section>

</div>
@endsection
