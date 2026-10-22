@php
    $groups = \App\Livewire\AdminPreview::GROUPS;
    $currentGroup = null;

    foreach ($groups as $group => $items) {
        if (in_array($active, $items, true)) {
            $currentGroup = $group;
            break;
        }
    }
@endphp

<div class="min-h-screen bg-[#f8fafc] text-senat-ink lg:flex">

    {{-- Sidebar Profesional --}}
    <aside class="w-full shrink-0 border-b border-senat-line bg-white lg:sticky lg:top-0 lg:h-screen lg:w-[290px] lg:overflow-y-auto lg:border-b-0 lg:border-r"
           aria-label="Navigasi Admin Sekretariat">
        
        {{-- Header Sidebar --}}
        <div class="flex items-center gap-3 border-b border-senat-line px-5 py-4">
            <img src="{{ asset('images/logo-polines.png') }}" alt="Logo Polines" class="h-9 w-9 rounded-md object-contain">
            <div class="min-w-0 flex-1 leading-tight">
                <div class="flex items-center gap-2">
                    <p class="truncate text-[13px] font-bold text-senat-ink">Admin Sekretariat</p>
                    <span class="inline-block h-2 w-2 rounded-full bg-emerald-500"></span>
                </div>
                <p class="truncate text-[11px] font-medium text-senat-muted">Senat Akademik Polines</p>
            </div>
        </div>

        {{-- Navigasi Sidebar --}}
        <nav class="space-y-6 px-3 py-4" aria-label="Menu Admin">
            @foreach ($groups as $group => $items)
                <div aria-labelledby="grup-{{ $loop->index }}">
                    <div class="flex items-center justify-between px-3 pb-2">
                        <h2 id="grup-{{ $loop->index }}" class="text-[11px] font-bold uppercase tracking-[0.08em] text-senat-muted">
                            {{ $group }}
                        </h2>
                    </div>
                    <ul class="space-y-1">
                        @foreach ($items as $item)
                            @php $isActive = ($active === $item); @endphp
                            <li>
                                <button type="button"
                                        wire:click="select(@js($item))"
                                        @if ($isActive) aria-current="page" @endif
                                        class="flex min-h-[40px] w-full items-center justify-between rounded-lg px-3 text-left text-[12.5px] font-medium transition-all {{ $isActive ? 'bg-[#003366] font-semibold text-white shadow-sm' : 'text-slate-700 hover:bg-slate-100' }}">
                                    <span class="truncate">{{ $item }}</span>
                                    @if ($isActive)
                                        <span class="ml-2 h-1.5 w-1.5 shrink-0 rounded-full bg-emerald-400"></span>
                                    @endif
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach

            <div class="border-t border-slate-200 pt-3">
                <a href="{{ route('public.home') }}"
                   class="flex min-h-[40px] w-full items-center gap-2.5 rounded-lg px-3 text-[12px] font-medium text-slate-600 transition-colors hover:bg-slate-100">
                    <x-senat.icon name="arrow-left" class="h-4 w-4 text-slate-400" />
                    <span>Kembali ke Portal Publik</span>
                </a>
            </div>
        </nav>
    </aside>

    {{-- Main Content Canvas --}}
    <main class="min-w-0 flex-1 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
        <div class="mx-auto max-w-6xl space-y-6">

            {{-- Flash Alert --}}
            @if ($flashMessage)
                <div class="flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-[13px] text-emerald-800 shadow-sm"
                     x-data="{ show: true }" x-show="show">
                    <div class="flex items-center gap-2.5">
                        <x-senat.icon name="check" class="h-4 w-4 shrink-0 text-emerald-600" />
                        <span>{{ $flashMessage }}</span>
                    </div>
                    <button type="button" @click="show = false" class="text-emerald-600 hover:text-emerald-800 text-base font-bold">×</button>
                </div>
            @endif

            {{-- Top Breadcrumb & Header --}}
            <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="flex items-center gap-2 text-[11px] font-semibold tracking-wide text-senat-muted uppercase">
                        <span>Panel Administrasi</span>
                        <span>/</span>
                        <span class="text-senat-primary">{{ $currentGroup ?? 'Umum' }}</span>
                    </div>
                    <h1 class="mt-1 text-[24px] font-bold tracking-tight text-slate-900 sm:text-[28px]">
                        {{ $active }}
                    </h1>
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    <span class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[12px] font-medium text-slate-700 shadow-xs">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        Status Sistem: Aktif
                    </span>
                    <button type="button"
                            wire:click="saveAction(@js($active))"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-senat-primary px-4 py-2 text-[12px] font-semibold text-white shadow-sm transition hover:bg-senat-primaryHover">
                        <x-senat.icon name="check" class="h-3.5 w-3.5" />
                        Simpan Perubahan
                    </button>
                </div>
            </header>

            {{-- 1. SCREEN: Dashboard Rapat --}}
            @if ($active === 'Dashboard Rapat' || $active === 'Dashboard')
                {{-- Stat KPI --}}
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs">
                        <p class="text-[12px] font-semibold text-slate-500">Total Sidang & Rapat</p>
                        <p class="mt-2 text-[26px] font-bold text-slate-900">{{ str_pad((string)$stats['meetings_count'], 2, '0', STR_PAD_LEFT) }}</p>
                        <p class="mt-1 text-[11px] font-medium text-emerald-600">Terjadwal di kalender</p>
                    </div>
                    <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs">
                        <p class="text-[12px] font-semibold text-slate-500">Tingkat Kehadiran Anggota</p>
                        <p class="mt-2 text-[26px] font-bold text-slate-900">{{ $stats['attendance_rate'] }}%</p>
                        <p class="mt-1 text-[11px] font-medium text-slate-500">Rata-rata sidang pleno</p>
                    </div>
                    <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs">
                        <p class="text-[12px] font-semibold text-slate-500">Sidang Aktif / Berjalan</p>
                        <p class="mt-2 text-[26px] font-bold text-senat-primary">01</p>
                        <p class="mt-1 text-[11px] font-medium text-emerald-600">PLN-892 (Sesi Dibuka)</p>
                    </div>
                    <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs">
                        <p class="text-[12px] font-semibold text-slate-500">Aspirasi Menunggu</p>
                        <p class="mt-2 text-[26px] font-bold text-amber-600">{{ str_pad((string)$stats['pending_aspirations'], 2, '0', STR_PAD_LEFT) }}</p>
                        <p class="mt-1 text-[11px] font-medium text-amber-700">Perlu telaah komisi</p>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-3">
                    {{-- Agenda Sidang Mendatang --}}
                    <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs lg:col-span-2">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                            <div>
                                <h2 class="text-[15px] font-bold text-slate-900">Jadwal Sidang & Musyawarah Senat</h2>
                                <p class="text-[12px] text-slate-500">Daftar agenda persidangan yang disiapkan Sekretariat</p>
                            </div>
                            <button type="button" wire:click="select('Jadwal & Agenda Sidang')"
                                    class="text-[12px] font-semibold text-senat-link hover:underline">
                                Kelola Agenda →
                            </button>
                        </div>
                        <div class="mt-4 divide-y divide-slate-100">
                            @foreach ($meetings as $m)
                                <div class="flex flex-col gap-3 py-3.5 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="rounded-md bg-slate-100 px-2 py-0.5 text-[10.5px] font-bold text-slate-700 uppercase">
                                                {{ $m['session_code'] }}
                                            </span>
                                            <span class="rounded-md {{ $m['status'] === 'Sedang Berlangsung' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600' }} px-2 py-0.5 text-[10.5px] font-semibold">
                                                {{ $m['status'] }}
                                            </span>
                                        </div>
                                        <p class="mt-1.5 text-[13px] font-semibold text-slate-900">{{ $m['title'] }}</p>
                                        <p class="mt-0.5 text-[11.5px] text-slate-500">
                                            {{ \Illuminate\Support\Carbon::parse($m['date'])->format('d M Y') }} · {{ $m['time'] }} · {{ $m['room'] }}
                                        </p>
                                    </div>
                                    <div class="flex shrink-0 items-center gap-2">
                                        <button type="button" wire:click="select('Presensi & Verifikasi QR')"
                                                class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[11.5px] font-semibold text-slate-700 hover:bg-slate-50">
                                            Presensi ({{ $m['present_count'] }}/{{ $m['total_invited'] }})
                                        </button>
                                        <button type="button" wire:click="select('Risalah & Notulensi Sidang')"
                                                class="rounded-lg bg-senat-primary px-3 py-1.5 text-[11.5px] font-semibold text-white hover:bg-senat-primaryHover">
                                            Notulen
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Panel Aksi Operasional --}}
                    <div class="space-y-5">
                        <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs">
                            <h2 class="text-[15px] font-bold text-slate-900">Aksi Cepat Persidangan</h2>
                            <p class="mt-1 text-[12px] text-slate-500">Jalur pintas operasional sekretaris</p>
                            <div class="mt-4 space-y-2.5">
                                <button type="button" wire:click="select('Jadwal & Agenda Sidang')"
                                        class="flex w-full items-center justify-between rounded-lg border border-slate-200 bg-slate-50/50 p-3 text-left transition hover:border-senat-link hover:bg-white">
                                    <div>
                                        <p class="text-[12.5px] font-semibold text-slate-900">Jadwalkan Sidang Baru</p>
                                        <p class="text-[11px] text-slate-500">Atur undangan, ruang, & jenis sidang</p>
                                    </div>
                                    <span class="text-slate-400">→</span>
                                </button>
                                <button type="button" wire:click="select('Presensi & Verifikasi QR')"
                                        class="flex w-full items-center justify-between rounded-lg border border-slate-200 bg-slate-50/50 p-3 text-left transition hover:border-senat-link hover:bg-white">
                                    <div>
                                        <p class="text-[12.5px] font-semibold text-slate-900">Generate QR Presensi</p>
                                        <p class="text-[11px] text-slate-500">Tampilkan token presensi anggota</p>
                                    </div>
                                    <span class="text-slate-400">→</span>
                                </button>
                                <button type="button" wire:click="select('E-Voting & Pengambilan Keputusan')"
                                        class="flex w-full items-center justify-between rounded-lg border border-slate-200 bg-slate-50/50 p-3 text-left transition hover:border-senat-link hover:bg-white">
                                    <div>
                                        <p class="text-[12.5px] font-semibold text-slate-900">Buka E-Voting</p>
                                        <p class="text-[11px] text-slate-500">Inisialisasi pemungutan suara resmi</p>
                                    </div>
                                    <span class="text-slate-400">→</span>
                                </button>
                            </div>
                        </div>

                        <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs">
                            <h2 class="text-[15px] font-bold text-slate-900">Status Sesi Berjalan</h2>
                            <div class="mt-3 rounded-lg border border-emerald-200 bg-emerald-50/60 p-3.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-bold text-emerald-800 uppercase">PLN-892 AKTIF</span>
                                    <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                </div>
                                <p class="mt-1 text-[12.5px] font-semibold text-slate-900">Sidang Pleno Evaluasi Akreditasi</p>
                                <p class="mt-2 text-[11px] text-slate-600">28 dari 34 anggota hadir (Kuorum 82% Terpenuhi)</p>
                            </div>
                        </div>
                    </div>
                </div>

            {{-- 2. SCREEN: Jadwal & Agenda Sidang --}}
            @elseif ($active === 'Jadwal & Agenda Sidang')
                <div class="grid gap-6 lg:grid-cols-3">
                    <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs lg:col-span-1">
                        <h2 class="text-[15px] font-bold text-slate-900">Formulir Sidang Baru</h2>
                        <p class="mt-1 text-[12px] text-slate-500">Entri agenda dan penetapan jadwal persidangan</p>
                        
                        <form class="mt-4 space-y-4" wire:submit.prevent="saveAction('Jadwal Sidang')">
                            <div>
                                <label class="text-[11.5px] font-semibold text-slate-700">Judul / Agenda Sidang</label>
                                <input type="text" placeholder="Contoh: Sidang Pleno Penetapan Standar Kurikulum"
                                       class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-[12px] text-slate-900 focus:border-senat-primary focus:outline-none">
                            </div>
                            <div>
                                <label class="text-[11.5px] font-semibold text-slate-700">Jenis Sidang</label>
                                <select class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-[12px] text-slate-900 focus:border-senat-primary focus:outline-none">
                                    <option>Sidang Pleno</option>
                                    <option>Rapat Komisi I (Pendidikan)</option>
                                    <option>Rapat Komisi II (Penelitian)</option>
                                    <option>Rapat Komisi III (Kelembagaan & SDM)</option>
                                    <option>Rapat Musyawarah Pimpinan</option>
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-2.5">
                                <div>
                                    <label class="text-[11.5px] font-semibold text-slate-700">Tanggal</label>
                                    <input type="date" value="{{ date('Y-m-d') }}"
                                           class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-[12px] text-slate-900 focus:border-senat-primary focus:outline-none">
                                </div>
                                <div>
                                    <label class="text-[11.5px] font-semibold text-slate-700">Waktu</label>
                                    <input type="text" value="09:00 - 12:00 WIB"
                                           class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-[12px] text-slate-900 focus:border-senat-primary focus:outline-none">
                                </div>
                            </div>
                            <div>
                                <label class="text-[11.5px] font-semibold text-slate-700">Lokasi / Ruang Sidang</label>
                                <input type="text" value="Ruang Sidang Utama Gd. Direktorat"
                                       class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-[12px] text-slate-900 focus:border-senat-primary focus:outline-none">
                            </div>
                            <div>
                                <label class="text-[11.5px] font-semibold text-slate-700">Target Undangan</label>
                                <input type="text" value="Seluruh Anggota Senat (34 Orang)"
                                       class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-[12px] text-slate-900 focus:border-senat-primary focus:outline-none">
                            </div>
                            <button type="submit"
                                    class="w-full rounded-lg bg-senat-primary py-2.5 text-[12px] font-semibold text-white transition hover:bg-senat-primaryHover">
                                Terbitkan Jadwal & Notifikasi
                            </button>
                        </form>
                    </div>

                    <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs lg:col-span-2">
                        <h2 class="text-[15px] font-bold text-slate-900">Daftar Agenda Sidang Terdaftar</h2>
                        <div class="mt-4 overflow-x-auto">
                            <table class="w-full text-left text-[12px]">
                                <thead class="border-b border-slate-200 bg-slate-50 text-[11px] font-bold uppercase text-slate-500">
                                    <tr>
                                        <th class="px-3 py-2.5">Kode</th>
                                        <th class="px-3 py-2.5">Agenda Sidang</th>
                                        <th class="px-3 py-2.5">Waktu & Tempat</th>
                                        <th class="px-3 py-2.5">Status</th>
                                        <th class="px-3 py-2.5 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($meetings as $m)
                                        <tr>
                                            <td class="px-3 py-3 font-bold text-slate-700">{{ $m['session_code'] }}</td>
                                            <td class="px-3 py-3">
                                                <p class="font-semibold text-slate-900">{{ $m['title'] }}</p>
                                                <p class="text-[11px] text-slate-500">{{ $m['type'] }}</p>
                                            </td>
                                            <td class="px-3 py-3 text-slate-600">
                                                {{ $m['date'] }}<br><span class="text-[11px] text-slate-400">{{ $m['room'] }}</span>
                                            </td>
                                            <td class="px-3 py-3">
                                                <span class="rounded-md px-2 py-0.5 text-[10.5px] font-semibold {{ $m['status'] === 'Sedang Berlangsung' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                                    {{ $m['status'] }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-3 text-right">
                                                <button type="button" wire:click="saveAction('Ubah Sidang {{ $m['session_code'] }}')"
                                                        class="rounded border border-slate-300 px-2.5 py-1 text-[11px] font-semibold text-slate-700 hover:bg-slate-50">
                                                    Edit
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            {{-- 3. SCREEN: Presensi & Verifikasi QR --}}
            @elseif ($active === 'Presensi & Verifikasi QR')
                <div class="grid gap-6 lg:grid-cols-3">
                    <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs lg:col-span-1 text-center">
                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-[11px] font-bold text-emerald-800 uppercase">Sesi Presensi Aktif</span>
                        <h2 class="mt-3 text-[16px] font-bold text-slate-900">PLN-892 — Sidang Pleno</h2>
                        <p class="mt-1 text-[12px] text-slate-500">Scan QR Code dengan aplikasi Senat Anggota</p>
                        
                        <div class="mx-auto my-5 flex h-48 w-48 items-center justify-center rounded-2xl border-2 border-dashed border-senat-primary bg-slate-50 p-4">
                            <div class="text-center">
                                <div class="mx-auto h-28 w-28 rounded-lg bg-slate-900 p-2 text-white flex items-center justify-center text-[10px] font-mono">
                                    [QR CODE TOKEN: PLN-892-SEC]
                                </div>
                                <p class="mt-2 text-[10px] font-mono text-slate-500">Token diperbarui tiap 60 detik</p>
                            </div>
                        </div>

                        <div class="flex justify-center gap-2">
                            <button type="button" wire:click="saveAction('Reset Token QR')"
                                    class="rounded-lg border border-slate-300 px-3 py-2 text-[11.5px] font-semibold text-slate-700 hover:bg-slate-50">
                                Refresh Token
                            </button>
                            <button type="button" wire:click="saveAction('Cetak Lembar Presensi')"
                                    class="rounded-lg bg-senat-primary px-3 py-2 text-[11.5px] font-semibold text-white hover:bg-senat-primaryHover">
                                Cetak PDF
                            </button>
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs lg:col-span-2">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <h2 class="text-[15px] font-bold text-slate-900">Log Kehadiran Masuk (Live)</h2>
                            <span class="text-[12px] font-semibold text-emerald-600">28 Hadir / 34 Undangan</span>
                        </div>
                        <div class="mt-4 space-y-2">
                            @foreach ($committees as $c)
                                <div class="flex items-center justify-between rounded-lg border border-slate-100 bg-slate-50/60 p-3 text-[12px]">
                                    <div>
                                        <p class="font-semibold text-slate-900">{{ $c['lead'] }}</p>
                                        <p class="text-[11px] text-slate-500">{{ $c['name'] }} (Ketua)</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Hadir 09:05 WIB
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

            {{-- 4. SCREEN: Kuorum & Kehadiran Anggota --}}
            @elseif ($active === 'Kuorum & Kehadiran Anggota')
                <div class="rounded-xl border border-slate-200/80 bg-white p-6 shadow-xs">
                    <h2 class="text-[16px] font-bold text-slate-900">Status Validasi Kuorum Sidang</h2>
                    <p class="text-[12px] text-slate-500">Syarat minimal pengambilan keputusan berdasarkan Statuta Polines</p>
                    
                    <div class="mt-6 grid gap-4 sm:grid-cols-3">
                        <div class="rounded-xl bg-slate-50 p-4 border border-slate-200">
                            <p class="text-[11.5px] font-semibold text-slate-500">Total Anggota Senat</p>
                            <p class="mt-1 text-[24px] font-bold text-slate-900">34 Orang</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-4 border border-slate-200">
                            <p class="text-[11.5px] font-semibold text-slate-500">Batas Minimal Kuorum (2/3)</p>
                            <p class="mt-1 text-[24px] font-bold text-slate-900">23 Orang</p>
                        </div>
                        <div class="rounded-xl bg-emerald-50 p-4 border border-emerald-200">
                            <p class="text-[11.5px] font-semibold text-emerald-800">Anggota Hadir Fisik/Hybrid</p>
                            <p class="mt-1 text-[24px] font-bold text-emerald-900">28 Orang (82.3%)</p>
                        </div>
                    </div>

                    <div class="mt-6 rounded-lg bg-emerald-500/10 p-4 text-[13px] text-emerald-900 border border-emerald-200 flex items-center gap-3">
                        <x-senat.icon name="check" class="h-5 w-5 text-emerald-600 shrink-0" />
                        <div>
                            <p class="font-bold">KUORUM SAH TERPENUHI</p>
                            <p class="text-[12px] text-emerald-800">Sidang Pleno sah untuk mengambil keputusan voting dan penetapan peraturan akademik.</p>
                        </div>
                    </div>
                </div>

            {{-- 5. SCREEN: Risalah & Notulensi Sidang --}}
            @elseif ($active === 'Risalah & Notulensi Sidang')
                <div class="rounded-xl border border-slate-200/80 bg-white p-6 shadow-xs">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div>
                            <h2 class="text-[16px] font-bold text-slate-900">Penyusunan Risalah & Notulensi Sidang</h2>
                            <p class="text-[12px] text-slate-500">Pencatatan jalannya persidangan, poin kesepakatan, dan tindak lanjut</p>
                        </div>
                        <button type="button" wire:click="saveAction('Risalah Sidang')"
                                class="rounded-lg bg-senat-primary px-4 py-2 text-[12px] font-semibold text-white hover:bg-senat-primaryHover">
                            Simpan Draft Risalah
                        </button>
                    </div>

                    <div class="mt-5 space-y-4">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="text-[11.5px] font-semibold text-slate-700">Pilih Sidang</label>
                                <select class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-[12px] text-slate-900">
                                    @foreach ($meetings as $m)
                                        <option>{{ $m['session_code'] }} - {{ $m['title'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="text-[11.5px] font-semibold text-slate-700">Notulis / Sekretaris Sidang</label>
                                <input type="text" value="Sekretariat Senat Akademik Polines"
                                       class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-[12px] text-slate-900">
                            </div>
                        </div>

                        <div>
                            <label class="text-[11.5px] font-semibold text-slate-700">Ringkasan Pembahasan & Dinamika Sidang</label>
                            <textarea rows="4" class="mt-1 w-full rounded-lg border border-slate-300 p-3 text-[12px] text-slate-900 focus:border-senat-primary focus:outline-none" placeholder="Catat poin-poin utama aspirasi dan tanggapan komisi..."></textarea>
                        </div>

                        <div>
                            <label class="text-[11.5px] font-semibold text-slate-700">Keputusan & Tindak Lanjut Komisi</label>
                            <textarea rows="3" class="mt-1 w-full rounded-lg border border-slate-300 p-3 text-[12px] text-slate-900 focus:border-senat-primary focus:outline-none" placeholder="1. Komisi I menindaklanjuti revisi RPS dalam 7 hari kerja..."></textarea>
                        </div>
                    </div>
                </div>

            {{-- 6. SCREEN: E-Voting & Pengambilan Keputusan --}}
            @elseif ($active === 'E-Voting & Pengambilan Keputusan')
                <div class="grid gap-6 lg:grid-cols-3">
                    <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs lg:col-span-1">
                        <h2 class="text-[15px] font-bold text-slate-900">Inisialisasi Sesi Voting</h2>
                        <form class="mt-4 space-y-4" wire:submit.prevent="saveAction('Buka Voting Baru')">
                            <div>
                                <label class="text-[11.5px] font-semibold text-slate-700">Mosi / Keputusan yang Divoting</label>
                                <textarea rows="3" class="mt-1 w-full rounded-lg border border-slate-300 p-2.5 text-[12px] text-slate-900 focus:border-senat-primary focus:outline-none">Persetujuan Pembukaan Program Studi Magister Terapan Rekayasa Siber</textarea>
                            </div>
                            <div>
                                <label class="text-[11.5px] font-semibold text-slate-700">Metode Voting</label>
                                <select class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-[12px] text-slate-900">
                                    <option>Voting Terbuka (Opsi: Setuju, Menolak, Abstain)</option>
                                    <option>Voting Tertutup / Rahasia</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-[11.5px] font-semibold text-slate-700">Durasi Pemilihan</label>
                                <input type="text" value="15 Menit" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-[12px] text-slate-900">
                            </div>
                            <button type="submit" class="w-full rounded-lg bg-emerald-600 py-2.5 text-[12px] font-semibold text-white transition hover:bg-emerald-700">
                                Buka Pemungutan Suara
                            </button>
                        </form>
                    </div>

                    <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs lg:col-span-2">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <h2 class="text-[15px] font-bold text-slate-900">Hasil Pemungutan Suara Sementara</h2>
                            <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-800">Live Result</span>
                        </div>
                        <div class="mt-4 space-y-3">
                            <div class="rounded-lg border border-emerald-200 bg-emerald-50/60 p-4">
                                <div class="flex justify-between text-[13px] font-bold text-emerald-900">
                                    <span>SETUJU</span>
                                    <span>24 Suara (85.7%)</span>
                                </div>
                                <div class="mt-2 h-2.5 w-full rounded-full bg-emerald-200">
                                    <div class="h-2.5 rounded-full bg-emerald-600" style="width: 85.7%"></div>
                                </div>
                            </div>
                            <div class="rounded-lg border border-rose-200 bg-rose-50/60 p-4">
                                <div class="flex justify-between text-[13px] font-bold text-rose-900">
                                    <span>TIDAK SETUJU</span>
                                    <span>2 Suara (7.1%)</span>
                                </div>
                                <div class="mt-2 h-2.5 w-full rounded-full bg-rose-200">
                                    <div class="h-2.5 rounded-full bg-rose-600" style="width: 7.1%"></div>
                                </div>
                            </div>
                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                                <div class="flex justify-between text-[13px] font-bold text-slate-800">
                                    <span>ABSTAIN</span>
                                    <span>2 Suara (7.1%)</span>
                                </div>
                                <div class="mt-2 h-2.5 w-full rounded-full bg-slate-200">
                                    <div class="h-2.5 rounded-full bg-slate-600" style="width: 7.1%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            {{-- 7. SCREEN: Pengaturan Informasi & Hero --}}
            @elseif ($active === 'Pengaturan Informasi & Hero')
                <div class="rounded-xl border border-slate-200/80 bg-white p-6 shadow-xs">
                    <h2 class="text-[16px] font-bold text-slate-900">Editor Hero Banner & Informasi Beranda Publik</h2>
                    <p class="text-[12px] text-slate-500">Konfigurasi visual dan pesan sambutan pada halaman utama portal Senat</p>

                    <form class="mt-5 space-y-4" wire:submit.prevent="saveAction('Konten Hero Beranda')">
                        <div>
                            <label class="text-[11.5px] font-semibold text-slate-700">Judul Utama (Display Headline)</label>
                            <input type="text" value="Menegakkan Norma, Mengawal Mutu Akademik Vokasi Unggul"
                                   class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-[12.5px] font-semibold text-slate-900 focus:border-senat-primary focus:outline-none">
                        </div>
                        <div>
                            <label class="text-[11.5px] font-semibold text-slate-700">Sub-deskripsi Hero</label>
                            <textarea rows="3" class="mt-1 w-full rounded-lg border border-slate-300 p-3 text-[12px] text-slate-900 focus:border-senat-primary focus:outline-none">Badan normatif dan perwakilan tertinggi Politeknik Negeri Semarang yang menjalankan fungsi penetapan kebijakan, pertimbangan, dan pengawasan mutu tridharma perguruan tinggi.</textarea>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="text-[11.5px] font-semibold text-slate-700">Tombol Aksi Utama (CTA 1)</label>
                                <input type="text" value="Sampaikan Aspirasi" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-[12px] text-slate-900">
                            </div>
                            <div>
                                <label class="text-[11.5px] font-semibold text-slate-700">Tombol Aksi Kedua (CTA 2)</label>
                                <input type="text" value="Lihat Profil Senat" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-[12px] text-slate-900">
                            </div>
                        </div>
                        <div class="pt-2">
                            <button type="submit" class="rounded-lg bg-senat-primary px-5 py-2.5 text-[12px] font-semibold text-white hover:bg-senat-primaryHover">
                                Publikasikan Pembaruan ke Beranda
                            </button>
                        </div>
                    </form>
                </div>

            {{-- 8. SCREEN: Profil, Tupoksi & Struktur --}}
            @elseif ($active === 'Profil, Tupoksi & Struktur')
                <div class="rounded-xl border border-slate-200/80 bg-white p-6 shadow-xs">
                    <h2 class="text-[16px] font-bold text-slate-900">Manajemen Dokumen Profil & Struktur Senat</h2>
                    <p class="text-[12px] text-slate-500">Kelola visi, misi, landasan hukum, dan diagram struktur organisasi</p>

                    <div class="mt-5 space-y-4">
                        <div>
                            <label class="text-[11.5px] font-semibold text-slate-700">Visi Senat Akademik Polines</label>
                            <textarea rows="2" class="mt-1 w-full rounded-lg border border-slate-300 p-2.5 text-[12px] text-slate-900">Menjadi pilar tata pamong normatif akademik yang kredibel, transparan, dan inovatif dalam mewujudkan Politeknik bertaraf internasional.</textarea>
                        </div>
                        <div>
                            <label class="text-[11.5px] font-semibold text-slate-700">Misi Utama</label>
                            <textarea rows="3" class="mt-1 w-full rounded-lg border border-slate-300 p-2.5 text-[12px] text-slate-900">1. Merumuskan kebijakan akademik yang relevan dengan transformasi industri vokasi.&#10;2. Melakukan pengawasan independen terhadap mutu pembelajaran dan integritas riset.&#10;3. Menyalurkan aspirasi sivitas akademika secara akuntabel.</textarea>
                        </div>
                        <button type="button" wire:click="saveAction('Profil & Tupoksi')"
                                class="rounded-lg bg-senat-primary px-4 py-2 text-[12px] font-semibold text-white hover:bg-senat-primaryHover">
                            Simpan Perubahan Profil
                        </button>
                    </div>
                </div>

            {{-- 9. SCREEN: Direktori Komisi & Anggota --}}
            @elseif ($active === 'Direktori Komisi & Anggota')
                <div class="rounded-xl border border-slate-200/80 bg-white p-6 shadow-xs">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div>
                            <h2 class="text-[16px] font-bold text-slate-900">Direktori Komisi & Anggota Senat</h2>
                            <p class="text-[12px] text-slate-500">Daftar komisi dan susunan anggota periode 2026–2029</p>
                        </div>
                        <button type="button" wire:click="saveAction('Tambah Anggota Senat')"
                                class="rounded-lg bg-senat-primary px-3 py-1.5 text-[12px] font-semibold text-white hover:bg-senat-primaryHover">
                            + Tambah Anggota
                        </button>
                    </div>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($committees as $c)
                            <div class="rounded-xl border border-slate-200 p-4 bg-slate-50/50">
                                <span class="rounded-md bg-slate-200 px-2 py-0.5 text-[10.5px] font-bold text-slate-800">{{ $c['code'] }}</span>
                                <h3 class="mt-2 text-[13.5px] font-bold text-slate-900">{{ $c['name'] }}</h3>
                                <p class="mt-1 text-[11.5px] text-slate-600"><span class="font-semibold text-slate-700">Ketua:</span> {{ $c['lead'] }}</p>
                                <p class="text-[11.5px] text-slate-600"><span class="font-semibold text-slate-700">Sekretaris:</span> {{ $c['secretary'] }}</p>
                                <p class="mt-2 text-[11px] text-slate-500">{{ $c['members_count'] }} Anggota Terdaftar</p>
                            </div>
                        @endforeach
                    </div>
                </div>

            {{-- 10. SCREEN: Manajemen Berita & Siaran Pers --}}
            @elseif ($active === 'Manajemen Berita & Siaran Pers')
                <div class="rounded-xl border border-slate-200/80 bg-white p-6 shadow-xs">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div>
                            <h2 class="text-[16px] font-bold text-slate-900">Publikasi Warta & Berita Senat</h2>
                            <p class="text-[12px] text-slate-500">Kanal siaran pers dan transparansi kegiatan persidangan</p>
                        </div>
                        <button type="button" wire:click="saveAction('Tulis Berita Baru')"
                                class="rounded-lg bg-senat-primary px-3.5 py-1.5 text-[12px] font-semibold text-white hover:bg-senat-primaryHover">
                            + Tulis Berita
                        </button>
                    </div>
                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full text-left text-[12px]">
                            <thead class="border-b border-slate-200 bg-slate-50 text-[11px] font-bold uppercase text-slate-500">
                                <tr>
                                    <th class="px-3 py-2.5">Judul Berita</th>
                                    <th class="px-3 py-2.5">Kategori</th>
                                    <th class="px-3 py-2.5">Tanggal</th>
                                    <th class="px-3 py-2.5 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($posts as $post)
                                    <tr>
                                        <td class="px-3 py-3 font-semibold text-slate-900">{{ $post['title'] }}</td>
                                        <td class="px-3 py-3 text-slate-600">{{ $post['category'] }}</td>
                                        <td class="px-3 py-3 text-slate-500">{{ $post['date'] }}</td>
                                        <td class="px-3 py-3 text-right">
                                            <button type="button" wire:click="saveAction('Edit Berita {{ $post['id'] }}')"
                                                    class="rounded border border-slate-300 px-2.5 py-1 text-[11px] font-semibold text-slate-700 hover:bg-slate-50">
                                                Edit
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            {{-- 11. SCREEN: Katalog Regulasi & Produk Hukum --}}
            @elseif ($active === 'Katalog Regulasi & Produk Hukum')
                <div class="rounded-xl border border-slate-200/80 bg-white p-6 shadow-xs">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div>
                            <h2 class="text-[16px] font-bold text-slate-900">Katalog Produk Hukum & Peraturan Senat</h2>
                            <p class="text-[12px] text-slate-500">Arsip digital peraturan, SK, dan standar akademik resmi</p>
                        </div>
                        <button type="button" wire:click="saveAction('Upload Produk Hukum')"
                                class="rounded-lg bg-senat-primary px-3.5 py-1.5 text-[12px] font-semibold text-white hover:bg-senat-primaryHover">
                            + Unggah Dokumen
                        </button>
                    </div>
                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full text-left text-[12px]">
                            <thead class="border-b border-slate-200 bg-slate-50 text-[11px] font-bold uppercase text-slate-500">
                                <tr>
                                    <th class="px-3 py-2.5">Nomor Dokumen</th>
                                    <th class="px-3 py-2.5">Judul Regulasi</th>
                                    <th class="px-3 py-2.5">Kategori</th>
                                    <th class="px-3 py-2.5">Status</th>
                                    <th class="px-3 py-2.5 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($regulations as $reg)
                                    <tr>
                                        <td class="px-3 py-3 font-mono font-semibold text-slate-700">{{ $reg['nomor'] }}</td>
                                        <td class="px-3 py-3 font-semibold text-slate-900">{{ $reg['title'] }}</td>
                                        <td class="px-3 py-3 text-slate-600">{{ $reg['category'] }}</td>
                                        <td class="px-3 py-3">
                                            <span class="rounded-md px-2 py-0.5 text-[10.5px] font-semibold {{ $reg['status'] === 'Disahkan' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                                {{ $reg['status'] }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-3 text-right">
                                            <button type="button" wire:click="saveAction('Edit Dokumen {{ $reg['id'] }}')"
                                                    class="rounded border border-slate-300 px-2.5 py-1 text-[11px] font-semibold text-slate-700 hover:bg-slate-50">
                                                Edit
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            {{-- 12. SCREEN: Repositori Arsip & Dokumen --}}
            @elseif ($active === 'Repositori Arsip & Dokumen')
                <div class="rounded-xl border border-slate-200/80 bg-white p-6 shadow-xs">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div>
                            <h2 class="text-[16px] font-bold text-slate-900">Repositori Dokumen & Laporan Tahunan</h2>
                            <p class="text-[12px] text-slate-500">Berkas LPJ, OTK, dan arsip kelembagaan Senat Polines</p>
                        </div>
                        <button type="button" wire:click="saveAction('Upload Arsip')"
                                class="rounded-lg bg-senat-primary px-3.5 py-1.5 text-[12px] font-semibold text-white hover:bg-senat-primaryHover">
                            + Unggah Berkas
                        </button>
                    </div>
                    <div class="mt-4 divide-y divide-slate-100">
                        @foreach ($archives as $arc)
                            <div class="flex items-center justify-between py-3.5">
                                <div>
                                    <p class="text-[13px] font-semibold text-slate-900">{{ $arc['title'] }}</p>
                                    <p class="text-[11.5px] text-slate-500">{{ $arc['category'] }} · {{ $arc['year'] }} · {{ $arc['file_size'] }}</p>
                                </div>
                                <button type="button" wire:click="saveAction('Unduh Berkas {{ $arc['id'] }}')"
                                        class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[11.5px] font-semibold text-slate-700 hover:bg-slate-50">
                                    Unduh Berkas
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>

            {{-- 13. SCREEN: Pusat Aspirasi & Tindak Lanjut --}}
            @elseif ($active === 'Pusat Aspirasi & Tindak Lanjut')
                <div class="rounded-xl border border-slate-200/80 bg-white p-6 shadow-xs">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div>
                            <h2 class="text-[16px] font-bold text-slate-900">Manajemen Aspirasi Sivitas Akademika</h2>
                            <p class="text-[12px] text-slate-500">Monitoring tiket aspirasi mahasiswa, dosen, dan tendik</p>
                        </div>
                    </div>
                    <div class="mt-4 space-y-3">
                        @foreach ($aspirations as $asp)
                            <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-md bg-slate-200 px-2 py-0.5 text-[11px] font-mono font-bold text-slate-800">
                                            {{ $asp['ticket_code'] }}
                                        </span>
                                        <span class="text-[11.5px] font-semibold text-slate-600">{{ $asp['sender_category'] }} ({{ $asp['jurusan'] }})</span>
                                    </div>
                                    <span class="rounded-md px-2 py-0.5 text-[10.5px] font-semibold {{ $asp['status'] === 'Ditindaklanjuti Komisi' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $asp['status'] }}
                                    </span>
                                </div>
                                <h3 class="mt-2 text-[13.5px] font-bold text-slate-900">{{ $asp['subject'] }}</h3>
                                <p class="mt-1 text-[12px] text-slate-600">{{ $asp['description'] }}</p>
                                <div class="mt-3 rounded-lg bg-white p-3 border border-slate-200 text-[11.5px]">
                                    <span class="font-bold text-slate-800">Tanggapan Komisi:</span>
                                    <p class="text-slate-600 mt-0.5">{{ $asp['response'] }}</p>
                                </div>
                                <div class="mt-3 flex justify-end gap-2">
                                    <button type="button" wire:click="saveAction('Disposisi Tiket {{ $asp['ticket_code'] }}')"
                                            class="rounded-lg bg-senat-primary px-3 py-1.5 text-[11.5px] font-semibold text-white hover:bg-senat-primaryHover">
                                        Perbarui Respon Disposisi
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            @else
                {{-- Fallback --}}
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-xs">
                    <p class="text-[13px] text-slate-600">Modul ini siap dikonfigurasi lebih lanjut.</p>
                </div>
            @endif

        </div>
    </main>
</div>
