{{-- ================= MODAL: TAMBAH JADWAL SIDANG BARU ================= --}}
<div x-show="showAddMeetingModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 flex items-center justify-center p-4">
    <div class="bg-white rounded-md border border-slate-200 shadow-sm max-w-lg w-full p-6 space-y-4" @click.away="showAddMeetingModal = false">
        <div class="flex items-center justify-between border-b border-slate-200 pb-3">
            <h2 class="text-base font-bold text-polines-navy">Jadwalkan Sidang Senat Baru</h2>
            <button @click="showAddMeetingModal = false" type="button" aria-label="Tutup" class="text-slate-400 hover:text-slate-700 text-sm font-bold">&#10005;</button>
        </div>

        <div class="space-y-3 text-xs">
            <div>
                <label class="block font-semibold text-slate-600 uppercase tracking-wider mb-1">Judul / Agenda Sidang</label>
                <input type="text" x-model="newMeeting.title" placeholder="Contoh: Rapat Pleno Penetapan Standar Kurikulum Vokasi 2026" class="w-full rounded border border-slate-300 px-3 py-2.5 outline-none focus:border-polines-blue focus:ring-1 focus:ring-polines-blue">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-600 uppercase tracking-wider mb-1">Jenis Sidang</label>
                    <select x-model="newMeeting.type" class="w-full rounded border border-slate-300 px-3 py-2.5 outline-none focus:border-polines-blue focus:ring-1 focus:ring-polines-blue bg-white">
                        <option value="Sidang Pleno">Sidang Pleno</option>
                        <option value="Rapat Komisi I">Rapat Komisi I (Pendidikan dan Pengajaran)</option>
                        <option value="Rapat Komisi II">Rapat Komisi II (Penelitian dan Pengabdian)</option>
                        <option value="Rapat Komisi III">Rapat Komisi III (Kelembagaan dan SDM)</option>
                        <option value="Rapat Komisi IV">Rapat Komisi IV (Pengembangan dan Kerja Sama)</option>
                        <option value="Rapat Komisi V">Rapat Komisi V (Komisi Etik)</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-600 uppercase tracking-wider mb-1">Tanggal</label>
                    <input type="date" x-model="newMeeting.date" class="w-full rounded border border-slate-300 px-3 py-2.5 outline-none focus:border-polines-blue focus:ring-1 focus:ring-polines-blue tabular-nums">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-600 uppercase tracking-wider mb-1">Waktu / Jam Sidang</label>
                <input type="text" x-model="newMeeting.time" placeholder="Contoh: 09:00 - 12:00 WIB" class="w-full rounded border border-slate-300 px-3 py-2.5 outline-none focus:border-polines-blue focus:ring-1 focus:ring-polines-blue">
            </div>

            <div>
                <label class="block font-semibold text-slate-600 uppercase tracking-wider mb-1">Ruangan / Tempat Sidang</label>
                <input type="text" x-model="newMeeting.room" class="w-full rounded border border-slate-300 px-3 py-2.5 outline-none focus:border-polines-blue focus:ring-1 focus:ring-polines-blue">
            </div>
        </div>

        <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-4">
            <button @click="showAddMeetingModal = false" type="button" class="rounded border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                Batal
            </button>
            <button @click="saveNewMeeting" type="button" class="rounded bg-polines-navy px-5 py-2 text-xs font-semibold text-white hover:bg-polines-navyDark">
                Simpan Agenda Sidang
            </button>
        </div>
    </div>
</div>

