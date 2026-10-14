@extends('layouts.portal', ['sidebar' => 'sekretariat'])

@section('title', 'Produk, Arsip & Berita — Portal Internal Senat Akademik Polines')

@section('content')
    @php
        /* 13G — Dashboard Sekretariat / Produk, Arsip & Berita (Figma node 2281:1376) */
        $modul = [
            [
                'tone' => 'bg-[#eaf3fe]',
                'accent' => 'text-[#155ead]',
                'icon' => '▣',
                'title' => 'Produk / RUU Kampus',
                'desc' => 'Peraturan, SK, Ketetapan, AD/ART, dan tahapan dokumen.',
                'href' => route('portal.secretariat.publications'),
            ],
            [
                'tone' => 'bg-[#fff4df]',
                'accent' => 'text-[#a95100]',
                'icon' => '≡',
                'title' => 'Risalah & Notulensi',
                'desc' => 'Risalah sidang, keputusan, action items, dan lampiran.',
                'href' => route('portal.secretariat.minutes'),
            ],
            [
                'tone' => 'bg-[#f1eeff]',
                'accent' => 'text-[#5131b4]',
                'icon' => '◉',
                'title' => 'Warta & Berita',
                'desc' => 'Draf, review, terbit, serta arsip publikasi.',
                'href' => route('portal.secretariat.publications'),
            ],
        ];

        $antrian = collect()
            ->merge(collect($regulations)->where('status', '!=', 'Disahkan')->map(fn ($reg) => [
                'dot' => 'bg-[#a95100]',
                'title' => $reg['title'],
                'category' => 'Produk / RUU Kampus',
                'meta' => $reg['nomor'].' · '.$reg['status'],
            ]))
            ->merge(collect($archives)->map(fn ($arc) => [
                'dot' => ($arc['category'] ?? '') === 'Notulen Sidang' ? 'bg-[#5131b4]' : 'bg-[#155ead]',
                'title' => $arc['title'],
                'category' => ($arc['category'] ?? '') === 'Notulen Sidang' ? 'Risalah & Notulensi' : 'Produk / RUU Kampus',
                'meta' => $arc['category'].' · '.$arc['file_size'],
            ]))
            ->merge(collect($posts)->map(fn ($post) => [
                'dot' => 'bg-[#155ead]',
                'title' => $post['title'],
                'category' => 'Warta & Berita',
                'meta' => $post['category'].' · '.\Illuminate\Support\Carbon::parse($post['date'])->format('d M Y'),
            ]))
            ->take(6)
            ->values();
    @endphp

    <div class="mx-auto max-w-[1116px] space-y-6">
        <header>
            <h1 class="text-[25px] font-bold leading-tight text-senat-ink">Produk, Arsip &amp; Berita</h1>
            <p class="mt-2 text-[13px] leading-snug text-senat-body">
                Kelola dokumen hukum, risalah, lampiran, dan publikasi Warta Senat.
            </p>
        </header>

        {{-- Modul --}}
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($modul as $card)
                <section class="rounded-[14px] p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)] {{ $card['tone'] }}">
                    <p class="text-[22px] font-bold leading-none {{ $card['accent'] }}" aria-hidden="true">{{ $card['icon'] }}</p>
                    <h2 class="mt-5 text-[17px] font-bold text-senat-ink">{{ $card['title'] }}</h2>
                    <p class="mt-2 text-[13px] leading-snug text-senat-body">{{ $card['desc'] }}</p>
                    <x-senat.button variant="secondary" size="sm" class="mt-6" :href="$card['href']">Kelola</x-senat.button>
                </section>
            @endforeach
        </div>

        {{-- Antrian Publikasi --}}
        <section class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
            <h2 class="text-[18px] font-bold text-senat-ink">Antrian Publikasi</h2>

            @if ($antrian->isEmpty())
                <x-senat.empty class="mt-4" title="Belum ada dokumen untuk dipublikasikan"
                               message="Dokumen produk hukum, risalah, dan warta akan tampil di sini." />
            @else
                <ul class="mt-3 divide-y divide-senat-sidebarLine">
                    @foreach ($antrian as $row)
                        <li class="flex flex-wrap items-center gap-3 py-4 first:pt-2">
                            <span class="h-[10px] w-[10px] shrink-0 rounded-[5px] {{ $row['dot'] }}" aria-hidden="true"></span>
                            <div class="min-w-0 flex-1">
                                <p class="line-clamp-2 text-[14px] font-semibold leading-snug text-senat-ink">{{ $row['title'] }}</p>
                                <p class="mt-1 text-[12px] text-senat-body">{{ $row['category'] }}</p>
                            </div>
                            <p class="w-full text-[12px] text-senat-body sm:w-auto sm:max-w-[300px]">{{ $row['meta'] }}</p>
                            <x-senat.button variant="secondary" size="sm" class="ml-auto" type="button">Kelola</x-senat.button>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
@endsection
