@push('head')
<style>
    #qr-reader {
        width: 100% !important;
        height: 100% !important;
        border: none !important;
        background: #000;
    }
    #qr-reader video {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
        border-radius: 8px;
    }
    #qr-reader__scan_region {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    #qr-reader__scan_region img {
        display: none !important;
    }
</style>
@endpush

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

<div class="mx-auto w-full max-w-[1116px]" x-data="agendaScanner()">

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

            {{-- Presensi dengan Kamera & Verifikasi --}}
            <div class="rounded-xl border border-senat-line bg-[#f5f8fc] p-5 shadow-sm">
                {{-- Banner status kehadiran jika sudah hadir --}}
                <div x-show="isHadir" x-cloak class="rounded-xl border border-emerald-300 bg-emerald-50 p-5 mb-5 text-center">
                    <div class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 mb-2">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    </div>
                    <h3 class="text-base font-bold text-emerald-900">Kehadiran Anda Telah Terverifikasi</h3>
                    <p class="text-xs text-emerald-700 mt-1">
                        Tercatat atas nama <strong class="font-semibold">{{ $currentMember['name'] ?? 'Anggota Senat' }}</strong> pada pukul <span class="font-mono font-bold" x-text="waktuHadir"></span> WIB.
                    </p>
                    <p class="text-[11px] text-emerald-600 mt-1">Status keikutsertaan telah disinkronkan ke monitor kuorum Sekretariat Senat.</p>
                </div>

                <div x-show="!isHadir" class="grid grid-cols-1 gap-6 lg:grid-cols-[468px_1fr]">
                    {{-- Camera Scanner Container --}}
                    <div>
                        <div class="relative overflow-hidden rounded-[12px] bg-[#0d1b2d] border border-slate-700 shadow-md">
                            {{-- Top indicator --}}
                            <div class="flex items-center justify-between px-4 py-3 bg-[#0d1b2d]/90 border-b border-slate-700/60 z-10">
                                <span class="rounded-full px-3 py-1 text-[10px] font-bold tracking-[0.06em]"
                                      :class="kamera ? 'bg-emerald-950 text-emerald-400 border border-emerald-700' : 'bg-slate-800 text-slate-400'">
                                    <span x-text="kamera ? '● PEMINDAI KAMERA AKTIF' : '○ KAMERA NONAKTIF'"></span>
                                </span>
                                <span class="text-[11px] text-slate-400 font-mono">{{ $currentMember['name'] ?? 'Anggota' }}</span>
                            </div>

                            {{-- Area Video Pemindai --}}
                            <div class="relative aspect-square w-full flex items-center justify-center bg-black overflow-hidden">
                                {{-- Html5Qrcode mount target --}}
                                <div id="qr-reader" class="w-full h-full" x-show="kamera"></div>
                                

                                {{-- Fallback saat kamera mati --}}
                                <div x-show="!kamera" class="flex flex-col items-center justify-center p-6 text-center text-white">
                                    <div class="h-14 w-14 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 mb-3 border border-slate-700">
                                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-200">Kamera Scanner Presensi</p>
                                    <p class="text-xs text-slate-400 mt-1 max-w-[280px]">Arahkan kamera ke layar QR proyektor ruang sidang.</p>
                                    
                                    <div class="mt-4">
                                        <x-senat.button variant="primary" size="sm" type="button" @click="startCamera()">
                                            Buka Kamera Live
                                        </x-senat.button>
                                    </div>
                                </div>
                            </div>
                            {{-- Bottom Toolbar --}}
                            <div class="flex items-center justify-between px-4 py-2.5 bg-[#0d1b2d] border-t border-slate-700/60" x-show="kamera">
                                <span class="text-[11px] text-slate-300">Posisikan QR di dalam area bidik</span>
                                <button type="button" @click="stopCamera()"
                                        class="rounded bg-slate-800 px-3 py-1 text-[11px] font-semibold text-slate-200 hover:bg-slate-700">
                                    Tutup Kamera
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Scanner Controls & Manual Input --}}
                    <div class="flex flex-col justify-between">
                        <div>
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <h3 class="text-[18px] font-bold text-senat-ink">Presensi Sidang Elektronik</h3>
                                    <p class="text-xs text-senat-muted mt-0.5">Identitas: <strong class="text-senat-primary">{{ $currentMember['name'] ?? 'Anggota Senat' }}</strong> (ID: {{ $currentMember['id'] ?? 2 }})</p>
                                </div>
                                <x-senat.badge state="info" :dot="false">Akun Terverifikasi</x-senat.badge>
                            </div>

                            <p class="mt-3 text-[12px] leading-relaxed text-senat-body">
                                Pindai QR yang ditayangkan Sekretariat pada layar proyektor ruang sidang. Sistem akan memverifikasi hak kehadiran dan memperbarui status kuorum secara seketika.
                            </p>

                            {{-- Status Message Alert Box --}}
                            <div x-show="statusMessage" x-cloak class="mt-4 rounded-lg p-3 text-xs font-medium"
                                 :class="{
                                     'bg-blue-50 text-blue-800 border border-blue-200': scanStatus === 'scanning' || scanStatus === 'submitting',
                                     'bg-emerald-50 text-emerald-800 border border-emerald-200': scanStatus === 'success',
                                     'bg-rose-50 text-rose-800 border border-rose-200': scanStatus === 'error'
                                 }">
                                <div class="flex items-center gap-2">
                                    <span x-show="scanStatus === 'submitting'" class="h-3 w-3 animate-spin rounded-full border-2 border-blue-600 border-t-transparent"></span>
                                    <span x-text="statusMessage"></span>
                                </div>
                            </div>

                            {{-- Alternatif Input Manual Kode Sesi --}}
                            <div class="mt-5 rounded-xl border border-senat-line bg-white p-4">
                                <label for="sessionCodeInput" class="block text-[11px] font-bold uppercase tracking-wider text-senat-ink">
                                    Tidak dapat memindai kamera? Masukkan Kode Sesi:
                                </label>
                                <p class="text-[11px] text-senat-muted mt-0.5">Kode sesi 6-digit tertera pada layar proyektor (misal: PLN-892)</p>
                                <div class="mt-2.5 flex flex-wrap items-center gap-2">
                                    <input id="sessionCodeInput" type="text" x-model="kode" maxlength="10"
                                           placeholder="Contoh: PLN-892"
                                           @keydown.enter.prevent="submitManualCode()"
                                           class="h-10 w-full max-w-[240px] rounded-lg border border-[#9fb3c8] px-3 font-mono text-sm uppercase font-bold text-senat-ink placeholder:text-senat-muted placeholder:font-normal focus:border-senat-link focus:outline-none focus:ring-2 focus:ring-senat-badgeBg">
                                    <x-senat.button variant="primary" type="button" @click="submitManualCode()" ::disabled="scanStatus === 'submitting'">
                                        Verifikasi Kode Sesi
                                    </x-senat.button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center gap-2 text-[11px] text-senat-muted">
                            <span class="text-senat-link font-bold">ℹ</span>
                            <span>Presensi hanya dapat dilakukan saat sesi dibuka dan belum dibekukan oleh Sekretariat.</span>
                        </div>
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

@push('scripts')
<script>
(function() {
    function registerAgendaScanner() {
        if (!window.Alpine || window._agendaScannerRegistered) return;
        window._agendaScannerRegistered = true;
        window.Alpine.data('agendaScanner', () => ({
            tab: 'hari-ini',
            kamera: false,
            kode: '',
            scanner: null,
            scanStatus: 'idle',
            statusMessage: '',
            isHadir: false,
            waktuHadir: '',
            userId: {{ (int) ($currentMember['id'] ?? 6) }},
            meetingId: {{ (int) ($fokus['id'] ?? 0) }},

            startCamera() {
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    this.scanStatus = 'error';
                    this.statusMessage = 'Browser HP membatasi live video via HTTP biasa. Silakan masukkan Kode Sesi manual atau aktifkan izin via chrome://flags.';
                    return;
                }
                this.kamera = true;
                this.scanStatus = 'scanning';
                this.statusMessage = 'Arahkan kotak bidik kamera tepat ke QR Code di layar.';
                this.$nextTick(() => {
                    if (window.Html5Qrcode && !this.scanner) {
                        this.scanner = new window.Html5Qrcode('qr-reader');
                        this.scanner.start(
                            { facingMode: 'environment' },
                            {
                                fps: 15,
                                aspectRatio: 1.0,
                                qrbox: (viewfinderWidth, viewfinderHeight) => {
                                    const min = Math.min(viewfinderWidth, viewfinderHeight);
                                    const size = Math.floor(min * 0.75);
                                    return { width: size, height: size };
                                }
                            },
                            (decodedText) => {
                                this.onQrScanned(decodedText);
                            },
                            (err) => {}
                        ).catch(err => {
                            this.scanStatus = 'error';
                            this.statusMessage = 'Gagal mengakses kamera: ' + (err.message || 'Izin kamera ditolak. Silakan gunakan kode sesi manual.');
                        });
                    }
                });
            },

            stopCamera() {
                if (this.scanner) {
                    this.scanner.stop().then(() => {
                        this.scanner.clear();
                        this.scanner = null;
                    }).catch(() => { this.scanner = null; });
                }
                this.kamera = false;
                if (this.scanStatus === 'scanning') this.scanStatus = 'idle';
            },

            async onQrScanned(token) {
                if (this.scanStatus === 'submitting' || this.isHadir) return;
                this.stopCamera();
                await this.submitPresence({ token_qr: token });
            },

            async submitManualCode() {
                if (!this.kode || this.kode.trim().length < 3) {
                    alert('Mohon ketikkan kode sesi yang valid (contoh: PLN-892).');
                    return;
                }
                await this.submitPresence({ kode_sesi: this.kode.trim() });
            },

            async submitPresence(payload) {
                this.scanStatus = 'submitting';
                this.statusMessage = 'Memverifikasi token presensi ke sistem Senat...';

                try {
                    const res = await fetch('{{ route('portal.presensi.submit') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            id_user: this.userId,
                            id_rapat: this.meetingId,
                            ...payload
                        })
                    });

                    const data = await res.json();
                    if (res.ok && data.success) {
                        this.scanStatus = 'success';
                        this.isHadir = true;
                        this.waktuHadir = data.data?.waktu_presensi || new Date().toLocaleTimeString('id-ID');
                        this.statusMessage = data.message || 'Presensi berhasil dicatat!';
                    } else {
                        this.scanStatus = 'error';
                        this.statusMessage = data.message || 'Gagal memproses presensi.';
                    }
                } catch (e) {
                    this.scanStatus = 'error';
                    this.statusMessage = 'Kesalahan koneksi ke server: ' + e.message;
                }
            }
        }));
    }

    if (window.Alpine) {
        registerAgendaScanner();
    } else {
        document.addEventListener('alpine:init', registerAgendaScanner);
    }
})();
</script>
@endpush

@endsection