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

                    {{-- Notulensi Otomatis: rekaman audio/video → transkrip → draf notulensi --}}
                    <section class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="flex items-start gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-senat-link text-white">
                                    <x-senat.icon name="upload" class="h-4 w-4" />
                                </div>
                                <div>
                                    <h3 class="text-[16px] font-bold text-senat-ink">Notulensi Otomatis dari Rekaman</h3>
                                    <p class="mt-0.5 max-w-[620px] text-[12px] leading-relaxed text-senat-muted">
                                        Unggah rekaman rapat (audio atau video). Sistem mentranskripsi lalu menyusun draf notulensi
                                        secara otomatis. Maksimal <strong>1 GB</strong> dan <strong>4 jam</strong> per rekaman —
                                        untuk rapat panjang unggah audio saja (M4A/MP3), bukan video.
                                        Rekaman asli disimpan 90 hari, transkrip &amp; notulensi permanen.
                                    </p>
                                </div>
                            </div>
                            <x-senat.badge state="info">Whisper + Gemini</x-senat.badge>
                        </div>

                        <div class="mt-4 grid gap-3 sm:grid-cols-[1fr_auto] sm:items-end">
                            <div class="grid gap-3 sm:grid-cols-2">
                                <label class="block">
                                    <span class="text-[11px] font-semibold uppercase tracking-wide text-senat-muted">Rapat</span>
                                    <select wire:model.live="rekamanRapatId"
                                            class="mt-1 w-full rounded-lg border border-senat-border bg-white px-3 py-2 text-[13px] text-senat-ink">
                                        @foreach ($rapatList as $r)
                                            <option value="{{ $r['id_rapat'] ?? '' }}">{{ $r['nama_agenda'] ?? ('Rapat #' . ($r['id_rapat'] ?? '')) }}</option>
                                        @endforeach
                                    </select>
                                </label>

                                <label class="block">
                                    <span class="text-[11px] font-semibold uppercase tracking-wide text-senat-muted">Berkas Rekaman</span>
                                    <input type="file" wire:model="rekamanFile"
                                           accept="audio/*,video/*,.mp3,.wav,.m4a,.ogg,.mp4,.mov,.mkv,.webm,.3gp"
                                           class="mt-1 w-full rounded-lg border border-senat-border bg-white px-3 py-1.5 text-[12.5px] text-senat-body file:mr-3 file:rounded-md file:border-0 file:bg-senat-badgeBg file:px-3 file:py-1.5 file:text-[12px] file:font-semibold file:text-senat-ink">
                                </label>
                            </div>

                            <div class="flex items-center gap-2">
                                <button type="button" wire:click="mulaiRekaman" wire:loading.attr="disabled" wire:target="rekamanFile,mulaiRekaman"
                                        class="inline-flex items-center gap-1.5 rounded-[10px] bg-senat-link px-4 py-2.5 text-[12.5px] font-semibold text-white transition hover:opacity-90 disabled:cursor-wait disabled:opacity-60">
                                    <x-senat.icon name="upload" class="h-4 w-4" />
                                    <span wire:loading.remove wire:target="mulaiRekaman">Proses Jadi Notulensi</span>
                                    <span wire:loading wire:target="mulaiRekaman">Mengunggah&hellip;</span>
                                </button>
                            </div>
                        </div>

                        @error('rekamanFile') <p class="mt-2 text-[12px] text-rose-600">{{ $message }}</p> @enderror
                        @error('rekamanRapatId') <p class="mt-2 text-[12px] text-rose-600">{{ $message }}</p> @enderror

                        {{-- Progress selama proses berjalan --}}
                        @if (in_array($rekamanStatus, ['unggah', 'transkripsi', 'menyusun']))
                            <div wire:poll.2s="pollRekaman" class="mt-4 rounded-xl border border-senat-border bg-senat-badgeBg/40 p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-[12.5px] font-semibold text-senat-ink">{{ $rekamanNama ?: 'Rekaman' }}</p>
                                        <p class="mt-0.5 text-[11.5px] text-senat-muted">
                                            @if ($rekamanStatus === 'transkripsi')
                                                Mentranskripsi rekaman&hellip;
                                            @elseif ($rekamanStatus === 'menyusun')
                                                Menyusun draf notulensi&hellip;
                                            @else
                                                Menyiapkan berkas&hellip;
                                            @endif
                                        </p>
                                    </div>
                                    <span class="shrink-0 font-mono text-[12px] font-bold text-senat-link">{{ $rekamanProgres }}%</span>
                                </div>
                                <div class="mt-2.5 h-1.5 w-full overflow-hidden rounded-full bg-senat-border/60">
                                    <div class="h-full rounded-full bg-senat-link transition-all duration-500" style="width: {{ max(3, $rekamanProgres) }}%"></div>
                                </div>
                                <p class="mt-2 text-[11px] text-senat-muted">Halaman ini memperbarui sendiri. Aman untuk ditinggal.</p>
                            </div>
                        @endif

                        @if ($rekamanError !== '' && $rekamanStatus !== 'gagal')
                            <p class="mt-3 text-[12px] text-rose-600">{{ $rekamanError }}</p>
                        @endif

                        {{-- Hasil --}}
                        @if ($rekamanNotulen !== '')
                            <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50/40 p-4">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <p class="inline-flex items-center gap-1.5 text-[12.5px] font-bold text-emerald-800">
                                        <x-senat.icon name="check" class="h-4 w-4" /> Draf notulensi siap diperiksa
                                    </p>
                                    <span class="font-mono text-[11px] text-emerald-700">
                                        {{ $rekamanDurasi > 0 ? floor($rekamanDurasi / 60) . ' mnt ' . ($rekamanDurasi % 60) . ' dtk audio' : '' }}
                                    </span>
                                </div>

                                <div class="mt-3 max-h-[360px] overflow-y-auto rounded-lg border border-emerald-200 bg-white p-4 text-[12.5px] leading-relaxed text-senat-ink [&_h1]:mt-3 [&_h1]:text-[14px] [&_h1]:font-bold [&_h2]:mt-3 [&_h2]:text-[12px] [&_h2]:font-bold [&_h2]:uppercase [&_h2]:tracking-wide [&_h2]:text-senat-primary [&_h2:first-child]:mt-0 [&_h3]:mt-2 [&_h3]:font-semibold [&_li]:my-0.5 [&_ol]:my-1.5 [&_ol]:list-decimal [&_ol]:pl-5 [&_p]:my-1.5 [&_strong]:font-semibold [&_table]:my-2 [&_table]:w-full [&_td]:border-b [&_td]:border-senat-border/60 [&_td]:py-1 [&_td]:align-top [&_th]:border-b [&_th]:border-senat-border [&_th]:py-1 [&_th]:text-left [&_th]:text-[11px] [&_th]:uppercase [&_ul]:my-1.5 [&_ul]:list-disc [&_ul]:pl-5">
                                    {!! \Illuminate\Support\Str::markdown($rekamanNotulen, ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
                                </div>

                                <div class="mt-3 flex flex-wrap gap-2">
                                    <button type="button" wire:click="suntingHasilRekaman"
                                            class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3.5 py-2 text-[12px] font-semibold text-white hover:opacity-90">
                                        <x-senat.icon name="edit" class="h-3.5 w-3.5" /> Sunting &amp; Simpan ke Notulensi
                                    </button>

                                    @if ($rekamanAktifId)
                                        <button type="button" wire:click="hapusRekaman({{ $rekamanAktifId }})"
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-rose-300 bg-white px-3.5 py-2 text-[12px] font-semibold text-rose-700 hover:bg-rose-50">
                                            <x-senat.icon name="trash" class="h-3.5 w-3.5" /> Hapus Rekaman
                                        </button>
                                    @endif
                                </div>

                                @if ($rekamanTranskrip !== '')
                                    <details class="mt-3">
                                        <summary class="cursor-pointer text-[11.5px] font-semibold text-senat-muted hover:text-senat-ink">Lihat transkrip mentah</summary>
                                        <div class="mt-2 max-h-[240px] overflow-y-auto whitespace-pre-wrap rounded-lg border border-senat-border bg-white p-3 text-[12px] leading-relaxed text-senat-body">{{ $rekamanTranskrip }}</div>
                                    </details>
                                @endif
                            </div>
                        @endif

                        {{-- Riwayat rekaman rapat ini --}}
                        @if (!empty($rekamanList))
                            <div class="mt-4 border-t border-senat-sidebarLine pt-3">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-senat-muted">Riwayat Rekaman Rapat Ini</p>
                                <ul class="mt-2 space-y-1.5">
                                    @foreach ($rekamanList as $rk)
                                        <li class="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-senat-border bg-white px-3 py-2">
                                            <div class="min-w-0">
                                                <p class="truncate text-[12px] font-semibold text-senat-ink">{{ $rk['nama_asli'] }}</p>
                                                <p class="font-mono text-[10.5px] text-senat-muted">
                                                    {{ strtoupper($rk['status']) }} &middot; {{ $rk['progres'] }}%
                                                    @if ($rk['durasi'] > 0) &middot; {{ floor($rk['durasi'] / 60) }} mnt @endif
                                                    @if ($rk['id_notulensi']) &middot; notulensi #{{ $rk['id_notulensi'] }} @endif
                                                </p>
                                            </div>
                                            <button type="button" wire:click="hapusRekaman({{ $rk['id_rekaman'] }})"
                                                    class="shrink-0 rounded-md border border-senat-border px-2.5 py-1 text-[11px] font-semibold text-senat-muted hover:border-rose-300 hover:text-rose-700">
                                                Hapus
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
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
                                    <p class="line-clamp-3">{{ \Illuminate\Support\Str::of($item['isi_notulensi'])->replaceMatches('/^#{1,6}\s*/m', '')->replaceMatches('/\*\*(.+?)\*\*/', '$1')->replaceMatches('/^\s*[-*]\s+/m', '• ')->replace('|', ' ')->squish()->limit(240) }}</p>
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
                                            <div class="mt-2 rounded-xl border border-senat-border bg-slate-50/50 p-4 text-[13px] leading-relaxed text-senat-ink [&_h2]:mt-3 [&_h2]:text-[12px] [&_h2]:font-bold [&_h2]:uppercase [&_h2]:tracking-wide [&_h2]:text-senat-primary [&_h2:first-child]:mt-0 [&_li]:my-0.5 [&_ol]:my-1.5 [&_ol]:list-decimal [&_ol]:pl-5 [&_p]:my-1.5 [&_strong]:font-semibold [&_table]:my-2 [&_table]:w-full [&_td]:border-b [&_td]:border-senat-border/60 [&_td]:py-1 [&_th]:border-b [&_th]:border-senat-border [&_th]:py-1 [&_th]:text-left [&_th]:text-[11px] [&_th]:uppercase [&_ul]:my-1.5 [&_ul]:list-disc [&_ul]:pl-5">
                                                {!! \Illuminate\Support\Str::markdown($viewItem['isi_notulensi'], ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
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
