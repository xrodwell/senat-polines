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
