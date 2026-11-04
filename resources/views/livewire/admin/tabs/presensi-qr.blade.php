<div wire:key="screen-presensi-qr"
                     wire:poll.15s.visible="refreshAttendance"
                     class="space-y-6">

                    {{-- Meeting Selector Bar --}}
                    <div class="rounded-[14px] border border-senat-sidebarLine bg-white p-4 shadow-sm flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-senat-primary/10 text-senat-primary font-bold">
                                📅
                            </div>
                            <div class="min-w-0 flex-1">
                                <label for="select-meeting-presensi" class="text-[10.5px] font-bold uppercase tracking-wider text-senat-muted block">Pilih Agenda Sidang (Aktif / Terjadwal / Riwayat):</label>
                                <div class="mt-1 flex flex-wrap items-center gap-2">
                                    <select id="select-meeting-presensi"
                                            wire:change="selectMeeting($event.target.value)"
                                            class="h-9 min-w-[280px] max-w-full rounded-lg border border-senat-border bg-slate-50 px-3 text-[12.5px] font-bold text-senat-ink focus:border-senat-link focus:bg-white focus:outline-none focus:ring-1 focus:ring-senat-link">
                                        @foreach ($availableMeetings as $m)
                                            <option value="{{ $m['id'] ?? ($m['id_rapat'] ?? '') }}" {{ ($selectedMeetingId == ($m['id'] ?? ($m['id_rapat'] ?? null))) ? 'selected' : '' }}>
                                                [{{ $m['status'] ?? 'Terjadwal' }}] {{ $m['title'] ?? ($m['nama_agenda'] ?? 'Sidang Senat') }} — ({{ \Illuminate\Support\Carbon::parse($m['date'] ?? now())->format('d M Y') }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="rounded-md bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 border border-emerald-200 shrink-0">
                                        {{ count($liveAnggota) }} Dosen Diundang
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <a href="{{ route('admin.preview', ['tab' => 'Jadwal & Agenda Sidang']) }}"
                               class="senat-tap inline-flex h-9 items-center gap-1.5 rounded-lg border border-senat-border bg-white px-3 text-[12px] font-semibold text-senat-body hover:bg-slate-50 shadow-2xs">
                                <span>+ Buat Sidang Baru</span>
                            </a>
                        </div>
                    </div>
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

                            <div class="flex flex-wrap items-center gap-2">
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

                                <div class="flex items-center gap-1.5">
                                    <button type="button"
                                            wire:click="exportExcel"
                                            class="inline-flex items-center justify-center h-8 px-3 rounded-lg text-[11px] font-semibold bg-[#116a3c] text-white hover:bg-[#0d542f] transition-colors shadow-xs"
                                            title="Unduh Rekap Kehadiran Sidang ke Excel (.xls)">
                                        <span>Ekspor Excel (.xls)</span>
                                    </button>
                                    <button type="button"
                                            wire:click="exportCsv"
                                            class="inline-flex items-center justify-center h-8 px-3 rounded-lg text-[11px] font-semibold bg-[#116a3c] text-white hover:bg-[#0d542f] transition-colors shadow-xs"
                                            title="Unduh format CSV">
                                        <span>Ekspor CSV</span>
                                    </button>
                                </div>
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
