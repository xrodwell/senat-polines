@extends('layouts.public')

@section('title', $post['title'] . ' — Warta Senat Akademik Politeknik Negeri Semarang')

@section('content')

    @php
        $body = $post['body'] ?? [];
        $images = array_values(array_unique(array_filter(
            [$post['thumbnail'] ?? null, ...(is_array($post['images'] ?? null) ? $post['images'] : [])],
            fn ($image) => is_string($image) && $image !== ''
        )));
        $attachments = is_array($post['attachments'] ?? null) ? $post['attachments'] : [];
        $wordCount = collect($body)->sum(fn ($paragraph) => str_word_count(strip_tags($paragraph)));
        $readMinutes = max(1, (int) ceil($wordCount / 200));
    @endphp

    {{-- ===================== BREADCRUMB BAR (Figma 2224:265) ===================== --}}
    <div class="border-b border-slate-200 bg-white">
        <div class="senat-shell flex h-14 items-center gap-3">
            <a href="{{ route('public.news') }}"
               class="inline-flex shrink-0 items-center gap-1 text-[12.5px] font-semibold text-senat-hero transition-colors duration-150 hover:text-senat-primary">
                <span aria-hidden="true">‹</span>
                Kembali ke Warta Senat
            </a>
            <nav class="hidden min-w-0 text-[12px] text-slate-400 sm:block" aria-label="Breadcrumb">
                <span class="mx-2">/</span>
                <a href="{{ route('public.home') }}" class="transition-colors duration-150 hover:text-senat-primary">Beranda</a>
                <span class="mx-1.5">/</span>
                <a href="{{ route('public.news') }}" class="transition-colors duration-150 hover:text-senat-primary">Warta Senat</a>
                <span class="mx-1.5">/</span>
                <span class="text-slate-500">{{ \Illuminate\Support\Str::limit($post['title'], 60) }}</span>
            </nav>
        </div>
    </div>

    <article class="senat-shell py-10">

        {{-- Header warta --}}
        <span class="inline-flex items-center rounded bg-senat-badgeBg px-3 py-1 text-[10.5px] font-bold uppercase tracking-[0.06em] text-senat-hero">
            {{ $post['category'] }}
        </span>

        <h1 class="mt-4 max-w-[1264px] text-[26px] font-bold leading-[1.15] tracking-tight text-[#0f172a] sm:text-[28px]">
            {{ $post['title'] }}
        </h1>

        {{-- Meta: kategori, tanggal, penulis (Figma 2225:250) --}}
        <p class="mt-4 flex flex-wrap items-center gap-x-3 gap-y-1 text-[12.5px] font-medium text-slate-500">
            <span class="inline-flex items-center gap-1.5">
                <x-senat.icon name="calendar" class="h-3.5 w-3.5" />
                {{ \Carbon\Carbon::parse($post['date'])->translatedFormat('d F Y') }}
            </span>
            <span class="text-slate-300">|</span>
            <span class="inline-flex items-center gap-1.5">
                <x-senat.icon name="user" class="h-3.5 w-3.5" />
                {{ $post['author'] }}
            </span>
            <span class="text-slate-300">|</span>
            <span class="inline-flex items-center gap-1.5">
                <x-senat.icon name="clock" class="h-3.5 w-3.5" />
                {{ $readMinutes }} Menit Membaca
            </span>
            <span class="text-slate-300">|</span>
            <span class="inline-flex items-center gap-1.5">
                <span aria-hidden="true">🏷️</span>
                {{ $post['category'] }}
            </span>
        </p>

        {{-- Dokumentasi foto (Figma 2225:251) --}}
        @if (count($images) > 0)
            <figure class="relative mt-12 h-[240px] overflow-hidden rounded-[10px] bg-[#0f172a] sm:mt-14 sm:h-[340px]">
                <img src="{{ $images[0] }}" alt="{{ $post['title'] }}"
                     class="absolute inset-0 h-full w-full object-cover" loading="lazy">
                <div class="absolute inset-0 bg-gradient-to-t from-[#0f172a] via-[#0f172a]/40 to-transparent"></div>
                <figcaption class="absolute inset-x-0 bottom-0 p-6 sm:p-8">
                    <p class="text-[14px] font-bold text-slate-100">Dokumentasi Resmi — {{ $post['title'] }}</p>
                    <p class="mt-1 text-[11.5px] leading-relaxed text-slate-400">
                        Ruang Sidang Utama Gedung Rektorat Lt. 2, Kampus Terpadu Tembalang, Semarang (Foto: Humas &amp; Protokoler Senat).
                    </p>
                </figcaption>
            </figure>

            @if (count($images) > 1)
                <div class="mt-6 grid grid-cols-2 gap-3 sm:mt-7 sm:grid-cols-3 sm:gap-4">
                    @foreach (array_slice($images, 1) as $image)
                        <figure class="overflow-hidden rounded-lg border border-slate-200 bg-slate-100">
                            <img src="{{ $image }}" alt="Dokumentasi tambahan {{ $loop->iteration }} — {{ $post['title'] }}"
                                 class="aspect-[4/3] h-full w-full object-cover" loading="lazy">
                        </figure>
                    @endforeach
                </div>
            @endif
        @endif

        <div class="mt-14 grid gap-8 lg:grid-cols-[minmax(0,1fr)_420px]">

            {{-- ===================== BODY ARTIKEL ===================== --}}
            <div class="rounded-[10px] border border-slate-200 bg-white p-6 sm:p-9">
                @forelse ($body as $paragraph)
                    <p class="{{ $loop->first
                        ? 'text-[15px] font-semibold leading-relaxed text-[#1e293b]'
                        : 'mt-5 text-[14px] leading-relaxed text-[#334155]' }}">
                        {{ $paragraph }}
                    </p>
                @empty
                    <p class="text-[14px] text-slate-500">Isi warta belum tersedia.</p>
                @endforelse

                @if (count($attachments) > 0)
                    <section class="mt-8 border-t border-slate-200 pt-5" aria-labelledby="attachments-title">
                        <h2 id="attachments-title" class="text-[14px] font-bold text-[#0f172a]">Lampiran Berita</h2>
                        <ul class="mt-3 space-y-2">
                            @foreach ($attachments as $attachment)
                                <li>
                                    <a href="{{ $attachment['url'] }}" target="_blank" rel="noopener noreferrer"
                                       class="inline-flex items-center gap-2 text-[13px] font-semibold text-senat-link hover:underline">
                                        <x-senat.icon name="file-text" class="h-4 w-4 shrink-0" />
                                        <span>{{ $attachment['name'] ?? 'Unduh lampiran' }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif
            </div>

            {{-- ===================== SIDEBAR ===================== --}}
            <aside class="space-y-6">

                {{-- Kartu info warta (Figma 2225:280) --}}
                <div class="rounded-[10px] border border-slate-200 bg-white p-6">
                    <h2 class="text-[15px] font-bold text-[#0f172a]">Informasi Warta</h2>

                    <div class="mt-5 space-y-4">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.08em] text-slate-400">Kategori</p>
                            <p class="mt-1 text-[12.5px] font-semibold text-[#1e293b]">{{ $post['category'] }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.08em] text-slate-400">Tanggal Terbit</p>
                            <p class="mt-1 text-[12.5px] font-semibold text-[#1e293b]">
                                {{ \Carbon\Carbon::parse($post['date'])->translatedFormat('d F Y') }}
                            </p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.08em] text-slate-400">Penulis</p>
                            <p class="mt-1 text-[12.5px] font-semibold text-[#1e293b]">{{ $post['author'] }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.08em] text-slate-400">Estimasi Baca</p>
                            <p class="mt-1 text-[12.5px] font-semibold text-[#1e293b]">{{ $readMinutes }} menit</p>
                        </div>
                    </div>
                </div>

                {{-- Kartu warta terkait (Figma 2225:292) --}}
                @if (count($related) > 0)
                    <div class="rounded-[10px] border border-slate-200 bg-white p-6">
                        <h2 class="text-[15px] font-bold text-[#0f172a]">Warta Terkait Lainnya</h2>
                        <div class="mt-2">
                            @foreach ($related as $item)
                                <a href="{{ route('public.news.show', $item['slug']) }}"
                                   class="block border-b border-slate-100 py-4 last:border-b-0">
                                    <p class="text-[11px] font-medium text-slate-400">
                                        <x-senat.icon name="calendar" class="h-3.5 w-3.5" />
                                        {{ \Carbon\Carbon::parse($item['date'])->translatedFormat('d M Y') }}
                                    </p>
                                    <p class="mt-1 text-[12.5px] font-semibold leading-snug text-[#0f172a] transition-colors duration-150 hover:text-senat-primary">
                                        {{ $item['title'] }}
                                    </p>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Banner aspirasi (Figma 2225:302) --}}
                <div class="rounded-[10px] bg-senat-primary p-6">
                    <span class="inline-flex rounded bg-senat-orange px-3 py-1 text-[10px] font-bold uppercase tracking-[0.06em] text-white">
                        Suara Civitas
                    </span>
                    <h2 class="mt-4 text-[14px] font-bold text-white">Punya Masukan Terkait Kebijakan Akademik?</h2>
                    <p class="mt-2 text-[11.5px] leading-relaxed text-slate-300">
                        Kanal aspirasi Senat siap menampung telaah dan saran dosen maupun mahasiswa secara transparan.
                    </p>
                    <a href="{{ route('public.aspirations') }}"
                       class="mt-4 inline-flex h-[34px] items-center gap-1.5 rounded-md bg-white px-5 text-[11.5px] font-bold text-senat-primary transition-colors duration-150 hover:bg-slate-100">
                        Sampaikan Aspirasi
                        <span aria-hidden="true">›</span>
                    </a>
                </div>
            </aside>
        </div>
    </article>

@endsection
