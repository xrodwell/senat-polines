@extends('layouts.public')

@section('title', 'Warta & Berita — Senat Akademik Politeknik Negeri Semarang')

@section('content')

    @php
        $featured = $posts[0] ?? null;
        $gridPosts = array_slice($posts, 1);
        $newsCategories = collect($posts)->pluck('category')->filter()->unique()->values()->all();
    @endphp

    {{-- ===================== HERO (Figma 2218:224 News Hero) ===================== --}}
    <section class="relative overflow-hidden bg-senat-primary">
        <div class="absolute inset-x-0 top-0 h-1 bg-senat-orange" aria-hidden="true"></div>
        <div class="senat-shell py-14 lg:py-[52px]">
            <p class="text-[11px] font-bold uppercase tracking-[0.1em] text-senat-orange">Kabar &amp; Informasi Resmi Senat</p>
            <h1 class="mt-4 max-w-[820px] text-[28px] font-bold leading-tight tracking-tight text-white sm:text-[32px]">
                Warta &amp; Berita Senat Akademik
            </h1>
            <p class="mt-5 max-w-[800px] text-[15px] leading-relaxed text-slate-300">
                Publikasi siaran pers resmi, liputan sidang pleno, risalah kebijakan akademik, serta kegiatan komisi Senat Politeknik Negeri Semarang.
            </p>
            <nav class="mt-8 text-[12.5px] font-medium text-slate-400" aria-label="Breadcrumb">
                <a href="{{ route('public.home') }}" class="transition-colors duration-150 hover:text-white">Beranda</a>
                <span class="mx-2">/</span>
                <a href="{{ route('public.archives') }}" class="transition-colors duration-150 hover:text-white">Arsip &amp; Publikasi</a>
                <span class="mx-2">/</span>
                <span class="text-slate-200">Warta Senat</span>
            </nav>
        </div>
    </section>

    {{-- ============ FILTER + BERITA UTAMA + GRID + PAGINASI ============ --}}
    <section class="senat-shell py-10"
        x-data="{
            query: '',
            cat: '',
            matches(el) {
                const q = this.query.toLowerCase().trim();
                const inCat = this.cat === '' || el.dataset.cat === this.cat;
                const inQuery = !q || (el.dataset.text || '').includes(q);
                return inCat && inQuery;
            }
        }">

        @if (count($posts) > 0)

            {{-- Filter bar (Figma 2219:222) --}}
            <div class="mb-8 rounded-lg border border-slate-200 bg-white p-3">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                    <div class="relative w-full lg:w-[320px]">
                        <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input x-model="query" type="search" placeholder="Cari warta atau topik sidang..."
                               class="h-9 w-full rounded-md border border-slate-200 bg-slate-50 pl-9 pr-3 text-[12.5px] text-slate-800 placeholder-slate-400 transition-colors duration-150 focus:border-senat-primary focus:outline-none focus:ring-2 focus:ring-senat-primary/25">
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button type="button" data-cat="" @click="cat = ''"
                                :class="cat === '' ? 'border-senat-primary bg-senat-primary text-white' : 'border-slate-200 bg-white text-slate-600 hover:border-senat-primary/50'"
                                class="inline-flex h-8 items-center rounded-md border px-3.5 text-[12px] font-semibold transition-colors duration-150">
                            Semua Kategori
                        </button>
                        @foreach ($newsCategories as $category)
                            <button type="button" data-cat="{{ $category }}" @click="cat = $el.dataset.cat"
                                    :class="cat === $el.dataset.cat ? 'border-senat-primary bg-senat-primary text-white' : 'border-slate-200 bg-white text-slate-600 hover:border-senat-primary/50'"
                                    class="inline-flex h-8 items-center rounded-md border px-3.5 text-[12px] font-semibold transition-colors duration-150">
                                {{ $category }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Berita utama / featured (Figma 2219:235) --}}
            @if ($featured)
                <a href="{{ route('public.news.show', $featured['slug']) }}"
                   x-show="cat === '' && query.trim() === ''"
                   class="group mb-12 grid overflow-hidden rounded-[10px] border border-slate-200 bg-white transition-colors duration-150 hover:border-senat-primary/40 lg:grid-cols-[520px_minmax(0,1fr)]">
                    <div class="relative h-[240px] overflow-hidden bg-[#0f1e36] lg:h-full lg:min-h-[320px]">
                        @if (! empty($featured['thumbnail']))
                            <img src="{{ $featured['thumbnail'] }}" alt="{{ $featured['title'] }}"
                                 class="absolute inset-0 h-full w-full object-cover transition-transform duration-300 group-hover:scale-[1.03]" loading="lazy">
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-t from-[#0f1e36]/85 via-[#0f1e36]/20 to-transparent"></div>
                        <span class="absolute left-6 top-6 rounded bg-senat-orange px-3 py-1 text-[10.5px] font-bold uppercase tracking-[0.06em] text-white">
                            Berita Utama
                        </span>
                        <p class="absolute bottom-6 left-6 right-6 text-[13px] font-medium text-slate-300">
                            Dokumentasi Kegiatan Senat Akademik Polines
                        </p>
                    </div>

                    <div class="flex flex-col justify-center p-6 lg:px-9 lg:py-8">
                        <p class="text-[11px] font-bold uppercase tracking-[0.06em] text-senat-hero">
                            {{ $featured['category'] }}
                            <span class="mx-1 text-slate-300">•</span>
                            {{ \Carbon\Carbon::parse($featured['date'])->translatedFormat('d F Y') }}
                        </p>
                        <h2 class="mt-3 text-[20px] font-bold leading-snug tracking-tight text-[#0f172a] transition-colors duration-150 group-hover:text-senat-primary">
                            {{ $featured['title'] }}
                        </h2>
                        @if (! empty($featured['excerpt']))
                            <p class="mt-3 line-clamp-3 text-[13.5px] leading-relaxed text-slate-600">{{ $featured['excerpt'] }}</p>
                        @endif
                        <p class="mt-4 text-[12px] font-medium text-slate-400">Oleh: {{ $featured['author'] }}</p>
                        <span class="mt-5 inline-flex h-9 w-fit items-center gap-1.5 rounded-md bg-senat-primary px-5 text-[12px] font-bold text-white">
                            Baca Siaran Pers
                            <span aria-hidden="true">›</span>
                        </span>
                    </div>
                </a>
            @endif

            {{-- Judul grid --}}
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.1em] text-senat-hero">Arsip Warta Terbaru</p>
                <h2 class="mt-2 text-[20px] font-bold tracking-tight text-[#0f172a]">Semua Siaran &amp; Liputan Kegiatan Senat</h2>
            </div>

            {{-- Grid kartu berita (Figma 2220:224 News Card) --}}
            @if (count($gridPosts) > 0)
                <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($gridPosts as $post)
                        <div
                            data-news-card
                            data-cat="{{ $post['category'] }}"
                            data-text="{{ strtolower($post['title'] . ' ' . $post['category'] . ' ' . ($post['excerpt'] ?? '')) }}"
                            x-show="matches($el)"
                            x-transition.opacity.duration.150ms
                        >
                            <x-news-card :post="$post" />
                        </div>
                    @endforeach
                </div>

                <div
                    x-show="!Array.from($root.querySelectorAll('[data-news-card]')).some(el => matches(el))"
                    x-cloak
                    class="mt-6 rounded-xl border border-dashed border-senat-border bg-white px-6 py-10 text-center"
                >
                    <p class="text-sm text-slate-500">Tidak ada warta yang cocok dengan filter atau pencarian Anda.</p>
                </div>
            @endif

            {{-- Paginasi visual (Figma 2220:272) --}}
            <div class="mt-8 flex flex-col items-center justify-between gap-4 rounded-lg border border-slate-200 bg-white px-5 py-3 sm:flex-row">
                <p class="text-[12.5px] text-slate-500">
                    Menampilkan 1 – {{ count($gridPosts) }} dari {{ count($posts) }} warta dan siaran pers
                </p>
                <nav class="flex items-center gap-2" aria-label="Navigasi halaman">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 bg-slate-50 text-[12px] font-bold text-slate-400">«</span>
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-senat-primary text-[12px] font-bold text-white">1</span>
                    @foreach ([2, 3, 4] as $page)
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 bg-slate-50 text-[12px] font-bold text-slate-500">{{ $page }}</span>
                    @endforeach
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 bg-slate-50 text-[12px] font-bold text-slate-400">»</span>
                </nav>
            </div>

        @else
            <x-senat.empty title="Belum ada warta" message="Siaran pers dan berita kegiatan Senat Akademik akan tampil di sini." />
        @endif
    </section>

@endsection
