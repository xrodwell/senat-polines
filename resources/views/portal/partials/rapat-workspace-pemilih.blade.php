            {{-- ================= MODUL 0: PEMILIH RAPAT (GERBANG AKSES) ================= --}}
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden">
                <div class="bg-slate-50 border-b border-slate-200 px-6 py-4 flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800">Langkah 1 &mdash; Pilih Rapat</h2>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-mono font-semibold uppercase tracking-wide px-2 py-0.5 rounded border"
                              :class="apiOnline ? 'bg-blue-50 text-polines-navy border-blue-300' : 'bg-slate-100 text-slate-500 border-slate-300'"
                              x-text="apiOnline ? 'TERHUBUNG SERVER' : 'MODE LOKAL'"></span>
                        <span class="text-[11px] font-mono font-semibold uppercase tracking-wide px-2 py-0.5 rounded border"
                              :class="selectedMeetingId ? 'bg-emerald-50 text-emerald-700 border-emerald-300' : 'bg-amber-50 text-amber-700 border-amber-300'"
                              x-text="selectedMeetingId ? 'RAPAT TERPILIH' : 'BELUM DIPILIH'"></span>
                    </div>
                </div>
                <div class="p-5 sm:p-6">
                    <p class="text-xs text-slate-500 leading-relaxed mb-4">
                        Catatan risalah, unggahan dokumen, dan hak suara e-voting terikat pada satu rapat.
                        Pilih rapat di bawah ini untuk membuka modul tersebut.
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <template x-for="m in meetings" :key="m.id">
                            <button type="button" @click="selectMeeting(m)"
                                    class="text-left rounded-xl border-2 p-4 transition-all"
                                    :class="selectedMeetingId === m.id ? 'border-polines-navy bg-blue-50/60 shadow-sm' : 'border-slate-200 bg-white hover:border-slate-300'">
                                <div class="flex items-center justify-between gap-2 mb-1.5">
                                    <span class="text-[10px] font-bold uppercase tracking-wide px-2 py-0.5 rounded bg-slate-100 text-slate-700" x-text="m.type"></span>
                                    <span class="text-[10px] font-semibold flex items-center gap-1"
                                          :class="m.status === 'Sedang Berlangsung' ? 'text-emerald-700' : 'text-slate-500'">
                                        <span class="w-1.5 h-1.5 rounded-full"
                                              :class="m.status === 'Sedang Berlangsung' ? 'bg-emerald-500' : 'bg-slate-400'"></span>
                                        <span x-text="m.status"></span>
                                    </span>
                                </div>
                                <h4 class="text-xs font-bold text-slate-900 leading-snug" x-text="m.title"></h4>
                                <p class="text-[11px] text-slate-500 mt-2 tabular-nums">
                                    <span x-text="m.date"></span> &bull; <span x-text="m.time"></span>
                                </p>
                                <p class="text-[11px] text-slate-500 mt-0.5" x-text="m.room"></p>
                                <p class="mt-2 text-[11px] font-bold"
                                   :class="selectedMeetingId === m.id ? 'text-polines-navy' : 'text-slate-400'"
                                   x-text="selectedMeetingId === m.id ? '✓ Rapat ini dipilih' : 'Pilih rapat ini →'"></p>
                            </button>
                        </template>
                    </div>
                </div>
            </div>

