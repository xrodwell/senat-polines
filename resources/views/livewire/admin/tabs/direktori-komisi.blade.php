<div wire:key="screen-direktori-komisi" class="space-y-6">
    {{-- Header & Manajemen Anggota Senat --}}
    <div class="rounded-xl border border-slate-200/80 bg-white p-6 shadow-xs">
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <div>
                <h2 class="text-[16px] font-bold text-slate-900">Direktori Komisi &amp; Anggota Senat</h2>
                <p class="text-[12px] text-slate-500">Kelola susunan komisi kerja dan penugasan anggota Senat Akademik periode 2026–2029</p>
            </div>
            <button type="button" wire:click="openCreateMemberModal"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-senat-primary px-3.5 py-2 text-[12px] font-semibold text-white shadow-xs transition hover:bg-senat-primaryHover cursor-pointer">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                <span>+ Tambah Anggota</span>
            </button>
        </div>

        {{-- Kartu Ringkasan Komisi --}}
        <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($committees as $c)
                <div class="flex flex-col justify-between rounded-xl border border-slate-200 bg-slate-50/50 p-4 transition hover:border-slate-300">
                    <div>
                        <div class="flex items-center justify-between gap-2">
                            <span class="rounded-md bg-slate-200 px-2 py-0.5 text-[10.5px] font-bold text-slate-800">{{ $c['code'] }}</span>
                            <span class="text-[11px] font-semibold text-slate-500">{{ $c['members_count'] }} Anggota</span>
                        </div>
                        <h3 class="mt-2 text-[13.5px] font-bold text-slate-900">{{ $c['name'] }}</h3>
                        <p class="mt-2 text-[11.5px] text-slate-600"><span class="font-semibold text-slate-700">Ketua:</span> {{ $c['lead'] ?: 'Belum ditentukan' }}</p>
                        <p class="text-[11.5px] text-slate-600"><span class="font-semibold text-slate-700">Sekretaris:</span> {{ $c['secretary'] ?: 'Belum ditentukan' }}</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-200/60 flex items-center justify-between">
                        <button type="button" wire:click="openCreateMemberModal('{{ $c['code'] }}')"
                                class="text-[11px] font-semibold text-senat-link hover:underline cursor-pointer">
                            + Tambah ke {{ $c['code'] }}
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Daftar Anggota Komisi & Tindakan Administrasi --}}
    <div class="rounded-xl border border-slate-200/80 bg-white p-6 shadow-xs">
        <div class="border-b border-slate-100 pb-4">
            <h3 class="text-[15px] font-bold text-slate-900">Susunan Lengkap Komisi I – V</h3>
            <p class="text-[12px] text-slate-500">Edit data anggota atau mutasi komisi langsung dari panel administrasi ini.</p>
        </div>

        <div class="mt-6 space-y-6">
            @foreach ($committees as $c)
                @php
                    $leadName = $c['lead'] ?? '';
                    $secName = $c['secretary'] ?? '';
                    $membersList = $c['members'] ?? [];
                @endphp
                <div class="rounded-xl border border-slate-200 overflow-hidden">
                    <div class="flex flex-wrap items-center justify-between gap-2 bg-slate-50 px-4 py-3 border-b border-slate-200">
                        <div class="flex items-center gap-2.5">
                            <span class="rounded bg-senat-primary px-2 py-0.5 text-[11px] font-bold text-white">{{ $c['code'] }}</span>
                            <span class="text-[13px] font-bold text-slate-900">{{ $c['name'] }}</span>
                        </div>
                        <button type="button" wire:click="openCreateMemberModal('{{ $c['code'] }}')"
                                class="text-xs font-semibold text-senat-link hover:underline cursor-pointer">
                            + Tambah Anggota ke Komisi
                        </button>
                    </div>

                    <div class="divide-y divide-slate-100 bg-white text-xs">
                        {{-- Ketua Komisi --}}
                        <div class="flex items-center justify-between px-4 py-2.5 bg-blue-50/30 hover:bg-blue-50/50 transition">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="rounded bg-blue-100 px-2 py-0.5 text-[10px] font-bold text-blue-800 shrink-0">KETUA</span>
                                <span class="font-semibold text-slate-900 truncate">{{ $leadName ?: 'Belum ditentukan' }}</span>
                            </div>
                            @if (!empty($leadName) && $leadName !== 'Belum ditentukan')
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <button type="button"
                                            wire:click="openEditMemberModal(@js(['name' => $leadName, 'role' => 'Ketua', 'committee_code' => $c['code']]), '{{ $c['code'] }}')"
                                            class="rounded border border-slate-200 px-2.5 py-1 text-[11px] font-semibold text-slate-700 hover:bg-slate-100 transition cursor-pointer">
                                        Edit
                                    </button>
                                </div>
                            @endif
                        </div>

                        {{-- Sekretaris Komisi --}}
                        <div class="flex items-center justify-between px-4 py-2.5 bg-slate-50/40 hover:bg-slate-50/80 transition">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="rounded bg-sky-100 px-2 py-0.5 text-[10px] font-bold text-sky-800 shrink-0">SEKRETARIS</span>
                                <span class="font-semibold text-slate-900 truncate">{{ $secName ?: 'Belum ditentukan' }}</span>
                            </div>
                            @if (!empty($secName) && $secName !== 'Belum ditentukan')
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <button type="button"
                                            wire:click="openEditMemberModal(@js(['name' => $secName, 'role' => 'Sekretaris', 'committee_code' => $c['code']]), '{{ $c['code'] }}')"
                                            class="rounded border border-slate-200 px-2.5 py-1 text-[11px] font-semibold text-slate-700 hover:bg-slate-100 transition cursor-pointer">
                                        Edit
                                    </button>
                                </div>
                            @endif
                        </div>

                        {{-- Anggota Komisi --}}
                        @forelse ($membersList as $m)
                            @php
                                $mName = is_array($m) ? ($m['nama'] ?? $m['name'] ?? '—') : $m;
                                $mNip = is_array($m) ? ($m['nim_nip'] ?? '') : '';
                            @endphp
                            <div class="flex items-center justify-between px-4 py-2.5 hover:bg-slate-50 transition">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="rounded bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-600 shrink-0">ANGGOTA</span>
                                    <div class="min-w-0">
                                        <p class="font-medium text-slate-800 truncate">{{ $mName }}</p>
                                        @if ($mNip)
                                            <p class="font-mono text-[10.5px] text-slate-500">NIP: {{ $mNip }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <button type="button"
                                            wire:click="openEditMemberModal(@js(['name' => $mName, 'nip' => $mNip, 'role' => 'Anggota', 'committee_code' => $c['code']]), '{{ $c['code'] }}')"
                                            class="rounded border border-slate-200 px-2.5 py-1 text-[11px] font-semibold text-slate-700 hover:bg-slate-100 transition cursor-pointer">
                                        Edit
                                    </button>
                                    <button type="button"
                                            wire:click="confirmDeleteMember(@js(['name' => $mName, 'committee_code' => $c['code']]), '{{ $c['code'] }}')"
                                            class="rounded border border-red-200 px-2.5 py-1 text-[11px] font-semibold text-red-600 hover:bg-red-50 transition cursor-pointer">
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="px-4 py-3 text-center text-slate-400 text-xs">
                                Belum ada anggota terdaftar di {{ $c['name'] }}.
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Tabel Anggota Senat & Akses Cepat Impersonasi --}}
    <div class="rounded-xl border border-slate-200/80 bg-white p-6 shadow-xs">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-4">
            <div>
                <h3 class="text-[15px] font-bold text-slate-900">Daftar Akun Anggota Senat &amp; Akses Cepat Portal</h3>
                <p class="text-[12px] text-slate-500">Administrator dapat beralih akun (&ldquo;Login As&rdquo;) ke anggota Senat untuk verifikasi tampilan portal atau mendampingi anggota.</p>
            </div>
            <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-semibold text-slate-600">
                {{ count($allLecturers ?? []) }} Anggota Terdaftar
            </span>
        </div>

        <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold">
                    <tr>
                        <th class="px-4 py-3">Nama Anggota</th>
                        <th class="px-4 py-3">NIP / Identitas</th>
                        <th class="px-4 py-3">Komisi / Penugasan</th>
                        <th class="px-4 py-3">Peran Akun</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white text-slate-700">
                    @forelse ($allLecturers ?? [] as $u)
                        @php
                            $isCurrentAdmin = ((int) ($u['id_user'] ?? 0) === (int) session('auth_user_id'));
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-4 py-3 font-semibold text-slate-900">
                                {{ $u['nama'] ?? 'Anggota Senat' }}
                            </td>
                            <td class="px-4 py-3 font-mono text-slate-600">
                                {{ $u['nim_nip'] ?: '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-700">
                                    {{ $u['komisi'] ?? 'Komisi Senat' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded px-2 py-0.5 text-[10.5px] font-semibold uppercase tracking-wider {{ ($u['role'] ?? '') === 'admin' ? 'bg-amber-100 text-amber-800' : 'bg-blue-50 text-blue-700' }}">
                                    {{ $u['role'] ?? 'anggota' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if ($isCurrentAdmin)
                                    <span class="inline-flex items-center rounded bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-500">
                                        Akun Anda Saat Ini
                                    </span>
                                @else
                                    <form method="POST" action="{{ route('admin.impersonate', $u['id_user']) }}" class="inline m-0">
                                        @csrf
                                        <button type="submit"
                                                class="inline-flex items-center gap-1.5 rounded-lg bg-senat-primary px-3 py-1 text-xs font-semibold text-white transition hover:bg-senat-primaryHover shadow-xs cursor-pointer"
                                                title="Login sebagai {{ $u['nama'] }}">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                            </svg>
                                            <span>Login Sebagai</span>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-slate-500">
                                Belum ada data anggota senat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- MODAL TAMBAH & EDIT ANGGOTA KOMISI (LIVEWIRE)                             --}}
    {{-- ========================================================================= --}}
    @if ($showMemberModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" aria-modal="true" role="dialog">
            {{-- Backdrop --}}
            <div wire:click="closeMemberModal" class="fixed inset-0 bg-[#0f172a]/60 backdrop-blur-xs transition-opacity"></div>

            <div class="relative w-full max-w-[560px] transform overflow-hidden rounded-[16px] border border-[#d6e0ed] bg-white text-left shadow-2xl transition-all z-10">
                <div class="flex items-center justify-between border-b border-[#e2e8f0] bg-[#f8fafc] px-6 py-4">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-senat-badgeBg text-senat-hero">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                        </span>
                        <div>
                            <h3 class="text-[15px] font-bold text-[#0f172a]">
                                {{ $isEditingMember ? 'Perbarui Anggota Komisi' : 'Tambah Anggota Komisi Baru' }}
                            </h3>
                            <p class="text-[11px] text-[#64748b]">Kelola keanggotaan komisi Senat Akademik Polines</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeMemberModal"
                            class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-200 hover:text-slate-700 transition cursor-pointer">
                        ✕
                    </button>
                </div>

                <form wire:submit.prevent="saveMember" class="p-6 space-y-4">
                    <div>
                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-[#334155]">
                            Nama Lengkap &amp; Gelar Akademik <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model="memberNama" required
                               placeholder="Contoh: Prof. Dr. Ir. Dwiana Hendrawati, S.T., M.T."
                               class="mt-1 w-full rounded-lg border border-[#cbd5e1] px-3.5 py-2.5 text-[13px] text-[#0f172a] focus:border-senat-primary focus:outline-none focus:ring-1 focus:ring-senat-primary">
                        @error('memberNama') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-[11.5px] font-bold uppercase tracking-wider text-[#334155]">NIP / Identitas Pegawai</label>
                            <input type="text" wire:model="memberNimNip"
                                   placeholder="Contoh: 196803121993031002"
                                   class="mt-1 w-full rounded-lg border border-[#cbd5e1] px-3.5 py-2.5 font-mono text-[12.5px] text-[#0f172a] focus:border-senat-primary focus:outline-none focus:ring-1 focus:ring-senat-primary">
                            @error('memberNimNip') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-[11.5px] font-bold uppercase tracking-wider text-[#334155]">Email Resmi</label>
                            <input type="email" wire:model="memberEmail"
                                   placeholder="nama@polines.ac.id"
                                   class="mt-1 w-full rounded-lg border border-[#cbd5e1] px-3.5 py-2.5 text-[12.5px] text-[#0f172a] focus:border-senat-primary focus:outline-none focus:ring-1 focus:ring-senat-primary">
                            @error('memberEmail') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-[11.5px] font-bold uppercase tracking-wider text-[#334155]">
                                Komisi Penugasan <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="memberCommitteeCode" required
                                    class="mt-1 w-full rounded-lg border border-[#cbd5e1] px-3 py-2.5 text-[12.5px] text-[#0f172a] focus:border-senat-primary focus:outline-none focus:ring-1 focus:ring-senat-primary">
                                @foreach ($committees as $com)
                                    <option value="{{ $com['code'] }}">{{ $com['code'] }} — {{ $com['name'] }}</option>
                                @endforeach
                            </select>
                            @error('memberCommitteeCode') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-[11.5px] font-bold uppercase tracking-wider text-[#334155]">
                                Jabatan di Komisi <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="memberRole" required
                                    class="mt-1 w-full rounded-lg border border-[#cbd5e1] px-3 py-2.5 text-[12.5px] text-[#0f172a] focus:border-senat-primary focus:outline-none focus:ring-1 focus:ring-senat-primary">
                                <option value="Anggota">Anggota Komisi</option>
                                <option value="Ketua">Ketua Komisi</option>
                                <option value="Sekretaris">Sekretaris Komisi</option>
                            </select>
                            @error('memberRole') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-[#e2e8f0] pt-4 mt-6">
                        <button type="button" wire:click="closeMemberModal"
                                class="rounded-lg border border-[#cbd5e1] bg-white px-4 py-2 text-[12.5px] font-semibold text-[#475569] hover:bg-[#f1f5f9] transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-senat-primary px-5 py-2 text-[12.5px] font-bold text-white shadow-xs transition hover:bg-senat-primaryHover cursor-pointer">
                            <span>{{ $isEditingMember ? 'Simpan Perubahan' : 'Tambahkan Anggota' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- MODAL KONFIRMASI HAPUS ANGGOTA (LIVEWIRE)                                 --}}
    {{-- ========================================================================= --}}
    @if ($showDeleteMemberModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" aria-modal="true" role="dialog">
            <div wire:click="closeDeleteMemberModal" class="fixed inset-0 bg-[#0f172a]/60 backdrop-blur-xs transition-opacity"></div>

            <div class="relative w-full max-w-[440px] transform overflow-hidden rounded-[16px] border border-[#d6e0ed] bg-white text-left shadow-2xl transition-all z-10 p-6">
                <div class="flex items-start gap-4">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-[16px] font-bold text-[#0f172a]">Hapus Anggota Komisi?</h3>
                        <p class="mt-2 text-[12.5px] leading-relaxed text-[#64748b]">
                            Apakah Anda yakin ingin menghapus <strong class="text-slate-900">{{ $deleteMemberName }}</strong> dari penugasan komisi <strong class="text-slate-900">{{ $deleteMemberCommitteeCode }}</strong>? Tindakan ini akan menghapus keanggotaan dari direktori.
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <button type="button" wire:click="closeDeleteMemberModal"
                            class="rounded-lg border border-[#cbd5e1] bg-white px-4 py-2 text-[12.5px] font-semibold text-[#475569] hover:bg-[#f1f5f9] transition cursor-pointer">
                        Batal
                    </button>
                    <button type="button" wire:click="deleteMember"
                            class="rounded-lg bg-red-600 px-4 py-2 text-[12.5px] font-bold text-white shadow-xs transition hover:bg-red-700 cursor-pointer">
                        Ya, Hapus Anggota
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
