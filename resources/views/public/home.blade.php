@extends('layouts.public')

@section('title', 'Beranda — Senat Akademik Politeknik Negeri Semarang')

@section('content')

    {{-- ======================= HERO ======================= --}}
    <section class="relative overflow-hidden bg-polines-navy">
        <div class="absolute inset-0 bg-gradient-to-br from-polines-navyDark to-polines-navy"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-20 lg:pt-24 lg:pb-28">

            @if ($activeSession)
                <div class="mb-8 inline-flex items-start gap-3 rounded-xl border border-amber-400/40 bg-amber-500/10 px-4 py-3 backdrop-blur-sm">
                    <span class="relative flex h-2.5 w-2.5 mt-1.5 shrink-0">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-amber-400"></span>
                    </span>
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
                        <p class="text-sm font-semibold text-amber-100">
                            Sidang sedang berlangsung — kode sesi
                            <span class="font-mono font-bold tabular-nums text-white">{{ $activeSession['session_code'] }}</span>
                            · {{ \Illuminate\Support\Str::limit($activeSession['title'], 60) }}
                        </p>
                        <a
                            href="{{ route('portal.session') }}"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-polines-orange hover:bg-[#d95f14] px-3 py-1.5 text-xs font-bold text-white transition-colors duration-150"
                        >
                            Masuk Portal Sidang
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>
            @endif

            <div class="max-w-3xl">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-300 mb-4">
                    Organ Tertinggi Normatif Akademik
                </p>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-white leading-[1.1]">
                    Senat Akademik<br>
                    <span class="text-polines-orange">Politeknik Negeri Semarang</span>
                </h1>
                <p class="mt-6 text-base sm:text-lg text-blue-100/90 leading-relaxed">
                    Perumus kebijakan akademik, penjaga mutu pendidikan vokasi, dan penyalur aspirasi
                    civitas akademika — memastikan tata kelola Polines berjalan transparan, akuntabel,
                    dan berintegritas.
                </p>

                <div class="mt-8 flex flex-wrap items-center gap-3">
                    <a
                        href="{{ route('public.profile') }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-polines-orange hover:bg-[#d95f14] px-6 py-3 text-sm font-bold text-white transition-colors duration-150"
                    >
                        Profil &amp; Komisi
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                    <a
                        href="{{ route('public.regulations') }}"
                        class="inline-flex items-center gap-2 rounded-lg border border-white/25 bg-white/10 hover:bg-white/15 backdrop-blur-sm px-6 py-3 text-sm font-semibold text-white transition-colors duration-150"
                    >
                        Repositori Produk Hukum
                    </a>
                    <a
                        href="{{ route('public.aspirations') }}"
                        class="inline-flex items-center gap-2 text-sm font-semibold text-blue-200 hover:text-white transition-colors duration-150"
                    >
                        Sampaikan Aspirasi
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </a>
                </div>
            </div>

            {{-- Quick Stat Grid --}}
            <dl class="mt-14 grid grid-cols-2 lg:grid-cols-4 gap-px bg-white/10 rounded-2xl overflow-hidden border border-white/10">
                <div class="bg-polines-navyDark/60 backdrop-blur-sm px-6 py-5">
                    <dt class="text-xs font-semibold uppercase tracking-wider text-blue-300">Anggota Senat</dt>
                    <dd class="mt-1.5 text-3xl font-extrabold text-white tabular-nums">34</dd>
                    <dd class="mt-0.5 text-xs text-blue-200/80">Guru Besar &amp; Pendidik</dd>
                </div>
                <div class="bg-polines-navyDark/60 backdrop-blur-sm px-6 py-5">
                    <dt class="text-xs font-semibold uppercase tracking-wider text-blue-300">Komisi Aktif</dt>
                    <dd class="mt-1.5 text-3xl font-extrabold text-white tabular-nums">5</dd>
                    <dd class="mt-0.5 text-xs text-blue-200/80">Komisi I s.d. V</dd>
                </div>
                <div class="bg-polines-navyDark/60 backdrop-blur-sm px-6 py-5">
                    <dt class="text-xs font-semibold uppercase tracking-wider text-blue-300">Produk Regulasi</dt>
                    <dd class="mt-1.5 text-3xl font-extrabold text-white tabular-nums">18</dd>
                    <dd class="mt-0.5 text-xs text-blue-200/80">Peraturan &amp; SK Senat</dd>
                </div>
                <div class="bg-polines-navyDark/60 backdrop-blur-sm px-6 py-5">
                    <dt class="text-xs font-semibold uppercase tracking-wider text-blue-300">Kepatuhan Tata Kelola</dt>
                    <dd class="mt-1.5 text-3xl font-extrabold text-white tabular-nums">100%</dd>
                    <dd class="mt-0.5 text-xs text-blue-200/80">Audit Internal 2026</dd>
                </div>
            </dl>
        </div>
    </section>

    {{-- ================= AGENDA SIDANG TERDEKAT ================= --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        <div class="flex items-end justify-between gap-4 mb-6">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.15em] text-polines-orange mb-1.5">Kalender Senat</p>
                <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
                    Agenda Sidang Terdekat
                </h2>
            </div>
            <a
                href="{{ route('portal.session') }}"
                class="hidden sm:inline-flex items-center gap-1.5 text-sm font-semibold text-polines-blue hover:text-polines-navy transition-colors duration-150 shrink-0"
            >
                Lihat Portal Sidang
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>

        <div class="space-y-4">
            @forelse ($upcomingMeetings as $meeting)
                @include('components.agenda-item', ['meeting' => $meeting])
            @empty
                <div class="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-10 text-center">
                    <p class="text-sm text-slate-500">Belum ada agenda sidang terjadwal dalam waktu dekat.</p>
                </div>
            @endforelse
        </div>
    </section>

    {{-- ================= TUPOKSI 5 KOMISI ================= --}}
    <section class="border-y border-slate-200/80 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
            <div class="flex items-end justify-between gap-4 mb-8">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.15em] text-polines-orange mb-1.5">Struktur Kerja</p>
                    <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
                        Lima Komisi Senat Polines
                    </h2>
                </div>
                <a
                    href="{{ route('public.profile') }}"
                    class="hidden sm:inline-flex items-center gap-1.5 text-sm font-semibold text-polines-blue hover:text-polines-navy transition-colors duration-150 shrink-0"
                >
                    Lihat Profil Lengkap
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            @php
                $komisi = [
                    ['roman' => 'I',   'bidang' => 'Pendidikan & Pengajaran',            'ringkas' => 'Norma kurikulum vokasi, mutu pembelajaran, dan pengawasan akademik antarjurusan.'],
                    ['roman' => 'II',  'bidang' => 'Penelitian & Pengabdian',            'ringkas' => 'Arah riset hilirisasi vokasi, paten teknologi terapan, dan kemitraan industri.'],
                    ['roman' => 'III', 'bidang' => 'Kelembagaan & Sumber Daya',          'ringkas' => 'Kelayakan program studi baru, efisiensi fasilitas lab, dan formasi pendidik.'],
                    ['roman' => 'IV',  'bidang' => 'Kemahasiswaan & Alumni',             'ringkas' => 'Pembinaan minat bakat, etika organisasi, dan jejaring ikatan alumni.'],
                    ['roman' => 'V',   'bidang' => 'Penegakan Etika & Tata Tertib',      'ringkas' => 'Kepatuhan kode etik civitas, pemeriksaan pelanggaran, rekomendasi sanksi.'],
                ];
            @endphp

            <div class="grid sm:grid-cols-2 lg:grid-cols-5 gap-4">
                @foreach ($komisi as $k)
                    <a
                        href="{{ route('public.profile') }}"
                        class="group rounded-xl border border-slate-200/80 bg-white p-5 hover:border-polines-navy/40 transition-all duration-150 flex flex-col"
                    >
                        <div class="flex items-center gap-2.5 mb-3">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-polines-navy text-white text-sm font-extrabold tabular-nums">
                                {{ $k['roman'] }}
                            </span>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Komisi {{ $k['roman'] }}</h3>
                        </div>
                        <p class="text-sm font-bold text-slate-900 leading-snug tracking-tight group-hover:text-polines-navy transition-colors duration-150">
                            {{ $k['bidang'] }}
                        </p>
                        <p class="mt-2 text-xs text-slate-600 leading-relaxed flex-1">{{ $k['ringkas'] }}</p>
                        <span class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-polines-blue">
                            Selengkapnya
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================= WARTA TERBARU ================= --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        <div class="mb-8">
            <p class="text-xs font-bold uppercase tracking-[0.15em] text-polines-orange mb-1.5">Siaran Pers</p>
            <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
                Warta &amp; Keputusan Senat Terbaru
            </h2>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @forelse ($posts as $post)
                @include('components.news-card', ['post' => $post])
            @empty
                <div class="sm:col-span-2 lg:col-span-4 rounded-xl border border-dashed border-slate-300 bg-white px-6 py-10 text-center">
                    <p class="text-sm text-slate-500">Belum ada warta yang diterbitkan.</p>
                </div>
            @endforelse
        </div>
    </section>

@endsection
