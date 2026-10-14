@extends('layouts.portal')

@section('title', 'Aspirasi & RUU Kampus — Portal Internal Senat Akademik Polines')

@section('content')
@php
    /**
     * 12E — Aspirasi & RUU Kampus (Figma node 2279:1075)
     */
    $pipeline = [
        ['no' => '01', 'title' => 'Draft', 'text' => 'Penyusunan awal', 'bg' => 'bg-[#eaf3fe]', 'chip' => 'bg-[#155ead]'],
        ['no' => '02', 'title' => 'Kajian', 'text' => 'Telaah anggota', 'bg' => 'bg-[#f1eeff]', 'chip' => 'bg-[#603dcc]'],
        ['no' => '03', 'title' => 'Sidang Pleno', 'text' => 'Pembahasan resmi', 'bg' => 'bg-[#fff4df]', 'chip' => 'bg-[#c56f00]'],
        ['no' => '04', 'title' => 'Pengesahan', 'text' => 'Dokumen berlaku', 'bg' => 'bg-[#eaf8f0]', 'chip' => 'bg-[#147a4a]'],
    ];
    $statusState = function (string $status): string {
        return match (true) {
            str_contains($status, 'Ditindaklanjuti'), str_contains($status, 'Diterima') => 'info',
            str_contains($status, 'Telaah'), str_contains($status, 'Ditinjau') => 'warning',
            default => 'neutral',
        };
    };
@endphp

<div class="mx-auto w-full max-w-[1116px] space-y-6">

    {{-- ================= PAGE HEADER ================= --}}
    <header>
        <h1 class="text-[25px] font-bold leading-tight tracking-tight text-senat-ink">Aspirasi &amp; RUU Kampus</h1>
        <p class="mt-1.5 text-[13px] text-senat-muted">Lacak aspirasi, naskah akademik, dan proses RUU Kampus secara transparan.</p>
    </header>

    {{-- ================= PIPELINE ================= --}}
    <section class="rounded-[14px] border border-senat-line bg-white p-5 shadow-[0_3px_12px_rgba(13,26,41,0.07)]">
        <x-senat.badge state="info" :dot="false">Smart legislative pipeline</x-senat.badge>
        <h2 class="mt-4 text-[20px] font-bold text-senat-ink">Tahap pembentukan RUU Kampus</h2>
        <p class="mt-2 max-w-[800px] text-[13px] leading-relaxed text-senat-muted">
            Status mengikuti alur dokumen: Draft, Kajian, Sidang Pleno, dan Pengesahan.
        </p>

        <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($pipeline as $stage)
                <div class="{{ $stage['bg'] }} rounded-[12px] px-4 py-4">
                    <span class="flex h-[30px] w-[30px] items-center justify-center rounded-[15px] {{ $stage['chip'] }} text-[11px] font-bold text-white tabular-nums">
                        {{ $stage['no'] }}
                    </span>
                    <p class="mt-3 text-[14px] font-semibold text-senat-ink">{{ $stage['title'] }}</p>
                    <p class="mt-0.5 text-[11px] text-senat-muted">{{ $stage['text'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[730px_1fr]">

        {{-- ================= ASPIRASI MASUK ================= --}}
        <section class="rounded-[14px] border border-senat-line bg-white p-5 shadow-[0_3px_12px_rgba(13,26,41,0.07)]">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-[18px] font-bold text-senat-ink">Aspirasi Masuk</h2>
                <x-senat.button size="sm" :href="route('public.aspirations')">Buat Aspirasi</x-senat.button>
            </div>

            <ul class="mt-5 divide-y divide-senat-line">
                @forelse ($aspirations as $aspiration)
                    <li class="flex flex-wrap items-start justify-between gap-x-4 gap-y-2 py-4 first:pt-0 last:pb-0">
                        <div class="min-w-0 flex-1">
                            <p class="text-[14px] font-semibold leading-snug text-senat-ink">{{ $aspiration['subject'] }}</p>
                            <p class="mt-1 text-[12px] text-senat-muted">
                                {{ $aspiration['sender_category'] }} · {{ $aspiration['committee_target'] }}
                            </p>
                            <p class="mt-1 text-[11px] text-senat-muted">
                                {{ $aspiration['ticket_code'] }} · {{ $aspiration['submitted_at'] }}
                            </p>
                        </div>
                        <x-senat.badge :state="$statusState($aspiration['status'])" :dot="false">
                            {{ $aspiration['status'] }}
                        </x-senat.badge>
                    </li>
                @empty
                    <li class="py-4"><p class="text-[13px] text-senat-muted">Belum ada aspirasi yang tercatat.</p></li>
                @endforelse
            </ul>
        </section>

        {{-- ================= PUBLIC HEARING DIGITAL ================= --}}
        <section class="rounded-[14px] border border-senat-line bg-white p-5 shadow-[0_3px_12px_rgba(13,26,41,0.07)]">
            <h2 class="text-[18px] font-bold text-senat-ink">Public Hearing Digital</h2>
            <p class="mt-3 text-[13px] leading-relaxed text-senat-muted">
                Tanggapan terbatas atau terbuka dapat dikumpulkan sebelum aturan disahkan.
            </p>

            <div class="mt-5 rounded-[12px] bg-[#eaf3fe] px-5 py-5">
                <x-senat.icon name="aspiration" class="h-[22px] w-[22px] text-senat-link" />
                <p class="mt-2 text-[14px] font-semibold text-senat-ink">Komentar Draf Aturan</p>
                <p class="mt-1 text-[12px] text-senat-muted">Akan tersedia ketika draf dibuka.</p>
            </div>

            <x-senat.button class="mt-6" variant="secondary" :href="route('public.regulations')">Lihat Draf</x-senat.button>
        </section>
    </div>

</div>
@endsection
