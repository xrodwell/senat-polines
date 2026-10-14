@props([
    'title' => 'Belum ada data',
    'message' => null,
    'actionLabel' => null,
    'actionHref' => null,
])

{{-- Empty / Content — Figma node 2339:1329 --}}
<div {{ $attributes->class('flex flex-col items-center justify-center rounded-xl border border-dashed border-senat-border bg-white px-6 py-12 text-center') }}>
    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-senat-surface text-[18px] text-senat-muted" aria-hidden="true">📭</span>
    <p class="mt-4 text-[14px] font-semibold text-senat-ink">{{ $title }}</p>
    @if ($message)
        <p class="mt-1 max-w-md text-[12px] leading-relaxed text-senat-muted">{{ $message }}</p>
    @endif
    @if ($actionLabel && $actionHref)
        <x-senat.button class="mt-5" variant="secondary" size="md" :href="$actionHref">{{ $actionLabel }}</x-senat.button>
    @endif
</div>
