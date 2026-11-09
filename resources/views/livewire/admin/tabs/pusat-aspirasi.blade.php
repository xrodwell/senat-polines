@php
    $listAspirasi = $this->filteredAspirations;
    $stats = $this->aspirationStats;
@endphp
<section wire:key="screen-pusat-aspirasi" class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-senat-sidebarLine pb-4">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-[18px] font-bold text-senat-ink">Pusat Aspirasi & Disposisi Komisi</h2>
                <span class="rounded-md bg-emerald-50 px-2 py-0.5 text-[11px] font-mono font-bold text-emerald-700 border border-emerald-200">
                    ● Tersambung Database Backend
                </span>
            </div>
            <p class="text-[12px] text-senat-body">Penyerapan, telaah, dan disposisi aspirasi sivitas akademika ke komisi terkait</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" wire:click="loadAspirationsFromApi"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-[12px] font-semibold text-senat-ink shadow-xs hover:bg-slate-50 transition">
                <span wire:loading.remove wire:target="loadAspirationsFromApi">🔄 Refresh Data</span>
                <span wire:loading wire:target="loadAspirationsFromApi">Memuat...</span>
            </button>
        </div>
    </div>

    {{-- Filter Bar: Status, Komisi & Pencarian --}}
    <div class="mt-4 space-y-3 rounded-[12px] bg-slate-50/70 p-3.5 border border-slate-200">
        {{-- Baris 1: Pencarian & Filter Status --}}
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            {{-- Kotak Pencarian --}}
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400 text-xs">
                    🔍
                </span>
                <input type="text"
                       wire:model.live.debounce.300ms="aspirationSearch"
                       placeholder="Cari nomor tiket, judul, isi, pengirim, atau jurusan..."
                       class="w-full rounded-lg border border-slate-300 bg-white pl-8 pr-3 py-1.5 text-[12px] text-slate-900 placeholder:text-slate-400 focus:border-senat-primary focus:outline-none focus:ring-1 focus:ring-senat-primary">
                @if(!empty($aspirationSearch))
                    <button type="button" wire:click="$set('aspirationSearch', '')"
                            class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400 hover:text-slate-600 text-xs">
                        ✕
                    </button>
                @endif
            </div>

            {{-- Filter Status --}}
            <div class="flex items-center gap-1 shrink-0 overflow-x-auto">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mr-1 hidden md:inline">Status:</span>
                <button type="button" wire:click="setFilterStatus('all')"
                        class="rounded-full px-2.5 py-1 text-[11px] font-semibold transition {{ $aspirationFilterStatus === 'all' ? 'bg-senat-primary text-white shadow-xs' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100' }}">
                    Semua ({{ $stats['total'] }})
                </button>
                <button type="button" wire:click="setFilterStatus('pending')"
                        class="rounded-full px-2.5 py-1 text-[11px] font-semibold transition {{ $aspirationFilterStatus === 'pending' ? 'bg-amber-600 text-white shadow-xs' : 'bg-white text-amber-700 border border-amber-200 hover:bg-amber-50' }}">
                    Pending ({{ $stats['pending'] }})
                </button>
                <button type="button" wire:click="setFilterStatus('diproses')"
                        class="rounded-full px-2.5 py-1 text-[11px] font-semibold transition {{ $aspirationFilterStatus === 'diproses' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white text-blue-700 border border-blue-200 hover:bg-blue-50' }}">
                    Diproses ({{ $stats['diproses'] }})
                </button>
                <button type="button" wire:click="setFilterStatus('selesai')"
                        class="rounded-full px-2.5 py-1 text-[11px] font-semibold transition {{ $aspirationFilterStatus === 'selesai' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-emerald-700 border border-emerald-200 hover:bg-emerald-50' }}">
                    Selesai ({{ $stats['selesai'] }})
                </button>
            </div>
        </div>

        {{-- Baris 2: Pengelompokan Tab Disposisi Komisi --}}
        <div>
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-thin">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mr-1 shrink-0">Disposisi Komisi:</span>
                <button type="button" wire:click="setFilterKomisi('all')"
                        class="shrink-0 rounded-lg px-2.5 py-1 text-[11.5px] font-semibold transition {{ $aspirationFilterKomisi === 'all' ? 'bg-[#003c75] text-white shadow-xs' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-100' }}">
                    Semua Komisi
                </button>
                <button type="button" wire:click="setFilterKomisi('1')"
                        class="shrink-0 rounded-lg px-2.5 py-1 text-[11.5px] font-semibold transition {{ $aspirationFilterKomisi === '1' ? 'bg-blue-700 text-white shadow-xs' : 'bg-white text-blue-800 border border-blue-200 hover:bg-blue-50' }}">
                    Komisi I (Pendidikan) <span class="ml-1 opacity-75">({{ $stats['komisi_1'] }})</span>
                </button>
                <button type="button" wire:click="setFilterKomisi('2')"
                        class="shrink-0 rounded-lg px-2.5 py-1 text-[11.5px] font-semibold transition {{ $aspirationFilterKomisi === '2' ? 'bg-emerald-700 text-white shadow-xs' : 'bg-white text-emerald-800 border border-emerald-200 hover:bg-emerald-50' }}">
                    Komisi II (Penelitian/Sarana) <span class="ml-1 opacity-75">({{ $stats['komisi_2'] }})</span>
                </button>
                <button type="button" wire:click="setFilterKomisi('3')"
                        class="shrink-0 rounded-lg px-2.5 py-1 text-[11.5px] font-semibold transition {{ $aspirationFilterKomisi === '3' ? 'bg-purple-700 text-white shadow-xs' : 'bg-white text-purple-800 border border-purple-200 hover:bg-purple-50' }}">
                    Komisi III (SDM & Kemahasiswaan) <span class="ml-1 opacity-75">({{ $stats['komisi_3'] }})</span>
                </button>
                <button type="button" wire:click="setFilterKomisi('4')"
                        class="shrink-0 rounded-lg px-2.5 py-1 text-[11.5px] font-semibold transition {{ $aspirationFilterKomisi === '4' ? 'bg-amber-700 text-white shadow-xs' : 'bg-white text-amber-800 border border-amber-200 hover:bg-amber-50' }}">
                    Komisi IV (Kerja Sama) <span class="ml-1 opacity-75">({{ $stats['komisi_4'] }})</span>
                </button>
                <button type="button" wire:click="setFilterKomisi('5')"
                        class="shrink-0 rounded-lg px-2.5 py-1 text-[11.5px] font-semibold transition {{ $aspirationFilterKomisi === '5' ? 'bg-rose-700 text-white shadow-xs' : 'bg-white text-rose-800 border border-rose-200 hover:bg-rose-50' }}">
                    Komisi V (Etik) <span class="ml-1 opacity-75">({{ $stats['komisi_5'] }})</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Daftar Kartu Aspirasi --}}
    <div class="mt-4 space-y-4">
        @forelse ($listAspirasi as $asp)
            <div wire:key="aspirasi-card-{{ $asp['id'] }}" class="rounded-[12px] border border-senat-sidebarLine bg-senat-surface p-4 transition hover:border-senat-badgeLine shadow-xs">
                {{-- Header Kartu --}}
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200/80 pb-2.5">
                    <div class="flex flex-wrap items-center gap-2">
                        {{-- Kode Tiket --}}
                        <span class="rounded-[6px] bg-senat-badgeBg px-2 py-0.5 text-[11.5px] font-mono font-bold text-senat-ink border border-senat-badgeLine/50">
                            {{ $asp['ticket_code'] }}
                        </span>

                        {{-- Badge Disposisi Komisi --}}
                        <span class="rounded-[6px] px-2.5 py-0.5 text-[11px] font-semibold border {{ $asp['committee_badge'] ?? 'bg-blue-50 text-blue-700 border-blue-200' }}">
                            🎯 {{ $asp['committee_name'] ?? 'Komisi I — Bidang Pendidikan' }}
                        </span>

                        {{-- Waktu Masuk --}}
                        <span class="text-[11px] text-senat-muted">
                            🕒 {{ $asp['submitted_at'] }}
                        </span>
                    </div>

                    {{-- Tombol Ubah Status Langsung --}}
                    <div class="flex items-center gap-1.5">
                        <span class="text-[11px] font-semibold text-senat-muted mr-1">Status:</span>
                        <button type="button" wire:key="status-pending-{{ $asp['id'] }}" wire:click="setAspirasiStatus({{ $asp['id'] }}, 'pending')"
                                class="rounded px-2.5 py-1 text-[11px] font-semibold transition {{ ($asp['raw_status'] ?? '') === 'pending' ? 'bg-amber-100 text-amber-800 font-bold ring-1 ring-amber-300' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
                            Pending
                        </button>
                        <button type="button" wire:key="status-diproses-{{ $asp['id'] }}" wire:click="setAspirasiStatus({{ $asp['id'] }}, 'diproses')"
                                class="rounded px-2.5 py-1 text-[11px] font-semibold transition {{ ($asp['raw_status'] ?? '') === 'diproses' ? 'bg-blue-100 text-blue-800 font-bold ring-1 ring-blue-300' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
                            Diproses
                        </button>
                        <button type="button" wire:key="status-selesai-{{ $asp['id'] }}" wire:click="setAspirasiStatus({{ $asp['id'] }}, 'selesai')"
                                class="rounded px-2.5 py-1 text-[11px] font-semibold transition {{ ($asp['raw_status'] ?? '') === 'selesai' ? 'bg-emerald-100 text-emerald-800 font-bold ring-1 ring-emerald-300' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
                            Selesai
                        </button>
                    </div>
                </div>

                {{-- Baris Identitas Pengirim & Jurusan Asli --}}
                <div class="mt-2.5 flex flex-wrap items-center justify-between gap-2 text-[12px]">
                    <div class="flex items-center gap-2 text-senat-ink font-medium">
                        <span>👤 <strong>{{ $asp['sender_name'] }}</strong></span>
                        @if(!empty($asp['sender_email']) && $asp['sender_email'] !== '-')
                            <span class="text-senat-muted font-normal">({{ $asp['sender_email'] }})</span>
                        @endif
                        <span class="text-slate-300">•</span>
                        <span class="rounded bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-700">Jurusan: {{ $asp['jurusan'] }}</span>
                    </div>

                    {{-- Tombol Alihkan / Ubah Disposisi Komisi --}}
                    <div class="relative">
                        <button type="button" wire:click="toggleDisposisiDropdown({{ $asp['id'] }})"
                                class="inline-flex items-center gap-1 rounded-md border border-slate-300 bg-white px-2 py-1 text-[11px] font-semibold text-slate-700 hover:bg-slate-100 transition shadow-2xs">
                            <span>🔀</span>
                            <span>Alihkan Disposisi</span>
                        </button>

                        @if($editingDisposisiId === $asp['id'])
                            <div class="absolute right-0 top-full z-30 mt-1.5 w-64 rounded-xl border border-slate-300 bg-white p-2 shadow-xl text-left">
                                <p class="px-2 py-1 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Disposisikan ke:</p>
                                <div class="space-y-1 mt-1">
                                    <button type="button" wire:click="setAspirasiDisposisi({{ $asp['id'] }}, 1)"
                                            class="w-full text-left rounded-lg px-2.5 py-1.5 text-[11.5px] hover:bg-blue-50 text-slate-800 font-medium">
                                        Komisi I — Bidang Pendidikan & Pengajaran
                                    </button>
                                    <button type="button" wire:click="setAspirasiDisposisi({{ $asp['id'] }}, 2)"
                                            class="w-full text-left rounded-lg px-2.5 py-1.5 text-[11.5px] hover:bg-emerald-50 text-slate-800 font-medium">
                                        Komisi II — Penelitian & Pengabdian (Sarana)
                                    </button>
                                    <button type="button" wire:click="setAspirasiDisposisi({{ $asp['id'] }}, 3)"
                                            class="w-full text-left rounded-lg px-2.5 py-1.5 text-[11.5px] hover:bg-purple-50 text-slate-800 font-medium">
                                        Komisi III — Kelembagaan & SDM
                                    </button>
                                    <button type="button" wire:click="setAspirasiDisposisi({{ $asp['id'] }}, 4)"
                                            class="w-full text-left rounded-lg px-2.5 py-1.5 text-[11.5px] hover:bg-amber-50 text-slate-800 font-medium">
                                        Komisi IV — Pengembangan & Kerja Sama
                                    </button>
                                    <button type="button" wire:click="setAspirasiDisposisi({{ $asp['id'] }}, 5)"
                                            class="w-full text-left rounded-lg px-2.5 py-1.5 text-[11.5px] hover:bg-rose-50 text-slate-800 font-medium">
                                        Komisi V — Komisi Etik
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Judul dan Isi Keluhan Bersih --}}
                <div class="mt-2.5">
                    <h3 class="text-[14px] font-bold text-senat-ink">{{ $asp['subject'] }}</h3>
                    <p class="mt-1 text-[12.5px] text-senat-body leading-relaxed whitespace-pre-line">{{ $asp['description'] }}</p>
                </div>
                
                {{-- Kotak Tanggapan Resmi Komisi --}}
                <div class="mt-3 rounded-[10px] bg-white p-3 border border-senat-sidebarLine text-[12px] shadow-2xs">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-senat-ink flex items-center gap-1.5">
                            <span>🏛️</span>
                            <span>Tanggapan Resmi {{ $asp['committee_code'] ?? 'Komisi' }}:</span>
                        </span>
                    </div>
                    <p class="text-senat-body mt-1 leading-relaxed">{{ $asp['response'] }}</p>
                </div>

                {{-- Form Input Balasan / Tindak Lanjut --}}
                @if ($selectedAspirasiId == $asp['id'])
                    <form wire:submit.prevent="submitTanggapan({{ $asp['id'] }})" wire:key="tanggapan-form-box-{{ $asp['id'] }}" class="mt-3 rounded-[10px] bg-blue-50/80 p-4 border border-blue-200">
                        <div class="flex items-center justify-between">
                            <label class="text-[12px] font-bold text-senat-ink">Tulis Tanggapan Resmi Komisi:</label>
                            <span class="text-[11px] text-senat-muted">Akan langsung tersimpan ke database & tampil di pelacak tiket</span>
                        </div>

                        {{-- Tombol Template Cepat --}}
                        <div class="mt-2 flex flex-wrap items-center gap-1.5">
                            <span class="text-[11px] text-slate-500 font-medium">Template Cepat:</span>
                            <button type="button" wire:click="setQuickTemplate('Aspirasi telah diterima dan dijadwalkan dalam rapat telaah Komisi Senat.')"
                                    class="rounded bg-white border border-slate-300 px-2 py-0.5 text-[10.5px] text-slate-700 hover:bg-slate-100">
                                Diterima & Dijadwalkan Rapat
                            </button>
                            <button type="button" wire:click="setQuickTemplate('Telah dikoordinasikan dengan jurusan terkait untuk peninjauan teknis lapangan.')"
                                    class="rounded bg-white border border-slate-300 px-2 py-0.5 text-[10.5px] text-slate-700 hover:bg-slate-100">
                                Koordinasi Teknis Jurusan
                            </button>
                            <button type="button" wire:click="setQuickTemplate('Rekomendasi tindak lanjut telah disetujui dan diteruskan kepada Pimpinan Senat.')"
                                    class="rounded bg-white border border-slate-300 px-2 py-0.5 text-[10.5px] text-slate-700 hover:bg-slate-100">
                                Diteruskan ke Pimpinan
                            </button>
                        </div>

                        {{-- Textarea --}}
                        <textarea wire:model="tanggapanText" rows="3" placeholder="Ketik tindak lanjut atau jawaban resmi komisi di sini..."
                                  class="mt-2 w-full rounded-lg border border-slate-300 bg-white p-2.5 text-[12px] text-slate-900 focus:border-senat-primary focus:outline-none" required></textarea>

                        {{-- Pilihan Status Setelah Tanggapan --}}
                        <div class="mt-2.5 flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-3 text-[12px]">
                                <span class="text-slate-600 font-medium">Perbarui status ke:</span>
                                <label class="inline-flex items-center gap-1 cursor-pointer">
                                    <input type="radio" wire:model="tanggapanStatusChoice" value="diproses" class="text-senat-primary">
                                    <span>Sedang Ditelaah / Diproses</span>
                                </label>
                                <label class="inline-flex items-center gap-1 cursor-pointer">
                                    <input type="radio" wire:model="tanggapanStatusChoice" value="selesai" class="text-senat-primary">
                                    <span>Selesai / Ditindaklanjuti</span>
                                </label>
                            </div>

                            <div class="flex justify-end gap-2">
                                <button type="button" wire:click="openTanggapanForm({{ $asp['id'] }})"
                                        class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-[11.5px] font-medium text-slate-700 hover:bg-slate-100">
                                    Batal
                                </button>
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 rounded-md bg-senat-primary px-3.5 py-1.5 text-[11.5px] font-semibold text-white shadow-xs transition hover:bg-senat-primaryHover">
                                    <span wire:loading.remove wire:target="submitTanggapan">Kirim Tanggapan ke Database</span>
                                    <span wire:loading wire:target="submitTanggapan">Menyimpan ke Backend...</span>
                                </button>
                            </div>
                        </div>
                    </form>
                @else
                    <div wire:key="tanggapan-btn-box-{{ $asp['id'] }}" class="mt-3 flex justify-end">
                        <button type="button"
                                wire:click="openTanggapanForm({{ $asp['id'] }})"
                                class="inline-flex items-center gap-1.5 rounded-md bg-senat-primary px-3.5 py-1.5 text-[11.5px] font-semibold text-white shadow-xs transition hover:bg-senat-primaryHover">
                            <span>✍️</span>
                            <span>Tindak Lanjuti / Beri Tanggapan Resmi</span>
                        </button>
                    </div>
                @endif
            </div>
        @empty
            <div class="rounded-xl border border-dashed border-slate-300 p-8 text-center bg-slate-50/50">
                <span class="text-3xl">📭</span>
                <h4 class="mt-2 text-sm font-bold text-senat-ink">Tidak ada aspirasi yang cocok</h4>
                <p class="mt-1 text-xs text-senat-muted">Coba ubah kata kunci pencarian atau pilih filter komisi / status lainnya.</p>
                @if($aspirationFilterKomisi !== 'all' || $aspirationFilterStatus !== 'all' || !empty($aspirationSearch))
                    <button type="button" wire:click="$set('aspirationFilterKomisi', 'all'); $set('aspirationFilterStatus', 'all'); $set('aspirationSearch', '');"
                            class="mt-3 inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-1 text-xs font-semibold text-senat-primary hover:bg-slate-50">
                        Reset Semua Filter
                    </button>
                @endif
            </div>
        @endforelse
    </div>
</section>
