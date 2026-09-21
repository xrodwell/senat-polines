@props(['post'])

<article class="group border border-slate-200/80 bg-white rounded-xl overflow-hidden hover:border-slate-300 transition-all duration-150 flex flex-col">

    {{-- Thumbnail --}}
    @if (!empty($post['thumbnail']))
        <div class="aspect-video overflow-hidden bg-slate-100">
            <img
                src="{{ $post['thumbnail'] }}"
                alt="{{ $post['title'] }}"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300 ease-out"
                loading="lazy"
            >
        </div>
    @else
        <div class="aspect-video bg-slate-100 flex items-center justify-center">
            <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
            </svg>
        </div>
    @endif

    {{-- Body --}}
    <div class="p-5 flex flex-col flex-1 gap-3">

        {{-- Category & Date --}}
        <div class="flex items-center justify-between gap-2">
            @if (!empty($post['category']))
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-blue-50 text-blue-800">
                    {{ $post['category'] }}
                </span>
            @endif
            @if (!empty($post['date']))
                <time class="text-xs text-slate-400 tabular-nums" datetime="{{ $post['date'] }}">
                    {{ \Carbon\Carbon::parse($post['date'])->translatedFormat('d M Y') }}
                </time>
            @endif
        </div>

        {{-- Title --}}
        <h3 class="text-sm font-bold text-slate-900 leading-snug tracking-tight line-clamp-2 group-hover:text-polines-navy transition-colors duration-150">
            {{ $post['title'] }}
        </h3>

        {{-- Excerpt --}}
        @if (!empty($post['excerpt']))
            <p class="text-sm text-slate-600 leading-relaxed line-clamp-3 flex-1">
                {{ $post['excerpt'] }}
            </p>
        @endif

        {{-- Read More --}}
        @if (!empty($post['slug']))
            <div class="pt-1 mt-auto">
                <a
                    href="{{ url('/berita/' . $post['slug']) }}"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-polines-blue hover:text-polines-navy transition-colors duration-150"
                >
                    Baca Selengkapnya
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        @endif

    </div>
</article>
