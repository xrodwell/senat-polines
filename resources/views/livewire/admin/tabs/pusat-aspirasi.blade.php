@php
                    $listAspirasi = !empty($apiAspirations) ? $apiAspirations : $aspirations;
                @endphp
                <section wire:key="screen-pusat-aspirasi" class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
                    <div class="flex items-center justify-between border-b border-senat-sidebarLine pb-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-[18px] font-bold text-senat-ink">Manajemen Aspirasi Sivitas Akademika</h2>
                                <span class="rounded-md bg-blue-50 px-2 py-0.5 text-[11px] font-mono font-bold text-senat-primary">Tersambung API Backend</span>
                            </div>
                            <p class="text-[12px] text-senat-body">Monitoring tiket aspirasi mahasiswa, dosen, dan tendik</p>
                        </div>
                        <button type="button" wire:click="loadAspirationsFromApi" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-[12px] font-semibold text-senat-ink hover:bg-slate-50 transition">
                            🔄 Refresh Data API
                        </button>
                    </div>
                    <div class="mt-4 space-y-4">
                        @foreach ($listAspirasi as $asp)
                            <div wire:key="aspirasi-card-{{ $asp['id'] }}" class="rounded-[12px] border border-senat-sidebarLine bg-senat-surface p-4 transition hover:border-senat-badgeLine">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-[6px] bg-senat-badgeBg px-2 py-0.5 text-[11px] font-mono font-bold text-senat-ink">
                                            {{ $asp['ticket_code'] }}
                                        </span>
                                        <span class="text-[12px] font-semibold text-senat-ink">{{ $asp['sender_category'] }} ({{ $asp['jurusan'] }})</span>
                                    </div>
                                    {{-- Tombol Ubah Status Langsung --}}
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[11px] font-semibold text-senat-muted mr-1">Status:</span>
                                        <button type="button" wire:key="status-pending-{{ $asp['id'] }}" wire:click="setAspirasiStatus({{ $asp['id'] }}, 'pending')"
                                                class="rounded px-2.5 py-1 text-[11px] font-semibold transition {{ ($asp['raw_status'] ?? '') === 'pending' ? 'bg-amber-100 text-amber-800 font-bold ring-1 ring-amber-300' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">Pending</button>
                                        <button type="button" wire:key="status-diproses-{{ $asp['id'] }}" wire:click="setAspirasiStatus({{ $asp['id'] }}, 'diproses')"
                                                class="rounded px-2.5 py-1 text-[11px] font-semibold transition {{ ($asp['raw_status'] ?? '') === 'diproses' ? 'bg-blue-100 text-blue-800 font-bold ring-1 ring-blue-300' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">Diproses</button>
                                        <button type="button" wire:key="status-selesai-{{ $asp['id'] }}" wire:click="setAspirasiStatus({{ $asp['id'] }}, 'selesai')"
                                                class="rounded px-2.5 py-1 text-[11px] font-semibold transition {{ ($asp['raw_status'] ?? '') === 'selesai' ? 'bg-emerald-100 text-emerald-800 font-bold ring-1 ring-emerald-300' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">Selesai</button>
                                    </div>
                                </div>
                                <h3 class="mt-2.5 text-[14px] font-bold text-senat-ink">{{ $asp['subject'] }}</h3>
                                <p class="mt-1 text-[12.5px] text-senat-body leading-relaxed">{{ $asp['description'] }}</p>
                                
                                {{-- Kotak Tanggapan Resmi --}}
                                <div class="mt-3 rounded-[10px] bg-white p-3 border border-senat-sidebarLine text-[12px]">
                                    <span class="font-bold text-senat-ink">Tanggapan Komisi Senat:</span>
                                    <p class="text-senat-body mt-0.5 leading-relaxed">{{ $asp['response'] }}</p>
                                </div>

                                {{-- Form Input Balasan (Muncul saat tombol diklik) --}}
                                @if ($selectedAspirasiId == $asp['id'])
                                    <form wire:submit.prevent="submitTanggapan({{ $asp['id'] }})" wire:key="tanggapan-form-box-{{ $asp['id'] }}" class="mt-3 rounded-[10px] bg-blue-50/70 p-3.5 border border-blue-200">
                                        <label class="text-[11.5px] font-bold text-senat-ink">Tulis Tanggapan Resmi Baru:</label>
                                        <textarea wire:model="tanggapanText" rows="3" placeholder="Ketik tindak lanjut atau jawaban komisi di sini..."
                                                  class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white p-2.5 text-[12px] text-slate-900 focus:border-senat-primary focus:outline-none" required></textarea>
                                        <div class="mt-2.5 flex justify-end gap-2">
                                            <button type="button" wire:click="openTanggapanForm({{ $asp['id'] }})"
                                                    class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-[11.5px] font-medium text-slate-700 hover:bg-slate-50">Batal</button>
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1.5 rounded-md bg-senat-primary px-3.5 py-1.5 text-[11.5px] font-semibold text-white shadow-xs transition hover:bg-senat-primaryHover">
                                                <span wire:loading.remove wire:target="submitTanggapan">Kirim Tanggapan ke Database</span>
                                                <span wire:loading wire:target="submitTanggapan">Menyimpan...</span>
                                            </button>
                                        </div>
                                    </form>
                                @else
                                    <div wire:key="tanggapan-btn-box-{{ $asp['id'] }}" class="mt-3 flex justify-end">
                                        <button type="button"
                                                wire:click="openTanggapanForm({{ $asp['id'] }})"
                                                class="inline-flex items-center gap-1.5 rounded-md bg-senat-primary px-3.5 py-1.5 text-[11.5px] font-semibold text-white shadow-xs transition hover:bg-senat-primaryHover">
                                            <span>✍️</span>
                                            <span>Tindak Lanjuti / Balas Aspirasi</span>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>
