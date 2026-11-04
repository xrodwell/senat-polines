<div wire:key="screen-profil-struktur" class="space-y-6">
                    
                    {{-- Form Visi & Misi --}}
                    <div class="rounded-xl border border-slate-200/80 bg-white p-6 shadow-xs">
                        <h2 class="text-[16px] font-bold text-slate-900">Manajemen Dokumen Profil & Struktur Senat</h2>
                        <p class="text-[12px] text-slate-500">Kelola visi, misi, landasan hukum, dan diagram struktur organisasi</p>
                        <div class="mt-5 space-y-4">
                            <div>
                                <label class="text-[11.5px] font-semibold text-slate-700">Visi Senat Akademik Polines</label>
                                <textarea rows="2" class="mt-1 w-full rounded-lg border border-slate-300 p-2.5 text-[12px] text-slate-900">Menjadi pilar tata pamong normatif akademik yang kredibel, transparan, dan inovatif dalam mewujudkan Politeknik bertaraf internasional.</textarea>
                            </div>
                            <div>
                                <label class="text-[11.5px] font-semibold text-slate-700">Misi Utama</label>
                                <textarea rows="3" class="mt-1 w-full rounded-lg border border-slate-300 p-2.5 text-[12px] text-slate-900">1. Merumuskan kebijakan akademik yang relevan dengan transformasi industri vokasi.&#10;2. Melakukan pengawasan independen terhadap mutu pembelajaran dan integritas riset.&#10;3. Menyalurkan aspirasi sivitas akademika secara akuntabel.</textarea>
                            </div>
                            <button type="button" wire:click="saveAction('Profil & Tupoksi')"
                                    class="rounded-lg bg-senat-primary px-4 py-2 text-[12px] font-semibold text-white hover:bg-senat-primaryHover">
                                Simpan Perubahan Profil
                            </button>
                        </div>
                    </div>
                    {{-- Form Pimpinan Kolektif Senat (Tersambung API Backend) --}}
                    <div class="rounded-xl border border-slate-200/80 bg-white p-6 shadow-xs">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                            <div>
                                <h2 class="text-[16px] font-bold text-slate-900">Pimpinan Kolektif Senat (Tingkat I)</h2>
                                <p class="text-[12px] text-slate-500">Kelola nama dan NIP Ketua &amp; Sekretaris Senat langsung ke database Backend</p>
                            </div>
                            <span class="rounded-md bg-blue-50 px-2.5 py-1 text-[11px] font-mono font-bold text-senat-primary">Tersambung API Backend</span>
                        </div>
                        <div class="grid gap-5 md:grid-cols-2">
                            {{-- Form Ketua Senat --}}
                            <div class="rounded-lg border border-slate-200 bg-slate-50/60 p-4">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Jabatan: Ketua Senat</span>
                                <div class="mt-3 space-y-3">
                                    <div>
                                        <label class="text-[11.5px] font-semibold text-slate-700">Nama Lengkap &amp; Gelar</label>
                                        <input type="text" wire:model="ketuaNama" placeholder="Contoh: Dr. Sukamto, S.Kom., M.T."
                                               class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-[12.5px] font-semibold text-slate-900 focus:border-senat-primary focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="text-[11.5px] font-semibold text-slate-700">NIP / NIM</label>
                                        <input type="text" wire:model="ketuaNip" placeholder="Contoh: 196803121993031002"
                                               class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-[12px] font-mono text-slate-900 focus:border-senat-primary focus:outline-none">
                                    </div>
                                </div>
                            </div>
                            {{-- Form Sekretaris Senat --}}
                            <div class="rounded-lg border border-slate-200 bg-slate-50/60 p-4">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Jabatan: Sekretaris Senat</span>
                                <div class="mt-3 space-y-3">
                                    <div>
                                        <label class="text-[11.5px] font-semibold text-slate-700">Nama Lengkap &amp; Gelar</label>
                                        <input type="text" wire:model="sekretarisNama" placeholder="Contoh: Andi Setyawan, S.E., M.M."
                                               class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-[12.5px] font-semibold text-slate-900 focus:border-senat-primary focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="text-[11.5px] font-semibold text-slate-700">NIP / NIM</label>
                                        <input type="text" wire:model="sekretarisNip" placeholder="Contoh: 197405191999031001"
                                               class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-[12px] font-mono text-slate-900 focus:border-senat-primary focus:outline-none">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 flex justify-end">
                            <button type="button" wire:click="updatePimpinan"
                                    class="senat-tap inline-flex h-9 items-center gap-1.5 rounded-lg bg-senat-primary px-4 text-[12px] font-semibold text-white transition hover:bg-senat-primaryHover">
                                <x-senat.icon name="check" class="h-3.5 w-3.5" />
                                <span>Simpan Perubahan Pimpinan ke Backend</span>
                            </button>
                        </div>
                    </div>
                </div>
