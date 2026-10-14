@extends('layouts.portal', ['sidebar' => 'sekretariat'])

@section('title', 'Presensi & Kuorum — Portal Internal Senat Akademik Polines')

@section('content')
    @php
        /* 13D — Dashboard Sekretariat / Presensi & Kuorum (Figma node 2281:1043) */
        $activeMeeting = $stats['active'] ?? collect($meetings)->first();
        $invited = (int) ($activeMeeting['total_invited'] ?? 0);
        $present = (int) ($activeMeeting['present_count'] ?? 0);
        $pending = max($invited - $present, 0);
    @endphp

    <div class="mx-auto max-w-[1116px] space-y-6">
        <header>
            <h1 class="text-[25px] font-bold leading-tight text-senat-ink">Presensi &amp; Kuorum</h1>
            <p class="mt-2 text-[13px] leading-snug text-senat-body">
                Validasi kehadiran sidang sebelum status kuorum diputuskan.
            </p>
        </header>

        <div class="grid gap-5 xl:grid-cols-[730px_minmax(0,1fr)]">
            {{-- Status Presensi --}}
            <section class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
                @if ($activeMeeting)
                    @php $isActive = ($activeMeeting['status'] ?? '') === 'Sedang Berlangsung'; @endphp
                    <x-senat.badge :state="$isActive ? 'success' : 'neutral'">
                        {{ $isActive ? 'Sidang Aktif' : $activeMeeting['status'] }}
                    </x-senat.badge>
                    <h2 class="mt-4 text-[20px] font-bold leading-snug text-senat-ink">{{ $activeMeeting['title'] }}</h2>
                    <p class="mt-2 text-[13px] text-senat-body">
                        {{ $activeMeeting['room'] }} · {{ $activeMeeting['time'] }}
                    </p>
                @else
                    <h2 class="text-[20px] font-bold text-senat-ink">Belum ada sidang aktif</h2>
                    <p class="mt-2 text-[13px] text-senat-body">Presensi dapat dibuka pada sidang yang sedang berlangsung.</p>
                @endif

                <h3 class="mt-6 text-[15px] font-semibold text-senat-ink">Rekap Kehadiran</h3>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-[10px] bg-[#eaf8f0] px-3.5 py-3">
                        <p class="text-[12px] font-medium text-senat-ink">Hadir tercatat</p>
                        <p class="mt-1 text-[11px] text-senat-muted">
                            {{ $activeMeeting ? $present.' peserta' : 'Belum ada data' }}
                        </p>
                    </div>
                    <div class="rounded-[10px] bg-[#fff4df] px-3.5 py-3">
                        <p class="text-[12px] font-medium text-senat-ink">Menunggu validasi</p>
                        <p class="mt-1 text-[11px] text-senat-muted">
                            {{ $activeMeeting ? $pending.' peserta' : 'Belum ada data' }}
                        </p>
                    </div>
                    <div class="rounded-[10px] bg-senat-badgeBg px-3.5 py-3">
                        <p class="text-[12px] font-medium text-senat-ink">Izin resmi</p>
                        <p class="mt-1 text-[11px] text-senat-muted">Belum ada data</p>
                    </div>
                    <div class="rounded-[10px] bg-[#fdecef] px-3.5 py-3">
                        <p class="text-[12px] font-medium text-senat-ink">Tidak hadir</p>
                        <p class="mt-1 text-[11px] text-senat-muted">Belum ada data</p>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap items-center gap-3">
                    <x-senat.button variant="secondary" type="button">Bekukan setelah validasi</x-senat.button>
                    <x-senat.button variant="primary" :href="route('portal.attendance')">Buka Pemindai QR</x-senat.button>
                </div>

                <div class="mt-5 flex items-center gap-3 rounded-[8px] bg-[#fff4df] px-3 py-2">
                    <span class="text-[15px] font-semibold text-[#a95100]" aria-hidden="true">i</span>
                    <p class="text-[12px] text-senat-ink">Status validasi dan penguncian tampil langsung di kartu presensi.</p>
                </div>
            </section>

            {{-- Status Kuorum --}}
            <section class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
                <h2 class="text-[18px] font-bold text-senat-ink">Status Kuorum</h2>
                <p class="mt-1.5 text-[12px] text-senat-body">Angka diisi otomatis sesudah presensi disahkan.</p>

                <dl class="mt-5 space-y-3">
                    <div class="flex items-center justify-between gap-3 rounded-[10px] bg-senat-surface px-4 py-3.5">
                        <dt class="text-[13px] font-medium text-senat-ink">Peserta terdaftar</dt>
                        <dd class="text-[11px] text-senat-muted">{{ $activeMeeting ? $invited.' peserta' : 'Belum ada data' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 rounded-[10px] bg-[#eaf8f0] px-4 py-3.5">
                        <dt class="text-[13px] font-medium text-senat-ink">Hadir tercatat</dt>
                        <dd class="text-[11px] text-senat-muted">{{ $activeMeeting ? $present.' peserta' : 'Belum ada data' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 rounded-[10px] bg-senat-surface px-4 py-3.5">
                        <dt class="text-[13px] font-medium text-senat-ink">Ambang kuorum</dt>
                        <dd class="text-[11px] text-senat-muted">Belum ada data</dd>
                    </div>
                </dl>

                <p class="mt-4 text-[12px] leading-relaxed text-senat-body">
                    Data contoh: angka kuorum tidak dipublikasikan sebelum validasi kehadiran selesai.
                </p>

                <x-senat.button variant="primary" size="sm" class="mt-5" type="button">Ekspor Rekap</x-senat.button>
            </section>
        </div>

        {{-- Daftar Hadir Sidang --}}
        <section class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
            <h2 class="text-[18px] font-bold text-senat-ink">Daftar Hadir Sidang</h2>

            <div class="mt-4 grid grid-cols-2 gap-3 text-[11px] font-semibold text-senat-body lg:grid-cols-[440px_150px_140px_minmax(0,1fr)]">
                <span>Anggota</span>
                <span class="hidden lg:block">Status</span>
                <span class="hidden lg:block">Waktu</span>
                <span class="hidden lg:block">Keterangan</span>
            </div>
            <div class="mt-2 h-px bg-senat-sidebarLine"></div>

            <x-senat.empty class="mt-4 border-0 bg-senat-surface" title="Belum ada data presensi tervalidasi"
                           message="Buka pemindai QR atau masukkan data presensi sebelum membekukan kuorum." />
        </section>
    </div>
@endsection
