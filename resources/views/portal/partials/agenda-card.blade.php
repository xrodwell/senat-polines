{{--
    Partial — highlight agenda sidang pada 12B (Agenda & Presensi).
    Dipakai oleh portal/agenda.blade.php dengan $meeting dan closure $tanggalPanjang.
--}}
@php
    $status = $meeting['status'] ?? 'Terjadwal';
    $statusState = match ($status) {
        'Sedang Berlangsung' => 'success',
        'Terjadwal' => 'info',
        default => 'neutral',
    };
    $sedangBerlangsung = $status === 'Sedang Berlangsung';
@endphp

<article class="rounded-[14px] border border-senat-line bg-[#eaf3fe] p-5 shadow-[0_3px_12px_rgba(13,26,41,0.07)]">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0">
            <h3 class="text-[18px] font-bold leading-snug text-senat-ink">{{ $meeting['title'] }}</h3>
            <div class="mt-3 flex flex-wrap items-center gap-2">
                <x-senat.badge :state="$sedangBerlangsung ? 'success' : 'warning'" :dot="false">
                    {{ $sedangBerlangsung ? 'Sedang berlangsung' : 'Perlu presensi' }}
                </x-senat.badge>
                <x-senat.badge state="neutral" :dot="false">{{ $meeting['type'] }}</x-senat.badge>
                <x-senat.badge :state="$statusState" :dot="false">{{ $status }}</x-senat.badge>
            </div>
        </div>

        <div class="flex shrink-0 flex-wrap items-center gap-2">
            <x-senat.button size="sm" type="button">Presensi QR</x-senat.button>
            <x-senat.button size="sm" variant="secondary" type="button">Ajukan Izin</x-senat.button>
        </div>
    </div>

    <p class="mt-4 text-[13px] leading-relaxed text-senat-muted">{{ $meeting['room'] }}</p>

    <dl class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div>
            <dt class="flex items-center gap-2 text-[11px] font-medium text-senat-muted">
                <span aria-hidden="true"><x-senat.icon name="calendar" class="h-[18px] w-[18px]" /></span> Waktu
            </dt>
            <dd class="mt-1 text-[13px] font-semibold text-senat-ink">
                {{ $tanggalPanjang($meeting['date']) }} · {{ $meeting['time'] }}
            </dd>
        </div>
        <div>
            <dt class="flex items-center gap-2 text-[11px] font-medium text-senat-muted">
                <span aria-hidden="true"><x-senat.icon name="folder" class="h-[18px] w-[18px]" /></span> Lokasi
            </dt>
            <dd class="mt-1 text-[13px] font-semibold text-senat-ink">{{ $meeting['room'] }}</dd>
        </div>
        <div>
            <dt class="flex items-center gap-2 text-[11px] font-medium text-senat-muted">
                <span aria-hidden="true">#</span> Kode sesi
            </dt>
            <dd class="mt-1 text-[13px] font-semibold tabular-nums text-senat-ink">{{ $meeting['session_code'] }}</dd>
        </div>
    </dl>

    <div class="mt-5 flex flex-wrap gap-x-6 gap-y-1 border-t border-senat-line pt-4 text-[11px] text-senat-muted">
        <p>Terundang: <span class="font-semibold text-senat-ink tabular-nums">{{ $meeting['total_invited'] }}</span> anggota</p>
        <p>Hadir tercatat: <span class="font-semibold text-senat-ink tabular-nums">{{ $meeting['present_count'] }}</span> anggota</p>
    </div>
</article>
