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
                <p class="text-xs font-bold uppercase tracking-[0.15em] text-polines-orange mb-1.5">Arah Strategis</p>
                <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Visi Senat</h2>
                <blockquote class="mt-4 rounded-xl border-l-4 border-polines-orange bg-white border border-slate-200/80 p-6">
                    <p class="text-base font-semibold text-slate-800 leading-relaxed">
                        "Menjadi penjaga mutu dan integritas akademik yang mendorong Polines sebagai
                        institusi pendidikan vokasi unggul, berkarakter, dan berdaya saing global."
                    </p>
                </blockquote>
            </div>
            <div class="lg:col-span-3">
                <p class="text-xs font-bold uppercase tracking-[0.15em] text-polines-orange mb-1.5">Pelaksanaan</p>
                <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Misi Senat</h2>
                <ul class="mt-4 space-y-3">
                    @foreach ([
                        'Merumuskan kebijakan dan norma akademik yang selaras dengan perkembangan industri dan teknologi vokasi.',
                        'Melakukan pengawasan mutu pelaksanaan pendidikan, penelitian, dan pengabdian kepada masyarakat secara berkelanjutan.',
                        'Memberikan pertimbangan kepada Direktur Polines dalam pengambilan keputusan strategis bidang akademik.',
                        'Menegakkan kode etik dan tata tertib civitas akademika secara adil, transparan, dan akuntabel.',
                        'Menyalurkan aspirasi mahasiswa, dosen, dan tenaga kependidikan melalui kanal resmi yang terdokumentasi.',
                    ] as $i => $misi)
                        <li class="flex items-start gap-3 rounded-xl border border-slate-200/80 bg-white px-5 py-4">
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

    {{-- ================= BAGAN STRUKTUR ================= --}}
    <section class="border-y border-slate-200/80 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
            <div class="mb-8">
                <p class="text-xs font-bold uppercase tracking-[0.15em] text-polines-orange mb-1.5">Tata Kelola</p>
                <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Bagan Struktur Organisasi</h2>
            </div>

            <div class="flex flex-col items-center gap-0">
                {{-- Pimpinan Senat --}}
                <div class="rounded-xl border border-slate-200/80 bg-polines-navy px-8 py-5 text-center w-full max-w-md shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-widest text-blue-300 mb-1">Pimpinan Senat</p>
                    <p class="text-base font-extrabold text-white">Ketua, Wakil Ketua &amp; Sekretaris Senat</p>
                    <p class="mt-1 text-xs text-blue-200/80">Perumus arah kebijakan &amp; pengendali sidang pleno</p>
                </div>
                <div class="w-px h-6 bg-slate-300"></div>

                {{-- BPH --}}
                <div class="rounded-xl border border-slate-200/80 bg-white px-8 py-4 text-center w-full max-w-md">
                    <p class="text-xs font-bold uppercase tracking-widest text-polines-blue mb-1">Badan Pekerja Harian (BPH)</p>
                    <p class="text-sm font-bold text-slate-900">Koordinasi agenda, administrasi, dan persiapan sidang</p>
                </div>
                <div class="w-px h-6 bg-slate-300"></div>

                {{-- Komisi I-V --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 w-full">
                    @foreach (['I', 'II', 'III', 'IV', 'V'] as $roman)
                        <div class="rounded-xl border border-slate-200/80 bg-white px-4 py-4 text-center hover:border-polines-navy/40 transition-colors duration-150">
                            <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-polines-navy text-white text-sm font-extrabold tabular-nums mb-2">
                                {{ $roman }}
                            </span>
                            <p class="text-xs font-bold text-slate-900 uppercase tracking-wide">Komisi {{ $roman }}</p>
                            <p class="mt-1 text-[11px] text-slate-500 leading-snug">
                                {{ [
                                    'I'   => 'Pendidikan & Pengajaran',
                                    'II'  => 'Penelitian & Pengabdian',
                                    'III' => 'Kelembagaan & Sumber Daya',
                                    'IV'  => 'Kemahasiswaan & Alumni',
                                    'V'   => 'Penegakan Etika & Tata Tertib',
                                ][$roman] }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ================= RINCIAN KOMISI ================= --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        <div class="mb-8">
            <p class="text-xs font-bold uppercase tracking-[0.15em] text-polines-orange mb-1.5">Tugas Pokok &amp; Fungsi</p>
            <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Rincian Komisi I – V</h2>
        </div>

        <div class="space-y-4">
            @forelse ($committees as $committee)
                <article class="rounded-xl border border-slate-200/80 bg-white p-6 hover:border-slate-300 transition-all duration-150">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="flex items-start gap-4 min-w-0">
                            <span class="inline-flex items-center justify-center w-11 h-11 rounded-xl bg-polines-navy text-white text-sm font-extrabold shrink-0">
                                {{ \Illuminate\Support\Str::after($committee['code'], 'KOM-') }}
                            </span>
                            <div class="min-w-0">
                                <h3 class="text-base font-extrabold tracking-tight text-slate-900 leading-snug">
                                    {{ $committee['name'] }}
                                </h3>
                                <p class="mt-1 text-sm text-slate-500">
                                    Ketua:
                                    <span class="font-semibold text-slate-800">{{ $committee['lead'] }}</span>
                                </p>
                            </div>
                        </div>
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-blue-50 text-blue-800 shrink-0 tabular-nums">
                            {{ $committee['members_count'] }} Anggota
                        </span>
                    </div>
                    <p class="mt-4 text-sm text-slate-600 leading-relaxed border-t border-slate-100 pt-4">
                        {{ $committee['tupoksi'] }}
                    </p>
                </article>
            @empty
                <div class="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-10 text-center">
                    <p class="text-sm text-slate-500">Data komisi belum tersedia.</p>
                </div>
            @endforelse
        </div>
    </section>

@endsection
