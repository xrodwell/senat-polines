@props(['meeting'])

@php
    $date       = \Carbon\Carbon::parse($meeting['date']);
    $day        = $date->format('d');
    $month      = $date->translatedFormat('M');
    $isActive   = ($meeting['status'] ?? '') === 'aktif';
    $statusLabel = $isActive ? 'Sedang Berlangsung' : 'Terjadwal';
    $statusClass = $isActive
        ? 'bg-emerald-50 text-emerald-700'
        : 'bg-slate-100 text-slate-600';
@endphp

<div class="flex items-start gap-4 border border-slate-200/80 bg-white rounded-xl p-5 hover:border-slate-300 transition-all duration-150">

    {{-- Date Badge --}}
    <div class="shrink-0 w-14 text-center">
        <div class="bg-polines-navy rounded-xl py-2.5 px-1">
            <p class="text-2xl font-extrabold text-white leading-none tabular-nums">{{ $day }}</p>
            <p class="text-xs font-semibold text-blue-200 mt-1 uppercase tracking-wide">{{ $month }}</p>
        </div>
    </div>

    {{-- Detail --}}
    <div class="flex-1 min-w-0">
        <div class="flex flex-wrap items-start justify-between gap-2">
            <div class="min-w-0">
                {{-- Type --}}
                @if (!empty($meeting['type']))
                    <p class="text-xs font-semibold text-polines-blue uppercase tracking-widest mb-0.5">
                        {{ $meeting['type'] }}
                    </p>
                @endif

                {{-- Title --}}
                <h3 class="text-sm font-bold text-slate-900 tracking-tight leading-snug">
                    {{ $meeting['title'] }}
                </h3>
            </div>

            {{-- Status Pill --}}
            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold shrink-0 {{ $statusClass }}">
                @if ($isActive)
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                @endif
                {{ $statusLabel }}
            </span>
        </div>

        {{-- Meta --}}
        <div class="mt-2.5 flex flex-wrap items-center gap-x-4 gap-y-1.5">
            @if (!empty($meeting['time']))
                <span class="inline-flex items-center gap-1.5 text-xs text-slate-500">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="tabular-nums">{{ $meeting['time'] }} WIB</span>
                </span>
            @endif

            @if (!empty($meeting['room']))
                <span class="inline-flex items-center gap-1.5 text-xs text-slate-500">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    {{ $meeting['room'] }}
                </span>
            @endif

            @if (!empty($meeting['session_code']))
                <span class="inline-flex items-center gap-1.5 text-xs text-slate-500">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                    </svg>
                    <span class="tabular-nums font-mono font-medium text-slate-600">{{ $meeting['session_code'] }}</span>
                </span>
            @endif
        </div>
    </div>

</div>
