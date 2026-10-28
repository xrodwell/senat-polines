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

<div class="min-h-screen bg-senat-surface text-senat-body antialiased">

    {{-- Topbar khas Portal Senat (Figma 2269:728) --}}
    <header class="sticky top-0 z-40 flex h-[60px] items-center bg-senat-primary text-white lg:h-16 shadow-sm">
        <div class="relative flex w-full items-center gap-3 px-5 lg:px-6">
            <img src="{{ asset('images/logo-polines.png') }}" alt="Logo Polines" class="h-9 w-9 rounded-lg object-contain lg:h-[38px] lg:w-[38px]">
            <span class="leading-none">
                <span class="block text-[14px] font-bold">SENAT<span class="hidden sm:inline"> AKADEMIK</span></span>
                <span class="mt-[6px] hidden text-[11px] font-medium text-[#bfdbfe] sm:block">Politeknik Negeri Semarang</span>
            </span>

            <div class="ml-auto flex items-center gap-2 lg:gap-4">
                {{-- Badge Status Sistem --}}
                <div class="hidden items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-[11.5px] font-medium text-white sm:flex">
                    <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                    <span>Admin Sekretariat</span>
                </div>

                <a href="{{ route('portal.dashboard') }}"
                   class="senat-tap flex h-9 items-center gap-1.5 rounded-lg border border-white/20 bg-white/10 px-3 text-[12px] font-medium text-white transition hover:bg-white/20">
                    <x-senat.icon name="user" class="h-3.5 w-3.5 text-[#bfdbfe]" />
                    <span>Portal Anggota</span>
                </a>

                <a href="{{ route('public.home') }}"
                   class="senat-tap flex h-9 items-center gap-1.5 rounded-lg border border-white/20 bg-white/10 px-3 text-[12px] font-medium text-white transition hover:bg-white/20">
                    <x-senat.icon name="dashboard" class="h-3.5 w-3.5 text-[#bfdbfe]" />
                    <span class="hidden md:inline">Portal Publik</span>
                </a>
            </div>
        </div>
    </header>

    <div class="flex min-h-[calc(100vh-60px)] lg:min-h-[calc(100vh-64px)]">

        {{-- Sidebar (Figma 2269:764 style) --}}
        <aside class="w-full shrink-0 border-b border-senat-sidebarLine bg-white lg:sticky lg:top-16 lg:h-[calc(100vh-64px)] lg:w-[280px] lg:overflow-y-auto lg:border-b-0 lg:border-r"
               aria-label="Navigasi Admin Sekretariat">
            
            <div class="px-[23px] pt-[24px]">
                <p class="text-[11px] font-bold uppercase tracking-[0.08em] text-senat-muted">ADMIN SEKRETARIAT</p>
                <p class="mt-1 text-[12px] text-senat-body">Tata Kelola Sidang &amp; Publikasi</p>
            </div>

            <nav class="mt-5 space-y-6 px-[15px] pb-6" aria-label="Menu Admin">
                @foreach ($groups as $group => $items)
                    <div wire:key="group-{{ \Illuminate\Support\Str::slug($group) }}" aria-labelledby="grup-{{ $loop->index }}">
                        <p id="grup-{{ $loop->index }}" class="px-2 pb-2 text-[10.5px] font-bold uppercase tracking-[0.08em] text-senat-muted">
                            {{ $group }}
                        </p>
                        <div class="space-y-[4px]">
                            @foreach ($items as $item)
                                @php
                                    $isActive = ($active === $item);
                                    $itemSlug = \Illuminate\Support\Str::slug($item);
                                @endphp
                                <a href="{{ route('admin.preview', ['tab' => $item]) }}"
                                   wire:key="menu-link-{{ $itemSlug }}"
                                   @if ($isActive) aria-current="page" @endif
                                   class="senat-tap flex min-h-[40px] w-full items-center gap-[11px] rounded-[9px] px-[12px] text-left transition-colors {{ $isActive ? 'bg-[#eef4fb] ring-1 ring-senat-badgeLine' : 'hover:bg-senat-surface' }}">
                                    <span class="h-2 w-2 shrink-0 rounded-full {{ $isActive ? 'bg-senat-link' : 'bg-slate-300' }}"></span>
                                    <span class="truncate text-[12.5px] {{ $isActive ? 'font-semibold text-senat-primary' : 'font-medium text-senat-ink' }}">{{ $item }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </nav>
        </aside>

        {{-- Area Konten Utama Menggunakan Persis Desain Portal Sekretariat --}}
        <main class="min-w-0 flex-1 px-5 pb-20 pt-6 lg:px-10 lg:pb-12 lg:pt-8">
            <div class="mx-auto max-w-[1116px] space-y-6">

                {{-- Alert Flash --}}
                @if ($flashMessage)
                    <div class="flex items-center justify-between rounded-[10px] bg-[#eaf8f0] px-4 py-3 text-[13px] text-[#116a3c] shadow-xs">
                        <div class="flex items-center gap-2">
                            <x-senat.icon name="check" class="h-4 w-4 shrink-0 text-[#116a3c]" />
                            <span>{{ $flashMessage }}</span>
                        </div>
                        <button type="button" wire:click="$set('flashMessage', '')" class="text-base font-bold text-[#116a3c]">×</button>
                    </div>
                @endif

                {{-- Header Layar --}}
                <header class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.08em] text-senat-orange">
                            <span>Admin Sekretariat</span>
                            <span>/</span>
                            <span>{{ $currentGroup ?? 'Umum' }}</span>
                        </div>
                        <h1 class="mt-1 text-[25px] font-bold leading-tight text-senat-ink">
                            {{ $active }}
                        </h1>
                    </div>

                    <div class="flex items-center gap-2.5">
                        <button type="button"
                                wire:click="saveAction(@js($active))"
                                class="senat-tap inline-flex h-9 items-center gap-1.5 rounded-md bg-senat-primary px-4 text-[12px] font-semibold text-white transition hover:bg-senat-primaryHover">
                            <x-senat.icon name="check" class="h-3.5 w-3.5" />
                            <span>Simpan Perubahan</span>
                        </button>
                    </div>
                </header>

                @if ($flashMessage)
                    <div class="mb-5 flex items-center justify-between rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-[13px] text-emerald-800 shadow-sm" x-data="{ show: true }" x-show="show">
                        <div class="flex items-center gap-2">
                            <span>✅</span>
                            <span>{{ $flashMessage }}</span>
                        </div>
                        <button type="button" @click="show = false" class="text-sm font-bold text-emerald-600 hover:text-emerald-900" aria-label="Tutup">&times;</button>
                    </div>
                @endif

            {{-- 1. SCREEN: Dashboard Rapat --}}
            @if ($active === 'Dashboard Rapat' || $active === 'Dashboard')
                <div wire:key="screen-dashboard-rapat" class="space-y-6">
                {{-- Info Banner Khas Portal --}}
                <div class="flex items-start gap-3 rounded-[14px] bg-senat-badgeBg px-4 py-3.5 text-senat-link shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
                    <span class="text-[18px] font-bold leading-none" aria-hidden="true">i</span>
                    <p class="text-[13px] font-medium leading-snug">
                        Panel administrasi terpadu: kelola jadwal sidang, kuorum presensi, risalah, serta pembaharuan konten situs resmi.
                    </p>
                </div>

                {{-- Stat / KPI Khas Portal --}}
                <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
                    <x-senat.stat label="Sidang terjadwal" :value="str_pad((string) $stats['meetings_count'], 2, '0', STR_PAD_LEFT)"
                                  :hint="count($meetings).' agenda terdaftar'" icon="calendar" />
                    <x-senat.stat label="Notulensi draft" :value="str_pad((string) ($notulenItem ? 1 : 0), 2, '0', STR_PAD_LEFT)"
                                  hint="Perlu verifikasi sekretariat" icon="minutes" />
                    <x-senat.stat label="Voting dibuka" :value="str_pad((string) ($activeMeeting ? 1 : 0), 2, '0', STR_PAD_LEFT)"
                                  :hint="$activeMeeting ? 'Sesi sedang berlangsung' : 'Belum ada voting aktif'" icon="check" />
                    <x-senat.stat label="Aspirasi kajian" :value="str_pad((string) $stats['pending_aspirations'], 2, '0', STR_PAD_LEFT)"
                                  hint="Menunggu telaah komisi" icon="aspiration" />
                </div>

                <div class="grid gap-5 lg:grid-cols-2">
                    {{-- Aksi Cepat --}}
                    <section class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
                        <h2 class="text-[18px] font-bold text-senat-ink">Aksi Cepat</h2>
                        <div class="mt-4 space-y-3">
                            @foreach ($aksiCepat as $aksi)
                                <a href="{{ route('admin.preview', ['tab' => $aksi['action']]) }}"
                                   class="flex w-full items-center gap-3 rounded-[10px] px-4 py-3 text-left transition-colors {{ $aksi['accent'] ? 'bg-senat-badgeBg' : 'border border-senat-sidebarLine bg-white hover:border-senat-badgeLine' }}">
                                    <x-senat.icon :name="$aksi['icon']" class="h-4 w-4 shrink-0 {{ $aksi['accent'] ? 'text-senat-link' : 'text-senat-muted' }}" />
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-[14px] font-semibold text-senat-ink">{{ $aksi['title'] }}</span>
                                        <span class="mt-0.5 block truncate text-[11px] text-senat-body">{{ $aksi['desc'] }}</span>
                                    </span>
                                    <span class="text-[20px] font-bold leading-none text-senat-link" aria-hidden="true">›</span>
                                </a>
                            @endforeach
                        </div>
                    </section>

                    {{-- Antrean Tindakan --}}
                    <section class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
                        <h2 class="text-[18px] font-bold text-senat-ink">Antrean Tindakan</h2>
                        @if ($antrean->isEmpty())
                            <x-senat.empty class="mt-4" title="Tidak ada tindakan tertunda"
                                           message="Tidak ada berkas yang menunggu verifikasi sekretariat saat ini." />
                        @else
                            <ul class="mt-4 divide-y divide-senat-sidebarLine">
                                @foreach ($antrean as $item)
                                    <li class="flex items-start gap-3 py-3 first:pt-1 last:pb-1">
                                        <span class="mt-1.5 h-[9px] w-[9px] shrink-0 rounded-[5px] {{ $item['tone'] }}" aria-hidden="true"></span>
                                        <span class="min-w-0">
                                            <span class="block text-[13px] font-semibold text-senat-ink">{{ $item['title'] }}</span>
                                            <span class="mt-1 block text-[11px] leading-snug text-senat-body">{{ $item['meta'] }}</span>
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </section>
                </div>

                {{-- Agenda Sidang --}}
                <section class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
                    <div class="flex items-center justify-between">
                        <h2 class="text-[18px] font-bold text-senat-ink">Agenda Sidang Terjadwal</h2>
                        <a href="{{ route('admin.preview', ['tab' => 'Jadwal & Agenda Sidang']) }}"
                           class="text-[12px] font-semibold text-senat-link hover:underline">
                            Kelola Jadwal →
                        </a>
                    </div>
                    @if (empty($meetings))
                        <x-senat.empty class="mt-4" title="Belum ada agenda sidang"
                                       message="Agenda akan tampil di sini setelah sidang dijadwalkan." />
                    @else
                        <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                            @foreach ($meetings as $meeting)
                                <article class="flex flex-col justify-between rounded-[12px] border border-senat-sidebarLine bg-senat-surface p-4">
                                    <div>
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="text-[11px] font-bold uppercase text-senat-muted">{{ $meeting['session_code'] }}</span>
                                            <x-senat.badge :state="$this->chipState($meeting['type'])">{{ $this->chipLabel($meeting['type']) }}</x-senat.badge>
                                        </div>
                                        <h3 class="mt-2 text-[14px] font-bold leading-snug text-senat-ink">{{ $meeting['title'] }}</h3>
                                        <p class="mt-2 text-[12px] text-senat-body">
                                            {{ \Illuminate\Support\Carbon::parse($meeting['date'])->format('d M Y') }} · {{ $meeting['time'] }}
                                        </p>
                                        <p class="mt-1 text-[11px] text-senat-muted">{{ $meeting['room'] }}</p>
                                    </div>
                                    <div class="mt-4 flex items-center justify-between border-t border-senat-sidebarLine pt-3 text-[11px]">
                                        <span class="font-medium text-senat-body">Hadir: {{ $meeting['present_count'] }}/{{ $meeting['total_invited'] }}</span>
                                        <a href="{{ route('admin.preview', ['tab' => 'Presensi & Verifikasi QR']) }}"
                                           class="font-semibold text-senat-link hover:underline">Presensi ›</a>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </section>
                </div>

            {{-- 2. SCREEN: Jadwal & Agenda Sidang (Figma 13B) --}}
            @elseif ($active === 'Jadwal & Agenda Sidang')
                <div wire:key="screen-jadwal-agenda" class="grid gap-5 xl:grid-cols-[730px_minmax(0,1fr)]">
                    <section class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-[18px] font-bold text-senat-ink">
                                    {{ $editingMeetingId ? 'Sunting Sidang #' . $editingMeetingId : 'Buat Sidang Baru' }}
                                </h2>
                                <p class="mt-1.5 text-[13px] text-senat-body">Gunakan jenis sidang sesuai ketentuan Senat (tersambung Backend API).</p>
                            </div>
                            @if ($editingMeetingId)
                                <span class="rounded-full bg-amber-100 px-3 py-1 text-[11px] font-bold text-amber-900">Mode Edit Aktif</span>
                            @endif
                        </div>

                        <form class="mt-5 space-y-4" wire:submit.prevent="saveMeeting('Terjadwal')">
                            <div>
                                <label class="text-[12px] font-semibold text-senat-ink">Judul / agenda sidang <span class="text-rose-500">*</span></label>
                                <input type="text" wire:model="meetingTitle" placeholder="Contoh: Sidang Pleno Penetapan Standar Kurikulum Vokasi" required
                                       class="mt-1.5 h-[38px] w-full rounded-[8px] border border-senat-border bg-white px-3 text-[12px] text-senat-ink focus:border-senat-link focus:outline-none focus:ring-1 focus:ring-senat-link">
                                @error('meetingTitle') <span class="text-[11px] text-rose-500">{{ $message }}</span> @enderror
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="text-[12px] font-semibold text-senat-ink">Jenis Sidang Senat <span class="text-rose-500">*</span></label>
                                    <select wire:model="meetingJenisRapatId"
                                            class="mt-1.5 h-[38px] w-full rounded-[8px] border border-senat-border bg-white px-3 text-[12px] text-senat-ink focus:border-senat-link focus:outline-none focus:ring-1 focus:ring-senat-link">
                                        <option value="1">Rapat Pleno</option>
                                        <option value="2">Rapat Komisi</option>
                                        <option value="3">Rapat Koordinasi</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-[12px] font-semibold text-senat-ink">Tanggal dan waktu <span class="text-rose-500">*</span></label>
                                    <input type="datetime-local" wire:model="meetingDateTime" required
                                           class="mt-1.5 h-[38px] w-full rounded-[8px] border border-senat-border bg-white px-3 text-[12px] text-senat-ink focus:border-senat-link focus:outline-none focus:ring-1 focus:ring-senat-link">
                                    @error('meetingDateTime') <span class="text-[11px] text-rose-500">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="text-[12px] font-semibold text-senat-ink">Lokasi atau tautan hybrid <span class="text-rose-500">*</span></label>
                                    <input type="text" wire:model="meetingRoom" placeholder="Contoh: Ruang Sidang Utama Gd. Direktorat" required
                                           class="mt-1.5 h-[38px] w-full rounded-[8px] border border-senat-border bg-white px-3 text-[12px] text-senat-ink focus:border-senat-link focus:outline-none focus:ring-1 focus:ring-senat-link">
                                    @error('meetingRoom') <span class="text-[11px] text-rose-500">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="text-[12px] font-semibold text-senat-ink">Peserta dan undangan</label>
                                    <input type="text" wire:model="meetingPeserta" placeholder="Contoh: Seluruh Anggota Senat (34 Orang)"
                                           class="mt-1.5 h-[38px] w-full rounded-[8px] border border-senat-border bg-white px-3 text-[12px] text-senat-ink focus:border-senat-link focus:outline-none focus:ring-1 focus:ring-senat-link">
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-3 pt-2">
                                <x-senat.button variant="secondary" type="button" wire:click="saveMeeting('Draft')">
                                    Simpan sebagai Draft
                                </x-senat.button>
                                <x-senat.button variant="primary" type="submit">
                                    {{ $editingMeetingId ? 'Perbarui Sidang' : 'Jadwalkan & Terbitkan' }}
                                </x-senat.button>
                                @if ($editingMeetingId)
                                    <x-senat.button variant="secondary" type="button" wire:click="resetMeetingForm">
                                        Batal Edit
                                    </x-senat.button>
                                @endif
                            </div>
                        </form>
                    </section>

                    <section class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
                        <div class="flex items-center justify-between gap-2 border-b border-senat-sidebarLine pb-3">
                            <div>
                                <h2 class="text-[18px] font-bold text-senat-ink">Sidang Terjadwal</h2>
                                <p class="text-[11px] text-senat-muted">Data dinamis dari Backend API port 8001</p>
                            </div>
                            <span class="rounded-full bg-blue-50 px-2.5 py-0.5 text-[11px] font-bold text-blue-700">
                                {{ count($meetings) }} Sidang
                            </span>
                        </div>
                        <ul class="mt-3 divide-y divide-senat-sidebarLine max-h-[580px] overflow-y-auto pr-1">
                            @forelse ($meetings as $meeting)
                                <li class="py-4 first:pt-2">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0 flex-1">
                                            <p class="text-[14px] font-semibold text-senat-ink leading-snug">{{ $meeting['title'] }}</p>
                                            <p class="mt-1 text-[12px] text-senat-body">
                                                {{ \Illuminate\Support\Carbon::parse($meeting['date'])->format('d M Y') }} · {{ $meeting['time'] }}
                                            </p>
                                            <p class="mt-0.5 text-[11px] text-senat-muted flex items-center gap-1">
                                                <span>📍</span> {{ $meeting['room'] }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="mt-3 flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-2">
                                            <x-senat.badge :state="$this->chipState($meeting['type'])">{{ $this->chipLabel($meeting['type']) }}</x-senat.badge>
                                            <span class="rounded px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider {{ in_array($meeting['status'], ['Terjadwal', 'dijadwalkan']) ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($meeting['status'] === 'Draft' ? 'bg-slate-100 text-slate-600' : 'bg-amber-50 text-amber-700 border border-amber-200') }}">
                                                {{ $meeting['status'] }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <x-senat.button variant="secondary" size="sm" type="button" wire:click="editMeeting({{ $meeting['id'] }})">Kelola / Edit</x-senat.button>
                                            <button type="button" wire:click="deleteMeeting({{ $meeting['id'] }})"
                                                    onclick="return confirm('Hapus agenda sidang ini dari backend?') || event.stopImmediatePropagation()"
                                                    class="rounded px-2 py-1 text-[11px] font-semibold text-rose-600 hover:bg-rose-50 transition-colors">
                                                Hapus
                                            </button>
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li class="py-6 text-center">
                                    <p class="text-[13px] text-senat-muted">Belum ada sidang terjadwal di Backend API.</p>
                                    <p class="mt-1 text-[11px] text-senat-muted">Gunakan form di sebelah kiri untuk menjadwalkan sidang baru.</p>
                                </li>
                            @endforelse
                        </ul>
                    </section>
                </div>

            {{-- 3. SCREEN: Presensi & Verifikasi QR (Figma 13D) --}}
            @elseif ($active === 'Presensi & Verifikasi QR')
                <div wire:key="screen-presensi-qr"
                     wire:poll.15s.visible="refreshAttendance"
                     class="space-y-6">
                    <div class="grid gap-5 xl:grid-cols-[730px_minmax(0,1fr)]">
                        {{-- Kartu Status Presensi & Sidang Aktif --}}
                        <section class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    @if ($isPresensiOpen)
                                        <x-senat.badge state="success">
                                            Sesi Presensi Aktif
                                        </x-senat.badge>
                                    @else
                                        <x-senat.badge state="danger">
                                            Sesi Presensi Dibekukan (Terkunci)
                                        </x-senat.badge>
                                    @endif

                                    @if ($isKuorumLocked)
                                        <x-senat.badge state="neutral">
                                            Kuorum Dikunci
                                        </x-senat.badge>
                                    @endif
                                </div>

                                <div class="text-right">
                                    <span class="text-[11px] font-mono uppercase text-senat-muted">Kode Sesi:</span>
                                    <span class="ml-1 rounded border border-senat-line bg-senat-surface px-2 py-0.5 font-mono text-[13px] font-bold tracking-wider text-senat-primary">{{ $sessionCode ?: 'PLN-892' }}</span>
                                </div>
                            </div>

                            <h2 class="mt-4 text-[20px] font-bold leading-snug text-senat-ink">
                                {{ $liveMeeting['title'] ?? ($activeMeeting['title'] ?? 'Sidang Senat Akademik Polines') }}
                            </h2>
                            <p class="mt-2 text-[13px] text-senat-body">
                                {{ $liveMeeting['room'] ?? ($activeMeeting['room'] ?? 'Ruang Sidang Utama') }} · {{ $liveMeeting['time'] ?? ($activeMeeting['time'] ?? '09:00 WIB') }}
                            </p>

                            <h3 class="mt-6 text-[15px] font-semibold text-senat-ink">Rekap Kehadiran</h3>
                            <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                <div class="rounded-[10px] bg-[#eaf8f0] px-3.5 py-3 border border-emerald-100">
                                    <p class="text-[12px] font-medium text-senat-ink">Hadir tercatat</p>
                                    <p class="mt-1 text-[18px] font-bold text-senat-ink">
                                        {{ $liveKuorum['present_count'] ?? 0 }} <span class="text-[11px] font-normal text-senat-body">({{ $liveKuorum['present_percentage'] ?? 0 }}%)</span>
                                    </p>
                                    <p class="mt-0.5 text-[11px] text-senat-muted">Peserta terverifikasi</p>
                                </div>
                                <div class="rounded-[10px] bg-[#fff4df] px-3.5 py-3 border border-amber-200">
                                    <p class="text-[12px] font-medium text-senat-ink">Menunggu validasi</p>
                                    <p class="mt-1 text-[18px] font-bold text-senat-ink">
                                        {{ $liveKuorum['menunggu_count'] ?? 0 }} <span class="text-[11px] font-normal text-senat-body">peserta</span>
                                    </p>
                                    <p class="mt-0.5 text-[11px] text-senat-muted">Belum presensi</p>
                                </div>
                                <div class="rounded-[10px] bg-senat-badgeBg px-3.5 py-3 border border-senat-badgeLine">
                                    <p class="text-[12px] font-medium text-senat-ink">Izin resmi</p>
                                    <p class="mt-1 text-[18px] font-bold text-senat-ink">
                                        {{ $liveKuorum['izin_count'] ?? 0 }} <span class="text-[11px] font-normal text-senat-body">peserta</span>
                                    </p>
                                    <p class="mt-0.5 text-[11px] text-senat-muted">Tidak dihitung kuorum</p>
                                </div>
                                <div class="rounded-[10px] bg-[#fdecef] px-3.5 py-3 border border-rose-200">
                                    <p class="text-[12px] font-medium text-senat-ink">Tidak hadir</p>
                                    <p class="mt-1 text-[18px] font-bold text-senat-ink">
                                        {{ $liveKuorum['tidak_hadir_count'] ?? 0 }} <span class="text-[11px] font-normal text-senat-body">peserta</span>
                                    </p>
                                    <p class="mt-0.5 text-[11px] text-senat-muted">Tanpa keterangan</p>
                                </div>
                            </div>

                            <div class="mt-6 flex flex-wrap items-center gap-3 pt-3 border-t border-senat-sidebarLine">
                                @if ($isPresensiOpen)
                                    <x-senat.button variant="primary"
                                                    wire:key="btn-open-modal-qr"
                                                    type="button"
                                                    wire:click="openQrModal">
                                        Tampilkan Layar QR Presensi
                                    </x-senat.button>
                                    <x-senat.button variant="secondary"
                                                    wire:key="btn-freeze-presensi"
                                                    type="button"
                                                    wire:click="freezePresensi"
                                                    wire:confirm="Yakin ingin membekukan sesi presensi? QR Code akan langsung kedaluwarsa dan status kuorum dikunci untuk berita acara.">
                                        Bekukan setelah validasi
                                    </x-senat.button>
                                @else
                                    <x-senat.button variant="primary"
                                                    wire:key="btn-reopen-presensi"
                                                    type="button"
                                                    wire:click="generateQrPresensi">
                                        ↻ Buka Kembali Sesi &amp; Generate QR Baru
                                    </x-senat.button>
                                    <span class="text-[11px] text-senat-muted italic">Sesi telah dikunci. Anggota tidak bisa scan mandiri kecuali dibuka kembali.</span>
                                @endif
                            </div>

                            <div class="mt-5 flex items-center gap-3 rounded-[8px] bg-[#fff4df] px-3 py-2 border border-[#fed7aa]">
                                <span class="text-[15px] font-semibold text-[#a95100]" aria-hidden="true">i</span>
                                <p class="text-[12px] text-senat-ink">Status validasi dan penguncian tampil langsung di kartu presensi.</p>
                            </div>
                        </section>

                        {{-- Status Kuorum (Figma 13D Right Column) --}}
                        <section class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)] flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between">
                                    <h2 class="text-[18px] font-bold text-senat-ink">Status Kuorum</h2>
                                    @if ($liveKuorum['is_kuorum'] ?? false)
                                        <x-senat.badge state="success">✓ Memenuhi Kuorum</x-senat.badge>
                                    @else
                                        <x-senat.badge state="warning">⚠ Belum Kuorum</x-senat.badge>
                                    @endif
                                </div>
                                <p class="mt-1.5 text-[12px] text-senat-body">Angka diisi otomatis sesudah presensi disahkan.</p>

                                <dl class="mt-5 space-y-3">
                                    <div class="flex items-center justify-between gap-3 rounded-[10px] bg-senat-surface px-4 py-3.5">
                                        <dt class="text-[13px] font-medium text-senat-ink">Peserta terdaftar</dt>
                                        <dd class="text-[11px] font-mono font-bold text-senat-muted">{{ $liveKuorum['total_invited'] ?? 34 }} peserta</dd>
                                    </div>
                                    <div class="flex items-center justify-between gap-3 rounded-[10px] bg-[#eaf8f0] px-4 py-3.5">
                                        <dt class="text-[13px] font-medium text-senat-ink">Hadir tercatat</dt>
                                        <dd class="text-[11px] font-mono font-bold text-[#116a3c]">{{ $liveKuorum['present_count'] ?? 0 }} peserta ({{ $liveKuorum['present_percentage'] ?? 0 }}%)</dd>
                                    </div>
                                    <div class="flex items-center justify-between gap-3 rounded-[10px] bg-senat-surface px-4 py-3.5">
                                        <dt class="text-[13px] font-medium text-senat-ink">Ambang kuorum</dt>
                                        <dd class="text-[11px] font-mono font-bold text-senat-orange">{{ $liveKuorum['ambang_kuorum'] ?? 23 }} peserta (67%)</dd>
                                    </div>
                                </dl>

                                <p class="mt-4 text-[12px] leading-relaxed text-senat-body">
                                    Syarat sah putusan: kehadiran minimal 2/3 anggota aktif. Izin resmi tidak dihitung dalam kalkulasi kuorum.
                                </p>
                            </div>

                            <div class="mt-5 pt-3 border-t border-senat-sidebarLine">
                                <x-senat.button variant="primary" size="sm" type="button" wire:click="select('Kuorum & Kehadiran Anggota')">
                                    Buka Lembar Kuorum &amp; Berita Acara ›
                                </x-senat.button>
                            </div>
                        </section>
                    </div>

                    {{-- TABEL ANGGOTA & VALIDASI PRESENSI SECARA LIVE --}}
                    <section class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
                        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-senat-sidebarLine pb-4">
                            <div>
                                <h3 class="text-[17px] font-bold text-senat-ink flex items-center gap-2">
                                    Daftar Kehadiran Anggota Sidang
                                    <span class="inline-flex items-center gap-1 text-[11px] font-normal text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-ping"></span> Live Sync
                                    </span>
                                </h3>
                                <p class="text-[12px] text-senat-muted mt-0.5">Admin dapat mengubah status kehadiran peserta secara langsung. Perubahan akan memiliki cap verifikasi Admin.</p>
                            </div>

                            {{-- Filter status --}}
                            <div class="flex items-center gap-1.5 bg-senat-surface p-1 rounded-lg border border-senat-line text-xs">
                                @foreach (['semua' => 'Semua', 'hadir' => 'Hadir', 'izin' => 'Izin', 'tidak_hadir' => 'Tidak Hadir', 'menunggu' => 'Menunggu'] as $fKey => $fLabel)
                                    <button type="button"
                                            wire:key="filter-tab-{{ $fKey }}"
                                            wire:click="$set('filterKehadiran', '{{ $fKey }}')"
                                            class="px-3 py-1 rounded-md text-[11px] font-semibold transition-colors {{ $filterKehadiran === $fKey ? 'bg-white shadow text-senat-primary font-bold' : 'text-senat-muted hover:text-senat-ink' }}">
                                        {{ $fLabel }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Tabel Data --}}
                        <div class="mt-4 overflow-x-auto">
                            <table class="w-full text-left text-xs border-collapse">
                                <thead>
                                    <tr class="bg-senat-surface text-senat-muted uppercase tracking-wider text-[10px] border-b border-senat-line font-bold">
                                        <th class="py-2.5 px-3">No</th>
                                        <th class="py-2.5 px-3">Anggota Senat</th>
                                        <th class="py-2.5 px-3">Status Kehadiran</th>
                                        <th class="py-2.5 px-3">Waktu &amp; Metode</th>
                                        <th class="py-2.5 px-3">Catatan / Verifikasi</th>
                                        <th class="py-2.5 px-3 text-right">Aksi Validasi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-senat-line">
                                    @forelse ($filteredAnggota as $idx => $a)
                                        <tr wire:key="row-member-{{ $a['id_user'] }}"
                                            class="hover:bg-senat-surface/60 transition-colors {{ $a['status_kehadiran'] === 'hadir' ? 'bg-[#eaf8f0]/30' : '' }}">
                                            <td class="py-3 px-3 font-mono text-senat-muted">{{ $idx + 1 }}</td>
                                            <td class="py-3 px-3">
                                                <div class="font-bold text-senat-ink text-[13px]">{{ $a['nama'] }}</div>
                                                <div class="text-[11px] font-mono text-senat-muted">NIP/ID: {{ $a['nim_nip'] }}</div>
                                            </td>
                                            <td class="py-3 px-3">
                                                @if ($a['status_kehadiran'] === 'hadir')
                                                    <x-senat.badge state="success">Hadir</x-senat.badge>
                                                @elseif ($a['status_kehadiran'] === 'izin')
                                                    <x-senat.badge state="info">Izin Resmi</x-senat.badge>
                                                @elseif ($a['status_kehadiran'] === 'tidak_hadir')
                                                    <x-senat.badge state="danger">Tidak Hadir</x-senat.badge>
                                                @else
                                                    <x-senat.badge state="neutral">Menunggu</x-senat.badge>
                                                @endif
                                            </td>
                                            <td class="py-3 px-3">
                                                @if ($a['waktu_presensi'])
                                                    <span class="font-mono text-senat-ink font-semibold text-[12px]">{{ $a['waktu_presensi'] }} WIB</span>
                                                    <div class="text-[10px] text-senat-muted capitalize">
                                                        {{ str_replace('_', ' ', $a['metode_presensi'] ?: 'Mandiri') }}
                                                    </div>
                                                @else
                                                    <span class="text-senat-muted text-[11px]">—</span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-3">
                                                @if ($a['is_edited_by_admin'])
                                                    <div class="inline-flex flex-col">
                                                        <x-senat.badge state="warning" :dot="false">★ Diedit Admin</x-senat.badge>
                                                        <span class="text-[10px] text-senat-muted mt-0.5 max-w-[200px] truncate" title="{{ $a['catatan_admin'] }}">{{ $a['catatan_admin'] }}</span>
                                                    </div>
                                                @else
                                                    <span class="text-[11px] text-senat-muted">Otomatis</span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-3 text-right">
                                                <div class="inline-flex items-center gap-1.5 justify-end" wire:key="actions-group-{{ $a['id_user'] }}">
                                                    {{-- Button Hadir --}}
                                                    <button type="button"
                                                            wire:key="btn-act-hadir-{{ $a['id_user'] }}"
                                                            wire:click="validateMember({{ $a['id_user'] }}, 'hadir')"
                                                            wire:loading.attr="disabled"
                                                            wire:target="validateMember"
                                                            @disabled($a['status_kehadiran'] === 'hadir')
                                                            class="h-7 px-2.5 rounded text-[11px] font-semibold transition-colors duration-150 {{ $a['status_kehadiran'] === 'hadir' ? 'bg-[#eaf8f0] text-[#116a3c] font-bold border border-[#bbf0d2] opacity-80 cursor-default' : 'bg-white text-senat-ink border border-senat-line hover:border-[#16a34a] hover:text-[#16a34a] hover:bg-[#f0fdf4]' }}"
                                                            title="{{ $a['status_kehadiran'] === 'hadir' ? 'Status saat ini: Hadir' : 'Ubah status menjadi Hadir' }}">
                                                        {{ $a['status_kehadiran'] === 'hadir' ? '✓ Hadir' : '+ Hadir' }}
                                                    </button>

                                                    {{-- Button Izin --}}
                                                    <button type="button"
                                                            wire:key="btn-act-izin-{{ $a['id_user'] }}"
                                                            wire:click="validateMember({{ $a['id_user'] }}, 'izin')"
                                                            wire:loading.attr="disabled"
                                                            wire:target="validateMember"
                                                            @disabled($a['status_kehadiran'] === 'izin')
                                                            class="h-7 px-2.5 rounded text-[11px] font-semibold transition-colors duration-150 {{ $a['status_kehadiran'] === 'izin' ? 'bg-senat-badgeBg text-senat-link font-bold border border-senat-badgeLine opacity-80 cursor-default' : 'bg-white text-senat-ink border border-senat-line hover:border-senat-link hover:text-senat-link hover:bg-senat-badgeBg' }}"
                                                            title="{{ $a['status_kehadiran'] === 'izin' ? 'Status saat ini: Izin' : 'Ubah status menjadi Izin Resmi' }}">
                                                        {{ $a['status_kehadiran'] === 'izin' ? '✓ Izin' : 'Izin' }}
                                                    </button>

                                                    {{-- Button Alpa / Tidak Hadir --}}
                                                    <button type="button"
                                                            wire:key="btn-act-alpa-{{ $a['id_user'] }}"
                                                            wire:click="validateMember({{ $a['id_user'] }}, 'tidak_hadir')"
                                                            wire:loading.attr="disabled"
                                                            wire:target="validateMember"
                                                            @disabled($a['status_kehadiran'] === 'tidak_hadir')
                                                            class="h-7 px-2.5 rounded text-[11px] font-semibold transition-colors duration-150 {{ $a['status_kehadiran'] === 'tidak_hadir' ? 'bg-[#fdecef] text-[#991b1b] font-bold border border-[#fca5a5] opacity-80 cursor-default' : 'bg-white text-senat-ink border border-senat-line hover:border-[#dc2626] hover:text-[#dc2626] hover:bg-[#fef2f2]' }}"
                                                            title="{{ $a['status_kehadiran'] === 'tidak_hadir' ? 'Status saat ini: Tidak Hadir' : 'Ubah status menjadi Tidak Hadir' }}">
                                                        {{ $a['status_kehadiran'] === 'tidak_hadir' ? '✓ Alpa' : 'Alpa' }}
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr wire:key="empty-table-row">
                                            <td colspan="6" class="py-8 text-center text-senat-muted">
                                                Tidak ada anggota yang sesuai dengan filter kehadiran.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>

                    {{-- MODAL PROYEKTOR / TAMPILAN QR PRESENSI BESAR --}}
                    @if ($showQrModal)
                        <div wire:key="modal-proyektor-qr"
                             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/80 backdrop-blur-sm p-4"
                             @keydown.escape.window="$wire.closeQrModal()">
                            <div class="relative w-full max-w-lg rounded-[14px] bg-white p-6 sm:p-8 text-center shadow-2xl border border-senat-sidebarLine"
                                 @click.outside="$wire.closeQrModal()">
                                <button type="button"
                                        wire:click="closeQrModal"
                                        class="absolute top-4 right-4 rounded-full p-2 text-senat-muted hover:bg-senat-surface hover:text-senat-ink transition-colors"
                                        aria-label="Tutup">
                                    ✕
                                </button>

                                <h3 class="mt-3 text-[20px] font-bold text-senat-ink">Pindai QR Presensi Sidang</h3>
                                <p class="text-[12px] text-senat-body mt-1 max-w-sm mx-auto">
                                    Buka kamera ponsel di alamat <span class="font-mono text-senat-link font-bold">/portal/agenda</span> untuk mencatat kehadiran resmi.
                                </p>

                                {{-- Kanvas QR Code (wire:ignore agar tidak di-wipe oleh wire:poll setiap 15 detik) --}}
                                <div wire:ignore
                                     class="mt-6 flex flex-col items-center justify-center"
                                     x-data="{
                                         token: @js($qrToken),
                                         draw() {
                                             this.$nextTick(() => {
                                                 const canvas = this.$refs.qrCanvas;
                                                 if (canvas && window.renderQrCode && this.token) {
                                                     window.renderQrCode(canvas, this.token, { width: 280, margin: 2 });
                                                 }
                                             });
                                         }
                                     }"
                                     x-init="draw()"
                                     @qr-updated.window="token = $event.detail.token; draw()">
                                    <div class="p-4 bg-white border border-senat-sidebarLine rounded-[12px] shadow-sm">
                                        <canvas x-ref="qrCanvas" class="max-w-full"></canvas>
                                    </div>
                                </div>

                                {{-- Kode Sesi Alternatif 6 Digit --}}
                                <div class="mt-5 rounded-[10px] bg-senat-surface border border-senat-line p-3">
                                    <p class="text-[11px] font-semibold uppercase tracking-wider text-senat-muted">Kamera Bermasalah? Masukkan Kode Sesi Manual:</p>
                                    <div class="font-mono text-[28px] font-bold text-senat-primary tracking-widest mt-1">{{ $sessionCode }}</div>
                                </div>

                                <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                                    <x-senat.button variant="secondary" size="sm" type="button" wire:click="closeQrModal">
                                        Tutup Tampilan
                                    </x-senat.button>
                                    <x-senat.button variant="primary"
                                                    size="sm"
                                                    type="button"
                                                    wire:click="freezePresensi"
                                                    wire:confirm="Yakin ingin membekukan presensi sekarang?">
                                        Bekukan Presensi &amp; Kunci Kuorum
                                    </x-senat.button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

            {{-- 4. SCREEN: Kuorum & Kehadiran Anggota --}}
            @elseif ($active === 'Kuorum & Kehadiran Anggota')
                <section wire:key="screen-kuorum-kehadiran" wire:poll.15s.visible="refreshAttendance" class="rounded-[14px] border border-senat-sidebarLine bg-white p-6 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)] space-y-6">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            @if ($liveKuorum['is_kuorum'] ?? false)
                                <x-senat.badge state="success">✓ Kuorum Terpenuhi</x-senat.badge>
                            @else
                                <x-senat.badge state="warning">⚠ Belum Memenuhi Kuorum</x-senat.badge>
                            @endif
                            <h2 class="mt-3 text-[22px] font-bold text-senat-ink">
                                Status Kuorum: {{ $liveMeeting['title'] ?? 'Sidang Pleno Senat Akademik' }}
                            </h2>
                            <p class="mt-1 text-[13px] text-senat-body">
                                Ketentuan Statuta Politeknik Negeri Semarang: minimal kehadiran 2/3 anggota aktif untuk keabsahan penetapan keputusan resmi.
                            </p>
                        </div>

                        <div class="text-right">
                            <span class="text-[12px] text-senat-muted">Status Presensi:</span>
                            <div class="font-bold text-[13px] {{ $isPresensiOpen ? 'text-emerald-700' : 'text-slate-700' }}">
                                {{ $isPresensiOpen ? '● Presensi Sedang Dibuka' : '🔒 Presensi Telah Dikunci' }}
                            </div>
                        </div>
                    </div>

                    {{-- Visual Progress Bar Kuorum --}}
                    <div class="rounded-xl border border-senat-line bg-senat-surface p-5">
                        <div class="flex items-center justify-between text-xs font-bold mb-2">
                            <span class="text-senat-ink">Tingkat Kehadiran Anggota: {{ $liveKuorum['present_percentage'] ?? 0 }}%</span>
                            <span class="text-senat-orange">Ambang Kuorum Sah: 67% ({{ $liveKuorum['ambang_kuorum'] ?? 23 }} dari {{ $liveKuorum['total_invited'] ?? 34 }} anggota)</span>
                        </div>
                        <div class="relative w-full h-5 bg-slate-200 rounded-full overflow-hidden">
                            <div class="absolute inset-y-0 left-[66.7%] w-1 bg-senat-orange z-10" title="Batas Kuorum Sah 2/3 (66.7%)"></div>
                            <div class="h-full {{ ($liveKuorum['is_kuorum'] ?? false) ? 'bg-emerald-600' : 'bg-amber-500' }} transition-all duration-500"
                                 style="width: {{ min(100, $liveKuorum['present_percentage'] ?? 0) }}%"></div>
                        </div>
                        <div class="flex justify-between text-[11px] font-mono text-slate-500 mt-2">
                            <span>0 peserta</span>
                            <span class="text-senat-orange font-bold">▲ Garis Ambang Sah 2/3</span>
                            <span>{{ $liveKuorum['total_invited'] ?? 34 }} peserta total</span>
                        </div>
                    </div>

                    {{-- Metric Cards (Figma 13D Design Tokens) --}}
                    <div class="grid gap-4 sm:grid-cols-4">
                        <div class="rounded-[10px] bg-senat-surface p-4 border border-senat-sidebarLine">
                            <p class="text-[12px] text-senat-muted uppercase font-semibold">Total Undangan Sidang</p>
                            <p class="mt-1 text-[26px] font-bold text-senat-ink">{{ $liveKuorum['total_invited'] ?? 34 }} <span class="text-xs font-normal">Orang</span></p>
                            <p class="text-[10px] text-senat-muted mt-1">Berdasarkan daftar undangan rapat</p>
                        </div>
                        <div class="rounded-[10px] bg-senat-surface p-4 border border-senat-sidebarLine">
                            <p class="text-[12px] text-senat-muted uppercase font-semibold">Syarat Kuorum (2/3)</p>
                            <p class="mt-1 text-[26px] font-bold text-senat-orange">{{ $liveKuorum['ambang_kuorum'] ?? 23 }} <span class="text-xs font-normal">Orang</span></p>
                            <p class="text-[10px] text-senat-muted mt-1">Minimal kehadiran fisik/elektronik</p>
                        </div>
                        <div class="rounded-[10px] bg-[#eaf8f0] p-4 border border-[#bbf0d2]">
                            <p class="text-[12px] text-[#116a3c] font-semibold uppercase">Hadir Terverifikasi</p>
                            <p class="mt-1 text-[26px] font-bold text-[#116a3c]">{{ $liveKuorum['present_count'] ?? 0 }} <span class="text-xs font-normal">Orang</span></p>
                            <p class="text-[10px] text-emerald-700 mt-1">Dihitung dalam kuorum ({{ $liveKuorum['present_percentage'] ?? 0 }}%)</p>
                        </div>
                        <div class="rounded-[10px] bg-senat-badgeBg p-4 border border-senat-badgeLine">
                            <p class="text-[12px] text-senat-link font-semibold uppercase">Izin Resmi (Dispensasi)</p>
                            <p class="mt-1 text-[26px] font-bold text-senat-link">{{ $liveKuorum['izin_count'] ?? 0 }} <span class="text-xs font-normal">Orang</span></p>
                            <p class="text-[10px] text-senat-muted mt-1 italic">Tidak dihitung kuorum</p>
                        </div>
                    </div>

                    <div class="rounded-[10px] border border-senat-line bg-[#f8fafc] p-4 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-blue-700 text-sm font-bold">i</span>
                            <div>
                                <p class="text-xs font-bold text-senat-ink">Ketentuan Yuridis Kuorum Politeknik Negeri Semarang</p>
                                <p class="text-[11px] text-senat-muted">Peserta yang berstatus Izin Resmi tidak dihitung dalam kuorum. Hanya kehadiran yang sah secara hukum dapat mengesahkan putusan.</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <x-senat.button variant="secondary" size="sm" type="button" wire:click="saveAction('Sahkan Berita Acara Kuorum')">
                                Sahkan Berita Acara Kuorum
                            </x-senat.button>
                            <x-senat.button variant="primary" size="sm" type="button" wire:click="saveAction('Ekspor Daftar Hadir Kuorum (PDF)')">
                                Ekspor Daftar Hadir
                            </x-senat.button>
                        </div>
                    </div>
                </section>
{{-- 5. SCREEN: Risalah & Notulensi Sidang (Figma 13C) --}}
            @elseif ($active === 'Risalah & Notulensi Sidang')
                <div wire:key="screen-risalah-notulensi" class="space-y-6">
                    {{-- Header & Quick Action --}}
                    <section class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="min-w-0 max-w-[720px]">
                                <div class="flex items-center gap-2">
                                    <x-senat.badge state="info">Sistem Risalah &amp; Notulensi</x-senat.badge>
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> API Backend Aktif
                                    </span>
                                </div>
                                <h2 class="mt-2.5 text-[20px] font-bold leading-snug text-senat-ink">
                                    Risalah &amp; Notulensi Sidang Senat Akademik
                                </h2>
                                <p class="mt-1 text-[13px] leading-relaxed text-senat-body">
                                    Pencatatan resmi jalannya musyawarah sidang, perumusan putusan pleno/komisi, dan repositori risalah digital Sekretariat Senat Polines.
                                </p>
                            </div>
                            <div class="flex shrink-0 flex-wrap items-center gap-2.5">
                                <button type="button" wire:click="loadNotulensiData"
                                        class="inline-flex items-center gap-1.5 rounded-[10px] border border-senat-border bg-white px-3.5 py-2 text-[12.5px] font-medium text-senat-body transition hover:bg-senat-badgeBg hover:text-senat-ink">
                                    <x-senat.icon name="check" class="h-3.5 w-3.5" />
                                    <span>Segarkan Data</span>
                                </button>
                                <x-senat.button variant="primary" type="button" wire:click="openNotulensiForm">
                                    <span class="flex items-center gap-1.5">
                                        <x-senat.icon name="file-text" class="h-4 w-4" />
                                        <span>+ Catat Notulensi Baru</span>
                                    </span>
                                </x-senat.button>
                            </div>
                        </div>

                        {{-- Quick Stats Ribbon --}}
                        <div class="mt-5 grid grid-cols-2 gap-3 border-t border-senat-sidebarLine pt-4 sm:grid-cols-4">
                            <div class="rounded-lg bg-senat-badgeBg/60 p-3">
                                <span class="text-[11px] font-medium text-senat-muted uppercase">Total Risalah</span>
                                <p class="mt-0.5 text-[18px] font-bold text-senat-ink">{{ count($notulensiList) }}</p>
                            </div>
                            <div class="rounded-lg bg-emerald-50/60 p-3">
                                <span class="text-[11px] font-medium text-emerald-700 uppercase">Telah Disahkan</span>
                                <p class="mt-0.5 text-[18px] font-bold text-emerald-800">
                                    {{ collect($notulensiList)->filter(fn($n) => strtolower($n['status'] ?? '') === 'disahkan')->count() }}
                                </p>
                            </div>
                            <div class="rounded-lg bg-sky-50/60 p-3">
                                <span class="text-[11px] font-medium text-sky-700 uppercase">Tahap Review</span>
                                <p class="mt-0.5 text-[18px] font-bold text-sky-800">
                                    {{ collect($notulensiList)->filter(fn($n) => strtolower($n['status'] ?? '') === 'review')->count() }}
                                </p>
                            </div>
                            <div class="rounded-lg bg-amber-50/60 p-3">
                                <span class="text-[11px] font-medium text-amber-700 uppercase">Draf Notulensi</span>
                                <p class="mt-0.5 text-[18px] font-bold text-amber-800">
                                    {{ collect($notulensiList)->filter(fn($n) => in_array(strtolower($n['status'] ?? ''), ['draf', 'draft', '']))->count() }}
                                </p>
                            </div>
                        </div>
                    </section>

                    {{-- Collapsible Form: Input / Edit Notulensi --}}
                    @if ($isFormOpen)
                        <section class="rounded-[14px] border-2 border-senat-link/30 bg-white p-6 shadow-[0_4px_20px_0_rgba(13,26,41,0.08)]">
                            <div class="flex items-center justify-between border-b border-senat-sidebarLine pb-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-senat-link text-white">
                                        <x-senat.icon name="file-text" class="h-4 w-4" />
                                    </div>
                                    <div>
                                        <h3 class="text-[16px] font-bold text-senat-ink">
                                            {{ $selectedNotulensiId ? 'Edit Risalah / Notulensi Sidang (ID #' . $selectedNotulensiId . ')' : 'Pencatatan Notulensi Sidang Baru' }}
                                        </h3>
                                        <p class="text-[12px] text-senat-muted">Tersambung langsung ke database REST API Backend Senat Polines</p>
                                    </div>
                                </div>
                                <button type="button" wire:click="closeNotulensiForm" class="rounded-lg p-1.5 text-senat-muted hover:bg-senat-badgeBg hover:text-senat-ink">
                                    ✕
                                </button>
                            </div>

                            <form wire:submit.prevent="saveNotulensi" class="mt-5 space-y-4">
                                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                    <div class="sm:col-span-2 lg:col-span-1">
                                        <label class="block text-[12.5px] font-semibold text-senat-ink">Sidang / Rapat Terkait <span class="text-rose-500">*</span></label>
                                        <select wire:model="notulensiRapatId" required
                                                class="mt-1.5 w-full rounded-[10px] border border-senat-border bg-white px-3.5 py-2 text-[13px] text-senat-ink focus:border-senat-link focus:outline-none focus:ring-1 focus:ring-senat-link">
                                            <option value="">-- Pilih Agenda Sidang --</option>
                                            @foreach ($rapatList as $rpt)
                                                <option value="{{ $rpt['id_rapat'] }}">
                                                    {{ $rpt['nama_agenda'] }} ({{ $rpt['tanggal'] ? \Carbon\Carbon::parse($rpt['tanggal'])->format('d M Y') : '-' }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-[12.5px] font-semibold text-senat-ink">Notulis / Pencatat <span class="text-rose-500">*</span></label>
                                        <select wire:model="notulensiUserId" required
                                                class="mt-1.5 w-full rounded-[10px] border border-senat-border bg-white px-3.5 py-2 text-[13px] text-senat-ink focus:border-senat-link focus:outline-none focus:ring-1 focus:ring-senat-link">
                                            @foreach ($userList as $usr)
                                                <option value="{{ $usr['id_user'] }}">{{ $usr['nama'] }} ({{ $usr['nim_nip'] ?? 'User' }})</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-[12.5px] font-semibold text-senat-ink">Status Dokumen</label>
                                        <select wire:model="notulensiStatus"
                                                class="mt-1.5 w-full rounded-[10px] border border-senat-border bg-white px-3.5 py-2 text-[13px] text-senat-ink focus:border-senat-link focus:outline-none focus:ring-1 focus:ring-senat-link">
                                            <option value="draf">Draf (Penyusunan Awal)</option>
                                            <option value="review">Review (Verifikasi Sekretariat)</option>
                                            <option value="disahkan">Disahkan (Risalah Final)</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[12.5px] font-semibold text-senat-ink">Nama Berkas / Link Lampiran Risalah</label>
                                    <input type="text" wire:model="notulensiFile" placeholder="Contoh: risalah-sidang-pleno-okt2026.pdf atau link Google Drive dokumen"
                                           class="mt-1.5 w-full rounded-[10px] border border-senat-border bg-white px-3.5 py-2 text-[13px] text-senat-ink placeholder:text-senat-muted focus:border-senat-link focus:outline-none focus:ring-1 focus:ring-senat-link" />
                                </div>

                                <div>
                                    <label class="block text-[12.5px] font-semibold text-senat-ink">Jalannya Pembahasan &amp; Keputusan Sidang <span class="text-rose-500">*</span></label>
                                    <textarea rows="7" wire:model="notulensiIsi" required
                                              placeholder="Catat rangkuman pembukaan sidang, dinamika musyawarah pleno, poin tanggapan komisi, rumusan keputusan akhir, serta rencana tindak lanjut..."
                                              class="mt-1.5 w-full rounded-[10px] border border-senat-border bg-white p-3.5 text-[13px] leading-relaxed text-senat-ink placeholder:text-senat-muted focus:border-senat-link focus:outline-none focus:ring-1 focus:ring-senat-link"></textarea>
                                </div>

                                <div class="flex items-center justify-end gap-3 pt-2">
                                    <x-senat.button variant="secondary" type="button" wire:click="closeNotulensiForm">
                                        Batal
                                    </x-senat.button>
                                    <x-senat.button variant="primary" type="submit">
                                        <span class="flex items-center gap-1.5">
                                            <x-senat.icon name="check" class="h-4 w-4" />
                                            <span>{{ $selectedNotulensiId ? 'Perbarui Risalah' : 'Simpan Risalah ke Backend' }}</span>
                                        </span>
                                    </x-senat.button>
                                </div>
                            </form>
                        </section>
                    @endif

                    {{-- Filter & Search Controls --}}
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="inline-flex rounded-lg border border-senat-sidebarLine bg-white p-1 text-[12px] font-medium shadow-sm">
                            <button type="button" wire:click="$set('notulensiFilterStatus', 'all')"
                                    class="rounded-md px-3 py-1.5 transition {{ $notulensiFilterStatus === 'all' ? 'bg-senat-link text-white' : 'text-senat-body hover:text-senat-ink' }}">
                                Semua ({{ count($notulensiList) }})
                            </button>
                            <button type="button" wire:click="$set('notulensiFilterStatus', 'disahkan')"
                                    class="rounded-md px-3 py-1.5 transition {{ $notulensiFilterStatus === 'disahkan' ? 'bg-emerald-600 text-white' : 'text-senat-body hover:text-senat-ink' }}">
                                Disahkan
                            </button>
                            <button type="button" wire:click="$set('notulensiFilterStatus', 'review')"
                                    class="rounded-md px-3 py-1.5 transition {{ $notulensiFilterStatus === 'review' ? 'bg-sky-600 text-white' : 'text-senat-body hover:text-senat-ink' }}">
                                Review
                            </button>
                            <button type="button" wire:click="$set('notulensiFilterStatus', 'draf')"
                                    class="rounded-md px-3 py-1.5 transition {{ $notulensiFilterStatus === 'draf' ? 'bg-amber-600 text-white' : 'text-senat-body hover:text-senat-ink' }}">
                                Draf
                            </button>
                        </div>

                        <div class="w-full max-w-sm">
                            <input type="text" wire:model.live.debounce.300ms="notulensiSearch"
                                   placeholder="Cari agenda, isi pembahasan, tempat..."
                                   class="w-full rounded-[10px] border border-senat-border bg-white px-3.5 py-1.5 text-[12.5px] text-senat-ink placeholder:text-senat-muted focus:border-senat-link focus:outline-none focus:ring-1 focus:ring-senat-link" />
                        </div>
                    </div>

                    {{-- Main Data List --}}
                    <div class="space-y-4">
                        @forelse ($this->filteredNotulensi as $item)
                            <article wire:key="notulen-card-{{ $item['id_notulensi'] }}"
                                     class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_2px_8px_0_rgba(13,26,41,0.05)] transition hover:border-senat-link/40">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div class="min-w-0 max-w-[760px]">
                                        <div class="flex flex-wrap items-center gap-2">
                                            @php
                                                $st = strtolower($item['status'] ?? 'draf');
                                                $badgeState = match ($st) {
                                                    'disahkan' => 'success',
                                                    'review' => 'info',
                                                    default => 'warning',
                                                };
                                                $badgeLabel = match ($st) {
                                                    'disahkan' => 'Risalah Disahkan',
                                                    'review' => 'Dalam Review',
                                                    default => 'Draf Notulensi',
                                                };
                                            @endphp
                                            <x-senat.badge :state="$badgeState">{{ $badgeLabel }}</x-senat.badge>

                                            @if (!empty($item['rapat']['jenis_rapat']['nama_jenis']))
                                                <span class="rounded bg-senat-badgeBg px-2 py-0.5 text-[11px] font-semibold text-senat-ink">
                                                    {{ $item['rapat']['jenis_rapat']['nama_jenis'] }}
                                                </span>
                                            @endif

                                            <span class="text-[11.5px] text-senat-muted">
                                                ID #{{ $item['id_notulensi'] }} · {{ !empty($item['rapat']['tanggal']) ? \Carbon\Carbon::parse($item['rapat']['tanggal'])->isoFormat('D MMMM Y') : 'Sidang Senat' }}
                                            </span>
                                        </div>

                                        <h3 class="mt-2.5 text-[16.5px] font-bold leading-snug text-senat-ink">
                                            {{ $item['rapat']['nama_agenda'] ?? 'Agenda Sidang Senat' }}
                                        </h3>

                                        <div class="mt-1.5 flex flex-wrap items-center gap-x-4 gap-y-1 text-[12px] text-senat-muted">
                                            @if (!empty($item['rapat']['tempat']))
                                                <span class="flex items-center gap-1">
                                                    <span>📍</span> {{ $item['rapat']['tempat'] }}
                                                </span>
                                            @endif
                                            <span class="flex items-center gap-1">
                                                <span>👤</span> Notulis: <strong class="font-semibold text-senat-ink">{{ $item['user']['nama'] ?? 'Sekretariat' }}</strong>
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Actions Menu --}}
                                    <div class="flex items-center gap-2">
                                        <button type="button" wire:click="viewNotulensi({{ $item['id_notulensi'] }})"
                                                class="rounded-lg border border-senat-border bg-white px-3 py-1.5 text-[12px] font-medium text-senat-ink hover:bg-senat-badgeBg">
                                            Lihat Penuh
                                        </button>
                                        <button type="button" wire:click="editNotulensi({{ $item['id_notulensi'] }})"
                                                class="rounded-lg bg-senat-badgeBg px-3 py-1.5 text-[12px] font-medium text-senat-link hover:bg-senat-link hover:text-white">
                                            Edit
                                        </button>
                                        <button type="button"
                                                wire:click="deleteNotulensi({{ $item['id_notulensi'] }})"
                                                wire:confirm="Yakin ingin menghapus risalah ini dari database?"
                                                class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-[12px] font-medium text-rose-600 hover:bg-rose-100">
                                            Hapus
                                        </button>
                                    </div>
                                </div>

                                {{-- Excerpt / Ringkasan Jalannya Pembahasan --}}
                                <div class="mt-4 rounded-[10px] bg-slate-50/70 p-3.5 text-[12.5px] leading-relaxed text-senat-ink border border-slate-100">
                                    <p class="line-clamp-3 whitespace-pre-line">{{ $item['isi_notulensi'] }}</p>
                                </div>

                                {{-- Lampiran Dokumen & Quick Status Changer --}}
                                <div class="mt-3.5 flex flex-wrap items-center justify-between gap-3 border-t border-senat-sidebarLine/60 pt-3">
                                    <div class="flex items-center gap-2">
                                        @if (!empty($item['file_risalah']))
                                            <span class="inline-flex items-center gap-1.5 rounded-md bg-senat-link/10 px-2.5 py-1 text-[11.5px] font-medium text-senat-link">
                                                <x-senat.icon name="folder" class="h-3.5 w-3.5" />
                                                <span>{{ $item['file_risalah'] }}</span>
                                            </span>
                                        @else
                                            <span class="text-[11.5px] text-senat-muted italic">Tidak ada berkas lampiran</span>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-1.5 text-[11.5px]">
                                        <span class="text-senat-muted">Ubah status:</span>
                                        <button type="button" wire:click="setNotulensiStatus({{ $item['id_notulensi'] }}, 'draf')"
                                                class="rounded px-2 py-0.5 font-medium transition {{ $st === 'draf' ? 'bg-amber-100 text-amber-800 font-semibold' : 'text-senat-body hover:bg-slate-100' }}">
                                            Draf
                                        </button>
                                        <button type="button" wire:click="setNotulensiStatus({{ $item['id_notulensi'] }}, 'review')"
                                                class="rounded px-2 py-0.5 font-medium transition {{ $st === 'review' ? 'bg-sky-100 text-sky-800 font-semibold' : 'text-senat-body hover:bg-slate-100' }}">
                                            Review
                                        </button>
                                        <button type="button" wire:click="setNotulensiStatus({{ $item['id_notulensi'] }}, 'disahkan')"
                                                class="rounded px-2 py-0.5 font-medium transition {{ $st === 'disahkan' ? 'bg-emerald-100 text-emerald-800 font-semibold' : 'text-senat-body hover:bg-slate-100' }}">
                                            Sahkan
                                        </button>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="rounded-[14px] border border-dashed border-senat-border bg-white p-12 text-center">
                                <x-senat.icon name="file-text" class="mx-auto h-12 w-12 text-senat-muted" />
                                <h3 class="mt-3 text-[16px] font-bold text-senat-ink">Belum Ada Risalah / Notulensi Sidang</h3>
                                <p class="mt-1 text-[13px] text-senat-muted">
                                    {{ !empty($notulensiSearch) ? 'Tidak ditemukan risalah dengan kata kunci tersebut.' : 'Gunakan tombol "+ Catat Notulensi Baru" untuk menambahkan notulensi ke sistem.' }}
                                </p>
                                <div class="mt-4">
                                    <x-senat.button variant="secondary" type="button" wire:click="openNotulensiForm">
                                        Mulai Catat Notulensi
                                    </x-senat.button>
                                </div>
                            </div>
                        @endforelse
                    </div>

                    {{-- Modal View Detail Notulensi --}}
                    @if ($viewingNotulensiId)
                        @php
                            $viewItem = collect($notulensiList)->firstWhere('id_notulensi', $viewingNotulensiId);
                        @endphp
                        @if ($viewItem)
                            <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
                                <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl">
                                    <div class="flex items-start justify-between border-b border-senat-sidebarLine pb-4">
                                        <div>
                                            <span class="rounded bg-senat-badgeBg px-2 py-0.5 text-[11px] font-bold uppercase text-senat-link">
                                                Risalah Resmi Senat Polines
                                            </span>
                                            <h3 class="mt-2 text-[18px] font-bold text-senat-ink">
                                                {{ $viewItem['rapat']['nama_agenda'] ?? 'Risalah Sidang Senat' }}
                                            </h3>
                                            <p class="text-[12px] text-senat-muted">
                                                {{ !empty($viewItem['rapat']['tanggal']) ? \Carbon\Carbon::parse($viewItem['rapat']['tanggal'])->isoFormat('dddd, D MMMM Y') : '' }} · {{ $viewItem['rapat']['tempat'] ?? '' }}
                                            </p>
                                        </div>
                                        <button type="button" wire:click="viewNotulensi(null)" class="rounded-lg p-1 text-senat-muted hover:bg-slate-100 hover:text-senat-ink">
                                            ✕
                                        </button>
                                    </div>

                                    <div class="mt-4 space-y-4">
                                        <div class="grid grid-cols-2 gap-3 rounded-lg bg-senat-badgeBg/60 p-3 text-[12px]">
                                            <div>
                                                <span class="text-senat-muted block">Notulis / Pencatat:</span>
                                                <strong class="text-senat-ink font-semibold">{{ $viewItem['user']['nama'] ?? 'Sekretariat' }}</strong>
                                            </div>
                                            <div>
                                                <span class="text-senat-muted block">Status Dokumen:</span>
                                                <strong class="text-senat-ink font-semibold uppercase">{{ $viewItem['status'] ?? 'Draf' }}</strong>
                                            </div>
                                        </div>

                                        <div>
                                            <h4 class="text-[13px] font-bold text-senat-ink">Jalannya Musyawarah &amp; Catatan Risalah:</h4>
                                            <div class="mt-2 rounded-xl border border-senat-border bg-slate-50/50 p-4 text-[13px] leading-relaxed text-senat-ink whitespace-pre-line">
                                                {{ $viewItem['isi_notulensi'] }}
                                            </div>
                                        </div>

                                        @if (!empty($viewItem['file_risalah']))
                                            <div class="flex items-center justify-between rounded-xl border border-senat-link/20 bg-senat-link/5 p-3.5">
                                                <div class="flex items-center gap-2.5">
                                                    <x-senat.icon name="folder" class="h-5 w-5 text-senat-link" />
                                                    <div>
                                                        <span class="text-[12.5px] font-bold text-senat-ink block">{{ $viewItem['file_risalah'] }}</span>
                                                        <span class="text-[11px] text-senat-muted">Dokumen Risalah Pengesahan Pleno</span>
                                                    </div>
                                                </div>
                                                <span class="rounded bg-senat-link px-3 py-1 text-[11px] font-semibold text-white">
                                                    Tersimpan di Cloud
                                                </span>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="mt-6 flex justify-end gap-2 border-t border-senat-sidebarLine pt-4">
                                        <button type="button" wire:click="viewNotulensi(null)"
                                                class="rounded-lg border border-senat-border bg-white px-4 py-2 text-[12.5px] font-medium text-senat-ink hover:bg-slate-50">
                                            Tutup
                                        </button>
                                        <button type="button" wire:click="editNotulensi({{ $viewItem['id_notulensi'] }}); viewNotulensi(null);"
                                                class="rounded-lg bg-senat-link px-4 py-2 text-[12.5px] font-medium text-white hover:bg-senat-link/90">
                                            Edit Notulensi
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>

            {{-- 6. SCREEN: E-VOTING & PENGAMBILAN KEPUTUSAN (Figma 13E) --}}
            @elseif ($active === 'E-Voting & Pengambilan Keputusan')
                <div wire:key="screen-evoting-keputusan" class="grid gap-5 xl:grid-cols-[682px_minmax(0,1fr)]">
                    <section class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
                        @if ($votingStatus === 'dibuka')
                            <x-senat.badge state="success">Dibuka</x-senat.badge>
                        @elseif ($votingStatus === 'ditutup')
                            <x-senat.badge state="danger">Ditutup</x-senat.badge>
                        @else
                            <x-senat.badge state="neutral">Draft</x-senat.badge>
                        @endif

                        <h2 class="mt-4 text-[20px] font-bold leading-snug text-senat-ink">
                            {{ $votingTitle }}
                        </h2>
                        <p class="mt-2 text-[13px] leading-snug text-senat-body">
                            Sidang Pleno · {{ $votingMeetingCode }} · {{ $votingDate }}
                        </p>

                        <div class="mt-5 grid gap-3 sm:grid-cols-3">
                            <div class="rounded-[12px] bg-[#eaf8f0] px-4 py-3.5">
                                <p class="text-[14px] font-semibold text-senat-ink">Setuju</p>
                                <p class="mt-1.5 text-[12px] text-senat-body">
                                    {{ $votingStats['Setuju']['suara'] ?? 0 }} suara ({{ $votingStats['Setuju']['persentase'] ?? 0 }}%)
                                </p>
                            </div>
                            <div class="rounded-[12px] bg-[#fdecef] px-4 py-3.5">
                                <p class="text-[14px] font-semibold text-senat-ink">Tidak Setuju</p>
                                <p class="mt-1.5 text-[12px] text-senat-body">
                                    {{ $votingStats['Tidak Setuju']['suara'] ?? ($votingStats['Tolak']['suara'] ?? 0) }} suara ({{ $votingStats['Tidak Setuju']['persentase'] ?? ($votingStats['Tolak']['persentase'] ?? 0) }}%)
                                </p>
                            </div>
                            <div class="rounded-[12px] bg-senat-badgeBg px-4 py-3.5">
                                <p class="text-[14px] font-semibold text-senat-ink">Abstain</p>
                                <p class="mt-1.5 text-[12px] text-senat-body">
                                    {{ $votingStats['Abstain']['suara'] ?? 0 }} suara ({{ $votingStats['Abstain']['persentase'] ?? 0 }}%)
                                </p>
                            </div>
                        </div>

                        <div class="mt-6 flex flex-wrap items-center gap-3">
                            <x-senat.button variant="secondary" type="button" wire:click="simpanSesiVoting">Simpan Rekap</x-senat.button>
                            <x-senat.button variant="primary" type="button" wire:click="toggleVotingStatus">
                                <span>{{ $votingStatus === 'dibuka' ? 'Kunci & Tutup Voting' : 'Buka Voting' }}</span>
                            </x-senat.button>
                            <x-senat.button variant="secondary" type="button"
                                            onclick="document.getElementById('modal-voting-baru').style.display='flex'">Gelar Voting Baru</x-senat.button>
                            <x-senat.button variant="secondary" type="button" wire:click="refreshVotingData">Muat Ulang</x-senat.button>
                        </div>
                    </section>

                    <section class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
                        <h2 class="text-[18px] font-bold text-senat-ink">Rekap Suara Masuk</h2>
                        <p class="mt-1.5 text-[12px] text-senat-body">Total suara: {{ $votingStats['kuorum'] ?? (($votingStats['total_suara'] ?? 28) . ' / 34 anggota') }}</p>

                        <ul class="mt-5 space-y-3">
                            <li class="flex items-center gap-3 rounded-[10px] bg-senat-surface px-4 py-3.5">
                                <span class="h-[10px] w-[10px] shrink-0 rounded-[5px] bg-[#116a3c]"></span>
                                <span class="min-w-0 flex-1 text-[13px] font-medium text-senat-ink">Setuju</span>
                                <span class="text-[20px] font-bold leading-none text-[#116a3c]">{{ $votingStats['Setuju']['suara'] ?? 0 }}</span>
                            </li>
                            <li class="flex items-center gap-3 rounded-[10px] bg-senat-surface px-4 py-3.5">
                                <span class="h-[10px] w-[10px] shrink-0 rounded-[5px] bg-[#b4233b]"></span>
                                <span class="min-w-0 flex-1 text-[13px] font-medium text-senat-ink">Tidak Setuju</span>
                                <span class="text-[20px] font-bold leading-none text-[#b4233b]">{{ $votingStats['Tidak Setuju']['suara'] ?? ($votingStats['Tolak']['suara'] ?? 0) }}</span>
                            </li>
                            <li class="flex items-center gap-3 rounded-[10px] bg-senat-surface px-4 py-3.5">
                                <span class="h-[10px] w-[10px] shrink-0 rounded-[5px] bg-[#155ead]"></span>
                                <span class="min-w-0 flex-1 text-[13px] font-medium text-senat-ink">Abstain</span>
                                <span class="text-[20px] font-bold leading-none text-[#155ead]">{{ $votingStats['Abstain']['suara'] ?? 0 }}</span>
                            </li>
                        </ul>

                        <x-senat.button variant="secondary" size="sm" class="mt-5" type="button" wire:click="saveAction('Cetak Berita Acara Voting')">Cetak Berita Acara</x-senat.button>
                    </section>

                    {{-- Modal Form Gelar Voting Baru — handler native, tidak bergantung Livewire/Alpine --}}
                    <div id="modal-voting-baru" style="display:none"
                         class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/60 p-4"
                         onclick="if (event.target === this) this.style.display='none'">
                            <div class="w-full max-w-lg rounded-2xl border border-senat-sidebarLine bg-white p-6 shadow-2xl space-y-4">
                                <div class="flex items-center justify-between border-b border-senat-sidebarLine pb-3">
                                    <div>
                                        <h3 class="text-[17px] font-bold text-senat-ink">Gelar Sesi Voting Baru</h3>
                                        <p class="text-[12px] text-senat-body">Inisialisasi pemungutan suara sidang untuk anggota Senat</p>
                                    </div>
                                    <button type="button" onclick="document.getElementById('modal-voting-baru').style.display='none'" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition" aria-label="Tutup">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-[12px] font-semibold text-senat-ink mb-1">Agenda Sidang / Rapat</label>
                                        <select wire:model="newVotingMeetingId" class="w-full rounded-[10px] border border-senat-sidebarLine bg-white px-3.5 py-2.5 text-[13px] text-senat-ink focus:border-senat-link focus:outline-none">
                                            @forelse ($availableMeetings as $meeting)
                                                <option value="{{ $meeting['id_rapat'] ?? 1 }}">{{ $meeting['nama_agenda'] ?? 'Sidang Pleno Senat' }}</option>
                                            @empty
                                                <option value="1">Sidang Pleno Penetapan Standar Akademik &amp; Kurikulum</option>
                                            @endforelse
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-[12px] font-semibold text-senat-ink mb-1">Pokok Putusan / Judul Voting <span class="text-rose-500">*</span></label>
                                        <input type="text" id="input-voting-judul" wire:model="newVotingTitle" placeholder="Misal: Persetujuan Pengesahan Pedoman Akademik 2026/2027"
                                               class="w-full rounded-[10px] border border-senat-sidebarLine bg-white px-3.5 py-2.5 text-[13px] text-senat-ink placeholder-slate-400 focus:border-senat-link focus:outline-none">
                                        @error('newVotingTitle') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-[12px] font-semibold text-senat-ink mb-1">Penjelasan Draf / Keterangan Putusan</label>
                                        <textarea wire:model="newVotingDescription" rows="3" placeholder="Uraikan materi atau pokok keputusan yang dimusyawarahkan..."
                                                  class="w-full rounded-[10px] border border-senat-sidebarLine bg-white px-3.5 py-2.5 text-[13px] text-senat-ink placeholder-slate-400 focus:border-senat-link focus:outline-none"></textarea>
                                    </div>

                                    <div class="rounded-[10px] bg-senat-surface p-3.5 text-[12px] text-senat-body">
                                        <p class="font-semibold text-senat-ink">Ketentuan Bilik Suara:</p>
                                        <p class="mt-1 text-[11.5px] leading-relaxed text-senat-body">Sesi voting akan otomatis dibuat dengan status <strong>DIBUKA</strong> dan menyediakan 3 opsi standar persidangan: <strong>Setuju</strong>, <strong>Tidak Setuju</strong>, dan <strong>Abstain</strong>.</p>
                                    </div>

                                    <div class="flex items-center justify-end gap-3 pt-2 border-t border-senat-sidebarLine">
                                        @if ($flashMessage)
                                            <p class="mr-auto text-[11.5px] font-medium text-rose-700">{{ $flashMessage }}</p>
                                        @endif
                                        <button type="button" onclick="document.getElementById('modal-voting-baru').style.display='none'" class="inline-flex items-center justify-center rounded-md border border-senat-border bg-[#f8fafc] px-4 py-2 text-[12px] font-semibold text-senat-primary hover:border-senat-primary transition">
                                            Batal
                                        </button>
                                        <button type="button"
                                                onclick="var t=document.getElementById('input-voting-judul'); if(!t.value.trim()){t.focus();return;} document.getElementById('modal-voting-baru').style.display='none';"
                                                wire:click="deployNewVoting" class="inline-flex items-center justify-center rounded-md bg-senat-primary px-5 py-2 text-[12px] font-bold text-white transition hover:bg-senat-primaryHover">
                                            Buka Sesi Voting
                                        </button>
                                    </div>
                                </div>
                            </div>
                    </div>
                </div>

            {{-- 7. SCREEN: Pengaturan Informasi & Hero --}}
            @elseif ($active === 'Pengaturan Informasi & Hero')
                <div wire:key="screen-pengaturan-hero" class="rounded-xl border border-slate-200/80 bg-white p-6 shadow-xs">
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
                <div wire:key="screen-profil-struktur" class="space-y-6">
                    
                    {{-- Form Visi & Misi --}}
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
                    {{-- Form Pimpinan Kolektif Senat (Tersambung API Backend) --}}
                    <div class="rounded-xl border border-slate-200/80 bg-white p-6 shadow-xs">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                            <div>
                                <h2 class="text-[16px] font-bold text-slate-900">Pimpinan Kolektif Senat (Tingkat I)</h2>
                                <p class="text-[12px] text-slate-500">Kelola nama dan NIP Ketua &amp; Sekretaris Senat langsung ke database Backend</p>
                            </div>
                            <span class="rounded-md bg-blue-50 px-2.5 py-1 text-[11px] font-mono font-bold text-senat-primary">Tersambung API Backend</span>
                        </div>
                        <div class="grid gap-5 md:grid-cols-2">
                            {{-- Form Ketua Senat --}}
                            <div class="rounded-lg border border-slate-200 bg-slate-50/60 p-4">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Jabatan: Ketua Senat</span>
                                <div class="mt-3 space-y-3">
                                    <div>
                                        <label class="text-[11.5px] font-semibold text-slate-700">Nama Lengkap &amp; Gelar</label>
                                        <input type="text" wire:model="ketuaNama" placeholder="Contoh: Dr. Sukamto, S.Kom., M.T."
                                               class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-[12.5px] font-semibold text-slate-900 focus:border-senat-primary focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="text-[11.5px] font-semibold text-slate-700">NIP / NIM</label>
                                        <input type="text" wire:model="ketuaNip" placeholder="Contoh: 196803121993031002"
                                               class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-[12px] font-mono text-slate-900 focus:border-senat-primary focus:outline-none">
                                    </div>
                                </div>
                            </div>
                            {{-- Form Sekretaris Senat --}}
                            <div class="rounded-lg border border-slate-200 bg-slate-50/60 p-4">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Jabatan: Sekretaris Senat</span>
                                <div class="mt-3 space-y-3">
                                    <div>
                                        <label class="text-[11.5px] font-semibold text-slate-700">Nama Lengkap &amp; Gelar</label>
                                        <input type="text" wire:model="sekretarisNama" placeholder="Contoh: Andi Setyawan, S.E., M.M."
                                               class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-[12.5px] font-semibold text-slate-900 focus:border-senat-primary focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="text-[11.5px] font-semibold text-slate-700">NIP / NIM</label>
                                        <input type="text" wire:model="sekretarisNip" placeholder="Contoh: 197405191999031001"
                                               class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-[12px] font-mono text-slate-900 focus:border-senat-primary focus:outline-none">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 flex justify-end">
                            <button type="button" wire:click="updatePimpinan"
                                    class="senat-tap inline-flex h-9 items-center gap-1.5 rounded-lg bg-senat-primary px-4 text-[12px] font-semibold text-white transition hover:bg-senat-primaryHover">
                                <x-senat.icon name="check" class="h-3.5 w-3.5" />
                                <span>Simpan Perubahan Pimpinan ke Backend</span>
                            </button>
                        </div>
                    </div>
                </div>

            {{-- 9. SCREEN: Direktori Komisi & Anggota --}}
            @elseif ($active === 'Direktori Komisi & Anggota')
                <div wire:key="screen-direktori-komisi" class="rounded-xl border border-slate-200/80 bg-white p-6 shadow-xs">
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
                <div wire:key="screen-berita-siaran-pers" class="grid gap-5 xl:grid-cols-[730px_minmax(0,1fr)]">
                    <section class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
                        <div class="flex items-center justify-between border-b border-senat-sidebarLine pb-4">
                            <div>
                                <h2 class="text-[18px] font-bold text-senat-ink">Publikasi Warta & Berita Senat</h2>
                                <p class="text-[12px] text-senat-body">Kanal siaran pers dan transparansi kegiatan persidangan</p>
                            </div>
                            <x-senat.button variant="primary" size="sm" type="button" wire:click="saveAction('Tulis Berita Baru')">+ Tulis Berita</x-senat.button>
                        </div>
                        <ul class="mt-4 divide-y divide-senat-sidebarLine">
                            @foreach ($posts as $post)
                                <li class="py-4 first:pt-1">
                                    <div class="flex items-center justify-between gap-3">
                                        <x-senat.badge state="neutral">{{ $post['category'] }}</x-senat.badge>
                                        <span class="text-[11px] text-senat-muted">{{ $post['date'] }}</span>
                                    </div>
                                    <h3 class="mt-2 text-[15px] font-semibold text-senat-ink">{{ $post['title'] }}</h3>
                                    <p class="mt-1 text-[12.5px] leading-relaxed text-senat-body">{{ $post['excerpt'] }}</p>
                                    <div class="mt-3 flex items-center justify-end gap-2">
                                        <x-senat.button variant="secondary" size="sm" type="button" wire:click="saveAction('Edit Berita {{ $post['id'] }}')">Edit</x-senat.button>
                                        <x-senat.button variant="primary" size="sm" type="button" wire:click="saveAction('Publikasikan {{ $post['id'] }}')">Publikasikan</x-senat.button>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </section>

                    <section class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
                        <h2 class="text-[18px] font-bold text-senat-ink">Antrean Unggah Media</h2>
                        <p class="mt-1 text-[12px] text-senat-body">Format PDF atau gambar dokumentasi maksimal 10MB</p>
                        
                        <div class="mt-5 rounded-[12px] border-2 border-dashed border-senat-border bg-senat-surface p-6 text-center">
                            <span class="text-senat-link"><x-senat.icon name="file-text" class="mx-auto h-8 w-8 text-senat-link" /></span>
                            <p class="mt-2 text-[13px] font-medium text-senat-ink">Tarik berkas dokumentasi ke sini</p>
                            <p class="mt-1 text-[11px] text-senat-muted">Otomatis dioptimasi untuk portal publik</p>
                            <x-senat.button variant="secondary" size="sm" class="mt-4" type="button" wire:click="saveAction('Pilih Berkas')">Pilih Berkas</x-senat.button>
                        </div>
                    </section>
                </div>

            {{-- 11. SCREEN: Katalog Regulasi & Produk Hukum (Backend API) --}}
            @elseif ($active === 'Katalog Regulasi & Produk Hukum')
                @php $regStats = $this->regulasiStatistik(); @endphp
                <div wire:key="screen-regulasi-hukum" class="space-y-6">
                    {{-- Header + statistik --}}
                    <section class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="min-w-0 max-w-[720px]">
                                <div class="flex items-center gap-2">
                                    <x-senat.badge state="info">Katalog Produk Hukum</x-senat.badge>
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Sinkron Database Backend
                                    </span>
                                </div>
                                <h2 class="mt-2.5 text-[20px] font-bold leading-snug text-senat-ink">
                                    Katalog Regulasi &amp; Produk Hukum Senat
                                </h2>
                                <p class="mt-1 text-[13px] leading-relaxed text-senat-body">
                                    Repositori digital Peraturan Senat, Peraturan Direktur, SK Pertimbangan, rancangan peraturan, dan standar akademik Polines.
                                </p>
                            </div>
                            <div class="flex shrink-0 flex-wrap items-center gap-2.5">
                                <button type="button" wire:click="loadRegulasiData"
                                        class="inline-flex items-center gap-1.5 rounded-[10px] border border-senat-border bg-white px-3.5 py-2 text-[12.5px] font-medium text-senat-body transition hover:bg-senat-badgeBg hover:text-senat-ink">
                                    <x-senat.icon name="check" class="h-3.5 w-3.5" />
                                    <span>Segarkan</span>
                                </button>
                                <x-senat.button variant="primary" type="button" wire:click="openRegulasiForm">
                                    <span class="flex items-center gap-1.5">
                                        <x-senat.icon name="file-text" class="h-4 w-4" />
                                        <span>+ Tambah Produk Hukum</span>
                                    </span>
                                </x-senat.button>
                            </div>
                        </div>

                        <div class="mt-5 grid grid-cols-2 gap-3 border-t border-senat-sidebarLine pt-4 sm:grid-cols-4">
                            <div class="rounded-lg bg-senat-badgeBg/60 p-3">
                                <span class="text-[11px] font-medium uppercase text-senat-muted">Total Dokumen</span>
                                <p class="mt-0.5 text-[18px] font-bold text-senat-ink">{{ $regStats['total'] }}</p>
                            </div>
                            <div class="rounded-lg bg-emerald-50/60 p-3">
                                <span class="text-[11px] font-medium uppercase text-emerald-700">Disahkan</span>
                                <p class="mt-0.5 text-[18px] font-bold text-emerald-800">{{ $regStats['disahkan'] }}</p>
                            </div>
                            <div class="rounded-lg bg-amber-50/60 p-3">
                                <span class="text-[11px] font-medium uppercase text-amber-700">Dalam Proses</span>
                                <p class="mt-0.5 text-[18px] font-bold text-amber-800">{{ $regStats['proses'] }}</p>
                            </div>
                            <div class="rounded-lg bg-sky-50/60 p-3">
                                <span class="text-[11px] font-medium uppercase text-sky-700">Kategori</span>
                                <p class="mt-0.5 text-[18px] font-bold text-sky-800">{{ $regStats['kategori'] }}</p>
                            </div>
                        </div>
                    </section>

                    {{-- Form tambah / edit --}}
                    @if ($isRegulasiFormOpen)
                        <section class="rounded-[14px] border-2 border-senat-link/30 bg-white p-6 shadow-[0_4px_20px_0_rgba(13,26,41,0.08)]">
                            <div class="flex items-center justify-between border-b border-senat-sidebarLine pb-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-senat-link text-white">
                                        <x-senat.icon name="file-text" class="h-4 w-4" />
                                    </div>
                                    <div>
                                        <h3 class="text-[16px] font-bold text-senat-ink">
                                            {{ $regulasiId ? 'Edit Produk Hukum (ID #' . $regulasiId . ')' : 'Tambah Produk Hukum Baru' }}
                                        </h3>
                                        <p class="text-[12px] text-senat-muted">Data disimpan langsung ke tabel <code>peraturans</code> database Backend</p>
                                    </div>
                                </div>
                                <button type="button" wire:click="closeRegulasiForm" class="rounded-lg p-1.5 text-senat-muted hover:bg-senat-badgeBg hover:text-senat-ink">✕</button>
                            </div>

                            <form wire:submit.prevent="saveRegulasi" class="mt-5 space-y-4">
                                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                    <div>
                                        <label class="block text-[12.5px] font-semibold text-senat-ink">Nomor Dokumen <span class="text-rose-500">*</span></label>
                                        <input type="text" wire:model="regulasiNomor" required placeholder="PER-SA/01/POLINES/2026"
                                               class="mt-1.5 w-full rounded-[10px] border border-senat-border bg-white px-3.5 py-2 font-mono text-[13px] text-senat-ink placeholder:text-senat-muted focus:border-senat-link focus:outline-none focus:ring-1 focus:ring-senat-link" />
                                    </div>
                                    <div>
                                        <label class="block text-[12.5px] font-semibold text-senat-ink">Kategori <span class="text-rose-500">*</span></label>
                                        <select wire:model="regulasiKategori"
                                                class="mt-1.5 w-full rounded-[10px] border border-senat-border bg-white px-3.5 py-2 text-[13px] text-senat-ink focus:border-senat-link focus:outline-none focus:ring-1 focus:ring-senat-link">
                                            @foreach (\App\Livewire\AdminPreview::REGULASI_KATEGORI as $kat)
                                                <option value="{{ $kat }}">{{ $kat }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[12.5px] font-semibold text-senat-ink">Tahap Pembentukan <span class="text-rose-500">*</span></label>
                                        <select wire:model="regulasiTahap"
                                                class="mt-1.5 w-full rounded-[10px] border border-senat-border bg-white px-3.5 py-2 text-[13px] text-senat-ink focus:border-senat-link focus:outline-none focus:ring-1 focus:ring-senat-link">
                                            @foreach (\App\Livewire\AdminPreview::REGULASI_TAHAP as $th)
                                                <option value="{{ $th }}">{{ $th }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[12.5px] font-semibold text-senat-ink">Periode Senat <span class="text-rose-500">*</span></label>
                                        <select wire:model="regulasiPeriodeId" required
                                                class="mt-1.5 w-full rounded-[10px] border border-senat-border bg-white px-3.5 py-2 text-[13px] text-senat-ink focus:border-senat-link focus:outline-none focus:ring-1 focus:ring-senat-link">
                                            @foreach ($periodeList as $per)
                                                <option value="{{ $per['id_periode_senat'] }}">{{ $per['nama_periode'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[12.5px] font-semibold text-senat-ink">Tanggal Terbit <span class="text-rose-500">*</span></label>
                                        <input type="date" wire:model="regulasiTanggal" required
                                               class="mt-1.5 w-full rounded-[10px] border border-senat-border bg-white px-3.5 py-2 text-[13px] text-senat-ink focus:border-senat-link focus:outline-none focus:ring-1 focus:ring-senat-link" />
                                    </div>
                                    <div>
                                        <label class="block text-[12.5px] font-semibold text-senat-ink">Berkas Dokumen</label>
                                        <input type="text" wire:model="regulasiFile" placeholder="peraturan-senat-01-2026.pdf"
                                               class="mt-1.5 w-full rounded-[10px] border border-senat-border bg-white px-3.5 py-2 text-[13px] text-senat-ink placeholder:text-senat-muted focus:border-senat-link focus:outline-none focus:ring-1 focus:ring-senat-link" />
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[12.5px] font-semibold text-senat-ink">Judul Regulasi <span class="text-rose-500">*</span></label>
                                    <textarea rows="2" wire:model="regulasiJudul" required
                                              placeholder="Tulis judul lengkap produk hukum..."
                                              class="mt-1.5 w-full rounded-[10px] border border-senat-border bg-white p-3.5 text-[13px] leading-relaxed text-senat-ink placeholder:text-senat-muted focus:border-senat-link focus:outline-none focus:ring-1 focus:ring-senat-link"></textarea>
                                </div>

                                <div class="flex items-center justify-end gap-3 pt-2">
                                    <x-senat.button variant="secondary" type="button" wire:click="closeRegulasiForm">Batal</x-senat.button>
                                    <x-senat.button variant="primary" type="submit">
                                        {{ $regulasiId ? 'Perbarui Produk Hukum' : 'Simpan ke Backend' }}
                                    </x-senat.button>
                                </div>
                            </form>
                        </section>
                    @endif

                    {{-- Filter & pencarian --}}
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex flex-wrap items-center gap-2">
                            <div class="inline-flex rounded-lg border border-senat-sidebarLine bg-white p-1 text-[12px] font-medium shadow-sm">
                                <button type="button" wire:click="$set('regulasiFilterTahap', 'all')"
                                        class="rounded-md px-3 py-1.5 transition {{ $regulasiFilterTahap === 'all' ? 'bg-senat-link text-white' : 'text-senat-body hover:text-senat-ink' }}">
                                    Semua
                                </button>
                                @foreach (\App\Livewire\AdminPreview::REGULASI_TAHAP as $th)
                                    <button type="button" wire:click="$set('regulasiFilterTahap', '{{ $th }}')"
                                            class="rounded-md px-3 py-1.5 transition {{ strtolower($regulasiFilterTahap) === strtolower($th) ? 'bg-senat-link text-white' : 'text-senat-body hover:text-senat-ink' }}">
                                        {{ $th }}
                                    </button>
                                @endforeach
                            </div>

                            <select wire:model.live="regulasiFilterKategori"
                                    class="rounded-[10px] border border-senat-border bg-white px-3 py-1.5 text-[12.5px] text-senat-ink focus:border-senat-link focus:outline-none">
                                <option value="all">Semua Kategori</option>
                                @foreach (\App\Livewire\AdminPreview::REGULASI_KATEGORI as $kat)
                                    <option value="{{ $kat }}">{{ $kat }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="w-full max-w-xs">
                            <input type="text" wire:model.live.debounce.300ms="regulasiSearch"
                                   placeholder="Cari nomor, judul, kategori..."
                                   class="w-full rounded-[10px] border border-senat-border bg-white px-3.5 py-1.5 text-[12.5px] text-senat-ink placeholder:text-senat-muted focus:border-senat-link focus:outline-none focus:ring-1 focus:ring-senat-link" />
                        </div>
                    </div>

                    {{-- Tabel katalog --}}
                    <section class="overflow-hidden rounded-[14px] border border-senat-sidebarLine bg-white shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-[13px]">
                                <thead class="border-b border-senat-sidebarLine bg-senat-surface text-[11px] font-bold uppercase text-senat-muted">
                                    <tr>
                                        <th class="px-4 py-3">Nomor Dokumen</th>
                                        <th class="px-4 py-3">Judul Regulasi</th>
                                        <th class="px-4 py-3">Kategori</th>
                                        <th class="px-4 py-3">Tahap</th>
                                        <th class="px-4 py-3">Terbit</th>
                                        <th class="px-4 py-3 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-senat-sidebarLine">
                                    @forelse ($this->filteredRegulasi as $reg)
                                        @php
                                            $tahap = strtolower($reg['tahap_pembentukan'] ?? 'draf');
                                            $state = match ($tahap) {
                                                'disahkan' => 'success',
                                                'ditolak' => 'danger',
                                                'harmonisasi', 'pembahasan' => 'info',
                                                default => 'warning',
                                            };
                                        @endphp
                                        <tr wire:key="reg-row-{{ $reg['id_peraturan'] }}" class="hover:bg-senat-surface/60">
                                            <td class="px-4 py-3.5">
                                                <button type="button" wire:click="viewRegulasi({{ $reg['id_peraturan'] }})"
                                                        class="font-mono text-[12.5px] font-semibold text-senat-link hover:underline">
                                                    {{ $reg['nomor_sk'] }}
                                                </button>
                                            </td>
                                            <td class="max-w-[420px] px-4 py-3.5">
                                                <p class="font-medium leading-snug text-senat-ink">{{ $reg['judul'] }}</p>
                                                <p class="mt-0.5 text-[11px] text-senat-muted">
                                                    Periode {{ $reg['periode_senat']['nama_periode'] ?? '-' }}
                                                    @if (!empty($reg['file_dokumen']))
                                                        · 📎 {{ $reg['file_dokumen'] }}
                                                    @endif
                                                </p>
                                            </td>
                                            <td class="px-4 py-3.5 text-senat-body">{{ $reg['kategori'] }}</td>
                                            <td class="px-4 py-3.5">
                                                <x-senat.badge :state="$state">{{ $reg['tahap_pembentukan'] }}</x-senat.badge>
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3.5 text-[12px] text-senat-muted">
                                                {{ !empty($reg['tanggal_terbit']) ? \Carbon\Carbon::parse($reg['tanggal_terbit'])->format('d M Y') : '-' }}
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3.5 text-right">
                                                <div class="inline-flex items-center gap-1.5">
                                                    <button type="button" wire:click="viewRegulasi({{ $reg['id_peraturan'] }})"
                                                            class="rounded-lg border border-senat-border bg-white px-2.5 py-1 text-[11.5px] font-medium text-senat-ink hover:bg-senat-badgeBg">
                                                        Lihat
                                                    </button>
                                                    <button type="button" wire:click="editRegulasi({{ $reg['id_peraturan'] }})"
                                                            class="rounded-lg bg-senat-badgeBg px-2.5 py-1 text-[11.5px] font-medium text-senat-link hover:bg-senat-link hover:text-white">
                                                        Edit
                                                    </button>
                                                    <button type="button"
                                                            wire:click="deleteRegulasi({{ $reg['id_peraturan'] }})"
                                                            wire:confirm="Yakin hapus produk hukum ini dari database?"
                                                            class="rounded-lg border border-rose-200 bg-rose-50 px-2.5 py-1 text-[11.5px] font-medium text-rose-600 hover:bg-rose-100">
                                                        Hapus
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-4 py-12 text-center">
                                                <x-senat.icon name="file-text" class="mx-auto h-10 w-10 text-senat-muted" />
                                                <p class="mt-2 text-[14px] font-semibold text-senat-ink">Tidak ada produk hukum ditemukan</p>
                                                <p class="mt-1 text-[12.5px] text-senat-muted">
                                                    {{ $regulasiSearch ? 'Coba kata kunci lain atau ubah filter.' : 'Tambahkan produk hukum pertama lewat tombol di atas.' }}
                                                </p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="flex items-center justify-between border-t border-senat-sidebarLine bg-senat-surface/40 px-4 py-2.5 text-[11.5px] text-senat-muted">
                            <span>Menampilkan {{ count($this->filteredRegulasi) }} dari {{ count($regulasiList) }} dokumen</span>
                            <span>Sumber: <code class="font-mono">GET /api/peraturan</code></span>
                        </div>
                    </section>

                    {{-- Modal detail --}}
                    @if ($viewingRegulasiId)
                        @php $regView = collect($regulasiList)->firstWhere('id_peraturan', $viewingRegulasiId); @endphp
                        @if ($regView)
                            <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
                                <div class="max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl">
                                    <div class="flex items-start justify-between border-b border-senat-sidebarLine pb-4">
                                        <div>
                                            <span class="rounded bg-senat-badgeBg px-2 py-0.5 text-[11px] font-bold uppercase text-senat-link">
                                                {{ $regView['kategori'] }}
                                            </span>
                                            <h3 class="mt-2 text-[17px] font-bold leading-snug text-senat-ink">{{ $regView['judul'] }}</h3>
                                            <p class="mt-1 font-mono text-[12px] text-senat-link">{{ $regView['nomor_sk'] }}</p>
                                        </div>
                                        <button type="button" wire:click="viewRegulasi(null)" class="rounded-lg p-1 text-senat-muted hover:bg-slate-100">✕</button>
                                    </div>

                                    <dl class="mt-4 grid grid-cols-2 gap-3 text-[12.5px]">
                                        <div class="rounded-lg bg-senat-badgeBg/60 p-3">
                                            <dt class="text-senat-muted">Tahap Pembentukan</dt>
                                            <dd class="mt-0.5 font-bold text-senat-ink">{{ $regView['tahap_pembentukan'] }}</dd>
                                        </div>
                                        <div class="rounded-lg bg-senat-badgeBg/60 p-3">
                                            <dt class="text-senat-muted">Tanggal Terbit</dt>
                                            <dd class="mt-0.5 font-bold text-senat-ink">
                                                {{ !empty($regView['tanggal_terbit']) ? \Carbon\Carbon::parse($regView['tanggal_terbit'])->isoFormat('D MMMM Y') : '-' }}
                                            </dd>
                                        </div>
                                        <div class="rounded-lg bg-senat-badgeBg/60 p-3">
                                            <dt class="text-senat-muted">Periode Senat</dt>
                                            <dd class="mt-0.5 font-bold text-senat-ink">{{ $regView['periode_senat']['nama_periode'] ?? '-' }}</dd>
                                        </div>
                                        <div class="rounded-lg bg-senat-badgeBg/60 p-3">
                                            <dt class="text-senat-muted">Berkas Dokumen</dt>
                                            <dd class="mt-0.5 truncate font-medium text-senat-ink">{{ $regView['file_dokumen'] ?? 'Belum diunggah' }}</dd>
                                        </div>
                                    </dl>

                                    @if (!empty($regView['file_dokumen']))
                                        <div class="mt-4 flex items-center justify-between rounded-xl border border-senat-link/20 bg-senat-link/5 p-3.5">
                                            <div class="flex items-center gap-2.5">
                                                <x-senat.icon name="folder" class="h-5 w-5 text-senat-link" />
                                                <div>
                                                    <span class="block text-[12.5px] font-bold text-senat-ink">{{ $regView['file_dokumen'] }}</span>
                                                    <span class="text-[11px] text-senat-muted">Dokumen resmi produk hukum Senat</span>
                                                </div>
                                            </div>
                                            <span class="rounded bg-senat-link px-3 py-1 text-[11px] font-semibold text-white">Tersimpan</span>
                                        </div>
                                    @endif

                                    <div class="mt-5 flex flex-wrap items-center justify-between gap-2 border-t border-senat-sidebarLine pt-4">
                                        <div class="flex items-center gap-1.5 text-[11.5px]">
                                            <span class="text-senat-muted">Ubah tahap:</span>
                                            @foreach (\App\Livewire\AdminPreview::REGULASI_TAHAP as $th)
                                                <button type="button" wire:click="setRegulasiTahap({{ $regView['id_peraturan'] }}, '{{ $th }}')"
                                                        class="rounded px-2 py-0.5 font-medium transition {{ strtolower($regView['tahap_pembentukan'] ?? '') === strtolower($th) ? 'bg-senat-link text-white' : 'text-senat-body hover:bg-slate-100' }}">
                                                    {{ $th }}
                                                </button>
                                            @endforeach
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button type="button" wire:click="viewRegulasi(null)"
                                                    class="rounded-lg border border-senat-border bg-white px-3.5 py-1.5 text-[12px] font-medium text-senat-ink hover:bg-slate-50">
                                                Tutup
                                            </button>
                                            <button type="button" wire:click="editRegulasi({{ $regView['id_peraturan'] }}); viewRegulasi(null);"
                                                    class="rounded-lg bg-senat-link px-3.5 py-1.5 text-[12px] font-medium text-white hover:bg-senat-link/90">
                                                Edit Dokumen
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>

            {{-- 12. SCREEN: Repositori Arsip & Dokumen --}}
            @elseif ($active === 'Repositori Arsip & Dokumen')
                <section wire:key="screen-arsip-dokumen" class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
                    <div class="flex items-center justify-between border-b border-senat-sidebarLine pb-4">
                        <div>
                            <h2 class="text-[18px] font-bold text-senat-ink">Repositori Dokumen & Laporan Tahunan</h2>
                            <p class="text-[12px] text-senat-body">Berkas LPJ, OTK, dan arsip kelembagaan Senat Polines</p>
                        </div>
                        <x-senat.button variant="primary" size="sm" type="button" wire:click="saveAction('Upload Arsip')">+ Unggah Berkas</x-senat.button>
                    </div>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($archives as $arc)
                            <div class="rounded-[12px] border border-senat-sidebarLine bg-senat-surface p-4 flex flex-col justify-between">
                                <div>
                                    <x-senat.badge state="neutral">{{ $arc['category'] }}</x-senat.badge>
                                    <h3 class="mt-2 text-[14px] font-bold text-senat-ink">{{ $arc['title'] }}</h3>
                                    <p class="mt-1 text-[11.5px] text-senat-muted">{{ $arc['year'] }} · {{ $arc['file_size'] }}</p>
                                </div>
                                <div class="mt-4 flex items-center justify-end border-t border-senat-sidebarLine pt-3">
                                    <x-senat.button variant="secondary" size="sm" type="button" wire:click="saveAction('Unduh Berkas {{ $arc['id'] }}')">Unduh Berkas</x-senat.button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

            {{-- 13. SCREEN: Pusat Aspirasi & Tindak Lanjut --}}
            @elseif ($active === 'Pusat Aspirasi & Tindak Lanjut')
                <section wire:key="screen-pusat-aspirasi" class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
                    <div class="flex items-center justify-between border-b border-senat-sidebarLine pb-4">
                        <div>
                            <h2 class="text-[18px] font-bold text-senat-ink">Manajemen Aspirasi Sivitas Akademika</h2>
                            <p class="text-[12px] text-senat-body">Monitoring tiket aspirasi mahasiswa, dosen, dan tendik</p>
                        </div>
                        <x-senat.button variant="secondary" size="sm" type="button" wire:click="saveAction('Ekspor Rekap Aspirasi')">Ekspor Rekap</x-senat.button>
                    </div>
                    <div class="mt-4 space-y-3">
                        @foreach ($aspirations as $asp)
                            <div class="rounded-[12px] border border-senat-sidebarLine bg-senat-surface p-4">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-[6px] bg-senat-badgeBg px-2 py-0.5 text-[11px] font-mono font-bold text-senat-ink">
                                            {{ $asp['ticket_code'] }}
                                        </span>
                                        <span class="text-[12px] font-semibold text-senat-ink">{{ $asp['sender_category'] }} ({{ $asp['jurusan'] }})</span>
                                    </div>
                                    <x-senat.badge :state="$this->chipState($asp['status'])">{{ $this->chipLabel($asp['status']) }}</x-senat.badge>
                                </div>
                                <h3 class="mt-2 text-[14px] font-bold text-senat-ink">{{ $asp['subject'] }}</h3>
                                <p class="mt-1 text-[12.5px] text-senat-body">{{ $asp['description'] }}</p>
                                <div class="mt-3 rounded-[10px] bg-white p-3 border border-senat-sidebarLine text-[12px]">
                                    <span class="font-bold text-senat-ink">Tanggapan Komisi:</span>
                                    <p class="text-senat-body mt-0.5">{{ $asp['response'] }}</p>
                                </div>
                                <div class="mt-3 flex justify-end gap-2">
                                    <x-senat.button variant="primary" size="sm" type="button" wire:click="saveAction('Disposisi Tiket {{ $asp['ticket_code'] }}')">
                                        Perbarui Respon Disposisi
                                    </x-senat.button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

            @else
                {{-- Fallback --}}
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-xs">
                    <p class="text-[13px] text-slate-600">Modul ini siap dikonfigurasi lebih lanjut.</p>
                </div>
            @endif

        </div>
    </main>
</div>
</div>{{-- /root komponen Livewire --}}
