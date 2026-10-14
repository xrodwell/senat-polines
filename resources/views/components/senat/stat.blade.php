@props([
    'label' => null,
    'value' => null,
    'hint' => null,
    'icon' => null,
])

{{-- Stat / KPI card — Figma nodes 2271:785 (KPI/Agenda), 2280:996 (Stat/Sidang terjadwal), 2281:1570 (Stat/Kehadiran) --}}
<div {{ $attributes->class('rounded-xl border border-senat-line bg-white p-5') }}>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="text-[10px] font-bold uppercase tracking-[0.08em] text-senat-muted">{{ $label }}</p>
            <p class="mt-2 text-[26px] font-extrabold leading-none text-senat-ink tabular-nums">{{ $value }}</p>
            @if ($hint)
                <p class="mt-2 text-[11px] leading-snug text-senat-muted">{{ $hint }}</p>
            @endif
        </div>
        @if ($icon)
            <span class="flex h-[38px] w-[38px] shrink-0 items-center justify-center rounded-[10px] border border-senat-badgeLine bg-senat-badgeBg text-senat-link" aria-hidden="true">
                <x-senat.icon :name="$icon" class="h-[18px] w-[18px]" />
            </span>
        @endif
    </div>
</div>
