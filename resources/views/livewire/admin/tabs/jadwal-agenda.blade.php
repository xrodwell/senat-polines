<div wire:key="screen-jadwal-agenda" class="grid gap-5 xl:grid-cols-[680px_minmax(0,1fr)]">
                    {{-- Form Buat Sidang Baru & Seleksi Dosen --}}
                    <section class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
                        <div class="flex items-center justify-between border-b border-senat-line pb-3">
                            <div>
                                <h2 class="text-[18px] font-bold text-senat-ink">
                                    {{ $editingMeetingId ? 'Sunting Sidang #' . $editingMeetingId : 'Buat Sidang Baru' }}
                                </h2>
                                <p class="mt-0.5 text-[12px] text-senat-body">
                                    {{ $editingMeetingId ? 'Perbarui informasi agenda sidang di Backend API.' : 'Jadwalkan sidang dan tentukan dosen yang diundang.' }}
                                </p>
                            </div>
                            @if ($editingMeetingId)
                                <span class="rounded-full bg-amber-100 px-3 py-1 text-[11px] font-bold text-amber-900 border border-amber-200">Mode Edit Aktif</span>
                            @endif
                        </div>

                        @if ($editingMeetingId)
                            <form class="mt-4 space-y-4" wire:submit.prevent="saveMeeting('Terjadwal')">
                                <div>
                                    <label class="text-[12px] font-semibold text-senat-ink">Judul / Agenda Sidang <span class="text-rose-500">*</span></label>
                                    <input type="text" wire:model="meetingTitle" placeholder="Contoh: Sidang Pleno Penetapan Standar Kurikulum Vokasi" required
                                           class="mt-1 h-[38px] w-full rounded-[8px] border border-senat-border bg-white px-3 text-[12px] text-senat-ink focus:border-senat-link focus:outline-none focus:ring-1 focus:ring-senat-link">
                                    @error('meetingTitle') <span class="text-[11px] text-rose-500">{{ $message }}</span> @enderror
                                </div>

                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <label class="text-[12px] font-semibold text-senat-ink">Penyelenggara Sidang <span class="text-rose-500">*</span></label>
                                        <select wire:model="meetingOrganizer"
                                                class="mt-1 h-[38px] w-full rounded-[8px] border border-senat-border bg-white px-3 text-[12px] text-senat-ink focus:border-senat-link focus:outline-none focus:ring-1 focus:ring-senat-link">
                                            @foreach ($this->organizerOptions() as $val => $optLabel)
                                                <option value="{{ $val }}">{{ $optLabel }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-[12px] font-semibold text-senat-ink">Jenis Rapat / Sidang Senat <span class="text-rose-500">*</span></label>
                                        <select wire:model="meetingType"
                                                class="mt-1 h-[38px] w-full rounded-[8px] border border-senat-border bg-white px-3 text-[12px] text-senat-ink focus:border-senat-link focus:outline-none focus:ring-1 focus:ring-senat-link">
                                            @foreach ($this->meetingTypeOptions() as $val => $optLabel)
                                                <option value="{{ $val }}">{{ $optLabel }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <label class="text-[12px] font-semibold text-senat-ink">Tanggal Sidang <span class="text-rose-500">*</span></label>
                                        <input type="date" wire:model="meetingDate" required
                                               class="mt-1 h-[38px] w-full rounded-[8px] border border-senat-border bg-white px-3 text-[12px] text-senat-ink focus:border-senat-link focus:outline-none focus:ring-1 focus:ring-senat-link">
                                        @error('meetingDate') <span class="text-[11px] text-rose-500">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[12px] font-semibold text-senat-ink">Waktu Pelaksanaan <span class="text-rose-500">*</span></label>
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1">
                                                <input type="time" wire:model.live="meetingStartTime" required
                                                       class="h-[38px] w-full rounded-[8px] border border-senat-border bg-white px-2.5 text-[12px] text-senat-ink focus:border-senat-link focus:outline-none focus:ring-1 focus:ring-senat-link">
                                            </div>
                                            <span class="text-slate-400 font-bold text-xs">s/d</span>
                                            <div class="flex-1">
                                                <input type="time" wire:model.live="meetingEndTime" @disabled($meetingUntilFinished)
                                                       class="h-[38px] w-full rounded-[8px] border border-senat-border bg-white px-2.5 text-[12px] text-senat-ink focus:border-senat-link focus:outline-none focus:ring-1 focus:ring-senat-link disabled:bg-slate-100 disabled:text-slate-400">
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-between pt-0.5">
                                            <label class="inline-flex items-center gap-1.5 cursor-pointer text-[11px] text-slate-600">
                                                <input type="checkbox" wire:model.live="meetingUntilFinished" class="rounded border-slate-300 text-senat-primary focus:ring-senat-primary h-3.5 w-3.5">
                                                <span>Sampai Selesai</span>
                                            </label>
                                            <span class="text-[10.5px] font-semibold text-senat-primary">
                                                {{ $this->formatMeetingTime($meetingStartTime, $meetingEndTime, $meetingUntilFinished) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="text-[12px] font-semibold text-senat-ink">Lokasi / Ruangan <span class="text-rose-500">*</span></label>
                                    <input type="text" wire:model="meetingRoom" placeholder="Contoh: Ruang Sidang Utama Gd. Direktorat" required
                                           class="mt-1 h-[38px] w-full rounded-[8px] border border-senat-border bg-white px-3 text-[12px] text-senat-ink focus:border-senat-link focus:outline-none focus:ring-1 focus:ring-senat-link">
                                    @error('meetingRoom') <span class="text-[11px] text-rose-500">{{ $message }}</span> @enderror
                                </div>

                                <div class="flex flex-wrap items-center gap-3 pt-2">
                                    <button type="submit"
                                            class="senat-tap inline-flex h-9 items-center justify-center rounded-md bg-[#116a3c] px-5 text-[12px] font-semibold text-white transition hover:bg-[#0d542f] shadow-sm">
                                        Perbarui Sidang
                                    </button>
                                    <button type="button" wire:click="saveMeeting('Draft')"
                                            class="senat-tap inline-flex h-9 items-center justify-center rounded-md border border-slate-300 bg-white px-4 text-[12px] font-semibold text-slate-700 hover:bg-slate-50 shadow-2xs">
                                        Simpan sebagai Draft
                                    </button>
                                    <button type="button" wire:click="resetMeetingForm"
                                            class="senat-tap inline-flex h-9 items-center justify-center rounded-md border border-rose-200 bg-rose-50 px-4 text-[12px] font-semibold text-rose-700 hover:bg-rose-100 shadow-2xs">
                                        Batal Edit
                                    </button>
                                </div>
                            </form>
                        @else
                            <form class="mt-4 space-y-4" wire:submit.prevent="createNewMeeting">
                                <div>
                                    <label class="text-[12px] font-semibold text-senat-ink">Judul / Agenda Sidang <span class="text-rose-500">*</span></label>
                                    <input type="text"
                                           wire:model="newMeetingTitle"
                                           placeholder="Contoh: Sidang Pleno Penetapan Standar Kurikulum MBKM Vokasi"
                                           class="mt-1 h-[38px] w-full rounded-[8px] border border-senat-border bg-white px-3 text-[12px] text-senat-ink focus:border-senat-link focus:outline-none focus:ring-1 focus:ring-senat-link">
                                </div>

                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <label class="text-[12px] font-semibold text-senat-ink">Penyelenggara Sidang <span class="text-rose-500">*</span></label>
                                        <select wire:model.live="newMeetingOrganizer"
                                                class="mt-1 h-[38px] w-full rounded-[8px] border border-senat-border bg-white px-3 text-[12px] text-senat-ink focus:border-senat-link focus:outline-none focus:ring-1 focus:ring-senat-link">
                                            @foreach ($this->organizerOptions() as $val => $optLabel)
                                                <option value="{{ $val }}">{{ $optLabel }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-[12px] font-semibold text-senat-ink">Jenis Rapat / Sidang Senat <span class="text-rose-500">*</span></label>
                                        <select wire:model="newMeetingType"
                                                class="mt-1 h-[38px] w-full rounded-[8px] border border-senat-border bg-white px-3 text-[12px] text-senat-ink focus:border-senat-link focus:outline-none focus:ring-1 focus:ring-senat-link">
                                            @foreach ($this->meetingTypeOptions() as $val => $optLabel)
                                                <option value="{{ $val }}">{{ $optLabel }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <label class="text-[12px] font-semibold text-senat-ink">Tanggal Sidang <span class="text-rose-500">*</span></label>
                                        <input type="date"
                                               wire:model="newMeetingDate"
                                               class="mt-1 h-[38px] w-full rounded-[8px] border border-senat-border bg-white px-3 text-[12px] text-senat-ink focus:border-senat-link focus:outline-none focus:ring-1 focus:ring-senat-link">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[12px] font-semibold text-senat-ink">Waktu Pelaksanaan <span class="text-rose-500">*</span></label>
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1">
                                                <input type="time"
                                                       wire:model.live="newMeetingStartTime"
                                                       required
                                                       class="h-[38px] w-full rounded-[8px] border border-senat-border bg-white px-2.5 text-[12px] text-senat-ink focus:border-senat-link focus:outline-none focus:ring-1 focus:ring-senat-link">
                                            </div>
                                            <span class="text-slate-400 font-bold text-xs">s/d</span>
                                            <div class="flex-1">
                                                <input type="time"
                                                       wire:model.live="newMeetingEndTime"
                                                       @disabled($newMeetingUntilFinished)
                                                       class="h-[38px] w-full rounded-[8px] border border-senat-border bg-white px-2.5 text-[12px] text-senat-ink focus:border-senat-link focus:outline-none focus:ring-1 focus:ring-senat-link disabled:bg-slate-100 disabled:text-slate-400">
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-between pt-0.5">
                                            <label class="inline-flex items-center gap-1.5 cursor-pointer text-[11px] text-slate-600">
                                                <input type="checkbox" wire:model.live="newMeetingUntilFinished" class="rounded border-slate-300 text-senat-primary focus:ring-senat-primary h-3.5 w-3.5">
                                                <span>Sampai Selesai</span>
                                            </label>
                                            <span class="text-[10.5px] font-semibold text-senat-primary">
                                                {{ $this->formatMeetingTime($newMeetingStartTime, $newMeetingEndTime, $newMeetingUntilFinished) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="text-[12px] font-semibold text-senat-ink">Ruangan / Tempat <span class="text-rose-500">*</span></label>
                                    <input type="text"
                                           wire:model="newMeetingRoom"
                                           placeholder="Contoh: Ruang Sidang Utama Lantai 2 Gd. Direktorat"
                                           class="mt-1 h-[38px] w-full rounded-[8px] border border-senat-border bg-white px-3 text-[12px] text-senat-ink focus:border-senat-link focus:outline-none focus:ring-1 focus:ring-senat-link">
                                </div>

                                {{-- Komponen Seleksi Dosen yang Diundang --}}
                                <div class="rounded-xl border border-senat-border bg-slate-50/60 p-3.5 space-y-2.5">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <div>
                                            <label class="text-[12px] font-bold text-senat-ink">Dosen &amp; Anggota Senat yang Diundang</label>
                                            <p class="text-[11px] text-senat-muted">Tentukan anggota yang berhak hadir dan dihitung kuorumnya.</p>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="rounded-md bg-emerald-100 px-2 py-0.5 text-[11px] font-bold text-emerald-800">
                                                {{ count($selectedLecturerIds) }} Dosen Dipilih
                                            </span>
                                            <span class="text-[10.5px] text-slate-500 font-medium">
                                                (Kuorum 2/3 = {{ (int) ceil(count($selectedLecturerIds) * 2 / 3) }} Orang)
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Tombol Cepat / Preset Komisi --}}
                                    <div class="flex flex-wrap items-center gap-1.5 pt-1 text-[11px]">
                                        <span class="text-senat-muted font-semibold mr-1">Pilih Cepat:</span>
                                        <button type="button" wire:click="selectAllLecturers" class="px-2 py-1 rounded bg-white border border-slate-200 text-slate-700 font-semibold hover:border-senat-primary hover:text-senat-primary shadow-2xs">Semua ({{ count($allLecturers) }})</button>
                                        <button type="button" wire:click="selectCommitteeLecturers('Komisi I')" class="px-2 py-1 rounded bg-white border border-slate-200 text-slate-700 font-semibold hover:border-senat-primary hover:text-senat-primary shadow-2xs">Komisi I</button>
                                        <button type="button" wire:click="selectCommitteeLecturers('Komisi II')" class="px-2 py-1 rounded bg-white border border-slate-200 text-slate-700 font-semibold hover:border-senat-primary hover:text-senat-primary shadow-2xs">Komisi II</button>
                                        <button type="button" wire:click="selectCommitteeLecturers('Komisi III')" class="px-2 py-1 rounded bg-white border border-slate-200 text-slate-700 font-semibold hover:border-senat-primary hover:text-senat-primary shadow-2xs">Komisi III</button>
                                        <button type="button" wire:click="selectCommitteeLecturers('Komisi IV')" class="px-2 py-1 rounded bg-white border border-slate-200 text-slate-700 font-semibold hover:border-senat-primary hover:text-senat-primary shadow-2xs">Komisi IV</button>
                                        <button type="button" wire:click="selectCommitteeLecturers('Komisi V')" class="px-2 py-1 rounded bg-white border border-slate-200 text-slate-700 font-semibold hover:border-senat-primary hover:text-senat-primary shadow-2xs">Komisi V</button>
                                        <button type="button" wire:click="selectCommitteeLecturers('BP')" class="px-2 py-1 rounded bg-white border border-slate-200 text-slate-700 font-semibold hover:border-senat-primary hover:text-senat-primary shadow-2xs">BP (Badan Pekerja)</button>
                                        <button type="button" wire:click="selectCommitteeLecturers('Pimpinan')" class="px-2 py-1 rounded bg-white border border-slate-200 text-slate-700 font-semibold hover:border-senat-primary hover:text-senat-primary shadow-2xs">Pimpinan</button>
                                        <button type="button" wire:click="clearSelectedLecturers" class="px-2 py-1 rounded bg-white border border-rose-200 text-rose-600 font-semibold hover:bg-rose-50 shadow-2xs">Kosongkan</button>
                                    </div>

                                    {{-- Input Cari Dosen --}}
                                    <div class="pt-1">
                                        <input type="text"
                                               wire:model.live.debounce.200ms="lecturerSearch"
                                               placeholder="Cari nama dosen, NIP, atau komisi..."
                                               class="h-8 w-full rounded-md border border-slate-200 bg-white px-2.5 text-[11.5px] text-senat-ink focus:border-senat-link focus:outline-none">
                                    </div>

                                    {{-- Scrollable Checkbox List --}}
                                    <div class="max-h-52 overflow-y-auto divide-y divide-slate-100 rounded-lg border border-slate-200 bg-white p-1 text-xs">
                                        @forelse ($filteredLecturers as $lec)
                                            <label wire:key="lec-check-{{ $lec['id_user'] }}" class="flex items-center gap-2.5 py-1.5 px-2 rounded hover:bg-slate-50 cursor-pointer transition">
                                                <input type="checkbox"
                                                       value="{{ $lec['id_user'] }}"
                                                       wire:click="toggleLecturer({{ $lec['id_user'] }})"
                                                       {{ in_array($lec['id_user'], $selectedLecturerIds, true) ? 'checked' : '' }}
                                                       class="rounded border-slate-300 text-emerald-700 focus:ring-emerald-600 h-3.5 w-3.5">
                                                <div class="flex-1 min-w-0 leading-tight">
                                                    <p class="font-bold text-[12px] text-senat-ink truncate">{{ $lec['nama'] }}</p>
                                                    <p class="text-[10.5px] text-slate-500 font-mono">NIP: {{ $lec['nim_nip'] }} · <span class="font-semibold text-senat-primary">{{ $lec['komisi'] }}</span></p>
                                                </div>
                                            </label>
                                        @empty
                                            <p class="p-3 text-center text-slate-400 text-[11px]">Dosen tidak ditemukan dengan kata kunci tersebut.</p>
                                        @endforelse
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center gap-3 pt-2">
                                    <button type="submit"
                                            wire:loading.attr="disabled"
                                            wire:target="createNewMeeting"
                                            class="senat-tap inline-flex h-9 items-center justify-center gap-2 rounded-md bg-[#116a3c] px-5 text-[12px] font-semibold text-white transition hover:bg-[#0d542f] shadow-sm disabled:opacity-50">
                                        <span wire:loading.remove wire:target="createNewMeeting" class="flex items-center gap-1.5">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                            <span>Jadwalkan Sidang &amp; Terbitkan Undangan Broadcast ({{ count($selectedLecturerIds) }} Dosen)</span>
                                        </span>
                                        <span wire:loading wire:target="createNewMeeting" class="flex items-center gap-1.5">
                                            <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                            <span>Menjadwalkan &amp; Mengirim Broadcast Email...</span>
                                        </span>
                                    </button>
                                    <button type="button"
                                            wire:click="saveAction('Draf Sidang Disimpan')"
                                            class="senat-tap inline-flex h-9 items-center justify-center rounded-md border border-slate-300 bg-white px-4 text-[12px] font-semibold text-slate-700 hover:bg-slate-50 shadow-2xs">
                                        <span>Simpan sebagai Draf</span>
                                    </button>
                                </div>
                            </form>
                        @endif
                    </section>

                    {{-- Daftar Sidang Terjadwal & Riwayat Sidang --}}
                    <section class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-senat-line pb-3">
                            <div>
                                <h2 class="text-[18px] font-bold text-senat-ink">Agenda &amp; Riwayat Sidang</h2>
                                <p class="mt-0.5 text-[12px] text-senat-muted">Daftar Sidang Terjadwal, aktif, dan riwayat lalu (Data dinamis dari Backend API port 8001).</p>
                            </div>
                            <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-bold text-slate-700">
                                {{ count($filteredMeetings) }} Agenda
                            </span>
                        </div>

                        {{-- Filter Status Tabs --}}
                        <div class="mt-3 flex items-center gap-1.5 overflow-x-auto pb-1 text-[11px]">
                            @foreach (['semua' => 'Semua', 'berlangsung' => 'Sedang Berlangsung', 'terjadwal' => 'Terjadwal', 'selesai' => 'Riwayat Selesai'] as $tabKey => $tabName)
                                <button type="button"
                                        wire:key="meeting-tab-{{ $tabKey }}"
                                        wire:click="$set('meetingFilterTab', '{{ $tabKey }}')"
                                        class="px-2.5 py-1 rounded-md font-semibold transition-colors shrink-0 {{ $meetingFilterTab === $tabKey ? 'bg-senat-primary text-white shadow-2xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                                    {{ $tabName }}
                                </button>
                            @endforeach
                        </div>

                        <ul x-data="{ scrolling: false, _scrollTimer: null }"
                            @scroll.passive="scrolling = true; clearTimeout(_scrollTimer); _scrollTimer = setTimeout(() => scrolling = false, 1500)"
                            :class="{ 'is-scrolling': scrolling }"
                            class="auto-hide-scroll mt-3 divide-y divide-senat-sidebarLine max-h-[680px] overflow-y-auto pr-1">
                            @forelse ($filteredMeetings as $meeting)
                                <li wire:key="item-meeting-{{ $meeting['id'] ?? ($meeting['id_rapat'] ?? $loop->index) }}" class="py-3.5 first:pt-1">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-center gap-1.5">
                                                <span class="font-mono text-[10.5px] font-bold text-senat-muted uppercase">{{ $meeting['session_code'] ?? 'PLN' }}</span>
                                                <x-senat.badge :state="$this->chipState($meeting['type'] ?? '')">{{ $meeting['type'] ?? 'Sidang' }}</x-senat.badge>
                                                @if (!empty($meeting['organizer']))
                                                    <span class="rounded bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-700 border border-slate-200" title="Penyelenggara Sidang">
                                                        🏛️ {{ $meeting['organizer'] }}
                                                    </span>
                                                @endif
                                                @if (in_array(strtolower($meeting['status'] ?? ''), ['sedang berlangsung', 'aktif']))
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-amber-700 border border-amber-200">
                                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500 animate-pulse"></span> Berlangsung
                                                    </span>
                                                @elseif (in_array(strtolower($meeting['status'] ?? ''), ['terjadwal', 'dijadwalkan']))
                                                    <span class="rounded px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200">
                                                        Terjadwal
                                                    </span>
                                                @elseif (in_array(strtolower($meeting['status'] ?? ''), ['draft', 'draf']))
                                                    <span class="rounded px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider bg-slate-100 text-slate-600">
                                                        Draft
                                                    </span>
                                                @else
                                                    <span class="rounded px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                        {{ $meeting['status'] ?? 'Selesai' }}
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="mt-1 text-[13.5px] font-bold leading-snug text-senat-ink">{{ $meeting['title'] ?? ($meeting['nama_agenda'] ?? 'Sidang') }}</p>
                                            <p class="mt-1 text-[11.5px] text-senat-body">
                                                {{ \Illuminate\Support\Carbon::parse($meeting['date'] ?? now())->format('d M Y') }} · {{ $meeting['time'] ?? '09:00 WIB' }}
                                            </p>
                                            <p class="mt-0.5 text-[11px] text-senat-muted truncate">{{ $meeting['room'] ?? 'Ruang Sidang Senat' }}</p>
                                        </div>
                                    </div>

                                    <div class="mt-2.5 flex flex-wrap items-center justify-between border-t border-slate-100 pt-2.5 gap-2 text-[11px]">
                                        <span class="inline-flex items-center gap-1.5 font-semibold text-slate-600">
                                            <x-senat.icon name="users" class="h-3.5 w-3.5 shrink-0 text-slate-500" />
                                            <span>{{ $meeting['total_invited'] ?? 34 }} Dosen Diundang</span>
                                        </span>
                                        <div class="flex items-center gap-1.5">
                                            <button type="button"
                                                    wire:click="previewBroadcast({{ $meeting['id'] ?? ($meeting['id_rapat'] ?? 1) }})"
                                                    wire:loading.attr="disabled"
                                                    class="senat-tap inline-flex h-7 items-center justify-center rounded-md border border-blue-200 bg-blue-50 px-2 text-[11px] font-semibold text-senat-primary hover:bg-blue-100 transition shadow-2xs disabled:opacity-50"
                                                    title="Pratinjau & Broadcast Email Undangan">
                                                <span>Broadcast</span>
                                            </button>
                                            <button type="button"
                                                    wire:click="manageMeeting({{ $meeting['id'] ?? ($meeting['id_rapat'] ?? 1) }})"
                                                    class="senat-tap inline-flex h-7 items-center justify-center rounded-md border border-slate-300 bg-white px-2.5 text-[11px] font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs"
                                                    title="Buka Presensi Sidang Ini">
                                                <span>Presensi</span>
                                            </button>
                                            <button type="button"
                                                    wire:click="manageMeeting({{ $meeting['id'] ?? ($meeting['id_rapat'] ?? 1) }}, 'Kuorum & Kehadiran Anggota')"
                                                    class="senat-tap inline-flex h-7 items-center justify-center rounded-md border border-slate-300 bg-white px-2 text-[11px] font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs"
                                                    title="Pantau Kuorum Sidang Ini">
                                                <span>Kuorum</span>
                                            </button>
                                            <button type="button"
                                                    wire:click="editMeeting({{ $meeting['id'] ?? ($meeting['id_rapat'] ?? 1) }})"
                                                    class="senat-tap inline-flex h-7 items-center justify-center rounded-md border border-blue-200 bg-blue-50/70 px-2 text-[11px] font-semibold text-blue-600 hover:bg-blue-100 hover:border-blue-300 transition shadow-2xs"
                                                    title="Sunting Sidang">
                                                <span>Edit</span>
                                            </button>
                                            <button type="button"
                                                    wire:click="deleteMeeting({{ $meeting['id'] ?? ($meeting['id_rapat'] ?? 1) }})"
                                                    onclick="return confirm('Hapus agenda sidang ini dari backend?') || event.stopImmediatePropagation()"
                                                    class="senat-tap inline-flex h-7 items-center justify-center rounded-md border border-rose-200 bg-rose-50/70 px-2 text-[11px] font-semibold text-rose-600 hover:bg-rose-100 hover:border-rose-300 transition shadow-2xs"
                                                    title="Hapus Sidang">
                                                <span>Hapus</span>
                                            </button>
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li class="py-6 text-center text-slate-400 text-xs">
                                    Tidak ada sidang pada kategori filter ini.
                                </li>
                            @endforelse
                        </ul>
                    </section>

                    @if ($broadcastModalMeetingId)
                        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-xs">
                            <div class="w-full max-w-lg rounded-xl border border-senat-border bg-white shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                                <div class="flex items-center justify-between border-b border-senat-line bg-slate-50 px-5 py-3.5">
                                    <div class="flex items-center gap-2">
                                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-blue-100 text-senat-primary text-xs font-bold">✉️</span>
                                        <div>
                                            <h3 class="text-sm font-bold text-senat-ink">Pratinjau Broadcast Email Undangan</h3>
                                            <p class="text-[11px] text-senat-muted">Format pesan yang dikirimkan ke email dosen / anggota senat</p>
                                        </div>
                                    </div>
                                    <button type="button" wire:click="closeBroadcastModal" class="rounded p-1 text-slate-400 hover:bg-slate-200 hover:text-slate-600">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                                <div class="p-5 space-y-3">
                                    <div class="rounded-lg border border-blue-100 bg-blue-50/60 p-3 text-[11.5px] text-slate-700 flex items-start gap-2.5">
                                        <span class="text-base leading-none">ℹ️</span>
                                        <p class="m-0 leading-relaxed">
                                            Undangan email resmi berdesain kartu terstruktur (agenda, waktu, ruangan, tautan portal) akan dikirimkan secara otomatis kepada seluruh dosen dan anggota senat yang terdaftar sebagai peserta sidang ini.
                                        </p>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-bold text-senat-muted uppercase tracking-wider mb-1.5">Pratinjau Isi Format Surat / Teks Undangan</label>
                                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-3.5 font-mono text-[11.5px] leading-relaxed text-slate-800 whitespace-pre-wrap select-all max-h-72 overflow-y-auto">
{{ $broadcastPreviewText }}
                                        </div>
                                        <p class="mt-1.5 text-[11px] text-slate-500 italic">
                                            * Catatan: Nama penerima ("Yth. ...") akan diganti secara personal dan otomatis untuk setiap dosen/anggota yang diundang.
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center justify-end gap-2 border-t border-senat-line bg-slate-50 px-5 py-3">
                                    <button type="button" wire:click="closeBroadcastModal" class="h-8 rounded-md border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                                        Tutup
                                    </button>
                                    <button type="button"
                                            wire:click="sendBroadcastEmail({{ $broadcastModalMeetingId }})"
                                            wire:loading.attr="disabled"
                                            wire:target="sendBroadcastEmail"
                                            class="inline-flex h-8 items-center gap-1.5 rounded-md bg-[#116a3c] px-4 text-xs font-semibold text-white hover:bg-[#0d542f] shadow-xs disabled:opacity-50">
                                        <span wire:loading.remove wire:target="sendBroadcastEmail">Kirim Broadcast Sekarang</span>
                                        <span wire:loading wire:target="sendBroadcastEmail" class="flex items-center gap-1.5">
                                            <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                            <span>Mengirim Broadcast Email...</span>
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
