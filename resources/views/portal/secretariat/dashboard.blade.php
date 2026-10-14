@extends('layouts.portal', ['sidebar' => 'sekretariat'])

@section('title', 'Dashboard Sekretariat — Portal Internal Senat Akademik Polines')

@section('content')
    @php
        /* 13A — Dashboard Sekretariat / Ringkasan (Figma node 2021:118) */
        $activeMeeting = $stats['active'] ?? null;
        $invited = (int) ($stats['invited'] ?? 0);
        $present = (int) ($stats['present'] ?? 0);
        $upcomingCount = count($stats['upcoming'] ?? []);

        $notulenItem = collect($archives)->where('category', 'Notulen Sidang')->first();
        $aspirasiItem = collect($aspirations)->where('status', 'Sedang Ditelaah')->first();
        $regDraft = collect($regulations)->where('status', 'Draf')->first();
        $aspirasiKajian = collect($aspirations)->whereIn('status', ['Sedang Ditelaah', 'Ditindaklanjuti Komisi'])->count();

        $antrean = collect([
            $notulenItem ? [
                'tone' => 'bg-[#a95100]',
                'title' => 'Verifikasi notulen pleno',
                'meta' => $notulenItem['title'],
            ] : null,
            $aspirasiItem ? [
                'tone' => 'bg-[#5131b4]',
                'title' => 'Penetapan status aspirasi',
                'meta' => $aspirasiItem['ticket_code'].' · '.$aspirasiItem['subject'],
            ] : null,
            $regDraft ? [
                'tone' => 'bg-[#155ead]',
                'title' => 'Validasi status hukum draf',
                'meta' => $regDraft['nomor'].' · '.$regDraft['title'],
            ] : null,
        ])->filter()->values();

        $aksiCepat = [
            ['href' => route('portal.secretariat.sessions'), 'icon' => '+', 'title' => 'Buat Sidang', 'desc' => 'Atur jenis, waktu, lokasi, dan peserta', 'accent' => true],
            ['href' => route('portal.secretariat.minutes'), 'icon' => '≡', 'title' => 'Susun Notulensi', 'desc' => 'Catat pembahasan, keputusan, action items', 'accent' => false],
            ['href' => route('portal.secretariat.voting'), 'icon' => '✓', 'title' => 'Gelar Voting', 'desc' => 'Buka voting dan pantau kuorum', 'accent' => false],
        ];
    @endphp

    <div class="mx-auto max-w-[1116px] space-y-6">
        {{-- Judul halaman --}}
        <header>
            <h1 class="text-[25px] font-bold leading-tight text-senat-ink">Dashboard Sekretariat</h1>
            <p class="mt-2 text-[13px] leading-snug text-senat-body">
                Ringkasan operasional sidang, notulensi, voting, dan pipeline dokumen.
            </p>
        </header>

        {{-- Info --}}
        <div class="flex items-start gap-3 rounded-[14px] bg-senat-badgeBg px-4 py-3.5 text-senat-link shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
            <span class="text-[18px] font-bold leading-none" aria-hidden="true">i</span>
            <p class="text-[13px] font-medium leading-snug">
                Kelola siklus sidang tanpa mencampurkan data publik dan data kerja internal.
            </p>
        </div>

        {{-- Stat / KPI --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-senat.stat label="Sidang terjadwal" :value="str_pad((string) $stats['total'], 2, '0', STR_PAD_LEFT)"
                          :hint="$upcomingCount.' agenda mendatang'" icon="◫" />
            <x-senat.stat label="Notulensi draft" :value="str_pad((string) ($notulenItem ? 1 : 0), 2, '0', STR_PAD_LEFT)"
                          hint="Perlu verifikasi sekretariat" icon="≡" />
            <x-senat.stat label="Voting dibuka" :value="str_pad((string) ($activeMeeting ? 1 : 0), 2, '0', STR_PAD_LEFT)"
                          :hint="$activeMeeting ? 'Sesi sedang berlangsung' : 'Belum ada voting aktif'" icon="✓" />
            <x-senat.stat label="Aspirasi kajian" :value="str_pad((string) $aspirasiKajian, 2, '0', STR_PAD_LEFT)"
                          hint="Menunggu telaah komisi" icon="◉" />
        </div>

        <div class="grid gap-5 lg:grid-cols-2">
            {{-- Aksi Cepat --}}
            <section class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
                <h2 class="text-[18px] font-bold text-senat-ink">Aksi Cepat</h2>
                <div class="mt-4 space-y-3">
                    @foreach ($aksiCepat as $aksi)
                        <a href="{{ $aksi['href'] }}"
                           class="flex items-center gap-3 rounded-[10px] px-4 py-3 transition-colors {{ $aksi['accent'] ? 'bg-senat-badgeBg' : 'border border-senat-sidebarLine bg-white hover:border-senat-badgeLine' }}">
                            <span class="w-4 text-center text-[16px] font-semibold {{ $aksi['accent'] ? 'text-senat-link' : 'text-senat-muted' }}" aria-hidden="true">{{ $aksi['icon'] }}</span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-[14px] font-semibold text-senat-ink">{{ $aksi['title'] }}</span>
                                <span class="mt-0.5 block truncate text-[11px] text-senat-body">{{ $aksi['desc'] }}</span>
                            </span>
                            <span class="text-[20px] font-bold leading-none text-senat-link" aria-hidden="true">›</span>
                        </a>
                    @endforeach
                </div>
            </section>

            {{-- Antrean Tindakan --}}
            <section class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
                <h2 class="text-[18px] font-bold text-senat-ink">Antrean Tindakan</h2>
                @if ($antrean->isEmpty())
                    <x-senat.empty class="mt-4" title="Tidak ada tindakan tertunda"
                                   message="Tidak ada berkas yang menunggu verifikasi sekretariat saat ini." />
                @else
                    <ul class="mt-4 divide-y divide-senat-sidebarLine">
                        @foreach ($antrean as $item)
                            <li class="flex items-start gap-3 py-3 first:pt-1 last:pb-1">
                                <span class="mt-1.5 h-[9px] w-[9px] shrink-0 rounded-[5px] {{ $item['tone'] }}" aria-hidden="true"></span>
                                <span class="min-w-0">
                                    <span class="block text-[13px] font-semibold text-senat-ink">{{ $item['title'] }}</span>
                                    <span class="mt-1 block text-[11px] leading-snug text-senat-body">{{ $item['meta'] }}</span>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>

        {{-- Agenda Minggu Ini --}}
        <section class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
            <h2 class="text-[18px] font-bold text-senat-ink">Agenda Sidang</h2>
            @if (empty($meetings))
                <x-senat.empty class="mt-4" title="Belum ada agenda sidang"
                               message="Agenda akan tampil di sini setelah sidang dijadwalkan." />
            @else
                <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($meetings as $meeting)
                        @php $isActive = ($meeting['status'] ?? '') === 'Sedang Berlangsung'; @endphp
                        <article class="rounded-[12px] border p-4 {{ $isActive ? 'border-transparent bg-senat-badgeBg' : 'border-senat-sidebarLine bg-white' }}">
                            <p class="text-[12px] font-bold text-senat-link">
                                {{ \Illuminate\Support\Carbon::parse($meeting['date'])->format('d M') }}
                            </p>
                            <p class="mt-1.5 line-clamp-2 text-[14px] font-semibold text-senat-ink">{{ $meeting['title'] }}</p>
                            <p class="mt-1.5 text-[11px] text-senat-body">{{ $meeting['type'] }}</p>
                            <div class="mt-3 flex items-center justify-between gap-2">
                                <span class="text-[11px] font-medium text-senat-body">{{ $meeting['time'] }}</span>
                                <x-senat.badge :state="$isActive ? 'success' : 'neutral'">{{ $meeting['status'] }}</x-senat.badge>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection
