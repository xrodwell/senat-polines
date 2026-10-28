@extends('layouts.portal')

@section('title', 'Dokumen & Arsip — Portal Internal Senat Akademik Polines')

@section('content')
@php
    /**
     * 12F — Dokumen & Arsip (Figma node 2279:1181)
     */
    $docs = [];

    foreach ($regulations as $regulation) {
        $docs[] = [
            'title' => $regulation['title'],
            'category' => $regulation['category'],
            'meta' => $regulation['nomor'] . ' · ' . $regulation['year'],
            'status' => $regulation['status'],
            'size' => $regulation['file_size'],
            'url' => $regulation['download_url'],
            'kind' => 'Produk hukum',
        ];
    }

    foreach ($archives as $archive) {
        $docs[] = [
            'title' => $archive['title'],
            'category' => $archive['category'],
            'meta' => 'Tahun ' . $archive['year'],
            'status' => null,
            'size' => $archive['file_size'],
            'url' => $archive['download_url'],
            'kind' => 'Arsip',
        ];
    }

    $categories = array_values(array_unique(array_column($docs, 'category')));
@endphp

<div class="mx-auto w-full max-w-[1116px]" x-data="{ q: '', filter: 'semua' }">

    {{-- ================= PAGE HEADER ================= --}}
    <header class="mb-6">
        <h1 class="text-[25px] font-bold leading-tight tracking-tight text-senat-ink">Dokumen &amp; Arsip</h1>
        <p class="mt-1.5 text-[13px] text-senat-muted">Pusat ketetapan, SK, AD/ART, risalah, dan materi sidang.</p>
    </header>

    {{-- ================= SEARCH ================= --}}
    <div class="rounded-[14px] border border-senat-line bg-white px-5 py-4 shadow-[0_3px_12px_rgba(13,26,41,0.07)]">
        <div class="flex flex-wrap items-center gap-3">
            <span class="text-[20px] font-bold text-senat-muted" aria-hidden="true">⌕</span>
            <label for="dokumen-cari" class="sr-only">Cari dokumen</label>
            <input id="dokumen-cari" type="search" x-model="q"
                   placeholder="Cari ketetapan, SK, AD/ART, risalah, atau kata kunci..."
                   class="h-10 min-w-0 flex-1 border-0 bg-transparent p-0 text-[14px] text-senat-ink placeholder:text-senat-muted focus:outline-none focus:ring-0">
            <x-senat.button size="sm" type="button">Cari</x-senat.button>
        </div>
    </div>

    {{-- ================= FILTER ================= --}}
    <div class="mt-4 rounded-[14px] border border-senat-line bg-white px-5 py-3 shadow-[0_3px_12px_rgba(13,26,41,0.07)]">
        <div class="flex flex-wrap items-center gap-2">
            <span class="mr-1 text-[12px] font-semibold text-senat-muted">Filter:</span>
            <button type="button" @click="filter = 'semua'"
                    :class="filter === 'semua' ? 'bg-senat-primary text-white' : 'bg-[#eaf3fe] text-senat-link'"
                    class="rounded-full border border-transparent px-3.5 py-[3px] text-[11px] font-bold uppercase tracking-[0.06em] transition-colors">
                Semua
            </button>
            @foreach ($categories as $category)
                <button type="button" @click="filter = @js($category)"
                        :class="filter === @js($category) ? 'bg-senat-primary text-white' : 'bg-[#eaf3fe] text-senat-link'"
                        class="rounded-full border border-transparent px-3.5 py-[3px] text-[11px] font-bold uppercase tracking-[0.06em] transition-colors">
                    {{ $category }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- ================= DAFTAR DOKUMEN ================= --}}
    <section class="mt-4 rounded-[14px] border border-senat-line bg-white p-5 shadow-[0_3px_12px_rgba(13,26,41,0.07)]">
        <h2 class="text-[18px] font-bold text-senat-ink">Dokumen Terbaru</h2>

        <ul class="mt-4 divide-y divide-senat-line">
            @forelse ($docs as $doc)
                <li class="flex flex-wrap items-center gap-x-4 gap-y-3 py-4 first:pt-3"
                    x-show="(filter === 'semua' || filter === @js($doc['category'])) && (q === '' || @js(mb_strtolower($doc['title'] . ' ' . $doc['category'] . ' ' . $doc['meta'])).includes(q.toLowerCase()))">
                    <span class="flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-[10px] bg-[#eaf3fe] text-[16px] font-bold text-senat-link" aria-hidden="true"><x-senat.icon name="folder" class="h-[18px] w-[18px]" /></span>

                    <div class="min-w-0 flex-1">
                        <p class="text-[14px] font-semibold leading-snug text-senat-ink">{{ $doc['title'] }}</p>
                        <p class="mt-0.5 text-[12px] text-senat-muted">{{ $doc['category'] }} · {{ $doc['meta'] }}</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <x-senat.badge state="info" :dot="false">{{ $doc['kind'] }}</x-senat.badge>
                        @if ($doc['status'])
                            <x-senat.badge state="neutral" :dot="false">{{ $doc['status'] }}</x-senat.badge>
                        @endif
                        <x-senat.badge state="neutral" :dot="false">{{ $doc['size'] }}</x-senat.badge>
                        <x-senat.button size="sm" variant="secondary" :href="$doc['url']">Buka</x-senat.button>
                    </div>
                </li>
            @empty
                <li class="py-4"><p class="text-[13px] text-senat-muted">Belum ada dokumen yang tersedia.</p></li>
            @endforelse
        </ul>

        <p x-cloak x-show="q !== ''" class="mt-4 border-t border-senat-line pt-4 text-[12px] text-senat-muted">
            Hasil pencarian untuk: <span class="font-semibold text-senat-ink" x-text="q"></span>
        </p>
    </section>

</div>
@endsection
