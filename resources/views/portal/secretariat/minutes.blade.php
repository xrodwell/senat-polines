@extends('layouts.portal', ['sidebar' => 'sekretariat'])

@section('title', 'Notulensi & Tindak Lanjut — Portal Internal Senat Akademik Polines')

@section('content')
    @php
        /* 13C — Dashboard Sekretariat / Notulensi & Tindak Lanjut (Figma node 2280:939) */
        $activeMeeting = collect($meetings)->where('status', 'Sedang Berlangsung')->first() ?? collect($meetings)->first();
        $notulenItem = collect($archives)->where('category', 'Notulen Sidang')->first();
        $materiItem = collect($archives)->where('category', 'Dokumen Organisasi')->first()
            ?? collect($archives)->where('category', '!=', 'Notulen Sidang')->first();

        $keputusan = collect([
            $notulenItem ? [
                'state' => 'success',
                'chip' => 'Keputusan',
                'title' => $notulenItem['title'],
                'body' => 'Risalah sidang tercatat pada arsip sekretariat '.$notulenItem['year'].'.',
                'meta' => 'Dicatat',
                'metaClass' => 'text-[#116a3c]',
            ] : null,
            $materiItem ? [
                'state' => 'warning',
                'chip' => 'Action Item',
                'title' => $materiItem['category'],
                'body' => 'Lengkapi dan kategorikan lampiran: '.$materiItem['title'],
                'meta' => 'Perlu review',
                'metaClass' => 'text-[#a95100]',
            ] : null,
            [
                'state' => 'danger',
                'chip' => 'Action Item',
                'title' => 'Sekretariat',
                'body' => 'Validasi data kehadiran sebelum risalah dipublikasikan.',
                'meta' => 'Perlu review',
                'metaClass' => 'text-[#b4233b]',
            ],
        ])->filter()->values();
    @endphp

    <div class="mx-auto max-w-[1116px] space-y-6">
        <header>
            <h1 class="text-[25px] font-bold leading-tight text-senat-ink">Notulensi &amp; Tindak Lanjut</h1>
            <p class="mt-2 text-[13px] leading-snug text-senat-body">
                Kelola notulensi, keputusan sidang, serta action items yang dapat dilacak.
            </p>
        </header>

        {{-- Editor Header --}}
        <section class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]"
                 x-data="{ saved: false }">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="min-w-0 max-w-[720px]">
                    <x-senat.badge state="warning">Draft contoh</x-senat.badge>
                    <h2 class="mt-3 text-[19px] font-bold leading-snug text-senat-ink">
                        {{ $activeMeeting ? $activeMeeting['title'] : 'Belum ada sidang untuk dinotulensikan' }}
                    </h2>
                    <p class="mt-2 text-[12px] leading-snug text-senat-body">
                        @if ($activeMeeting)
                            {{ $activeMeeting['type'] }} · {{ \Illuminate\Support\Carbon::parse($activeMeeting['date'])->format('d F Y') }}
                            @if ($notulenItem)
                                — risalah terkait: {{ $notulenItem['title'] }}
                            @endif
                        @else
                            Notulen dapat disusun setelah sidang dijadwalkan.
                        @endif
                    </p>
                </div>
                <div class="flex shrink-0 flex-wrap items-center gap-3">
                    <x-senat.button variant="secondary" type="button" @click="saved = true">Simpan Draft</x-senat.button>
                    <x-senat.button variant="primary" type="button" @click="saved = true">Kirim Review</x-senat.button>
                </div>
            </div>
            <div x-cloak x-show="saved" x-transition class="mt-4 flex items-center gap-3 rounded-[8px] bg-[#eaf8f0] px-3 py-2">
                <span class="text-[15px] font-semibold text-[#116a3c]" aria-hidden="true"><x-senat.icon name="check" class="h-[18px] w-[18px]" /></span>
                <p class="text-[12px] text-senat-ink">Draft notulensi tersimpan pada antrean review sekretariat.</p>
            </div>
        </section>

        <div class="grid gap-5 xl:grid-cols-[680px_minmax(0,1fr)]">
            {{-- Isi Notulensi --}}
            <section class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
                <h2 class="text-[18px] font-bold text-senat-ink">Jalannya Pembahasan</h2>
                <textarea rows="10" aria-label="Jalannya pembahasan"
                          class="mt-3 w-full rounded-[10px] border border-senat-border bg-white p-4 text-[13px] leading-relaxed text-senat-ink placeholder:text-senat-muted focus:border-senat-link focus:outline-none focus:ring-1 focus:ring-senat-link"
                          placeholder="Catatan jalannya pembahasan sidang diisi oleh notulis dan diverifikasi sekretariat sebelum risalah disahkan."></textarea>

                <h3 class="mt-6 text-[13px] font-semibold text-senat-ink">Lampiran</h3>
                @if ($notulenItem)
                    <div class="mt-3 flex items-center gap-3 rounded-[10px] bg-senat-badgeBg px-4 py-2.5">
                        <span class="text-[18px] font-bold text-senat-link" aria-hidden="true"><x-senat.icon name="folder" class="h-[18px] w-[18px]" /></span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-[13px] font-medium text-senat-ink">{{ $notulenItem['title'] }}</span>
                            <span class="mt-0.5 block text-[11px] text-senat-muted">{{ $notulenItem['category'] }} · {{ $notulenItem['file_size'] }}</span>
                        </span>
                        <x-senat.button variant="secondary" size="sm" :href="$notulenItem['download_url']">Lihat</x-senat.button>
                    </div>
                @else
                    <p class="mt-3 rounded-[10px] bg-senat-surface px-4 py-3 text-[12px] text-senat-muted">Belum ada lampiran risalah.</p>
                @endif

                <x-senat.button variant="secondary" size="sm" class="mt-4" type="button">Tambah Lampiran</x-senat.button>
            </section>

            {{-- Action Items --}}
            <section class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
                <h2 class="text-[18px] font-bold text-senat-ink">Keputusan &amp; Action Items</h2>

                <ul class="mt-4 divide-y divide-senat-sidebarLine">
                    @foreach ($keputusan as $item)
                        <li class="py-4 first:pt-2 last:pb-1">
                            <x-senat.badge :state="$item['state']">{{ $item['chip'] }}</x-senat.badge>
                            <p class="mt-3 text-[14px] font-semibold leading-snug text-senat-ink">{{ $item['title'] }}</p>
                            <p class="mt-1.5 text-[12px] leading-snug text-senat-body">{{ $item['body'] }}</p>
                            <p class="mt-2 text-[11px] font-medium {{ $item['metaClass'] }}">{{ $item['meta'] }}</p>
                        </li>
                    @endforeach
                </ul>

                <x-senat.button variant="primary" size="sm" class="mt-4" type="button">Tambah Action Item</x-senat.button>
            </section>
        </div>
    </div>
@endsection
