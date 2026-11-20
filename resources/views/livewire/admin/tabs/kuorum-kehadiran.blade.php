<section wire:key="screen-kuorum-kehadiran" wire:poll.15s.visible="refreshAttendance" class="rounded-[14px] border border-senat-sidebarLine bg-white p-6 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)] space-y-6">

                    {{-- Meeting Selector Bar in Kuorum --}}
                    <div class="rounded-xl border border-senat-border bg-slate-50/80 p-3.5 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5 min-w-0 flex-1">
                            <span class="text-[11px] font-bold text-senat-muted uppercase shrink-0">Agenda Sidang Kuorum:</span>
                            <select wire:change="selectMeeting($event.target.value)"
                                    class="h-8.5 rounded-md border border-senat-border bg-white px-2.5 text-[12px] font-bold text-senat-ink focus:border-senat-link focus:outline-none">
                                @foreach ($availableMeetings as $m)
                                    <option value="{{ $m['id'] ?? ($m['id_rapat'] ?? '') }}" {{ ($selectedMeetingId == ($m['id'] ?? ($m['id_rapat'] ?? null))) ? 'selected' : '' }}>
                                        [{{ $m['status'] ?? 'Terjadwal' }}] {{ $m['title'] ?? ($m['nama_agenda'] ?? 'Sidang Senat') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <span class="text-xs font-semibold text-senat-primary bg-blue-50 px-2.5 py-1 rounded border border-blue-200">
                            Undangan: {{ $liveKuorum['total_invited'] ?? count($liveAnggota) }} Dosen
                        </span>
                    </div>
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
                            <button type="button"
                                    wire:click="exportExcel"
                                    class="senat-tap inline-flex h-9 items-center justify-center rounded-md bg-[#116a3c] px-3.5 text-[11.5px] font-semibold text-white transition hover:bg-[#0d542f] shadow-sm"
                                    title="Unduh Rekap Kehadiran dan Status Kuorum ke Excel (.xls)">
                                <span>Ekspor Excel (.xls)</span>
                            </button>
                            <button type="button"
                                    wire:click="exportCsv"
                                    class="senat-tap inline-flex h-9 items-center justify-center rounded-md bg-[#116a3c] px-3.5 text-[11.5px] font-semibold text-white transition hover:bg-[#0d542f] shadow-sm"
                                    title="Unduh format CSV">
                                <span>Ekspor CSV</span>
                            </button>
                        </div>
                    </div>
                </section>
