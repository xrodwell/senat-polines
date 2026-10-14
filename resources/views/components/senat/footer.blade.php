@php
    /**
     * Footer / Public — Figma node 2006:14 (4 kolom desktop).
     * Responsif: 1 kolom mobile → 2 kolom ≥640 → 4 kolom ≥1024.
     */
@endphp

<footer class="border-t border-senat-line bg-white">
    <div class="senat-shell grid grid-cols-1 gap-8 py-10 sm:grid-cols-2 sm:gap-10 lg:grid-cols-[300px_280px_1fr_1fr] lg:gap-0 lg:py-12">

        {{-- Kolom 1: identitas --}}
        <div class="lg:pr-10">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo-polines.png') }}" alt="Logo Politeknik Negeri Semarang"
                     class="h-[38px] w-[38px] rounded-md object-contain">
                <span class="leading-none">
                    <span class="block text-[13px] font-bold text-[#0f172a]">SENAT AKADEMIK</span>
                    <span class="mt-1 block text-[10px] font-medium text-senat-muted">Politeknik Negeri Semarang</span>
                </span>
            </div>
            <p class="senat-prose mt-5 max-w-[290px] text-[12px] leading-[1.5] text-senat-muted">
                Lembaga normatif tertinggi di bidang akademik Politeknik Negeri Semarang yang merumuskan kebijakan dan menjaga mutu akademik institusi.
            </p>
            <p class="mt-6 text-[12px] font-medium leading-[1.5] text-senat-muted">
                Jl. Prof. Soedarto, S.H.<br>
                Tembalang, Semarang<br>
                Jawa Tengah 50275
            </p>
        </div>

        {{-- Kolom 2: komisi --}}
        <div>
            <h2 class="text-[13px] font-bold text-[#0f172a]">Komisi Senat</h2>
            <ul class="mt-4 space-y-[2px] text-[12px] text-senat-muted sm:mt-5">
                @forelse (\App\Support\SenatDirectory::committees() as $committee)
                    <li>
                        <a href="{{ route('public.profile.structure') }}"
                           class="senat-tap inline-flex min-h-[36px] items-center transition-colors hover:text-senat-primary">
                            {{ \App\Support\SenatDirectory::committeeLabel($committee['code']) }}
                            — {{ \Illuminate\Support\Str::after($committee['name'], 'Komisi ') }}
                        </a>
                    </li>
                @empty
                    <li>Komisi I — Pendidikan dan Pengajaran</li>
                    <li>Komisi II — Penelitian dan Pengabdian</li>
                    <li>Komisi III — Kelembagaan dan SDM</li>
                    <li>Komisi IV — Pengembangan dan Kerja Sama</li>
                    <li>Komisi V — Komisi Etik</li>
                @endforelse
            </ul>
        </div>

        {{-- Kolom 3: layanan --}}
        <div>
            <h2 class="text-[13px] font-bold text-[#0f172a]">Layanan</h2>
            <ul class="mt-4 space-y-[2px] text-[12px] text-senat-muted sm:mt-5">
                <li><a href="{{ route('public.regulations') }}" class="senat-tap inline-flex min-h-[36px] items-center transition-colors hover:text-senat-primary">Produk Hukum &amp; Peraturan</a></li>
                <li><a href="{{ route('public.regulations.decrees') }}" class="senat-tap inline-flex min-h-[36px] items-center transition-colors hover:text-senat-primary">Surat Keputusan Senat</a></li>
                <li><a href="{{ route('public.aspirations') }}" class="senat-tap inline-flex min-h-[36px] items-center transition-colors hover:text-senat-primary">Kanal Aspirasi Civitas</a></li>
                <li><a href="{{ route('public.aspirations.track') }}" class="senat-tap inline-flex min-h-[36px] items-center transition-colors hover:text-senat-primary">Lacak Status Tiket</a></li>
                <li><a href="{{ route('public.archives') }}" class="senat-tap inline-flex min-h-[36px] items-center transition-colors hover:text-senat-primary">Arsip Kegiatan &amp; LPJ</a></li>
            </ul>
        </div>

        {{-- Kolom 4: kontak --}}
        <div>
            <h2 class="text-[13px] font-bold text-[#0f172a]">Kontak &amp; Jam Operasional</h2>
            <ul class="mt-4 space-y-1 text-[12px] text-senat-muted sm:mt-5">
                <li><a href="mailto:senat@polines.ac.id" class="transition-colors hover:text-senat-primary">senat@polines.ac.id</a></li>
                <li>(024) 7473417</li>
                <li>Senin – Jumat</li>
                <li>08.00 – 16.00 WIB</li>
                <li>Gedung Rektorat Lt. 2, Kampus Tembalang</li>
            </ul>
        </div>
    </div>

    <div class="senat-shell">
        <div class="h-px w-full bg-senat-line"></div>
        <p class="py-4 text-[10px] font-medium text-senat-muted">
            © {{ date('Y') }} Senat Akademik Politeknik Negeri Semarang. Seluruh hak cipta dilindungi.
        </p>
    </div>
</footer>
