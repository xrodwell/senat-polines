@extends('layouts.public')

@section('title', 'Visi & Misi Senat Akademik — Senat Akademik Politeknik Negeri Semarang')
@section('description', 'Visi kelembagaan dan misi strategis Senat Akademik Politeknik Negeri Semarang dalam mengawal mutu pendidikan vokasi terapan.')

@section('content')

    {{-- 02A — Profil · Visi & Misi (Figma node 2019:2) --}}
    <x-senat.page-hero
        eyebrow="Mengenal Senat Akademik"
        title="Visi & Misi Senat Akademik"
        subtitle="Komitmen kelembagaan dalam mewujudkan tata kelola akademik berintegritas, mandiri, dan berdaya saing global di Politeknik Negeri Semarang."
    />

    @php
        $pilarNilai = ['Integritas Keilmuan', 'Otonomi & Etika', 'Akuntabilitas Publik'];

        $visiSenat = 'Menjadi lembaga normatif akademik terpercaya yang mengawal integritas keilmuan, kebebasan akademik, dan keunggulan pendidikan vokasi terapan berstandar internasional di Politeknik Negeri Semarang.';

        $misiSenat = [
            [
                'title' => 'Kebijakan & Kurikulum Vokasi Terapan',
                'body' => 'Menetapkan norma dan kebijakan penyelenggaraan pendidikan vokasi yang adaptif terhadap transformasi industri global.',
            ],
            [
                'title' => 'Pengawasan Mutu & Kebebasan Akademik',
                'body' => 'Mengawasi pelaksanaan standar penjaminan mutu, etika keilmuan, dan kebebasan mimbar akademik secara objektif dan adil.',
            ],
            [
                'title' => 'Pertimbangan Strategis & Akreditasi',
                'body' => 'Memberikan rekomendasi atas pembukaan prodi, restrukturisasi kurikulum, dan pengusulan kenaikan jabatan fungsional dosen.',
            ],
            [
                'title' => 'Penyaluran Aspirasi & Tata Kelola Transparan',
                'body' => 'Menjembatani aspirasi sivitas akademika guna memperkuat tata kelola Polines yang transparan, profesional, dan akuntabel.',
            ],
        ];
    @endphp

    {{-- ================= ARAH KEBIJAKAN & LANDASAN NILAI ================= --}}
    <section class="senat-shell pt-9 lg:pt-10">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.1em] text-senat-hero">Arah Kebijakan &amp; Landasan Nilai</p>
            <h2 class="mt-3 text-[22px] font-bold leading-[1.2] text-[#0f172a]">Visi Kelembagaan &amp; Misi Strategis Senat</h2>
            <p class="mt-3 max-w-[1264px] text-[13.5px] leading-[1.5] text-senat-muted">
                Pedoman filosofis dan komitmen normatif Senat Akademik dalam mengawal mutu pendidikan vokasi terapan Politeknik Negeri Semarang.
            </p>
        </div>

        <div class="mt-8 grid gap-6 lg:grid-cols-[minmax(0,520fr)_minmax(0,720fr)]">

            {{-- Card — Visi Senat Akademik --}}
            <article class="relative overflow-hidden rounded-[10px] bg-senat-primary lg:min-h-[460px]">
                <div class="absolute inset-x-0 top-0 h-1 bg-senat-orange" aria-hidden="true"></div>

                <div class="p-8">
                    <span class="inline-flex h-6 items-center rounded-[4px] bg-white/15 px-[18px] text-[10.5px] font-bold uppercase tracking-[0.06em] text-white">
                        Visi Senat
                    </span>

                    <p class="mt-6 select-none text-[48px] font-bold leading-[0.6] text-senat-orange" aria-hidden="true">&ldquo;</p>

                    <p class="mt-5 max-w-[456px] text-[17px] font-semibold leading-[1.4] text-white">{{ $visiSenat }}</p>

                    <div class="mt-7 h-px w-full max-w-[456px] bg-white/15" aria-hidden="true"></div>

                    <p class="mt-5 text-[11px] font-bold uppercase tracking-[0.06em] text-senat-orange">3 Pilar Nilai Utama :</p>

                    <ul class="mt-4 max-w-[456px] space-y-2">
                        @foreach ($pilarNilai as $pilar)
                            <li class="flex h-8 items-center gap-4 rounded-[6px] bg-white/[0.08] px-3.5 text-[12.5px] font-medium text-white">
                                <span class="h-2 w-2 shrink-0 rounded-full bg-senat-orange" aria-hidden="true"></span>
                                {{ $pilar }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </article>

            {{-- Card — Misi Senat Akademik --}}
            <article class="relative overflow-hidden rounded-[10px] border border-[#e2e8f0] bg-white lg:min-h-[460px]">
                <div class="absolute inset-x-0 top-0 h-1 bg-senat-hero" aria-hidden="true"></div>

                <div class="p-8">
                    <span class="inline-flex h-6 items-center rounded-[4px] bg-senat-badgeBg px-[18px] text-[10.5px] font-bold uppercase tracking-[0.06em] text-senat-hero">
                        Misi Strategis
                    </span>

                    <h3 class="mt-4 text-[16px] font-bold leading-snug text-[#0f172a]">4 Pilar Misi &amp; Komitmen Kelembagaan Senat</h3>

                    <ul class="mt-5 space-y-2.5">
                        @foreach ($misiSenat as $misi)
                            <li class="flex gap-3 rounded-[6px] border border-[#f1f5f9] bg-[#f8fafc] px-[15px] py-[13px]">
                                <span class="mt-[1px] flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-senat-primary text-[11px] font-bold text-white tabular-nums">
                                    {{ $loop->iteration }}
                                </span>
                                <div class="min-w-0">
                                    <p class="text-[13.5px] font-bold leading-snug text-[#0f172a]">{{ $misi['title'] }}</p>
                                    <p class="mt-1 text-[12px] leading-[1.45] text-[#475569]">{{ $misi['body'] }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </article>
        </div>
    </section>

    {{-- ================= LIMA KOMISI & RINGKASAN DIREKTORI ================= --}}
    <section class="mt-14 border-t border-senat-line bg-white">
        <div class="senat-shell py-14">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.1em] text-senat-orange">Struktur Kerja</p>
                    <h2 class="mt-3 text-[22px] font-bold leading-[1.2] text-[#0f172a]">Lima Komisi Senat Akademik</h2>
                </div>
                <x-senat.button variant="secondary" size="md" :href="route('public.profile.structure')" trailing="›">
                    Lihat Struktur Organisasi
                </x-senat.button>
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                @forelse ($committees as $committee)
                    <a href="{{ route('public.profile.structure') }}"
                       class="group flex flex-col rounded-[12px] border border-senat-line bg-white p-5 transition-colors hover:border-senat-badgeLine">
                        <span class="inline-flex w-fit items-center rounded-full border border-senat-badgeLine bg-senat-badgeBg px-2.5 py-[3px] text-[10px] font-bold uppercase tracking-[0.06em] text-senat-link">
                            {{ \App\Support\SenatDirectory::committeeLabel($committee['code']) }}
                        </span>
                        <h3 class="mt-3 text-[13px] font-bold leading-snug text-senat-ink">{{ $committee['name'] }}</h3>
                        <p class="mt-2 text-[11px] font-medium text-senat-body">Ketua: {{ $committee['lead'] }}</p>
                        <p class="mt-1 flex-1 text-[11px] leading-relaxed text-senat-muted">{{ $committee['focus'] }}</p>
                        <span class="mt-3 inline-flex items-center gap-1 text-[11px] font-bold text-senat-link">
                            Selengkapnya
                            <span aria-hidden="true">›</span>
                        </span>
                    </a>
                @empty
                    <div class="sm:col-span-2 lg:col-span-5">
                        <x-senat.empty title="Data komisi belum dimuat"
                                       message="Struktur komisi Senat Akademik akan tampil setelah data kelembagaan tersedia." />
                    </div>
                @endforelse
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-3">
                <x-senat.stat label="Komisi Fungsional" :value="$direktori['committees'] ?? count($committees)"
                              hint="Komisi I s.d. Komisi V" icon="dashboard" />
                <x-senat.stat label="Anggota Senat" :value="$direktori['members'] ?? 0"
                              hint="Pimpinan, BPH, dan anggota komisi" icon="users" />
                <x-senat.stat label="Periode Jabatan" :value="$direktori['period'] ?? '—'"
                              hint="Masa bakti kolegial Senat" icon="calendar" />
            </div>
        </div>
    </section>

@endsection
