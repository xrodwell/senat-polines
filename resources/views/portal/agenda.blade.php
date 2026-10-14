@extends('layouts.portal')

@section('title', 'Agenda & Presensi — Portal Internal Senat Akademik Polines')

@section('content')
@php
    /**
     * 12B — Agenda & Presensi (Figma node 2279:763)
     * Area konten 1116px; topbar & sidebar berasal dari layouts.portal.
     */
    $bulanId = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $tanggal = function (string $value) use ($bulanId): string {
        $ts = strtotime($value);
        return $ts ? date('j', $ts) . ' ' . $bulanId[(int) date('n', $ts)] . ' ' . date('Y', $ts) : $value;
    };
    $hari = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
    $tanggalPanjang = function (string $value) use ($bulanId, $hari): string {
        $ts = strtotime($value);
        return $ts ? $hari[date('l', $ts)] . ', ' . date('j', $ts) . ' ' . $bulanId[(int) date('n', $ts)] : $value;
    };

    $active = $stats['active'] ?? null;
    $upcoming = array_values($stats['upcoming'] ?? []);
    $fokus = $active ?: ($upcoming[0] ?? ($meetings[0] ?? null));

    $khusus = array_values(array_filter($meetings, fn ($m) => str_contains($m['type'] ?? '', 'Pleno')));
    $reguler = array_values(array_filter($meetings, fn ($m) => ! str_contains($m['type'] ?? '', 'Pleno')));
    $hariIni = $active ? [$active] : [];
@endphp

<div class="mx-auto w-full max-w-[1116px]" x-data="{ tab: 'hari-ini', kamera: true, kode: '' }">

    {{-- ================= PAGE HEADER ================= --}}
    <header class="mb-6">
        <h1 class="text-[25px] font-bold leading-tight tracking-tight text-senat-ink">Agenda &amp; Presensi</h1>
        <p class="mt-1.5 text-[13px] text-senat-muted">Pilih agenda sidang, konfirmasi kehadiran, atau ajukan izin resmi.</p>
    </header>

    {{-- ================= NOTICE ================= --}}
    @if ($fokus)
        <div class="mb-6 flex flex-wrap items-center gap-x-4 gap-y-3 rounded-[14px] bg-[#fff4df] px-5 py-3 shadow-[0_3px_12px_rgba(13,26,41,0.07)]">
            <span class="text-[16px] font-bold leading-none text-[#a95100]" aria-hidden="true"><x-senat.icon name="warning" class="h-[18px] w-[18px]" /></span>
            <p class="min-w-0 flex-1 text-[13px] font-medium text-[#c56f00]">
                Kehadiran {{ $fokus['title'] }} belum dikonfirmasi.
            </p>
        </div>
    @endif

    {{-- ================= TABS ================= --}}
    <div class="rounded-[14px] border border-senat-line bg-white shadow-[0_3px_12px_rgba(13,26,41,0.07)]">
        <div class="flex flex-wrap" role="tablist" aria-label="Kelompok agenda">
            @foreach ([
                'hari-ini' => ['label' => 'HARI INI', 'icon' => 'calendar'],
                'reguler' => ['label' => 'REGULER', 'icon' => 'list'],
                'khusus' => ['label' => 'KHUSUS', 'icon' => 'list'],
            ] as $key => $meta)
                <button type="button" role="tab" @click="tab = '{{ $key }}'"
                        :aria-selected="tab === '{{ $key }}'"
                        class="relative flex flex-1 items-center justify-center gap-3 py-4 text-[12px] font-semibold tracking-[0.02em] transition-colors sm:flex-none sm:px-8"
                        :class="tab === '{{ $key }}' ? 'text-senat-link' : 'text-senat-ink hover:text-senat-link'">
                    <span class="text-[15px]" aria-hidden="true">{{ $meta['icon'] }}</span>
                    {{ $meta['label'] }}
                    <span x-cloak x-show="tab === '{{ $key }}'"
                          class="absolute inset-x-0 bottom-0 h-[3px] rounded-[2px] bg-senat-link"></span>
                </button>
            @endforeach
        </div>
    </div>

    {{-- ================= AGENDA ================= --}}
    <section class="mt-6 rounded-[14px] border border-senat-line bg-white p-5 shadow-[0_3px_12px_rgba(13,26,41,0.07)]">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-[18px] font-bold text-senat-ink">
                <span x-show="tab === 'hari-ini'">Hari Ini</span>
                <span x-cloak x-show="tab === 'reguler'">Agenda Reguler</span>
                <span x-cloak x-show="tab === 'khusus'">Agenda Khusus</span>
            </h2>
            <p class="text-[13px] font-semibold text-senat-muted">
                {{ $stats['present'] }}/{{ $stats['invited'] }} kehadiran tercatat
            </p>
        </div>

        {{-- ---------- HARI INI ---------- --}}
        <div x-show="tab === 'hari-ini'" class="mt-5 space-y-5">
            @if ($fokus)
                @include('portal.partials.agenda-card', ['meeting' => $fokus, 'tanggalPanjang' => $tanggalPanjang])
            @else
                <x-senat.empty title="Tidak ada sidang hari ini"
                               message="Agenda sidang yang sedang berlangsung akan tampil di sini." />
            @endif

            {{-- Presensi dengan Kamera --}}
            <div class="rounded-xl border border-senat-line bg-[#f5f8fc] p-4">
                <div class="grid grid-cols-1 gap-5 lg:grid-cols-[468px_1fr]">
                    {{-- Camera preview --}}
                    <div class="relative overflow-hidden rounded-[10px] bg-[#0d1b2d]" x-show="kamera">
                        <div class="flex h-[318px] flex-col px-4 py-4">
                            <div class="flex items-center justify-between">
                                <span class="rounded-full bg-[#16395f] px-3 py-1.5 text-[10px] font-semibold tracking-[0.06em] text-[#bfdbfe]">KAMERA AKTIF</span>
                                <span class="text-[12px] font-semibold text-[#55d68a]" aria-hidden="true">●</span>
                            </div>

                            <div class="relative mx-auto mt-3 flex h-[176px] w-[176px] items-center justify-center rounded-[12px] border-2 border-[#38bdf8] bg-[#102640]">
                                <span class="absolute left-0 top-0 h-[3px] w-7 bg-[#55d68a]"></span>
                                <span class="absolute left-0 top-0 h-7 w-[3px] bg-[#55d68a]"></span>
                                <span class="absolute right-0 top-0 h-[3px] w-7 bg-[#55d68a]"></span>
                                <span class="absolute right-0 top-0 h-7 w-[3px] bg-[#55d68a]"></span>
                                <span class="absolute bottom-0 left-0 h-[3px] w-7 bg-[#55d68a]"></span>
                                <span class="absolute bottom-0 left-0 h-7 w-[3px] bg-[#55d68a]"></span>
                                <span class="absolute bottom-0 right-0 h-[3px] w-7 bg-[#55d68a]"></span>
                                <span class="absolute bottom-0 right-0 h-7 w-[3px] bg-[#55d68a]"></span>
                                <span class="h-[2px] w-[144px] bg-[#55d68a]"></span>
                            </div>

                            <p class="mt-6 text-center text-[11px] font-semibold text-[#d6e4f5]">
                                Posisikan QR berbentuk persegi di dalam bingkai
                            </p>

                            <div class="mt-auto flex flex-wrap items-center gap-2">
                                <span class="rounded-[7px] bg-[#16395f] px-3 py-1.5 text-[10px] font-medium text-[#d6e4f5]">Kamera belakang ▾</span>
                                <button type="button" class="rounded-[7px] bg-white px-3 py-1.5 text-[10px] font-semibold text-senat-ink">↻ Ganti kamera</button>
                                <button type="button" class="rounded-[7px] bg-[#16395f] px-3 py-1.5 text-[10px] font-medium text-[#d6e4f5]">☼ Lampu</button>
                            </div>
                        </div>
                    </div>

                    {{-- Camera fallback --}}
                    <div x-cloak x-show="!kamera"
                         class="flex h-[318px] flex-col items-center justify-center rounded-[10px] bg-[#0d1b2d] px-6 text-center">
                        <p class="text-[13px] font-semibold text-[#d6e4f5]">Kamera dimatikan</p>
                        <p class="mt-2 text-[11px] text-[#8fa9c6]">Gunakan kode sesi di samping atau aktifkan kembali kamera.</p>
                        <button type="button" @click="kamera = true"
                                class="mt-4 rounded-[7px] bg-white px-4 py-2 text-[11px] font-semibold text-senat-ink">Aktifkan kamera</button>
                    </div>

                    {{-- Scanner controls --}}
                    <div>
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <h3 class="text-[18px] font-bold text-senat-ink">Presensi dengan Kamera</h3>
                            <x-senat.badge state="success" :dot="false">Izin kamera aktif</x-senat.badge>
                        </div>
                        <p class="mt-3 text-[13px] leading-relaxed text-senat-muted">
                            Pindai QR yang ditampilkan Sekretariat. Sistem akan memeriksa sesi, waktu presensi, dan identitas anggota sebelum mencatat kehadiran.
                        </p>

                        <div class="mt-4 flex items-start gap-3 rounded-lg bg-[#eaf3fe] px-3 py-3">
                            <span class="text-[13px] font-semibold text-senat-link" aria-hidden="true">i</span>
                            <div>
                                <p class="text-[12px] font-semibold text-senat-ink">Menunggu QR dipindai</p>
                                <p class="mt-0.5 text-[10px] text-senat-muted">QR valid akan langsung diperiksa tanpa pindah halaman.</p>
                            </div>
                        </div>

                        <p class="mt-4 text-[11px] font-medium text-senat-muted">Tidak dapat memindai? Masukkan kode sesi</p>
                        <div class="mt-2 flex flex-wrap items-center gap-2">
                            <input type="text" x-model="kode" inputmode="numeric" maxlength="6"
                                   placeholder="Kode sesi 6 digit"
                                   class="h-[38px] w-full max-w-[280px] rounded-lg border border-[#9fb3c8] px-3 text-[12px] text-senat-ink placeholder:text-senat-muted focus:border-senat-link focus:outline-none focus:ring-2 focus:ring-senat-badgeBg">
                            <x-senat.button size="sm" class="!h-[38px]" type="button">Verifikasi</x-senat.button>
                            <x-senat.button size="sm" variant="secondary" class="!h-[38px]" type="button" @click="kamera = false">Tutup kamera</x-senat.button>
                        </div>

                        <div class="mt-4 flex flex-wrap items-start gap-3 rounded-lg bg-[#fff4df] px-3 py-3">
                            <span class="text-[14px] font-semibold leading-none text-[#a95100]" aria-hidden="true"><x-senat.icon name="warning" class="h-[18px] w-[18px]" /></span>
                            <div class="min-w-0 flex-1">
                                <p class="text-[12px] font-semibold text-senat-ink">Izin ditolak atau kamera tidak tersedia?</p>
                                <p class="mt-1 text-[11px] text-senat-muted">Gunakan kode sesi atau aktifkan izin kamera pada browser.</p>
                            </div>
                            <span class="text-[11px] font-semibold text-[#a95100]">Buka pengaturan browser</span>
                        </div>

                        <p class="mt-4 text-[11px] leading-relaxed text-senat-muted">
                            Kamera diproses di perangkat untuk membaca QR. Video tidak direkam; stream berhenti saat kamera ditutup.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Status & izin inline --}}
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                <div class="rounded-xl bg-[#f5f8fc] p-4">
                    <x-senat.badge state="warning" :dot="false">Belum presensi</x-senat.badge>
                    <p class="mt-3 text-[10px] leading-relaxed text-senat-muted">
                        Setelah QR/kode valid, status agenda berubah menjadi Hadir dan waktu check-in dicatat.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-4 rounded-xl bg-[#f5f8fc] p-4">
                    <div class="min-w-0 flex-1">
                        <p class="text-[12px] font-semibold text-senat-ink">Tidak dapat hadir?</p>
                        <p class="mt-1 text-[10px] leading-relaxed text-senat-muted">
                            Ajukan alasan, lampiran bila diperlukan, lalu pantau status persetujuan pada agenda ini.
                        </p>
                    </div>
                    <x-senat.button size="sm" variant="secondary" type="button">Ajukan Izin</x-senat.button>
                </div>
            </div>
        </div>

        {{-- ---------- REGULER ---------- --}}
        <div x-cloak x-show="tab === 'reguler'" class="mt-5 space-y-4">
            @forelse ($reguler as $meeting)
                @include('portal.partials.agenda-card', ['meeting' => $meeting, 'tanggalPanjang' => $tanggalPanjang])
            @empty
                <x-senat.empty title="Belum ada agenda reguler" message="Rapat komisi terjadwal akan tampil di sini." />
            @endforelse
        </div>

        {{-- ---------- KHUSUS ---------- --}}
        <div x-cloak x-show="tab === 'khusus'" class="mt-5 space-y-4">
            @forelse ($khusus as $meeting)
                @include('portal.partials.agenda-card', ['meeting' => $meeting, 'tanggalPanjang' => $tanggalPanjang])
            @empty
                <x-senat.empty title="Belum ada agenda khusus" message="Sidang pleno dan musyawarah khusus akan tampil di sini." />
            @endforelse
        </div>
    </section>

    {{-- ================= JADWAL BERIKUTNYA ================= --}}
    <section class="mt-6 rounded-[14px] border border-senat-line bg-white p-5 shadow-[0_3px_12px_rgba(13,26,41,0.07)]">
        <h2 class="text-[18px] font-bold text-senat-ink">Jadwal Berikutnya</h2>

        <ul class="mt-5 divide-y divide-senat-line">
            @forelse ($upcoming as $meeting)
                <li class="flex flex-wrap items-center gap-x-4 gap-y-2 py-4 first:pt-0 last:pb-0">
                    <span class="flex h-[34px] w-[34px] shrink-0 items-center justify-center rounded-[9px] bg-[#eaf8f0] text-[13px] font-bold text-[#147a4a]" aria-hidden="true"><x-senat.icon name="calendar" class="h-[18px] w-[18px]" /></span>
                    <div class="min-w-0 flex-1">
                        <p class="text-[14px] font-semibold text-senat-ink">{{ $meeting['title'] }}</p>
                        <p class="mt-0.5 text-[12px] text-senat-muted">{{ $meeting['type'] }} · {{ $tanggalPanjang($meeting['date']) }}</p>
                    </div>
                    <p class="shrink-0 text-[13px] font-semibold text-senat-link">{{ $meeting['time'] }}</p>
                </li>
            @empty
                <li class="py-4"><p class="text-[13px] text-senat-muted">Belum ada jadwal berikutnya.</p></li>
            @endforelse
        </ul>
    </section>

</div>
@endsection
