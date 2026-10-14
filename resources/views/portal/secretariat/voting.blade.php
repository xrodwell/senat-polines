@extends('layouts.portal', ['sidebar' => 'sekretariat'])

@section('title', 'E-Voting — Portal Internal Senat Akademik Polines')

@section('content')
    @php
        /* 13E — Dashboard Sekretariat / E-Voting (Figma node 2281:1150) */
        $activeMeeting = $stats['active'] ?? collect($meetings)->first();

        $opsi = [
            ['label' => 'Setuju', 'tone' => 'bg-[#eaf8f0]', 'chip' => 'success'],
            ['label' => 'Tidak Setuju', 'tone' => 'bg-[#fdecef]', 'chip' => 'danger'],
            ['label' => 'Abstain', 'tone' => 'bg-senat-badgeBg', 'chip' => 'info'],
        ];
        $hasil = [
            ['label' => 'Setuju', 'dot' => 'bg-[#116a3c]', 'value' => '—', 'valueClass' => 'text-[#116a3c]'],
            ['label' => 'Tidak Setuju', 'dot' => 'bg-[#b4233b]', 'value' => '—', 'valueClass' => 'text-[#b4233b]'],
            ['label' => 'Abstain', 'dot' => 'bg-[#155ead]', 'value' => '—', 'valueClass' => 'text-[#155ead]'],
        ];
        $pengaturan = [
            ['label' => 'Mulai voting', 'value' => 'Atur waktu pembukaan'],
            ['label' => 'Peserta berhak suara', 'value' => 'Anggota aktif dalam sidang'],
            ['label' => 'Syarat kuorum', 'value' => 'Ditentukan sesuai rapat'],
        ];
    @endphp

    <div class="mx-auto max-w-[1116px] space-y-6">
        <header>
            <h1 class="text-[25px] font-bold leading-tight text-senat-ink">E-Voting</h1>
            <p class="mt-2 text-[13px] leading-snug text-senat-body">
                Buka voting, pantau kuorum, dan hasilkan rekapitulasi keputusan sidang.
            </p>
        </header>

        <div class="grid gap-5 xl:grid-cols-[682px_minmax(0,1fr)]">
            {{-- Kontrol Voting --}}
            <section class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]"
                     x-data="{ status: 'Draft' }">
                <x-senat.badge state="neutral" x-show="status === 'Draft'">Draft</x-senat.badge>
                <x-senat.badge state="success" x-cloak x-show="status === 'Dibuka'">Dibuka</x-senat.badge>

                <h2 class="mt-4 text-[20px] font-bold leading-snug text-senat-ink">
                    {{ $activeMeeting ? 'Pemungutan suara · '.$activeMeeting['title'] : 'Belum ada agenda untuk divoting' }}
                </h2>
                <p class="mt-2 text-[13px] leading-snug text-senat-body">
                    {{ $activeMeeting
                        ? $activeMeeting['type'].' · '.$activeMeeting['session_code'].' · '.\Illuminate\Support\Carbon::parse($activeMeeting['date'])->format('d M Y')
                        : 'Atur opsi, waktu, peserta, dan syarat kuorum sebelum voting dibuka.' }}
                </p>

                <div class="mt-5 grid gap-3 sm:grid-cols-3">
                    @foreach ($opsi as $option)
                        <div class="rounded-[12px] px-4 py-3.5 {{ $option['tone'] }}">
                            <p class="text-[14px] font-semibold text-senat-ink">{{ $option['label'] }}</p>
                            <p class="mt-1.5 text-[12px] text-senat-body">0 suara</p>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 h-px bg-senat-sidebarLine"></div>

                <h3 class="mt-5 text-[14px] font-semibold text-senat-ink">Pengaturan</h3>
                <dl class="mt-4 space-y-3">
                    @foreach ($pengaturan as $row)
                        <div class="grid grid-cols-2 gap-3">
                            <dt class="text-[12px] font-medium text-senat-body">{{ $row['label'] }}</dt>
                            <dd class="text-[12px] font-semibold text-senat-ink">{{ $row['value'] }}</dd>
                        </div>
                    @endforeach
                </dl>

                <div class="mt-6 flex flex-wrap items-center gap-3">
                    <x-senat.button variant="secondary" type="button">Simpan Draft</x-senat.button>
                    <x-senat.button variant="primary" type="button" @click="status = status === 'Draft' ? 'Dibuka' : 'Draft'">
                        <span x-text="status === 'Draft' ? 'Buka Voting' : 'Kunci Voting'"></span>
                    </x-senat.button>
                </div>

                <div class="mt-5 flex items-center gap-3 rounded-[8px] bg-senat-badgeBg px-3 py-2.5">
                    <span class="text-[15px] font-semibold text-senat-link" aria-hidden="true">i</span>
                    <p class="text-[12px] text-senat-ink">Draft, dibuka, dan dikunci ditampilkan pada kontrol ini.</p>
                </div>
            </section>

            {{-- Rekap Sementara --}}
            <section class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
                <h2 class="text-[18px] font-bold text-senat-ink">Rekap Sementara</h2>
                <p class="mt-1.5 text-[12px] text-senat-body">Rekap tersedia setelah voting dibuka dan suara masuk.</p>

                <ul class="mt-5 space-y-3">
                    @foreach ($hasil as $row)
                        <li class="flex items-center gap-3 rounded-[10px] bg-senat-surface px-4 py-3.5">
                            <span class="h-[10px] w-[10px] shrink-0 rounded-[5px] {{ $row['dot'] }}" aria-hidden="true"></span>
                            <span class="min-w-0 flex-1 text-[13px] font-medium text-senat-ink">{{ $row['label'] }}</span>
                            <span class="text-[20px] font-bold leading-none {{ $row['valueClass'] }}">{{ $row['value'] }}</span>
                        </li>
                    @endforeach
                </ul>

                <x-senat.button variant="secondary" size="sm" class="mt-5" type="button">Cetak Berita Acara</x-senat.button>
            </section>
        </div>

        {{-- Rekapitulasi Keputusan Sidang --}}
        <section class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
            <h2 class="text-[18px] font-bold text-senat-ink">Rekapitulasi Keputusan Sidang</h2>
            <p class="mt-1.5 text-[13px] text-senat-body">
                Hasil voting, kuorum, dan berita acara tersedia setelah voting ditutup.
            </p>

            <div class="mt-5 grid gap-4 md:grid-cols-3">
                @foreach ([['Status', 'Belum dibuka'], ['Suara masuk', '0'], ['Berita acara', 'Belum tersedia']] as $tile)
                    <div class="rounded-[10px] bg-senat-surface px-4 py-3.5">
                        <p class="text-[11px] font-medium text-senat-body">{{ $tile[0] }}</p>
                        <p class="mt-1.5 text-[14px] font-semibold text-senat-ink">{{ $tile[1] }}</p>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
@endsection
