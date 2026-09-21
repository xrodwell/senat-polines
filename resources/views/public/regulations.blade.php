@extends('layouts.public')

@section('title', 'Produk Hukum — Senat Akademik Politeknik Negeri Semarang')

@section('content')

    <section class="bg-polines-navy border-b border-polines-navyDark">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-300 mb-2">Legalitas Akademik</p>
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white">
                Repositori Produk Hukum &amp; SK Pertimbangan
            </h1>
            <p class="mt-3 max-w-2xl text-sm sm:text-base text-blue-100/90 leading-relaxed">
                Kumpulan peraturan senat, surat keputusan pertimbangan, dan rancangan regulasi yang
                sedang dalam proses pembahasan — terbuka untuk diunduh civitas akademika.
            </p>
        </div>
    </section>

    <section
        class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12"
        x-data="{
            query: '',
            copied: null,
            matches(reg) {
                const q = this.query.toLowerCase().trim();
                if (!q) return true;
                return (reg.nomor + ' ' + reg.title + ' ' + reg.category + ' ' + reg.status + ' ' + reg.year)
                    .toLowerCase().includes(q);
            },
            download(reg) {
                this.copied = reg.id;
                setTimeout(() => this.copied = null, 2200);
            }
        }"
    >
        {{-- Toolbar --}}
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center gap-3">
            <div class="relative flex-1 max-w-md">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input
                    x-model="query"
                    type="search"
                    placeholder="Cari nomor, judul, kategori, atau status..."
                    class="w-full rounded-lg border border-slate-200/80 bg-white pl-10 pr-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-polines-blue/40 focus:border-polines-blue transition-colors duration-150"
                >
            </div>
            <div class="flex items-center gap-4 text-xs font-semibold">
                <span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500"></span>Disahkan</span>
                <span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-amber-500"></span>Pembahasan</span>
                <span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-slate-400"></span>Draf</span>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto rounded-xl border border-slate-200/80 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Nomor Dokumen</th>
                        <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Perihal</th>
                        <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Kategori</th>
                        <th scope="col" class="px-5 py-3.5 text-center text-xs font-bold uppercase tracking-wider text-slate-500">Tahun</th>
                        <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Status</th>
                        <th scope="col" class="px-5 py-3.5 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($regulations as $reg)
                        @php
                            $badge = match($reg['status']) {
                                'Disahkan'   => 'bg-emerald-50 text-emerald-700',
                                'Pembahasan' => 'bg-amber-50 text-amber-700',
                                default      => 'bg-slate-100 text-slate-600',
                            };
                        @endphp
                        <tr
                            x-show="matches(@js($reg))"
                            x-transition.opacity.duration.150ms
                            class="hover:bg-slate-50/60 transition-colors duration-150"
                        >
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="font-mono text-xs font-semibold text-polines-navy tabular-nums">{{ $reg['nomor'] }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <p class="font-semibold text-slate-900 leading-snug">{{ $reg['title'] }}</p>
                                <p class="mt-0.5 text-xs text-slate-400 tabular-nums">{{ $reg['file_size'] }} · PDF</p>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-slate-600">{{ $reg['category'] }}</td>
                            <td class="px-5 py-4 text-center whitespace-nowrap text-slate-600 tabular-nums">{{ $reg['year'] }}</td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $badge }}">
                                    {{ $reg['status'] }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                <button
                                    type="button"
                                    @click="download(@js($reg))"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200/80 bg-white px-3 py-1.5 text-xs font-semibold text-polines-blue hover:border-polines-blue/50 hover:bg-blue-50 transition-colors duration-150"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    <span x-show="copied !== {{ $reg['id'] }}">Unduh PDF</span>
                                    <span x-show="copied === {{ $reg['id'] }}" x-cloak>Tersiapkan…</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">
                                Belum ada dokumen produk hukum yang terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Empty state pencarian --}}
        @if (count($regulations) > 0)
            <div
                x-show="query.trim() !== '' && ![@foreach($regulations as $reg)@js($reg){{ $loop->last ? '' : ',' }}@endforeach].some(r => matches(r))"
                x-cloak
                class="mt-4 rounded-xl border border-dashed border-slate-300 bg-white px-6 py-8 text-center"
            >
                <p class="text-sm text-slate-500">Tidak ada dokumen yang cocok dengan pencarian "<span x-text="query" class="font-semibold text-slate-700"></span>".</p>
            </div>
        @endif

        <p class="mt-5 text-xs text-slate-400 leading-relaxed">
            Unduhan bersifat simulasi untuk demonstrasi portal. Dokumen resmi bertanda tangan elektronik
            diterbitkan melalui Sekretariat Senat. Hubungi sekretariat untuk versi legalisir.
        </p>
    </section>

@endsection
