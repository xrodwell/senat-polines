{{-- ================= MODAL: GELAR VOTING BARU ================= --}}
<div x-show="showNewVoteModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 flex items-center justify-center p-4">
    <div class="bg-white rounded-md border border-slate-200 shadow-sm max-w-lg w-full p-6 space-y-4" @click.away="showNewVoteModal = false">
        <div class="flex items-center justify-between border-b border-slate-200 pb-3">
            <h2 class="text-base font-bold text-polines-navy">Gelar Sesi Pemungutan Suara Baru</h2>
            <button @click="showNewVoteModal = false" type="button" aria-label="Tutup" class="text-slate-400 hover:text-slate-700 text-sm font-bold">&#10005;</button>
        </div>

        <div class="space-y-3 text-xs">
            <div>
                <label class="block font-semibold text-slate-600 uppercase tracking-wider mb-1">Pokok Putusan / Pertanyaan Voting</label>
                <input type="text" x-model="newVoteForm.title" placeholder="Contoh: Pengesahan Draf Rencana Strategis (Renstra) Polines 2026-2030" class="w-full rounded border border-slate-300 px-3 py-2.5 outline-none focus:border-polines-blue focus:ring-1 focus:ring-polines-blue">
            </div>

            <div>
                <label class="block font-semibold text-slate-600 uppercase tracking-wider mb-1">Deskripsi / Penjelasan Pasal</label>
                <textarea rows="3" x-model="newVoteForm.description" placeholder="Jelaskan dasar pertimbangan putusan sidang..." class="w-full rounded border border-slate-300 px-3 py-2.5 outline-none focus:border-polines-blue focus:ring-1 focus:ring-polines-blue"></textarea>
            </div>
        </div>

        <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-4">
            <button @click="showNewVoteModal = false" type="button" class="rounded border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                Batal
            </button>
            <button @click="deployNewVote" type="button" class="rounded bg-polines-navy px-5 py-2 text-xs font-semibold text-white hover:bg-polines-navyDark">
                Luncurkan ke Bilik Suara
            </button>
        </div>
    </div>
</div>

