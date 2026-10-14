@extends('layouts.portal')

@section('title', 'Notulensi & Risalah — Portal Internal Senat Akademik Polines')

@section('content')
@php
    /**
     * 12C — Notulensi & Risalah (Figma node 2279:970)
     */
    $bulanId = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $tanggal = function (string $value) use ($bulanId): string {
        $ts = strtotime($value);
        return $ts ? date('j', $ts) . ' ' . $bulanId[(int) date('n', $ts)] . ' ' . date('Y', $ts) : $value;
    };

    $notulen = null;
    foreach ($meetings as $meeting) {
        if (($meeting['status'] ?? '') === 'Sedang Berlangsung' || str_contains($meeting['type'] ?? '', 'Pleno')) {
            $notulen = $meeting;
            break;
        }
    }
    $notulen = $notulen ?: ($meetings[0] ?? null);

    $risalah = array_values(array_filter($archives, fn ($a) => str_contains($a['category'] ?? '', 'Notulen')
        || str_contains($a['category'] ?? '', 'Struktur')
        || str_contains($a['category'] ?? '', 'Organisasi')));
    $keputusan = array_values(array_filter($archives, fn ($a) => ! in_array($a, $risalah, true)));
@endphp

<div class="mx-auto w-full max-w-[1116px]" x-data="{ tab: 'ringkasan', catatan: '' }">

    {{-- ================= PAGE HEADER ================= --}}
    <header class="mb-6">
        <h1 class="text-[25px] font-bold leading-tight tracking-tight text-senat-ink">Notulensi &amp; Risalah</h1>
        <p class="mt-1.5 text-[13px] text-senat-muted">
            @if ($notulen && ($notulen['status'] ?? '') === 'Sedang Berlangsung')
                Contoh notulen berasal dari {{ $notulen['title'] }}, {{ $tanggal($notulen['date']) }}.
            @else
                Risalah sidang dan notulensi rapat Senat.
            @endif
        </p>
    </header>

    {{-- ================= HEADER NOTULEN ================= --}}
    <section class="rounded-[14px] border border-senat-line bg-white p-5 shadow-[0_3px_12px_rgba(13,26,41,0.07)]">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0">
                <x-senat.badge state="warning" :dot="false">Draft contoh</x-senat.badge>
                <h2 class="mt-3 text-[20px] font-bold leading-snug text-senat-ink">
                    {{ $notulen['type'] ?? 'Risalah sidang' }}
                </h2>
                <p class="mt-2 max-w-[850px] text-[13px] leading-relaxed text-senat-muted">
                    {{ $notulen['title'] ?? 'Belum ada risalah yang ditampilkan.' }}
                </p>
            </div>
            <x-senat.button variant="secondary" size="sm" icon="⤓" type="button">Unduh Risalah</x-senat.button>
        </div>

        @if ($notulen)
            <dl class="mt-5 grid grid-cols-1 gap-4 border-t border-senat-line pt-4 sm:grid-cols-2">
                <div>
                    <dt class="flex items-center gap-2 text-[11px] font-medium text-senat-muted"><span aria-hidden="true">◫</span> Waktu</dt>
                    <dd class="mt-1 text-[13px] font-semibold text-senat-ink">
                        {{ $tanggal($notulen['date']) }} · {{ $notulen['time'] }}
                    </dd>
                </div>
                <div>
                    <dt class="flex items-center gap-2 text-[11px] font-medium text-senat-muted"><span aria-hidden="true">▣</span> Lokasi</dt>
                    <dd class="mt-1 text-[13px] font-semibold text-senat-ink">{{ $notulen['room'] }}</dd>
                </div>
            </dl>
        @endif
    </section>

    {{-- ================= TABS ================= --}}
    <div class="mt-6 rounded-[14px] border border-senat-line bg-white shadow-[0_2px_8px_rgba(13,26,41,0.05)]">
        <div class="flex flex-wrap" role="tablist" aria-label="Bagian risalah">
            @foreach (['ringkasan' => 'Ringkasan', 'keputusan' => 'Keputusan', 'action' => 'Action Items', 'lampiran' => 'Lampiran'] as $key => $label)
                <button type="button" role="tab" @click="tab = '{{ $key }}'"
                        :aria-selected="tab === '{{ $key }}'"
                        class="relative px-6 py-4 text-[13px] transition-colors"
                        :class="tab === '{{ $key }}' ? 'font-semibold text-senat-link' : 'font-medium text-senat-ink hover:text-senat-link'">
                    {{ $label }}
                    <span x-cloak x-show="tab === '{{ $key }}'"
                          class="absolute bottom-0 left-6 h-[3px] w-[125px] max-w-[calc(100%-3rem)] rounded-[2px] bg-[#146fc3]"></span>
                </button>
            @endforeach
        </div>
    </div>

    {{-- ================= CONTENT ================= --}}
    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-[670px_1fr]">

        {{-- Primary --}}
        <section class="rounded-[14px] border border-senat-line bg-white p-5 shadow-[0_2px_8px_rgba(13,26,41,0.05)]">
            {{-- Ringkasan --}}
            <div x-show="tab === 'ringkasan'">
                <h3 class="text-[18px] font-bold text-senat-ink">Ringkasan Pembahasan</h3>
                <p class="mt-3 text-[13px] leading-relaxed text-senat-muted">
                    {{ $notulen['title'] ?? 'Belum ada pokok pembahasan yang dicatat pada contoh ini.' }}
                </p>

                <div class="mt-5 border-t border-[#e5ecf4] pt-5">
                    <p class="text-[13px] font-semibold text-senat-ink">Pokok yang dicatat</p>
                    <ul class="mt-4 space-y-4">
                        @forelse ($archives as $archive)
                            <li class="flex items-start gap-4">
                                <span class="mt-1.5 h-2 w-2 shrink-0 rounded-[4px] bg-senat-link" aria-hidden="true"></span>
                                <div class="min-w-0">
                                    <p class="text-[13px] font-semibold text-senat-ink">{{ $archive['title'] }}</p>
                                    <p class="mt-0.5 text-[11px] text-senat-muted">
                                        {{ $archive['category'] }} · {{ $archive['year'] }} · {{ $archive['file_size'] }}
                                    </p>
                                </div>
                            </li>
                        @empty
                            <li><p class="text-[12px] text-senat-muted">Belum ada pokok yang dicatat.</p></li>
                        @endforelse
                    </ul>
                </div>
            </div>

            {{-- Keputusan --}}
            <div x-cloak x-show="tab === 'keputusan'">
                <h3 class="text-[18px] font-bold text-senat-ink">Keputusan Sidang</h3>
                <ul class="mt-4 divide-y divide-senat-line">
                    @forelse ($keputusan as $item)
                        <li class="flex items-start gap-4 py-3 first:pt-0 last:pb-0">
                            <span class="mt-1.5 h-2 w-2 shrink-0 rounded-[4px] bg-[#155ead]" aria-hidden="true"></span>
                            <div class="min-w-0 flex-1">
                                <p class="text-[13px] font-semibold text-senat-ink">{{ $item['title'] }}</p>
                                <p class="mt-0.5 text-[11px] text-senat-muted">{{ $item['category'] }} · {{ $item['year'] }}</p>
                            </div>
                        </li>
                    @empty
                        <li class="py-3"><p class="text-[12px] text-senat-muted">Belum ada keputusan tercatat pada contoh ini.</p></li>
                    @endforelse
                </ul>
            </div>

            {{-- Action Items --}}
            <div x-cloak x-show="tab === 'action'">
                <h3 class="text-[18px] font-bold text-senat-ink">Action Items</h3>
                <div class="mt-4">
                    <x-senat.empty title="Belum ada action item"
                                   message="Tindak lanjut keputusan sidang akan tercatat di area ini." />
                </div>
            </div>

            {{-- Lampiran --}}
            <div x-cloak x-show="tab === 'lampiran'">
                <h3 class="text-[18px] font-bold text-senat-ink">Lampiran Risalah</h3>
                <ul class="mt-4 divide-y divide-senat-line">
                    @forelse ($archives as $archive)
                        <li class="flex flex-wrap items-center gap-x-4 gap-y-2 py-3 first:pt-0 last:pb-0">
                            <div class="min-w-0 flex-1">
                                <p class="text-[13px] font-semibold text-senat-ink">{{ $archive['title'] }}</p>
                                <p class="mt-0.5 text-[11px] text-senat-muted">{{ $archive['category'] }}</p>
                            </div>
                            <x-senat.badge state="neutral" :dot="false">{{ $archive['file_size'] }}</x-senat.badge>
                            <x-senat.button size="sm" variant="secondary" :href="$archive['download_url']">Buka</x-senat.button>
                        </li>
                    @empty
                        <li class="py-3"><p class="text-[12px] text-senat-muted">Belum ada lampiran.</p></li>
                    @endforelse
                </ul>
            </div>
        </section>

        {{-- Secondary — Catatan Pribadi --}}
        <section class="rounded-[14px] border border-senat-line bg-white p-5 shadow-[0_2px_8px_rgba(13,26,41,0.05)]">
            <h3 class="text-[18px] font-bold text-senat-ink">Catatan Pribadi</h3>
            <div class="mt-3">
                <x-senat.badge state="info" :dot="false">Risalah contoh</x-senat.badge>
            </div>
            <p class="mt-4 text-[12px] leading-relaxed text-senat-muted">
                Tambahkan catatan untuk ditinjau kembali pada rapat berikutnya.
            </p>

            <label for="catatan-risalah" class="sr-only">Catatan pribadi</label>
            <textarea id="catatan-risalah" x-model="catatan" rows="4" placeholder="Tulis catatan pribadi…"
                      class="mt-4 w-full resize-none rounded-[9px] border border-[#c9d7e6] px-4 py-3 text-[12px] text-senat-ink placeholder:text-senat-muted focus:border-senat-link focus:outline-none focus:ring-2 focus:ring-senat-badgeBg"></textarea>

            <x-senat.button class="mt-4" type="button" :disabled="false">Simpan Catatan</x-senat.button>

            <p class="mt-3 text-[11px] text-senat-muted">Catatan hanya terlihat oleh Anda.</p>

            <div class="mt-5 border-t border-[#e5ecf4] pt-4">
                <p class="text-[11px] text-senat-muted">Belum ada catatan tersimpan pada risalah ini.</p>
            </div>
        </section>
    </div>

</div>
@endsection
