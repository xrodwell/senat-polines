@props([
    'state' => 'info',   // info | success | warning | danger | neutral
    'dot' => true,
])

@php
    /**
     * Status / Badge — Figma node 2339:1304 (Status=Info | Success | Warning | Danger)
     * Plus "Chip" pills used across the screens (neutral variant).
     */
    $styles = [
        'info' => 'bg-[#f2f9ff] text-senat-link border-[#8cb8e3]',
        'success' => 'bg-[#f0fdf4] text-[#166534] border-[#86efac]',
        'warning' => 'bg-[#fffbeb] text-[#92400e] border-[#fcd34d]',
        'danger' => 'bg-[#fef2f2] text-[#991b1b] border-[#fca5a5]',
        'neutral' => 'bg-senat-surface text-senat-body border-senat-line',
    ];
    $dotColor = [
        'info' => 'bg-senat-link',
        'success' => 'bg-[#16a34a]',
        'warning' => 'bg-[#f59e0b]',
        'danger' => 'bg-[#dc2626]',
        'neutral' => 'bg-senat-muted',
    ];
@endphp

<span {{ $attributes->class('inline-flex items-center gap-1.5 rounded-full border px-2.5 py-[3px] text-[10px] font-bold uppercase tracking-[0.06em] ' . ($styles[$state] ?? $styles['info'])) }}>
    @if ($dot)
        <span class="h-1.5 w-1.5 rounded-full {{ $dotColor[$state] ?? $dotColor['info'] }}" aria-hidden="true"></span>
    @endif
    {{ $slot }}
</span>
