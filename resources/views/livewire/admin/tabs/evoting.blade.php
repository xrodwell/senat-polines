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
                                            wire:click="openNewVotingModal">Gelar Voting Baru</x-senat.button>
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

                    {{-- Riwayat & Daftar Sesi E-Voting Persidangan --}}
                    <section class="lg:col-span-2 rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h2 class="text-[18px] font-bold text-senat-ink">Riwayat &amp; Daftar Sesi E-Voting Persidangan</h2>
                                <p class="mt-1 text-[12px] text-senat-body">Pilih sesi voting untuk ditampilkan pada kartu monitor utama.</p>
                            </div>
                            <x-senat.badge state="info">{{ count($votingHistory) }} Sesi</x-senat.badge>
                        </div>

                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full divide-y divide-senat-sidebarLine text-left">
                                <thead>
                                    <tr class="bg-senat-surface text-[11px] font-bold uppercase tracking-wider text-senat-muted">
                                        <th class="px-3 py-3 whitespace-nowrap">ID</th>
                                        <th class="px-3 py-3">Pokok Putusan / Judul Voting</th>
                                        <th class="px-3 py-3">Sidang/Rapat</th>
                                        <th class="px-3 py-3 whitespace-nowrap">Status</th>
                                        <th class="px-3 py-3 text-center whitespace-nowrap">Total Suara</th>
                                        <th class="px-3 py-3 whitespace-nowrap">Waktu</th>
                                        <th class="px-3 py-3 text-right whitespace-nowrap">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-senat-sidebarLine">
                                    @forelse ($votingHistory as $item)
                                        @php
                                            $itemId = (int) ($item['id_voting'] ?? $item['id'] ?? 0);
                                            $itemTitle = $item['judul_voting'] ?? $item['judul'] ?? 'Sesi Voting Senat';
                                            $itemStatus = strtolower($item['status'] ?? 'ditutup');
                                            $itemTotal = (int) ($item['total_suara'] ?? $item['suara_votings_count'] ?? 0);
                                            $itemRapat = $item['rapat'] ?? [];
                                            $itemRapatName = is_array($itemRapat)
                                                ? ($itemRapat['nama_agenda'] ?? $itemRapat['nama_rapat'] ?? $itemRapat['title'] ?? $itemRapat['kode'] ?? '-')
                                                : ($itemRapat ?: '-');
                                            $itemDate = ! empty($item['waktu_mulai'])
                                                ? date('d M Y H:i', strtotime($item['waktu_mulai']))
                                                : '-';
                                            $isActive = $itemId === (int) $votingId;
                                        @endphp
                                        <tr class="{{ $isActive ? 'bg-senat-badgeBg/60' : 'hover:bg-senat-surface/60' }} transition-colors">
                                            <td class="px-3 py-3 whitespace-nowrap text-[12px] font-mono font-semibold text-senat-ink">#{{ $itemId ?: '-' }}</td>
                                            <td class="px-3 py-3 min-w-[200px]">
                                                <p class="text-[13px] font-semibold text-senat-ink">{{ $itemTitle }}</p>
                                                @if ($isActive)
                                                    <span class="mt-1 inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10.5px] font-bold text-emerald-700">
                                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Sedang Dipantau
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-3 min-w-[140px] text-[12px] text-senat-body">{{ $itemRapatName }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap">
                                                @if ($itemStatus === 'dibuka')
                                                    <x-senat.badge state="success">DIBUKA</x-senat.badge>
                                                @else
                                                    <x-senat.badge state="danger">DITUTUP</x-senat.badge>
                                                @endif
                                            </td>
                                            <td class="px-3 py-3 text-center whitespace-nowrap text-[13px] font-bold text-senat-ink">{{ $itemTotal }}</td>
                                            <td class="px-3 py-3 whitespace-nowrap text-[12px] text-senat-body">{{ $itemDate }}</td>
                                            <td class="px-3 py-3 text-right whitespace-nowrap">
                                                <div class="inline-flex items-center justify-end gap-1.5 whitespace-nowrap">
                                                    <x-senat.button variant="{{ $isActive ? 'secondary' : 'primary' }}" size="sm" type="button" class="whitespace-nowrap" wire:click="selectVotingSession({{ $itemId }})">
                                                        {{ $isActive ? 'Sedang Dipantau' : 'Pilih Sesi' }}
                                                    </x-senat.button>
                                                    <button type="button"
                                                            wire:click="hapusSesiVoting({{ $itemId }})"
                                                            wire:confirm="Yakin ingin menghapus sesi voting ini?"
                                                            class="inline-flex shrink-0 items-center justify-center rounded-md p-1.5 text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition"
                                                            title="Hapus Sesi Voting">
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-3 py-6 text-center text-[12px] text-senat-muted">
                                                Belum ada riwayat sesi e-voting persidangan.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>

                    {{-- Modal Form Gelar Voting Baru --}}
                    @if ($showNewVotingModal)
                        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/60 p-4"
                             wire:keydown.escape="closeNewVotingModal"
                             wire:click.self="closeNewVotingModal">
                            <div class="w-full max-w-lg rounded-2xl border border-senat-sidebarLine bg-white p-6 shadow-2xl space-y-4"
                                 wire:click.stop>
                                <div class="flex items-center justify-between border-b border-senat-sidebarLine pb-3">
                                    <div>
                                        <h3 class="text-[17px] font-bold text-senat-ink">Gelar Sesi Voting Baru</h3>
                                        <p class="text-[12px] text-senat-body">Inisialisasi pemungutan suara sidang untuk anggota Senat</p>
                                    </div>
                                    <button type="button" wire:click="closeNewVotingModal" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition" aria-label="Tutup">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-[12px] font-semibold text-senat-ink mb-1">Agenda Sidang / Rapat</label>
                                        <select wire:model.live="newVotingMeetingId" class="w-full rounded-[10px] border border-senat-sidebarLine bg-white px-3.5 py-2.5 text-[13px] text-senat-ink focus:border-senat-link focus:outline-none">
                                            @forelse ($availableMeetings as $meeting)
                                                <option value="{{ $meeting['id_rapat'] ?? ($meeting['id'] ?? '') }}">{{ $meeting['nama_agenda'] ?? 'Sidang Pleno Senat' }}</option>
                                            @empty
                                                <option value="">Belum ada agenda rapat aktif</option>
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
                                        <textarea wire:model="newVotingDescription" rows="3" placeholder="Uraikan materi atau pokok keputusan yang dimusyawahkankan..."
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
                                        <button type="button" wire:click="closeNewVotingModal" class="inline-flex items-center justify-center rounded-md border border-senat-border bg-[#f8fafc] px-4 py-2 text-[12px] font-semibold text-senat-primary hover:border-senat-primary transition">
                                            Batal
                                        </button>
                                        <button type="button"
                                                wire:click="deployNewVoting" class="inline-flex items-center justify-center rounded-md bg-senat-primary px-5 py-2 text-[12px] font-bold text-white transition hover:bg-senat-primaryHover">
                                            Buka Sesi Voting
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
