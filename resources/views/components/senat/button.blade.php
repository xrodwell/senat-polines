@props([
    'variant' => 'primary',   // primary | secondary | ghost
    'size' => 'md',           // sm | md | lg
    'href' => null,
    'disabled' => false,
    'loading' => false,
    'icon' => null,           // leading glyph/emoji
    'trailing' => null,       // trailing glyph e.g. ›  ▾
    'type' => 'button',
])

@php
    /**
     * Control / Button — Figma node 2339:1271 (State=Primary | Secondary | Disabled | Loading)
     */
    $sizes = [
        'sm' => 'h-9 px-4 text-[11.5px]',
        'md' => 'h-10 px-5 text-[12px]',
        'lg' => 'h-12 px-6 text-[13px]',
    ];
    $variants = [
        'primary' => 'bg-senat-primary text-white font-bold hover:bg-senat-primaryHover',
        'secondary' => 'bg-[#f8fafc] text-senat-primary font-semibold border border-senat-border hover:border-senat-primary',
        'ghost' => 'bg-transparent text-senat-primary font-semibold hover:bg-senat-surface',
    ];
    $base = 'inline-flex items-center justify-center gap-2 rounded-md transition-colors duration-150 shrink-0';
    $classes = trim($base . ' ' . ($sizes[$size] ?? $sizes['md']) . ' ' . ($variants[$variant] ?? $variants['primary'])
        . ($disabled || $loading ? ' opacity-55 pointer-events-none' : ''));
@endphp

@if ($href && ! $disabled && ! $loading)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        @if ($icon)<span aria-hidden="true">{{ $icon }}</span>@endif
        {{ $slot }}
        @if ($trailing)<span aria-hidden="true">{{ $trailing }}</span>@endif
    </a>
@else
    <button type="{{ $type }}" @disabled($disabled || $loading) {{ $attributes->class($classes) }}>
        @if ($loading)
            <svg class="h-3.5 w-3.5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
            </svg>
        @elseif ($icon)
            <span aria-hidden="true">{{ $icon }}</span>
        @endif
        {{ $slot }}
        @if ($trailing)<span aria-hidden="true">{{ $trailing }}</span>@endif
    </button>
@endif
