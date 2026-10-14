@props([
    'eyebrow' => null,
    'title' => null,
    'subtitle' => null,
    'height' => 'h-[390px]',
])

{{-- Profile hero — navy band used on public sub-pages (Figma node 2204:149 pattern) --}}
<section class="bg-senat-hero {{ $height }}">
    <div class="senat-shell flex h-full flex-col justify-center">
        @if ($eyebrow)
            <p class="text-[11px] font-bold uppercase tracking-[0.1em] text-senat-sky">{{ $eyebrow }}</p>
        @endif
        @if ($title)
            <h1 class="mt-4 max-w-[720px] text-[42px] font-bold leading-[1.08] text-white">{{ $title }}</h1>
        @endif
        @if ($subtitle)
            <p class="mt-5 max-w-[620px] text-[15px] leading-[1.42] text-senat-skySoft">{{ $subtitle }}</p>
        @endif
        {{ $slot }}
    </div>
</section>
