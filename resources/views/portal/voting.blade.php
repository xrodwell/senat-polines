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
    $votingDibuka = (bool) $active;
    $kuorum = $stats['invited'] > 0
        ? $stats['present'] . '/' . $stats['invited'] . ' anggota terundang hadir'
        : 'Belum ada data kuorum';
@endphp

<div class="mx-auto w-full max-w-[1116px] space-y-6" x-data="{ pilihan: null }">

    {{-- ================= PAGE HEADER ================= --}}
    <header>
        <h1 class="text-[25px] font-bold leading-tight tracking-tight text-senat-ink">E-Voting</h1>
        <p class="mt-1.5 text-[13px] text-senat-muted">Gunakan satu suara untuk setiap voting yang dibuka Sekretariat.</p>
    </header>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[730px_1fr]">

        {{-- ================= KARTU VOTING ================= --}}
        <section class="rounded-[14px] border border-senat-line bg-white p-5 shadow-[0_3px_12px_rgba(13,26,41,0.07)]">
            @if ($agendaVoting)
                <x-senat.badge :state="$votingDibuka ? 'success' : 'neutral'" :dot="false">
                    {{ $votingDibuka ? 'Voting dibuka' : 'Voting belum dibuka' }}
                </x-senat.badge>

                <h2 class="mt-4 text-[21px] font-bold leading-snug text-senat-ink">{{ $agendaVoting['title'] }}</h2>
                <p class="mt-2 text-[13px] leading-relaxed text-senat-muted">
                    @if ($votingDibuka)
                        Persetujuan untuk melanjutkan hasil pembahasan agenda ini ke Sidang Pleno setelah masa kajian anggota selesai.
                    @else
                        Voting akan dibuka Sekretariat saat agenda {{ $agendaVoting['type'] }} memasuki tahap pengambilan keputusan.
                    @endif
                </p>

                <div class="mt-4 border-t border-senat-line pt-4">
                    <p class="text-[15px] font-semibold text-senat-ink">Pilih suara Anda</p>
                    <p class="mt-1 text-[12px] text-senat-muted">Satu anggota hanya dapat memberikan satu suara.</p>

                    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
                        @foreach ([
                            ['key' => 'setuju', 'label' => 'Setuju', 'icon' => '✓', 'bg' => 'bg-[#eaf8f0]', 'fg' => 'text-[#147a4a]'],
                            ['key' => 'tidak-setuju', 'label' => 'Tidak Setuju', 'icon' => '×', 'bg' => 'bg-[#fdecef]', 'fg' => 'text-[#c63b50]'],
                            ['key' => 'abstain', 'label' => 'Abstain', 'icon' => '–', 'bg' => 'bg-[#eaf3fe]', 'fg' => 'text-senat-link'],
                        ] as $opsi)
                            <button type="button"
                                    @click="pilihan = '{{ $opsi['key'] }}'"
                                    :aria-pressed="pilihan === '{{ $opsi['key'] }}'"
                                    :class="pilihan === '{{ $opsi['key'] }}' ? 'ring-2 ring-senat-link ring-offset-1' : 'ring-0'"
                                    class="{{ $opsi['bg'] }} rounded-[12px] px-[18px] py-4 text-left transition-shadow">
                                <span class="block text-[20px] font-bold {{ $opsi['fg'] }}" aria-hidden="true">{{ $opsi['icon'] }}</span>
                                <span class="mt-2 block text-[14px] font-semibold text-senat-ink">{{ $opsi['label'] }}</span>
                                <span class="mt-0.5 block text-[11px] text-senat-muted">Belum ada suara</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="mt-5 border-t border-senat-line pt-4">
                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="flex items-center gap-2 text-[11px] font-medium text-senat-muted"><span aria-hidden="true">◉</span> Status</dt>
                            <dd class="mt-1 text-[13px] font-semibold {{ $votingDibuka ? 'text-[#116a3c]' : 'text-senat-ink' }}">
                                {{ $votingDibuka ? 'Voting dibuka' : 'Menunggu dibuka' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="flex items-center gap-2 text-[11px] font-medium text-senat-muted"><span aria-hidden="true">◎</span> Kuorum</dt>
                            <dd class="mt-1 text-[13px] font-semibold text-senat-ink">{{ $kuorum }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="mt-5 flex items-start gap-3 rounded-lg bg-[#f1eeff] px-4 py-3">
                    <span class="text-[15px] font-semibold text-[#5131b4]" aria-hidden="true">i</span>
                    <p class="min-w-0 flex-1 text-[12px] text-senat-ink">
                        <span x-show="pilihan === null">Konfirmasi suara dan hasil voting tampil di area ini.</span>
                        <span x-cloak x-show="pilihan === 'setuju'">Pilihan Anda: Setuju. Tekan kirim untuk mengunci suara.</span>
                        <span x-cloak x-show="pilihan === 'tidak-setuju'">Pilihan Anda: Tidak Setuju. Tekan kirim untuk mengunci suara.</span>
                        <span x-cloak x-show="pilihan === 'abstain'">Pilihan Anda: Abstain. Tekan kirim untuk mengunci suara.</span>
                    </p>
                </div>

                <div class="mt-4">
                    <x-senat.button type="button" :disabled="false">
                        <span x-show="pilihan === null">Kirim Suara</span>
                        <span x-cloak x-show="pilihan !== null">Kirim &amp; Kunci Suara</span>
                    </x-senat.button>
                </div>
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
