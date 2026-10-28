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
                <div wire:key="screen-presensi-qr" class="grid gap-5 xl:grid-cols-[730px_minmax(0,1fr)]">
                    <section class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
                        @if ($activeMeeting)
                            <x-senat.badge state="success">Sidang Aktif</x-senat.badge>
                            <h2 class="mt-4 text-[20px] font-bold leading-snug text-senat-ink">{{ $activeMeeting['title'] }}</h2>
                            <p class="mt-2 text-[13px] text-senat-body">
                                {{ $activeMeeting['room'] }} · {{ $activeMeeting['time'] }}
                            </p>
                        @endif

                        <h3 class="mt-6 text-[15px] font-semibold text-senat-ink">Rekap Kehadiran</h3>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            <div class="rounded-[10px] bg-[#eaf8f0] px-3.5 py-3">
                                <p class="text-[12px] font-medium text-senat-ink">Hadir tercatat</p>
                                <p class="mt-1 text-[11px] text-senat-muted">{{ $activeMeeting['present_count'] ?? 28 }} peserta</p>
                            </div>
                            <div class="rounded-[10px] bg-[#fff4df] px-3.5 py-3">
                                <p class="text-[12px] font-medium text-senat-ink">Menunggu validasi</p>
                                <p class="mt-1 text-[11px] text-senat-muted">6 peserta</p>
                            </div>
                            <div class="rounded-[10px] bg-senat-badgeBg px-3.5 py-3">
                                <p class="text-[12px] font-medium text-senat-ink">Izin resmi</p>
                                <p class="mt-1 text-[11px] text-senat-muted">0 peserta</p>
                            </div>
                            <div class="rounded-[10px] bg-[#fdecef] px-3.5 py-3">
                                <p class="text-[12px] font-medium text-senat-ink">Tidak hadir</p>
                                <p class="mt-1 text-[11px] text-senat-muted">0 peserta</p>
                            </div>
                        </div>

                        <div class="mt-6 flex flex-wrap items-center gap-3">
                            <x-senat.button variant="secondary" type="button" wire:click="saveAction('Bekukan Presensi')">Bekukan setelah validasi</x-senat.button>
                            <x-senat.button variant="primary" type="button" wire:click="saveAction('Generate QR Presensi')">Tampilkan QR Presensi</x-senat.button>
                        </div>
                    </section>

                    <section class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
                        <h2 class="text-[18px] font-bold text-senat-ink">Status Kuorum</h2>
                        <p class="mt-1.5 text-[12px] text-senat-body">Angka diisi otomatis sesudah presensi disahkan.</p>

                        <dl class="mt-5 space-y-3">
                            <div class="flex items-center justify-between gap-3 rounded-[10px] bg-senat-surface px-4 py-3.5">
                                <dt class="text-[13px] font-medium text-senat-ink">Peserta terdaftar</dt>
                                <dd class="text-[11px] text-senat-muted">34 peserta</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3 rounded-[10px] bg-[#eaf8f0] px-4 py-3.5">
                                <dt class="text-[13px] font-medium text-senat-ink">Hadir tercatat</dt>
                                <dd class="text-[11px] font-bold text-[#116a3c]">28 peserta (82%)</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3 rounded-[10px] bg-senat-surface px-4 py-3.5">
                                <dt class="text-[13px] font-medium text-senat-ink">Ambang kuorum</dt>
                                <dd class="text-[11px] text-senat-muted">23 peserta (2/3)</dd>
                            </div>
                        </dl>
                        <x-senat.button variant="primary" size="sm" class="mt-5" type="button" wire:click="saveAction('Ekspor Rekap Kehadiran')">Ekspor Rekap</x-senat.button>
                    </section>
                </div>

            {{-- 4. SCREEN: Kuorum & Kehadiran Anggota --}}
            @elseif ($active === 'Kuorum & Kehadiran Anggota')
                <section wire:key="screen-kuorum-kehadiran" class="rounded-[14px] border border-senat-sidebarLine bg-white p-6 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
                    <x-senat.badge state="success">Kuorum Terpenuhi</x-senat.badge>
                    <h2 class="mt-3 text-[20px] font-bold text-senat-ink">Status Kuorum Sidang Pleno PLN-892</h2>
                    <p class="mt-1 text-[13px] text-senat-body">Ketentuan Statuta Politeknik Negeri Semarang: minimal kehadiran 2/3 anggota aktif untuk keabsahan penetapan keputusan.</p>

                    <div class="mt-6 grid gap-4 sm:grid-cols-3">
                        <div class="rounded-[12px] bg-senat-surface p-4 border border-senat-sidebarLine">
                            <p class="text-[12px] text-senat-muted">Total Anggota Senat</p>
                            <p class="mt-1 text-[24px] font-bold text-senat-ink">34 Orang</p>
                        </div>
                        <div class="rounded-[12px] bg-senat-surface p-4 border border-senat-sidebarLine">
                            <p class="text-[12px] text-senat-muted">Syarat Kuorum (2/3)</p>
                            <p class="mt-1 text-[24px] font-bold text-senat-ink">23 Orang</p>
                        </div>
                        <div class="rounded-[12px] bg-[#eaf8f0] p-4 border border-[#bbf0d2]">
                            <p class="text-[12px] text-[#116a3c] font-semibold">Kehadiran Terverifikasi</p>
                            <p class="mt-1 text-[24px] font-bold text-[#116a3c]">28 Orang (82.3%)</p>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-between border-t border-senat-sidebarLine pt-4">
                        <p class="text-[12px] text-senat-body">Sidang sah secara yuridis untuk menggelar voting dan penetapan draf peraturan.</p>
                        <x-senat.button variant="secondary" size="sm" type="button" wire:click="saveAction('Sahkan Berita Acara Kuorum')">Sahkan Berita Acara Kuorum</x-senat.button>
                    </div>
                </section>

            {{-- 5. SCREEN: Risalah & Notulensi Sidang (Figma 13C) --}}
            @elseif ($active === 'Risalah & Notulensi Sidang')
                <div wire:key="screen-risalah-notulensi" class="space-y-6">
                <section class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="min-w-0 max-w-[720px]">
                            <x-senat.badge state="warning">Draf Notulensi</x-senat.badge>
                            <h2 class="mt-3 text-[19px] font-bold leading-snug text-senat-ink">
                                {{ $activeMeeting ? $activeMeeting['title'] : 'Belum ada sidang untuk dinotulensikan' }}
                            </h2>
                            <p class="mt-2 text-[12px] leading-snug text-senat-body">
                                Sidang Pleno · {{ date('d F Y') }} — pencatatan resmi Sekretariat Senat Akademik Polines
                            </p>
                        </div>
                        <div class="flex shrink-0 flex-wrap items-center gap-3">
                            <x-senat.button variant="secondary" type="button" wire:click="saveAction('Draft Notulensi')">Simpan Draft</x-senat.button>
                            <x-senat.button variant="primary" type="button" wire:click="saveAction('Kirim Review Notulensi')">Kirim Review</x-senat.button>
                        </div>
                    </div>
                </section>

                <div class="grid gap-5 xl:grid-cols-[680px_minmax(0,1fr)]">
                    <section class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
                        <h2 class="text-[18px] font-bold text-senat-ink">Jalannya Pembahasan</h2>
                        <textarea rows="8" aria-label="Jalannya pembahasan"
                                  class="mt-3 w-full rounded-[10px] border border-senat-border bg-white p-4 text-[13px] leading-relaxed text-senat-ink placeholder:text-senat-muted focus:border-senat-link focus:outline-none focus:ring-1 focus:ring-senat-link"
                                  placeholder="Catatan jalannya pembahasan sidang diisi oleh notulis dan diverifikasi sekretariat sebelum risalah disahkan."></textarea>

                        <h3 class="mt-5 text-[13px] font-semibold text-senat-ink">Lampiran Dokumen</h3>
                        @if ($notulenItem)
                            <div class="mt-3 flex items-center gap-3 rounded-[10px] bg-senat-badgeBg px-4 py-2.5">
                                <span class="text-[18px] font-bold text-senat-link" aria-hidden="true"><x-senat.icon name="folder" class="h-[18px] w-[18px]" /></span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-[13px] font-medium text-senat-ink">{{ $notulenItem['title'] }}</span>
                                    <span class="mt-0.5 block text-[11px] text-senat-muted">{{ $notulenItem['category'] }} · {{ $notulenItem['file_size'] }}</span>
                                </span>
                                <x-senat.button variant="secondary" size="sm" type="button" wire:click="saveAction('Lihat Lampiran')">Lihat</x-senat.button>
                            </div>
                        @endif
                    </section>

                    <section class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
                        <h2 class="text-[18px] font-bold text-senat-ink">Keputusan &amp; Action Items</h2>
                        <ul class="mt-4 divide-y divide-senat-sidebarLine">
                            <li class="py-3 first:pt-1">
                                <x-senat.badge state="success">Keputusan</x-senat.badge>
                                <p class="mt-2 text-[13px] font-semibold text-senat-ink">Persetujuan Draf Peraturan Kurikulum MBKM</p>
                                <p class="mt-1 text-[11.5px] text-senat-body">Disahkan melalui persetujuan pleno dengan kuorum 82%.</p>
                            </li>
                            <li class="py-3">
                                <x-senat.badge state="warning">Action Item</x-senat.badge>
                                <p class="mt-2 text-[13px] font-semibold text-senat-ink">Komisi I — Penyempurnaan Lampiran RPS</p>
                                <p class="mt-1 text-[11.5px] text-senat-body">Waktu penyelesaian 7 hari kerja sebelum diajukan ke Direktur.</p>
                            </li>
                        </ul>
                    </section>
                </div>
                </div>

            {{-- 6. SCREEN: E-VOTING & PENGAMBILAN KEPUTUSAN (Figma 13E) --}}
            @elseif ($active === 'E-Voting & Pengambilan Keputusan')
                <div wire:key="screen-evoting-keputusan" class="grid gap-5 xl:grid-cols-[682px_minmax(0,1fr)]">
                    <section class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]"
                             x-data="{ status: 'Dibuka' }">
                        <x-senat.badge state="success" x-show="status === 'Dibuka'">Dibuka</x-senat.badge>
                        <x-senat.badge state="neutral" x-cloak x-show="status !== 'Dibuka'">Draft</x-senat.badge>

                        <h2 class="mt-4 text-[20px] font-bold leading-snug text-senat-ink">
                            Pemungutan Suara · Sidang Pleno Akreditasi Program Studi
                        </h2>
                        <p class="mt-2 text-[13px] leading-snug text-senat-body">
                            Sidang Pleno · PLN-892 · {{ date('d M Y') }}
                        </p>

                        <div class="mt-5 grid gap-3 sm:grid-cols-3">
                            <div class="rounded-[12px] bg-[#eaf8f0] px-4 py-3.5">
                                <p class="text-[14px] font-semibold text-senat-ink">Setuju</p>
                                <p class="mt-1.5 text-[12px] text-senat-body">24 suara (85.7%)</p>
                            </div>
                            <div class="rounded-[12px] bg-[#fdecef] px-4 py-3.5">
                                <p class="text-[14px] font-semibold text-senat-ink">Tidak Setuju</p>
                                <p class="mt-1.5 text-[12px] text-senat-body">2 suara (7.1%)</p>
                            </div>
                            <div class="rounded-[12px] bg-senat-badgeBg px-4 py-3.5">
                                <p class="text-[14px] font-semibold text-senat-ink">Abstain</p>
                                <p class="mt-1.5 text-[12px] text-senat-body">2 suara (7.1%)</p>
                            </div>
                        </div>

                        <div class="mt-6 flex flex-wrap items-center gap-3">
                            <x-senat.button variant="secondary" type="button" wire:click="saveAction('Simpan Sesi Voting')">Simpan Rekap</x-senat.button>
                            <x-senat.button variant="primary" type="button" @click="status = status === 'Dibuka' ? 'Tutup' : 'Dibuka'" wire:click="saveAction('Status Voting Diperbarui')">
                                <span x-text="status === 'Dibuka' ? 'Kunci & Tutup Voting' : 'Buka Voting'"></span>
                            </x-senat.button>
                        </div>
                    </section>

                    <section class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
                        <h2 class="text-[18px] font-bold text-senat-ink">Rekap Suara Masuk</h2>
                        <p class="mt-1.5 text-[12px] text-senat-body">Total suara: 28 / 34 anggota</p>

                        <ul class="mt-5 space-y-3">
                            <li class="flex items-center gap-3 rounded-[10px] bg-senat-surface px-4 py-3.5">
                                <span class="h-[10px] w-[10px] shrink-0 rounded-[5px] bg-[#116a3c]"></span>
                                <span class="min-w-0 flex-1 text-[13px] font-medium text-senat-ink">Setuju</span>
                                <span class="text-[20px] font-bold leading-none text-[#116a3c]">24</span>
                            </li>
                            <li class="flex items-center gap-3 rounded-[10px] bg-senat-surface px-4 py-3.5">
                                <span class="h-[10px] w-[10px] shrink-0 rounded-[5px] bg-[#b4233b]"></span>
                                <span class="min-w-0 flex-1 text-[13px] font-medium text-senat-ink">Tidak Setuju</span>
                                <span class="text-[20px] font-bold leading-none text-[#b4233b]">2</span>
                            </li>
                            <li class="flex items-center gap-3 rounded-[10px] bg-senat-surface px-4 py-3.5">
                                <span class="h-[10px] w-[10px] shrink-0 rounded-[5px] bg-[#155ead]"></span>
                                <span class="min-w-0 flex-1 text-[13px] font-medium text-senat-ink">Abstain</span>
                                <span class="text-[20px] font-bold leading-none text-[#155ead]">2</span>
                            </li>
                        </ul>

                        <x-senat.button variant="secondary" size="sm" class="mt-5" type="button" wire:click="saveAction('Cetak Berita Acara Voting')">Cetak Berita Acara</x-senat.button>
                    </section>
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

            {{-- 11. SCREEN: Katalog Regulasi & Produk Hukum --}}
            @elseif ($active === 'Katalog Regulasi & Produk Hukum')
                <section wire:key="screen-regulasi-hukum" class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
                    <div class="flex items-center justify-between border-b border-senat-sidebarLine pb-4">
                        <div>
                            <h2 class="text-[18px] font-bold text-senat-ink">Katalog Produk Hukum & Peraturan Senat</h2>
                            <p class="text-[12px] text-senat-body">Arsip digital peraturan, SK, dan standar akademik resmi</p>
                        </div>
                        <x-senat.button variant="primary" size="sm" type="button" wire:click="saveAction('Upload Produk Hukum')">+ Unggah Dokumen</x-senat.button>
                    </div>
                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full text-left text-[13px]">
                            <thead class="border-b border-senat-sidebarLine bg-senat-surface text-[11px] font-bold uppercase text-senat-muted">
                                <tr>
                                    <th class="px-4 py-3">Nomor Dokumen</th>
                                    <th class="px-4 py-3">Judul Regulasi</th>
                                    <th class="px-4 py-3">Kategori</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-senat-sidebarLine">
                                @foreach ($regulations as $reg)
                                    <tr>
                                        <td class="px-4 py-3.5 font-mono font-semibold text-senat-link">{{ $reg['nomor'] }}</td>
                                        <td class="px-4 py-3.5 font-medium text-senat-ink">{{ $reg['title'] }}</td>
                                        <td class="px-4 py-3.5 text-senat-muted">{{ $reg['category'] }}</td>
                                        <td class="px-4 py-3.5">
                                            <x-senat.badge :state="$reg['status'] === 'Disahkan' ? 'success' : 'warning'">{{ $reg['status'] }}</x-senat.badge>
                                        </td>
                                        <td class="px-4 py-3.5 text-right">
                                            <x-senat.button variant="secondary" size="sm" type="button" wire:click="saveAction('Edit Dokumen {{ $reg['id'] }}')">Edit</x-senat.button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>

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
