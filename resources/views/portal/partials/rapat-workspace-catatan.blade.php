            {{-- ================= MODUL 3: CATATAN & NOTULENSI (TEKS EDITOR + LAMPIRAN + EXPORT) ================= --}}
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden">
                <div class="bg-slate-50 border-b border-slate-200 px-6 py-4 flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800">Catatan &amp; Notulensi Rapat</h2>
                    <span class="text-[11px] text-slate-500 truncate max-w-full"
                          x-text="selectedMeeting ? selectedMeeting.type + ' — ' + selectedMeeting.title : 'Belum ada rapat dipilih'"></span>
                </div>

                {{-- Terkunci sampai rapat dipilih --}}
                <div x-show="!selectedMeetingId" class="p-6 sm:p-8">
                    <div class="rounded-xl border border-dashed border-amber-300 bg-amber-50/60 p-6 text-center">
                        <div class="w-10 h-10 rounded-xl bg-white border border-amber-300 text-amber-700 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75M6.75 10.5h10.5a1.5 1.5 0 011.5 1.5v7.5a1.5 1.5 0 01-1.5 1.5H6.75a1.5 1.5 0 01-1.5-1.5V12a1.5 1.5 0 011.5-1.5z" />
                            </svg>
                        </div>
                        <p class="text-sm font-bold text-slate-900">Catatan risalah belum dapat diisi</p>
                        <p class="text-xs text-slate-600 mt-1 max-w-md mx-auto leading-relaxed">
                            Pilih rapat terlebih dahulu pada modul <strong>Langkah 1 &mdash; Pilih Rapat</strong> di atas
                            agar catatan dan lampiran tersimpan pada rapat yang benar.
                        </p>
                    </div>
                </div>

                <div x-show="selectedMeetingId" x-cloak class="p-6 sm:p-8">
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900">Catatan Risalah Musyawarah</h3>
                            <p class="text-[11px] text-slate-500 mt-0.5">
                                Poin telaah, argumentasi pasal, atau pertimbangan norma yang Anda sampaikan selama sidang.
                            </p>
                        </div>
                        <span class="text-[10px] font-mono text-slate-400 uppercase"
                              x-text="selectedMeetingId ? 'ID RAPAT: ' + selectedMeetingId : ''"></span>
                    </div>

                    {{-- Toolbar editor teks --}}
                    <div class="flex flex-wrap items-center gap-1 rounded-t-xl border border-slate-300 bg-slate-100/70 px-2 py-1.5">
                        <template x-for="tool in editorTools" :key="tool.cmd + (tool.value || '')">
                            <button type="button"
                                    @mousedown.prevent="format(tool.cmd, tool.value)"
                                    :title="tool.title"
                                    class="h-7 min-w-7 px-2 rounded text-[11px] font-bold text-slate-700 border border-transparent hover:bg-white hover:text-polines-navy hover:border-slate-300 transition-colors"
                                    x-text="tool.label"></button>
                        </template>
                        <span class="ml-auto text-[10px] text-slate-400 font-mono pr-1" x-text="wordCount + ' kata'"></span>
                    </div>

                    <div x-ref="editor"
                         contenteditable="true"
                         role="textbox"
                         aria-multiline="true"
                         aria-label="Editor catatan risalah"
                         data-placeholder="Tulis poin telaah, argumentasi pasal, atau pertimbangan norma di sini..."
                         @input="syncNote()"
                         @paste="onPaste($event)"
                         class="min-h-[190px] rounded-b-xl border border-slate-300 bg-white px-4 py-3 text-sm leading-relaxed text-slate-800 outline-none transition-all focus:border-polines-blue focus:ring-1 focus:ring-polines-blue [&_ul]:list-disc [&_ul]:pl-6 [&_ol]:list-decimal [&_ol]:pl-6 [&_h3]:mt-2 [&_h3]:text-base [&_h3]:font-bold [&_blockquote]:border-l-2 [&_blockquote]:border-slate-300 [&_blockquote]:pl-3 [&_blockquote]:italic [&_blockquote]:text-slate-600 [&:empty]:before:content-[attr(data-placeholder)] [&:empty]:before:text-slate-400"></div>

                    {{-- Lampiran notulensi (unggah berkas) --}}
                    <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50/60 p-4">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700">Lampiran Notulensi</h4>
                                <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                                    Unggah draf risalah, daftar hadir, bukti dokumen, atau rekaman rapat (PDF, DOCX, XLSX, gambar, audio/video &mdash; termasuk rekaman WhatsApp .opus / .mp4 &mdash; maks. 1 GB/berkas).
                                </p>
                            </div>
                            <label class="shrink-0 cursor-pointer inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-100 px-3.5 py-2 text-xs font-bold text-slate-700 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                Unggah Berkas
                                <input type="file" class="hidden" multiple
                                       accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.jpg,.jpeg,.png,.mp3,.m4a,.aac,.ogg,.opus,.wav,.mp4,.mov,.mkv,.webm,.3gp,audio/*,video/*"
                                       @change="addAttachments($event)">
                            </label>
                        </div>

                        <p class="text-[11px] text-slate-400 mt-3" x-show="attachments.length === 0">
                            Belum ada berkas dilampirkan pada notulensi rapat ini.
                        </p>

                        <ul class="mt-3 space-y-2">
                            <template x-for="(f, i) in attachments" :key="f.id">
                                <li class="flex items-center justify-between gap-3 rounded-lg border border-slate-200 bg-white px-3 py-2">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <span class="shrink-0 w-7 h-7 rounded border border-slate-200 bg-slate-50 text-[9px] font-bold text-slate-600 flex items-center justify-center uppercase"
                                              x-text="extOf(f.name)"></span>
                                        <div class="min-w-0">
                                            <p class="text-xs font-semibold text-slate-800 truncate" x-text="f.name"></p>
                                            <p class="text-[10px] text-slate-500 font-mono" x-text="formatBytes(f.size) + ' · diunggah ' + f.time"></p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1 shrink-0">
                                        <button type="button" @click="downloadAttachment(i)" class="text-[11px] font-bold text-polines-navy hover:underline px-2 py-1">Unduh</button>
                                        <button type="button" @click="removeAttachment(i)" class="text-[11px] font-bold text-rose-600 hover:underline px-2 py-1">Hapus</button>
                                    </div>
                                </li>
                            </template>
                        </ul>
                    </div>

                    {{-- Aksi simpan & export --}}
                    <p class="text-[11px] text-rose-600 mt-4" x-show="noteError" x-cloak x-text="noteError"></p>
                    <div class="flex flex-wrap items-center justify-between gap-3 mt-5 pt-4 border-t border-slate-100">
                        <div class="flex items-center gap-3 text-[11px]">
                            <span class="text-slate-500" x-text="attachments.length + ' lampiran'"></span>
                            <span class="text-emerald-600 font-semibold" x-show="savedNote" x-cloak>&#10003; Tersimpan</span>
                            <span class="text-amber-600 font-semibold" x-show="noteDirty && !savedNote">&#9679; Belum disimpan</span>
                            <span class="text-polines-navy font-semibold" x-show="menyimpan" x-cloak>Mengirim&hellip;</span>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <button type="button" @click="saveNote()"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-polines-navy hover:bg-polines-navyDark text-white px-4 py-2.5 text-xs font-bold transition-colors">
                                Simpan Catatan
                            </button>
                            <button type="button" @click="exportNote('md')"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-100 text-slate-700 px-4 py-2.5 text-xs font-bold transition-colors">
                                Export .md
                            </button>
                            <button type="button" @click="exportNote('txt')"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-100 text-slate-700 px-4 py-2.5 text-xs font-bold transition-colors">
                                Export .txt
                            </button>
                            <button type="button" @click="printNote()"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-100 text-slate-700 px-4 py-2.5 text-xs font-bold transition-colors">
                                Cetak / PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= MODUL 4: BERBAGI DOKUMEN & TAUTAN ANTAR ANGGOTA ================= --}}
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden">
                <div class="bg-slate-50 border-b border-slate-200 px-6 py-4 flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800">Berbagi Dokumen &amp; Tautan</h2>
                    <span class="text-[10px] font-mono text-slate-500 uppercase"
                          x-text="selectedMeetingId ? sharedItems.length + ' item dibagikan' : 'TERKUNCI'"></span>
                </div>

                <div x-show="!selectedMeetingId" class="p-6 sm:p-8">
                    <div class="rounded-xl border border-dashed border-amber-300 bg-amber-50/60 p-6 text-center">
                        <p class="text-sm font-bold text-slate-900">Berbagi dokumen belum tersedia</p>
                        <p class="text-xs text-slate-600 mt-1 max-w-md mx-auto leading-relaxed">
                            Dokumen dan tautan dibagikan ke peserta satu rapat. Pilih rapat terlebih dahulu.
                        </p>
                    </div>
                </div>

                <div x-show="selectedMeetingId" x-cloak class="p-6 sm:p-8">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        {{-- Bagikan berkas --}}
                        <label class="cursor-pointer rounded-xl border-2 border-dashed border-slate-300 bg-slate-50/60 hover:border-polines-blue hover:bg-blue-50/40 px-4 py-5 text-center transition-colors">
                            <svg class="w-6 h-6 mx-auto text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                            </svg>
                            <span class="block text-xs font-bold text-slate-800 mt-1.5">Bagikan Berkas</span>
                            <span class="block text-[11px] text-slate-500 mt-0.5">PDF, DOCX, XLSX, gambar</span>
                            <input type="file" class="hidden" multiple
                                   accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.jpg,.jpeg,.png,.mp3,.m4a,.aac,.ogg,.opus,.wav,.mp4,.mov,.mkv,.webm,.3gp,audio/*,video/*"
                                   @change="shareFiles($event)">
                        </label>

                        {{-- Bagikan tautan --}}
                        <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 mb-1">Bagikan Tautan</label>
                            <input type="url" x-model="shareUrl" placeholder="https://drive.polines.ac.id/..."
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-800 outline-none focus:border-polines-blue focus:ring-1 focus:ring-polines-blue">
                            <input type="text" x-model="shareTitle" placeholder="Judul tautan (opsional)"
                                   class="w-full mt-2 rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-800 outline-none focus:border-polines-blue focus:ring-1 focus:ring-polines-blue">
                            <button type="button" @click="addLink()"
                                    class="w-full mt-2 rounded-lg bg-polines-navy hover:bg-polines-navyDark text-white px-3 py-2 text-xs font-bold transition-colors">
                                Bagikan Tautan
                            </button>
                            <p class="text-[10px] text-rose-600 mt-1.5" x-show="shareError" x-cloak x-text="shareError"></p>
                        </div>
                    </div>

                    <div class="mt-5">
                        <h4 class="text-[11px] font-bold uppercase tracking-wider text-slate-700 mb-2">Dibagikan dalam Rapat Ini</h4>
                        <p class="text-[11px] text-slate-400" x-show="sharedItems.length === 0">Belum ada dokumen atau tautan dibagikan.</p>

                        <ul class="space-y-2">
                            <template x-for="(item, i) in sharedItems" :key="item.id">
                                <li class="flex items-start justify-between gap-3 rounded-lg border border-slate-200 bg-slate-50/50 px-3 py-2.5">
                                    <div class="flex items-start gap-2.5 min-w-0">
                                        <span class="shrink-0 w-7 h-7 rounded border flex items-center justify-center text-[10px] font-bold uppercase"
                                              :class="item.kind === 'link' ? 'border-blue-200 bg-blue-50 text-polines-navy' : 'border-slate-200 bg-white text-slate-600'"
                                              x-text="item.kind === 'link' ? 'URL' : extOf(item.name)"></span>
                                        <div class="min-w-0">
                                            <p class="text-xs font-semibold text-slate-800 truncate" x-text="item.name"></p>
                                            <p class="text-[10px] text-slate-500 font-mono">
                                                <span x-text="item.by"></span> · <span x-text="item.time"></span>
                                                <template x-if="item.kind === 'file'"><span x-text="' · ' + formatBytes(item.size)"></span></template>
                                            </p>
                                            <p class="text-[10px] text-slate-400 truncate" x-show="item.kind === 'link'" x-text="item.url"></p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1 shrink-0">
                                        <button type="button" x-show="item.kind === 'file'" @click="downloadShared(item)"
                                                class="text-[11px] font-bold text-polines-navy hover:underline px-2 py-1">Unduh</button>
                                        <button type="button" x-show="item.kind === 'link'" @click="openShared(item)"
                                                class="text-[11px] font-bold text-polines-navy hover:underline px-2 py-1">Buka</button>
                                        <button type="button" x-show="item.mine" @click="removeShared(i)"
                                                class="text-[11px] font-bold text-rose-600 hover:underline px-2 py-1">Hapus</button>
                                    </div>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>
            </div>

