@extends('layouts.public')

@section('title', 'Produk Hukum · Peraturan Senat — Senat Akademik Politeknik Negeri Semarang')
@section('description', 'Himpunan peraturan mengikat, statuta institusi, kode etik sivitas, dan regulasi tata kelola akademik Politeknik Negeri Semarang.')

@section('content')

@php
    $categories = collect($regulations)->pluck('category')->unique()->values();
    $years = collect($regulations)->pluck('year')->unique()->sortDesc()->values();
    $statuses = collect($regulations)->pluck('status')->unique()->values();
    $total = count($regulations);
    $pageSize = 4;
@endphp

{{-- 05A — Produk Hukum · Peraturan Senat (Figma node 2019:120) --}}
<div x-data="{
        q: '',
        category: '',
        year: '',
        status: '',
        visible: {{ $pageSize }},
        pageSize: {{ $pageSize }},
        regs: @js($regulations),
        matches(reg) {
            if (! reg) return false;
            if (this.category !== '' &amp;&amp; reg.category !== this.category) return false;
            if (this.year !== '' &amp;&amp; String(reg.year) !== this.year) return false;
            if (this.status !== '' &amp;&amp; reg.status !== this.status) return false;
            const term = this.q.toLowerCase().trim();
            if (term === '') return true;
            return (reg.nomor + ' ' + reg.title + ' ' + reg.category + ' ' + reg.status + ' ' + reg.year + ' ' + reg.file_size).toLowerCase().indexOf(term) !== -1;
        },
        get matchedIndexes() {
            const out = [];
            this.regs.forEach((reg, i) => { if (this.matches(reg)) out.push(i); });
            return out;
        },
        get matchedCount() { return this.matchedIndexes.length; },
        get visibleIndexes() { return this.matchedIndexes.slice(0, this.visible); },
        get shownCount() { return this.visibleIndexes.length; },
        get hasMore() { return this.matchedCount > this.visible; },
        isVisible(i) { return this.visibleIndexes.indexOf(i) !== -1; },
        reset() {
            this.q = '';
            this.category = '';
            this.year = '';
            this.status = '';
            this.visible = this.pageSize;
        }
    }">

    {{-- ======================= HERO ======================= --}}
    <section class="bg-senat-hero">
        <div class="senat-shell pt-[62px] pb-[26px]">
            <p class="text-[11px] font-bold uppercase tracking-[0.1em] text-senat-sky">Regulasi Normatif Senat</p>

            <h1 class="mt-4 text-[30px] font-bold leading-[1.1] text-white sm:text-[36px] lg:text-[41px]">
                Peraturan Senat Akademik
            </h1>

            <p class="mt-4 max-w-[650px] text-[14px] leading-[1.42] text-senat-skySoft">
                Himpunan peraturan mengikat, statuta institusi, kode etik sivitas, dan regulasi tata kelola akademik Polines.
            </p>

            <div class="mt-10 max-w-[750px]">
                <label class="flex h-[46px] items-center gap-3 rounded-[9px] bg-white px-5">
                    <span class="text-[13px] leading-none text-senat-body" aria-hidden="true">⌕</span>
                    <span class="sr-only">Cari peraturan senat</span>
                    <input type="search" x-model="q" @input="visible = pageSize"
                           placeholder="Cari nomor, judul, atau kata kunci…  Enter untuk mencari"
                           class="h-full w-full min-w-0 bg-transparent text-[12px] text-senat-ink placeholder:text-senat-body focus:outline-none">
                </label>
            </div>
        </div>
    </section>

    {{-- ======================= HASIL & FILTER ======================= --}}
    <section class="senat-shell py-14">
        <div class="grid gap-x-11 gap-y-9 lg:grid-cols-[290px_minmax(0,1fr)]">

            {{-- Filter panel --}}
            <aside>
                <p class="text-[11px] font-bold uppercase tracking-[0.08em] text-senat-orange">Filter Dokumen</p>

                <div class="mt-4 rounded-xl border border-[#d9e3f0] bg-white p-5">
                    <div>
                        <label for="filter-jenis" class="block text-[10px] font-bold uppercase tracking-[0.04em] text-senat-body">Jenis Dokumen</label>
                        <select id="filter-jenis" x-model="category" @change="visible = pageSize"
                                class="mt-2 h-10 w-full rounded-[7px] border border-[#d9e3f0] bg-white px-3 text-[11px] font-medium text-senat-ink focus:border-senat-primary focus:outline-none">
                            <option value="">Semua Jenis Dokumen</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category }}">{{ $category }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mt-7">
                        <label for="filter-tahun" class="block text-[10px] font-bold uppercase tracking-[0.04em] text-senat-body">Tahun Terbit</label>
                        <select id="filter-tahun" x-model="year" @change="visible = pageSize"
                                class="mt-2 h-10 w-full rounded-[7px] border border-[#d9e3f0] bg-white px-3 text-[11px] font-medium text-senat-ink focus:border-senat-primary focus:outline-none">
                            <option value="">Semua Tahun</option>
                            @foreach ($years as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mt-7">
                        <label for="filter-status" class="block text-[10px] font-bold uppercase tracking-[0.04em] text-senat-body">Status Dokumen</label>
                        <select id="filter-status" x-model="status" @change="visible = pageSize"
                                class="mt-2 h-10 w-full rounded-[7px] border border-[#d9e3f0] bg-white px-3 text-[11px] font-medium text-senat-ink focus:border-senat-primary focus:outline-none">
                            <option value="">Semua Status</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}">{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="button" @click="reset()"
                            class="mt-8 inline-flex h-[38px] w-full items-center justify-center gap-2 rounded-[7px] border border-[#bacfe5] bg-senat-badgeBg text-[11px] font-bold text-senat-link transition-colors hover:border-senat-link">
                        <span aria-hidden="true">↺</span>
                        Atur Ulang Filter
                    </button>
                </div>
            </aside>

            {{-- Daftar peraturan --}}
            <div class="min-w-0">
                <h2 class="text-[21px] font-bold leading-snug text-senat-ink sm:text-[25px]">Daftar Peraturan Senat Aktif</h2>
                <p class="mt-2 text-[11px] text-senat-body">
                    {{ $total }} peraturan normatif berlaku dan terverifikasi secara hukum.
                </p>

                <div class="mt-6 space-y-4">
                    @forelse ($regulations as $reg)
                        @php
                            $state = match ($reg['status']) {
                                'Disahkan' => 'success',
                                'Pembahasan' => 'warning',
                                default => 'neutral',
                            };
                        @endphp

                        {{-- Regulation card --}}
                        <article x-show="isVisible({{ $loop->index }})"
                                 x-transition.opacity.duration.150ms
                                 class="flex flex-col gap-5 rounded-xl border border-[#d9e3f0] bg-white p-5 sm:flex-row sm:items-start sm:p-6">

                            <div class="grid h-[54px] w-12 shrink-0 place-items-center rounded-[10px] border border-[#a6c4e5] bg-senat-badgeBg text-[18px] font-bold text-senat-hero"
                                 aria-hidden="true">▤</div>

                            <div class="min-w-0 flex-1">
                                <p class="text-[10px] font-bold uppercase tracking-[0.06em] text-senat-link">
                                    {{ $reg['category'] }} &bull; {{ $reg['nomor'] }}
                                </p>

                                <h3 class="mt-2 text-[14px] font-bold leading-[1.25] text-senat-ink">
                                    {{ $reg['title'] }}
                                </h3>

                                <div class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-2 text-[10px] text-senat-body">
                                    <span class="tabular-nums">Tahun {{ $reg['year'] }}</span>
                                    <span aria-hidden="true" class="text-senat-line">|</span>
                                    <span class="tabular-nums whitespace-nowrap">{{ $reg['file_size'] }} · PDF</span>
                                    <x-senat.badge :state="$state" class="ml-1">{{ $reg['status'] }}</x-senat.badge>
                                </div>
                            </div>

                            <x-senat.button variant="primary" size="md" :href="$reg['download_url']" class="shrink-0 self-start">
                                Unduh PDF
                            </x-senat.button>
                        </article>
                    @empty
                        <x-senat.empty
                            title="Belum ada peraturan senat"
                            message="Sekretariat belum memublikasikan dokumen peraturan pada repositori ini." />
                    @endforelse
                </div>

                {{-- Empty state hasil pencarian / filter --}}
                @if ($total > 0)
                    <div x-cloak x-show="matchedCount === 0" class="mt-4">
                        <x-senat.empty
                            title="Tidak ada dokumen yang cocok"
                            message="Ubah kata kunci pencarian atau atur ulang filter dokumen untuk melihat hasil lainnya." />
                    </div>
                @endif

                {{-- Results footer — node 2351:1274 --}}
                @if ($total > 0)
                    <div class="mt-4 flex flex-col gap-3 rounded-[10px] border border-[#d9e2ef] bg-[#f8fafc] px-5 py-[13px] sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-[12px] font-medium text-[#4b5565]">
                            Menampilkan <span x-text="shownCount" class="tabular-nums">{{ min($pageSize, $total) }}</span>
                            dari <span x-text="matchedCount" class="tabular-nums">{{ $total }}</span>
                            peraturan yang tersedia.
                        </p>

                        <button type="button" @click="visible += pageSize"
                                x-cloak x-show="hasMore"
                                class="inline-flex h-[38px] shrink-0 items-center justify-center rounded-[8px] border border-[#9fb3c8] bg-white px-5 text-[12px] font-semibold text-[#155ead] transition-colors hover:border-[#155ead]">
                            Muat lebih banyak
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>

@endsection
