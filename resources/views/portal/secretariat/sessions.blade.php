@extends('layouts.portal', ['sidebar' => 'sekretariat'])

@section('title', 'Manajemen Sidang — Portal Internal Senat Akademik Polines')

@section('content')
    @php
        /* 13B — Dashboard Sekretariat / Manajemen Sidang (Figma node 2280:938) */
        $jenisSidang = ['Pleno', 'Terbuka', 'Pimpinan', 'Komisi', 'Panitia Kerja / Badan Pekerja', 'Lainnya'];

        $chipState = function (string $type): string {
            if (str_contains($type, 'Pleno')) {
                return 'warning';
            }
            if (str_contains($type, 'Komisi')) {
                return 'info';
            }
            return 'neutral';
        };
        $chipLabel = function (string $type): string {
            if (str_contains($type, 'Pleno')) {
                return 'Pleno';
            }
            if (str_contains($type, 'Komisi')) {
                return 'Komisi';
            }
            return $type;
        };
    @endphp

    <div class="mx-auto max-w-[1116px] space-y-6">
        <header>
            <h1 class="text-[25px] font-bold leading-tight text-senat-ink">Manajemen Sidang</h1>
            <p class="mt-2 text-[13px] leading-snug text-senat-body">
                Buat agenda, tetapkan jenis sidang, peserta, materi, dan undangan.
            </p>
        </header>

        <div class="grid gap-5 xl:grid-cols-[730px_minmax(0,1fr)]">
            {{-- Buat Sidang Baru --}}
            <section class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]"
                     x-data="{ jenis: 'Pleno', saved: false }">
                <h2 class="text-[18px] font-bold text-senat-ink">Buat Sidang Baru</h2>
                <p class="mt-1.5 text-[13px] text-senat-body">Gunakan jenis sidang sesuai ketentuan Senat.</p>

                <form class="mt-5 space-y-5" @submit.prevent>
                    <div>
                        <label for="agenda" class="text-[12px] font-semibold text-senat-ink">Judul / agenda sidang</label>
                        <input id="agenda" type="text" placeholder="Contoh: Pembahasan RUU Kampus"
                               class="mt-2 h-[38px] w-full rounded-[8px] border border-senat-border bg-white px-3 text-[12px] text-senat-ink placeholder:text-senat-muted focus:border-senat-link focus:outline-none focus:ring-1 focus:ring-senat-link">
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="tanggal" class="text-[12px] font-semibold text-senat-ink">Tanggal dan waktu</label>
                            <input id="tanggal" type="datetime-local"
                                   class="mt-2 h-[38px] w-full rounded-[8px] border border-senat-border bg-white px-3 text-[12px] text-senat-ink focus:border-senat-link focus:outline-none focus:ring-1 focus:ring-senat-link">
                        </div>
                        <div>
                            <label for="lokasi" class="text-[12px] font-semibold text-senat-ink">Lokasi atau tautan hybrid</label>
                            <input id="lokasi" type="text" placeholder="Ruang sidang / konferensi video"
                                   class="mt-2 h-[38px] w-full rounded-[8px] border border-senat-border bg-white px-3 text-[12px] text-senat-ink placeholder:text-senat-muted focus:border-senat-link focus:outline-none focus:ring-1 focus:ring-senat-link">
                        </div>
                    </div>

                    <div>
                        <label for="peserta" class="text-[12px] font-semibold text-senat-ink">Peserta dan undangan</label>
                        <input id="peserta" type="text" placeholder="Pilih anggota dan pihak terkait"
                               class="mt-2 h-[38px] w-full rounded-[8px] border border-senat-border bg-white px-3 text-[12px] text-senat-ink placeholder:text-senat-muted focus:border-senat-link focus:outline-none focus:ring-1 focus:ring-senat-link">
                    </div>

                    <fieldset>
                        <legend class="text-[12px] font-semibold text-senat-ink">Jenis Sidang</legend>
                        <div class="mt-3 grid gap-2 sm:grid-cols-2">
                            @foreach ($jenisSidang as $jenis)
                                <button type="button" @click="jenis = @js($jenis)"
                                        :class="jenis === @js($jenis)
                                            ? 'border-senat-link bg-senat-badgeBg text-senat-link font-semibold'
                                            : 'border-senat-sidebarLine bg-white text-senat-ink hover:border-senat-border'"
                                        class="flex h-[32px] items-center gap-2 rounded-[8px] border px-3 text-left text-[12px]">
                                    <span aria-hidden="true" x-text="jenis === @js($jenis) ? '●' : '○'"></span>
                                    <span>{{ $jenis }}</span>
                                </button>
                            @endforeach
                        </div>
                    </fieldset>

                    <div class="flex flex-wrap items-center gap-3 pt-2">
                        <x-senat.button variant="secondary" type="button" @click="saved = true">Simpan sebagai Draft</x-senat.button>
                        <x-senat.button variant="primary" type="button" @click="saved = true">Buat Undangan</x-senat.button>
                    </div>

                    <div x-cloak x-show="saved" x-transition
                         class="flex items-center gap-3 rounded-[8px] bg-[#eaf8f0] px-3 py-2">
                        <span class="text-[15px] font-semibold text-[#116a3c]" aria-hidden="true"><x-senat.icon name="check" class="h-[18px] w-[18px]" /></span>
                        <p class="text-[12px] text-senat-ink">Draft, undangan, dan status sidang tampil di sini.</p>
                    </div>
                </form>
            </section>

            {{-- Sidang Terjadwal --}}
            <section class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
                <h2 class="text-[18px] font-bold text-senat-ink">Sidang Terjadwal</h2>

                @if (empty($meetings))
                    <x-senat.empty class="mt-4" title="Belum ada sidang terjadwal"
                                   message="Buat sidang baru untuk melihatnya di daftar ini." />
                @else
                    <ul class="mt-3 divide-y divide-senat-sidebarLine">
                        @foreach ($meetings as $meeting)
                            <li class="py-4 first:pt-2">
                                <p class="text-[14px] font-semibold text-senat-ink">{{ $meeting['title'] }}</p>
                                <p class="mt-1 text-[12px] text-senat-body">
                                    {{ \Illuminate\Support\Carbon::parse($meeting['date'])->format('d M') }} · {{ $meeting['time'] }}
                                </p>
                                <div class="mt-3 flex items-center justify-between gap-3">
                                    <x-senat.badge :state="$chipState($meeting['type'])">{{ $chipLabel($meeting['type']) }}</x-senat.badge>
                                    <x-senat.button variant="secondary" size="sm" :href="route('portal.secretariat.sessions')">Kelola</x-senat.button>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif

                <div class="mt-5 rounded-[14px] bg-senat-badgeBg p-4 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
                    <p class="flex items-center gap-2 text-[14px] font-semibold text-senat-ink">
                        <span class="text-[18px] font-bold text-senat-link" aria-hidden="true"><x-senat.icon name="calendar" class="h-[18px] w-[18px]" /></span>
                        Virtual &amp; Hybrid Assembly
                    </p>
                    <p class="mt-2 text-[12px] leading-snug text-senat-body">
                        Tambahkan tautan konferensi video saat sidang perlu dilakukan jarak jauh atau hibrida.
                    </p>
                    <x-senat.button variant="primary" size="sm" class="mt-3" type="button">Atur Integrasi</x-senat.button>
                </div>
            </section>
        </div>
    </div>
@endsection
