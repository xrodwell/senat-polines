@extends('layouts.public')

@section('title', 'Profil & Struktur — Senat Akademik Politeknik Negeri Semarang')

@section('content')

    {{-- ================= PAGE HEADER ================= --}}
    <section class="bg-polines-navy border-b border-polines-navyDark">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
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
                        <span class="text-xs font-mono text-blue-200 bg-white/10 px-3 py-1 rounded-md">Periode Jabatan 2024–2028</span>
                    </div>

                    <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6 divide-y md:divide-y-0 md:divide-x divide-slate-100">
                        <div class="pt-4 md:pt-0 md:pr-6">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Ketua Senat</span>
                            <h4 class="text-base font-extrabold text-slate-900 mt-1">Prof. Ir. Supriyadi, M.T.</h4>
                            <p class="text-xs text-slate-500 font-mono mt-0.5">NIP. 196803121993031002</p>
                            <p class="text-xs text-slate-600 mt-3 leading-relaxed">Penanggung jawab utama persidangan pleno dan representasi Senat ke Direktur &amp; Kemendiktisaintek.</p>
                        </div>
                        <div class="pt-4 md:pt-0 md:px-6">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Sekretaris Senat</span>
                            <h4 class="text-base font-extrabold text-slate-900 mt-1">Dr. Eng. Agus Subagio, S.T., M.T.</h4>
                            <p class="text-xs text-slate-500 font-mono mt-0.5">NIP. 197405191999031001</p>
                            <p class="text-xs text-slate-600 mt-3 leading-relaxed">Pengelola persidangan, verifikasi risalah keputusan sidang pleno, dan pengelolaan arsip normatif.</p>
                        </div>
                        <div class="pt-4 md:pt-0 md:pl-6">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Badan Pekerja Harian (BPH)</span>
                            <h4 class="text-base font-extrabold text-slate-900 mt-1">Sekretariat Eksekutif Senat</h4>
                            <p class="text-xs text-slate-500 mt-0.5">Gedung Direktorat Lantai 2</p>
                            <p class="text-xs text-slate-600 mt-3 leading-relaxed">Penyusun rancangan agenda persidangan berkala, koordinasi antar-komisi, dan penyiapan berkas sidang.</p>
                        </div>
                    </div>
                </div>

                {{-- Garis Hubung Hierarki --}}
                <div class="flex items-center justify-center">
                    <div class="flex flex-col items-center">
                        <div class="w-px h-6 bg-slate-300"></div>
                        <span class="text-[10px] font-mono font-bold uppercase tracking-widest bg-slate-200 text-slate-600 px-3 py-0.5 rounded-full">
                            Membawahi 5 Komisi Tetap
                        </span>
                        <div class="w-px h-6 bg-slate-300"></div>
                    </div>
                </div>

                {{-- Tingkat 2: Grid 5 Komisi Tetap Fungsional --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    
                    {{-- Komisi I --}}
                    <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs hover:border-polines-blue transition-all">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-3">
                            <span class="text-xs font-mono font-extrabold text-polines-navy bg-blue-50 px-2 py-0.5 rounded">KOMISI I</span>
                            <span class="text-xs text-slate-400 tabular-nums">8 Anggota</span>
                        </div>
                        <h4 class="text-sm font-bold text-slate-900 leading-snug">Bidang Pendidikan &amp; Pengajaran</h4>
                        <p class="text-xs text-slate-500 font-medium mt-1">Ketua: Prof. Dr. Ir. Budi Rahardjo, M.T.</p>
                        <div class="mt-3 pt-3 border-t border-slate-100">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Fokus Pengawasan:</span>
                            <p class="text-xs text-slate-600 mt-1 leading-relaxed">Kurikulum vokasi, keselarasan MBKM industri, dan evaluasi PBM antarjurusan.</p>
                        </div>
                    </div>

                    {{-- Komisi II --}}
                    <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs hover:border-polines-blue transition-all">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-3">
                            <span class="text-xs font-mono font-extrabold text-polines-navy bg-blue-50 px-2 py-0.5 rounded">KOMISI II</span>
                            <span class="text-xs text-slate-400 tabular-nums">7 Anggota</span>
                        </div>
                        <h4 class="text-sm font-bold text-slate-900 leading-snug">Bidang Penelitian &amp; Pengabdian</h4>
                        <p class="text-xs text-slate-500 font-medium mt-1">Ketua: Dr. Eng. Tri Handayani, S.T., M.Kom.</p>
                        <div class="mt-3 pt-3 border-t border-slate-100">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Fokus Pengawasan:</span>
                            <p class="text-xs text-slate-600 mt-1 leading-relaxed">Hilirisasi riset terapan, perolehan paten vokasi, dan integrasi pengabdian P3M.</p>
                        </div>
                    </div>

                    {{-- Komisi III --}}
                    <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs hover:border-polines-blue transition-all">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-3">
                            <span class="text-xs font-mono font-extrabold text-polines-navy bg-blue-50 px-2 py-0.5 rounded">KOMISI III</span>
                            <span class="text-xs text-slate-400 tabular-nums">6 Anggota</span>
                        </div>
                        <h4 class="text-sm font-bold text-slate-900 leading-snug">Bidang Kelembagaan &amp; SDM</h4>
                        <p class="text-xs text-slate-500 font-medium mt-1">Ketua: Drs. Joko Prasetyo, M.M., Ak.</p>
                        <div class="mt-3 pt-3 border-t border-slate-100">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Fokus Pengawasan:</span>
                            <p class="text-xs text-slate-600 mt-1 leading-relaxed">Pertimbangan pembukaan prodi baru, kelayakan lab, dan formasi pendidik.</p>
                        </div>
                    </div>

                    {{-- Komisi IV --}}
                    <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs hover:border-polines-blue transition-all">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-3">
                            <span class="text-xs font-mono font-extrabold text-polines-navy bg-blue-50 px-2 py-0.5 rounded">KOMISI IV</span>
                            <span class="text-xs text-slate-400 tabular-nums">8 Anggota</span>
                        </div>
                        <h4 class="text-sm font-bold text-slate-900 leading-snug">Bidang Kemahasiswaan &amp; Alumni</h4>
                        <p class="text-xs text-slate-500 font-medium mt-1">Ketua: Ir. Hendro Wijaya, M.Sc.</p>
                        <div class="mt-3 pt-3 border-t border-slate-100">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Fokus Pengawasan:</span>
                            <p class="text-xs text-slate-600 mt-1 leading-relaxed">Pedoman ormawa, etika kemahasiswaan, dan sinergi jejaring alumni Polines.</p>
                        </div>
                    </div>

                    {{-- Komisi V --}}
                    <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs hover:border-polines-blue transition-all md:col-span-2 lg:col-span-1">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-3">
                            <span class="text-xs font-mono font-extrabold text-polines-navy bg-blue-50 px-2 py-0.5 rounded">KOMISI V</span>
                            <span class="text-xs text-slate-400 tabular-nums">5 Anggota</span>
                        </div>
                        <h4 class="text-sm font-bold text-slate-900 leading-snug">Bidang Penegakan Etika &amp; Tata Tertib</h4>
                        <p class="text-xs text-slate-500 font-medium mt-1">Ketua: Dr. Siti Aminah, S.H., M.Hum.</p>
                        <div class="mt-3 pt-3 border-t border-slate-100">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Fokus Pengawasan:</span>
                            <p class="text-xs text-slate-600 mt-1 leading-relaxed">Integritas akademik, penyelesaian sengketa etika, dan rekomendasi sanksi senat.</p>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </section>

    {{-- ================= RINCIAN TUGAS POKOK & FUNGSI ================= --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        <div class="mb-8">
            <p class="text-xs font-bold uppercase tracking-[0.15em] text-polines-orange mb-1.5">Tugas Pokok &amp; Fungsi</p>
            <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Rincian Tupoksi Komisi Fungsional</h2>
        </div>

        <div class="space-y-4">
            @forelse ($committees as $committee)
                <article class="rounded-xl border border-slate-200/80 bg-white p-6 hover:border-slate-300 transition-all duration-150">
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                        <div class="flex items-center gap-2.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold font-mono bg-blue-50 text-polines-navy">
                                {{ $committee['code'] }}
                            </span>
                            <h3 class="text-base font-bold text-slate-900">{{ $committee['name'] }}</h3>
                        </div>
                        <span class="text-xs font-semibold text-slate-500 tabular-nums">
                            {{ $committee['members_count'] }} Anggota Komisi
                        </span>
                    </div>

                    <p class="text-xs font-medium text-slate-600 mb-2">
                        <span class="text-slate-400">Ketua Komisi:</span> {{ $committee['lead'] }}
                    </p>

                    <p class="text-sm text-slate-700 leading-relaxed">{{ $committee['tupoksi'] }}</p>
                </article>
            @empty
                <p class="text-sm text-slate-500">Data komisi belum dimuat.</p>
            @endforelse
        </div>
    </section>

@endsection
