@extends('layouts.public')

@section('title', 'Profil & Struktur — Senat Akademik Politeknik Negeri Semarang')

@section('content')

    {{-- ================= PAGE HEADER ================= --}}
    <section class="relative overflow-hidden bg-polines-navyDark border-b border-polines-navyDark">
        {{-- Background: Politeknik Negeri Semarang Campus Environment --}}
        <div class="absolute inset-0 z-0 select-none pointer-events-none overflow-hidden">
            <img
                src="{{ asset('images/gedung-polines.jpg') }}"
                alt="Gedung Polines"
                class="w-full h-full object-cover object-center scale-105"
                loading="eager"
            />
            <div class="absolute inset-0 bg-gradient-to-r from-polines-navyDark/95 via-polines-navy/90 to-polines-navyDark/80"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-polines-navyDark via-transparent to-polines-navyDark/60"></div>
        </div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-300 mb-2">Tentang Kami</p>
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white">
                Profil &amp; Struktur Organisasi
            </h1>
            <p class="mt-3 max-w-2xl text-sm sm:text-base text-blue-100/90 leading-relaxed">
                Senat Akademik adalah organ normatif tertinggi Politeknik Negeri Semarang di bidang
                akademik, bertanggung jawab merumuskan kebijakan, menjaga mutu, dan menegakkan
                integritas civitas akademika.
            </p>
        </div>
    </section>

    {{-- ================= VISI & MISI ================= --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        <div class="grid lg:grid-cols-5 gap-8">
            <div class="lg:col-span-2">
                <p class="text-xs font-bold uppercase tracking-[0.15em] text-polines-orange mb-2">Arah Kebijakan</p>
                <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Visi &amp; Misi</h2>
                <div class="mt-6 rounded-2xl border border-blue-200/70 bg-gradient-to-br from-blue-50/70 to-slate-50 p-6">
                    <p class="text-xs font-bold uppercase tracking-wider text-polines-navy mb-2">Visi Senat</p>
                    <p class="text-sm font-semibold text-slate-900 leading-relaxed">
                        Menjadi lembaga normatif akademik yang kredibel, progresif, dan berintegritas
                        dalam mengawal keunggulan pendidikan vokasi Polines di tingkat nasional dan global.
                    </p>
                </div>
            </div>

            <div class="lg:col-span-3">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3">Misi Strategis Senat</p>
                <ul class="space-y-3">
                    @foreach ([
                        'Merumuskan norma dan tolok ukur kebijakan akademik yang selaras dengan kemajuan industri dan teknologi.',
                        'Memberikan pertimbangan dan pengawasan objektif terhadap rencana pengembangan akademik institusi.',
                        'Menegakkan kode etik dan tata tertib civitas akademika secara adil, transparan, dan akuntabel.',
                        'Menyalurkan aspirasi mahasiswa, dosen, dan tenaga kependidikan melalui kanal resmi yang terdokumentasi.',
                    ] as $i => $misi)
                        <li class="flex items-start gap-3 rounded-xl border border-slate-200/80 bg-white px-5 py-4 shadow-xs">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-polines-navy text-white text-xs font-bold tabular-nums shrink-0 mt-0.5">
                                {{ $i + 1 }}
                            </span>
                            <p class="text-sm text-slate-700 leading-relaxed">{{ $misi }}</p>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>

    {{-- ================= TATA PAMONG & BAGAN HIERARKI RESMI ================= --}}
    <section class="border-y border-slate-200/80 bg-slate-50/70 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mb-12">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100/70 text-polines-navy text-xs font-bold uppercase tracking-wider mb-3">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    Statuta Polines &bull; Struktur Tata Pamong
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
                    Hierarki Senat Akademik Politeknik Negeri Semarang
                </h2>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                    Sesuai Peraturan Senat Akademik Polines, tata kelola musyawarah dan pengawasan norma akademik dijalankan secara kolegial melalui pimpinan, badan pekerja, dan komisi fungsional.
                </p>
            </div>

            {{-- Blueprint Bagan Organisasi Resmi --}}
            <div class="space-y-6">

                {{-- Tingkat 1: Pimpinan Senat --}}
                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden">
                    <div class="bg-polines-navy px-6 py-4 flex flex-wrap items-center justify-between gap-4 text-white">
                        <div class="flex items-center gap-3">
                            <span class="w-2.5 h-2.5 rounded-full bg-polines-orange"></span>
                            <h3 class="text-sm font-bold uppercase tracking-wider">Tingkat I &bull; Pimpinan Kolektif Senat</h3>
                        </div>
                        <span class="text-xs font-mono text-blue-200 bg-white/10 px-3 py-1 rounded-md">Periode Jabatan 2026–2029</span>
                    </div>

                    <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6 divide-y md:divide-y-0 md:divide-x divide-slate-100">
                        <div class="pt-4 md:pt-0 md:pr-6">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Ketua Senat</span>
                            <h4 class="text-base font-extrabold text-slate-900 mt-1">
                                {{ $ketua['enrollment']['user']['nama'] ?? 'Dr. Sukamto, S.Kom., M.T.' }}
                            </h4>
                            <p class="text-xs text-slate-500 font-mono mt-0.5">
                                NIP. {{ $ketua['enrollment']['user']['nim_nip'] ?? '197101172003121001' }}
                            </p>
                            <p class="text-xs text-slate-600 mt-3 leading-relaxed">Penanggung jawab utama persidangan pleno dan representasi Senat ke Direktur &amp; Kemendiktisaintek.</p>
                        </div>
                        <div class="pt-4 md:pt-0 md:px-6">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Sekretaris Senat</span>
                            <h4 class="text-base font-extrabold text-slate-900 mt-1">
                                {{ $sekretaris['enrollment']['user']['nama'] ?? 'Andi Setiawan, S.E., M.M.' }}
                            </h4>
                            <p class="text-xs text-slate-500 font-mono mt-0.5">
                                NIP. {{ $sekretaris['enrollment']['user']['nim_nip'] ?? '197607202008011011' }}
                            </p>
                            <p class="text-xs text-slate-600 mt-3 leading-relaxed">Pengelola persidangan, verifikasi risalah keputusan sidang pleno, dan pengelolaan arsip normatif.</p>
                        </div>
                        <div class="pt-4 md:pt-0 md:pl-6">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Badan Pekerja Harian (BPH)</span>
                            <h4 class="text-base font-extrabold text-slate-900 mt-1">Andi Setiawan, S.E., M.M.</h4>
                            <p class="text-xs text-slate-500 font-mono mt-0.5">Ketua BPH</p>
                            <h4 class="text-sm font-bold text-slate-800 mt-2">Vinda Setya Kartika, S.T., M.T.</h4>
                            <p class="text-xs text-slate-600 mt-1 leading-relaxed">Penyusun rancangan agenda persidangan berkala, koordinasi antar-komisi, dan penyiapan berkas sidang.</p>
                        </div>
                    </div>
                </div>

                {{-- Garis Hubung Hierarki --}}
                <div class="flex items-center justify-center">
                    <div class="flex flex-col items-center">
                        <div class="w-px h-6 bg-slate-300"></div>
                        <span class="text-[10px] font-mono font-bold uppercase tracking-widest bg-slate-200 text-slate-600 px-3 py-0.5 rounded-full">
                            Membawahi {{ count($committees) }} Komisi Tetap
                        </span>
                        <div class="w-px h-6 bg-slate-300"></div>
                    </div>
                </div>

                {{-- Tingkat 2: Grid Komisi Tetap Fungsional (Dinamis dari API / Mock) --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @forelse ($committees as $committee)
                        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs hover:border-polines-blue transition-all">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-3">
                                <span class="text-xs font-mono font-extrabold text-polines-navy bg-blue-50 px-2 py-0.5 rounded">{{ strtoupper($committee['code'] ?? '') }}</span>
                                <span class="text-xs text-slate-400 tabular-nums">{{ $committee['members_count'] ?? count($committee['members'] ?? []) }} Anggota</span>
                            </div>
                            <h4 class="text-sm font-bold text-slate-900 leading-snug">{{ $committee['name'] ?? '-' }}</h4>
                            <p class="text-xs text-slate-500 font-medium mt-1">Ketua: {{ $committee['lead'] ?? '-' }}</p>
                            @if (!empty($committee['secretary']))
                                <p class="text-xs text-slate-500 font-medium">Sekretaris: {{ $committee['secretary'] }}</p>
                            @endif
                            <div class="mt-3 pt-3 border-t border-slate-100">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Fokus Pengawasan:</span>
                                <p class="text-xs text-slate-600 mt-1 leading-relaxed">{{ $committee['focus'] ?? $committee['tupoksi'] ?? '-' }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-8">
                            <p class="text-sm text-slate-500">Data komisi belum tersedia.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    {{-- ================= RINCIAN TUGAS POKOK & DIREKTORI ANGGOTA ================= --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        <div class="mb-8">
            <p class="text-xs font-bold uppercase tracking-[0.15em] text-polines-orange mb-1.5">Tugas Pokok &amp; Susunan Anggota</p>
            <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Direktori Komisi &amp; Anggota Fungsional</h2>
            <p class="text-sm text-slate-500 mt-1">Rincian tugas pokok serta susunan pengurus dan anggota pada masing-masing komisi.</p>
        </div>

        <div class="space-y-6">
            @forelse ($committees as $committee)
                <article class="rounded-2xl border border-slate-200/80 bg-white p-6 sm:p-7 shadow-xs hover:border-slate-300 transition-all duration-150">
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                        <div class="flex items-center gap-2.5">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold font-mono bg-blue-50 text-polines-navy border border-blue-100">
                                {{ $committee['code'] ?? 'KOM' }}
                            </span>
                            <h3 class="text-lg font-bold text-slate-900">{{ $committee['name'] ?? '-' }}</h3>
                        </div>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 tabular-nums">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            {{ $committee['members_count'] ?? count($committee['members'] ?? []) }} Anggota Komisi
                        </span>
                    </div>

                    <div class="bg-slate-50/80 rounded-xl p-4 mb-5 border border-slate-100">
                        <div class="flex flex-wrap gap-x-6 gap-y-1 mb-1.5">
                            <p class="text-xs font-medium text-slate-600">
                                <span class="text-slate-400 font-semibold text-[11px] uppercase tracking-wider">Ketua Komisi:</span>
                                <span class="font-bold text-slate-800 ml-1">{{ $committee['lead'] ?? 'Belum ditentukan' }}</span>
                            </p>
                            @if (!empty($committee['secretary']))
                                <p class="text-xs font-medium text-slate-600">
                                    <span class="text-slate-400 font-semibold text-[11px] uppercase tracking-wider">Sekretaris Komisi:</span>
                                    <span class="font-bold text-slate-800 ml-1">{{ $committee['secretary'] }}</span>
                                </p>
                            @endif
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            <span class="font-semibold text-slate-500">Tupoksi:</span> {{ $committee['tupoksi'] ?? $committee['focus'] ?? '-' }}
                        </p>
                    </div>

                    {{-- Susunan Anggota Komisi --}}
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 flex items-center gap-2">
                            <span>Susunan Anggota</span>
                            <span class="flex-1 h-px bg-slate-100"></span>
                        </h4>

                        @if (!empty($committee['members']) && count($committee['members']) > 0)
                            @if (is_array($committee['members'][0] ?? null))
                                {{-- Format objek dari Backend API (nama, nim_nip, jabatan, foto_profil) --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                    @foreach ($committee['members'] as $member)
                                        <div class="flex items-center gap-3 p-3 rounded-xl border border-slate-200/70 bg-white hover:border-polines-blue/40 hover:shadow-xs transition-all">
                                            <div class="w-9 h-9 rounded-full bg-blue-50 border border-blue-100 text-polines-navy font-bold text-xs flex items-center justify-center shrink-0">
                                                @if (!empty($member['foto_profil']))
                                                    <img src="{{ $member['foto_profil'] }}" alt="{{ $member['nama'] ?? 'Anggota' }}" class="w-full h-full rounded-full object-cover">
                                                @else
                                                    {{ strtoupper(substr($member['nama'] ?? 'A', 0, 1)) }}
                                                @endif
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-xs font-bold text-slate-800 truncate" title="{{ $member['nama'] ?? '-' }}">
                                                    {{ $member['nama'] ?? '-' }}
                                                </p>
                                                <div class="flex items-center gap-2 mt-0.5">
                                                    <span class="text-[10px] font-mono text-slate-400">
                                                        {{ $member['nim_nip'] ?? '-' }}
                                                    </span>
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold {{ strtolower($member['jabatan'] ?? '') === 'ketua' ? 'bg-amber-100 text-amber-800' : (strtolower($member['jabatan'] ?? '') === 'sekretaris' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-600') }}">
                                                        {{ $member['jabatan'] ?? 'Anggota' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                {{-- Format string daftar nama dari Mock JSON --}}
                                <div class="mt-2 pt-2 border-t border-slate-100">
                                    <p class="text-xs text-slate-600 leading-relaxed">{!! implode(' &nbsp;&bull;&nbsp; ', array_map(fn($m) => '<span class="font-medium text-slate-700">' . e($m) . '</span>', $committee['members'])) !!}</p>
                                </div>
                            @endif
                        @else
                            <p class="text-xs text-slate-400 italic">Belum ada daftar anggota yang terdaftar untuk komisi ini.</p>
                        @endif
                    </div>
                </article>
            @empty
                <div class="p-8 text-center bg-white rounded-xl border border-slate-200">
                    <p class="text-sm text-slate-500">Data komisi belum dimuat.</p>
                </div>
            @endforelse
        </div>
    </section>

@endsection
