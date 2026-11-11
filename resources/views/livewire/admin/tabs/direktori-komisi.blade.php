<div wire:key="screen-direktori-komisi" class="rounded-xl border border-slate-200/80 bg-white p-6 shadow-xs">
    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
        <div>
            <h2 class="text-[16px] font-bold text-slate-900">Direktori Komisi & Anggota Senat</h2>
            <p class="text-[12px] text-slate-500">Daftar komisi dan susunan anggota periode 2026–2029</p>
        </div>
        <button type="button" wire:click="saveAction('Tambah Anggota Senat')"
                class="rounded-lg bg-senat-primary px-3 py-1.5 text-[12px] font-semibold text-white hover:bg-senat-primaryHover">
            + Tambah Anggota
        </button>
    </div>
    <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($committees as $c)
            <div class="rounded-xl border border-slate-200 p-4 bg-slate-50/50">
                <span class="rounded-md bg-slate-200 px-2 py-0.5 text-[10.5px] font-bold text-slate-800">{{ $c['code'] }}</span>
                <h3 class="mt-2 text-[13.5px] font-bold text-slate-900">{{ $c['name'] }}</h3>
                <p class="mt-1 text-[11.5px] text-slate-600"><span class="font-semibold text-slate-700">Ketua:</span> {{ $c['lead'] }}</p>
                <p class="text-[11.5px] text-slate-600"><span class="font-semibold text-slate-700">Sekretaris:</span> {{ $c['secretary'] }}</p>
                <p class="mt-2 text-[11px] text-slate-500">{{ $c['members_count'] }} Anggota Terdaftar</p>
            </div>
        @endforeach
    </div>

    {{-- Tabel Anggota Senat & Akses Cepat Impersonasi --}}
    <div class="mt-8 border-t border-slate-100 pt-6">
        <div class="flex items-center justify-between pb-3">
            <div>
                <h3 class="text-[15px] font-bold text-slate-900">Daftar Anggota Senat &amp; Akses Cepat Portal</h3>
                <p class="text-[12px] text-slate-500">Administrator dapat beralih akun (&ldquo;Login As&rdquo;) ke anggota Senat untuk verifikasi tampilan portal atau mendampingi anggota.</p>
            </div>
            <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-semibold text-slate-600">
                {{ count($allLecturers ?? []) }} Anggota Terdaftar
            </span>
        </div>

        <div class="mt-3 overflow-x-auto rounded-xl border border-slate-200">
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
</div>
