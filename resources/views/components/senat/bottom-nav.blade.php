@php
    /**
     * Bottom navigation — Figma 2352:1335: "390: bottom navigation, drawer menu, card list 1 kolom;
     * tabel menjadi stacked rows." Hanya untuk <1024 pada portal internal.
     */
    $items = $items ?? [];
@endphp

@if (count($items))
    <nav class="senat-bottomnav fixed inset-x-0 bottom-0 z-30 border-t border-senat-sidebarLine bg-white lg:hidden"
         aria-label="Navigasi cepat">
        <ul class="grid grid-cols-5">
            @foreach (array_slice($items, 0, 5) as $item)
                @php $active = Route::has($item['route']) && request()->routeIs($item['route']); @endphp
                <li>
                    <a href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}"
                       @if ($active) aria-current="page" @endif
                       class="senat-tap flex min-h-[56px] flex-col items-center justify-center gap-1 px-1 {{ $active ? 'text-senat-primary' : 'text-senat-muted' }}">
                        <x-senat.icon :name="$item['icon']" class="h-[22px] w-[22px]" />
                        <span class="senat-clamp-2 max-w-full px-0.5 text-center text-[10px] font-semibold leading-tight">{{ $item['label'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>
@endif
