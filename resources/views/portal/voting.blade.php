@extends('layouts.portal')

@section('title', 'E-Voting — Portal Internal Senat Akademik Polines')

@section('content')
@php
    /**
     * 12D — E-Voting (Figma node 2279:866)
     */
    $active = $stats['active'] ?? null;
    $upcoming = array_values($stats['upcoming'] ?? []);
    $agendaVoting = $active ?: ($upcoming[0] ?? ($meetings[0] ?? null));

    $votingData = $activeVoting ?? null;
    $votingId = $votingData['id'] ?? $votingData['id_voting'] ?? 1;
    $votingStatus = strtolower($votingData['status'] ?? ($active ? 'dibuka' : 'menunggu'));
    $votingDibuka = in_array($votingStatus, ['dibuka', 'aktif', 'active', 'open'], true);
    $votingDitutup = in_array($votingStatus, ['ditutup', 'selesai', 'closed'], true);

    $judulVoting = $votingData['judul'] ?? $votingData['judul_voting'] ?? ($agendaVoting['title'] ?? 'Voting Senat Akademik');
    $deskripsiVoting = $votingData['deskripsi'] ?? (
        $votingDibuka
            ? 'Persetujuan untuk melanjutkan hasil pembahasan agenda ini ke Sidang Pleno setelah masa kajian anggota selesai.'
            : ($votingDitutup ? 'Sesi pemungutan suara telah ditutup. Hasil rekapitulasi perolehan suara tercantum di bawah.' : 'Voting akan dibuka Sekretariat saat agenda memasuki tahap pengambilan keputusan.')
    );

    $opsiList = $votingData['opsi'] ?? [
        ['id_opsi' => 1, 'nama_opsi' => 'Setuju', 'total' => 24, 'persentase' => 85.7],
        ['id_opsi' => 2, 'nama_opsi' => 'Tidak Setuju', 'total' => 2, 'persentase' => 7.1],
        ['id_opsi' => 3, 'nama_opsi' => 'Abstain', 'total' => 2, 'persentase' => 7.1],
    ];

    $totalSuara = $votingData['total_suara'] ?? collect($opsiList)->sum('total');
    $kuorum = $stats['invited'] > 0
        ? $stats['present'] . '/' . $stats['invited'] . ' anggota terundang hadir'
        : ($totalSuara > 0 ? "{$totalSuara} suara masuk" : 'Belum ada data kuorum');
@endphp

<div class="mx-auto w-full max-w-[1116px] space-y-6" x-data="{ pilihan: null, pilihanNama: '' }">

    {{-- ================= FLASH ALERTS ================= --}}
    @if (session('success'))
        <div class="rounded-lg bg-[#eaf8f0] border border-[#147a4a]/20 px-4 py-3 text-[13px] font-semibold text-[#147a4a]">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="rounded-lg bg-[#fdecef] border border-[#c63b50]/20 px-4 py-3 text-[13px] font-semibold text-[#c63b50]">
            {{ session('error') }}
        </div>
    @endif

    {{-- ================= PAGE HEADER ================= --}}
    <header>
        <h1 class="text-[25px] font-bold leading-tight tracking-tight text-senat-ink">E-Voting</h1>
        <p class="mt-1.5 text-[13px] text-senat-muted">Gunakan satu suara untuk setiap voting yang dibuka Sekretariat.</p>
    </header>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[730px_1fr]">

        {{-- ================= KARTU VOTING ================= --}}
        <section class="rounded-[14px] border border-senat-line bg-white p-5 shadow-[0_3px_12px_rgba(13,26,41,0.07)]">
            @if ($agendaVoting || $votingData)
                <x-senat.badge :state="$votingDibuka ? 'success' : ($votingDitutup ? 'danger' : 'neutral')" :dot="false">
                    {{ $votingDibuka ? 'Voting dibuka' : ($votingDitutup ? 'Voting ditutup' : 'Voting belum dibuka') }}
                </x-senat.badge>

                <h2 class="mt-4 text-[21px] font-bold leading-snug text-senat-ink">{{ $judulVoting }}</h2>
                <p class="mt-2 text-[13px] leading-relaxed text-senat-muted">
                    {{ $deskripsiVoting }}
                </p>

                <form method="POST" action="{{ route('portal.voting.submit') }}" class="mt-4 border-t border-senat-line pt-4">
                    @csrf
                    <input type="hidden" name="id_voting" value="{{ $votingId }}">
                    <input type="hidden" name="id_opsi" :value="pilihan">

                    <p class="text-[15px] font-semibold text-senat-ink">Pilih suara Anda</p>
                    <p class="mt-1 text-[12px] text-senat-muted">Satu anggota hanya dapat memberikan satu suara.</p>

                    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
                        @foreach ($opsiList as $opsi)
                            @php
                                $opsiId = $opsi['id_opsi'] ?? $opsi['id'] ?? $loop->iteration;
                                $opsiNama = $opsi['nama_opsi'] ?? $opsi['label'] ?? '';
                                $opsiKey = strtolower(str_replace(' ', '-', $opsiNama));
                                $totalVote = $opsi['total'] ?? 0;
                                $persenVote = $opsi['persentase'] ?? 0;

                                $style = match (true) {
                                    str_contains($opsiKey, 'tidak') || str_contains($opsiKey, 'tolak') => ['icon' => '×', 'bg' => 'bg-[#fdecef]', 'fg' => 'text-[#c63b50]'],
                                    str_contains($opsiKey, 'setuju') => ['icon' => '✓', 'bg' => 'bg-[#eaf8f0]', 'fg' => 'text-[#147a4a]'],
                                    default => ['icon' => '–', 'bg' => 'bg-[#eaf3fe]', 'fg' => 'text-senat-link'],
                                };
                            @endphp
                            <button type="button"
                                    @click="pilihan = {{ $opsiId }}; pilihanNama = '{{ $opsiNama }}'"
                                    :aria-pressed="pilihan === {{ $opsiId }}"
                                    :class="pilihan === {{ $opsiId }} ? 'ring-2 ring-senat-link ring-offset-1' : 'ring-0'"
                                    @disabled(!$votingDibuka)
                                    class="{{ $style['bg'] }} rounded-[12px] px-[18px] py-4 text-left transition-shadow disabled:opacity-75 disabled:cursor-not-allowed">
                                <span class="block text-[20px] font-bold {{ $style['fg'] }}" aria-hidden="true">{{ $style['icon'] }}</span>
                                <span class="mt-2 block text-[14px] font-semibold text-senat-ink">{{ $opsiNama }}</span>
                                <span class="mt-0.5 block text-[11px] text-senat-muted">
                                    {{ $totalVote > 0 ? $totalVote . ' suara (' . $persenVote . '%)' : 'Belum ada suara' }}
                                </span>
                            </button>
                        @endforeach
                    </div>

                    <div class="mt-5 border-t border-senat-line pt-4">
                        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="flex items-center gap-2 text-[11px] font-medium text-senat-muted"><span aria-hidden="true"><x-senat.icon name="eye" class="h-[18px] w-[18px]" /></span> Status</dt>
                                <dd class="mt-1 text-[13px] font-semibold {{ $votingDibuka ? 'text-[#116a3c]' : ($votingDitutup ? 'text-[#c63b50]' : 'text-senat-ink') }}">
                                    {{ $votingDibuka ? 'Voting dibuka' : ($votingDitutup ? 'Voting ditutup' : 'Menunggu dibuka') }}
                                </dd>
                            </div>
                            <div>
                                <dt class="flex items-center gap-2 text-[11px] font-medium text-senat-muted"><span aria-hidden="true"><x-senat.icon name="eye-off" class="h-[18px] w-[18px]" /></span> Kuorum &amp; Total Suara</dt>
                                <dd class="mt-1 text-[13px] font-semibold text-senat-ink">{{ $kuorum }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="mt-5 flex items-start gap-3 rounded-lg bg-[#f1eeff] px-4 py-3">
                        <span class="text-[15px] font-semibold text-[#5131b4]" aria-hidden="true">i</span>
                        <p class="min-w-0 flex-1 text-[12px] text-senat-ink">
                            <span x-show="!pilihan">Pilih salah satu opsi suara di atas, lalu tekan tombol kirim.</span>
                            <span x-cloak x-show="pilihan">Pilihan Anda: <strong x-text="pilihanNama"></strong>. Tekan tombol kirim untuk mengunci suara Anda.</span>
                        </p>
                    </div>

                    <div class="mt-4">
                        <button type="submit"
                                :disabled="!pilihan || {{ $votingDibuka ? 'false' : 'true' }}"
                                class="inline-flex items-center justify-center rounded-[10px] bg-senat-primary px-5 py-2.5 text-[13px] font-semibold text-white transition hover:bg-senat-primaryHover disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!pilihan">Pilih Suara Terlebih Dahulu</span>
                            <span x-cloak x-show="pilihan">Kirim &amp; Kunci Suara</span>
                        </button>
                    </div>
                </form>
            @else
                <x-senat.empty title="Belum ada voting dibuka"
                               message="Sekretariat belum membuka pengambilan suara. Halaman ini akan menampilkan opsi ketika voting dibuka." />
            @endif
        </section>

        {{-- ================= KETENTUAN VOTING ================= --}}
        <section class="rounded-[14px] border border-senat-line bg-white p-5 shadow-[0_3px_12px_rgba(13,26,41,0.07)]">
            <h2 class="text-[18px] font-bold text-senat-ink">Ketentuan Voting</h2>

            @php
                $ketentuan = [
                    ['no' => '01', 'title' => 'Akses terbatas', 'text' => 'Hanya anggota aktif dan terdaftar pada sidang.'],
                    ['no' => '02', 'title' => 'Satu suara', 'text' => 'Pilihan tidak dapat diubah setelah dikirim.'],
                    ['no' => '03', 'title' => 'Kuorum', 'text' => 'Status kuorum tampil ketika voting dibuka.'],
                    ['no' => '04', 'title' => 'Rekap', 'text' => 'Hasil tersedia pada risalah sidang.'],
                ];
            @endphp

            <ul class="mt-5 space-y-5">
                @foreach ($ketentuan as $item)
                    <li class="flex items-start gap-4">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-[14px] bg-[#eaf3fe] text-[11px] font-bold text-senat-link tabular-nums">{{ $item['no'] }}</span>
                        <div class="min-w-0">
                            <p class="text-[13px] font-semibold text-senat-ink">{{ $item['title'] }}</p>
                            <p class="mt-0.5 text-[12px] leading-relaxed text-senat-muted">{{ $item['text'] }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    </div>

    {{-- ================= RIWAYAT VOTING ================= --}}
    <section class="rounded-[14px] border border-senat-line bg-white p-5 shadow-[0_3px_12px_rgba(13,26,41,0.07)]">
        <h2 class="text-[18px] font-bold text-senat-ink">Riwayat Voting</h2>
        <p class="mt-3 text-[13px] text-senat-muted">Belum ada voting terdahulu yang ditampilkan pada contoh ini.</p>
        <x-senat.button class="mt-6" variant="secondary" :href="route('portal.dashboard')">Kembali ke Dashboard</x-senat.button>
    </section>

</div>
@endsection
