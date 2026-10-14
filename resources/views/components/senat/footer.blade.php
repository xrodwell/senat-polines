@php
    /**
     * Footer / Public — Figma node 2006:14
     * "Shared four-column footer for all Senat Polines public and portal screens."
     */
@endphp

<footer class="border-t border-senat-line bg-white">
    <div class="senat-shell grid gap-10 py-12 lg:grid-cols-[330px_300px_300px_300px] lg:gap-0">

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
            <p class="mt-5 max-w-[270px] text-[12px] leading-[1.45] text-senat-muted">
                Lembaga normatif tertinggi di bidang akademik Politeknik Negeri Semarang yang merumuskan kebijakan dan menjaga mutu akademik institusi.
            </p>
            <p class="mt-6 text-[12px] font-medium leading-[1.45] text-senat-muted">
                Jl. Prof. Soedarto, S.H.<br>
                Tembalang, Semarang<br>
                Jawa Tengah 50275
            </p>
        </div>

        {{-- Kolom 2: komisi --}}
        <div>
            <h3 class="text-[13px] font-bold text-[#0f172a]">Komisi Senat</h3>
            <ul class="mt-5 space-y-[6px] text-[12px] text-senat-muted">
                @forelse (\App\Support\SenatDirectory::committees() as $committee)
                    <li>
                        <a href="{{ route('public.profile.structure') }}" class="transition-colors hover:text-senat-primary">
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
            <h3 class="text-[13px] font-bold text-[#0f172a]">Layanan</h3>
            <ul class="mt-5 space-y-[6px] text-[12px] text-senat-muted">
                <li><a href="{{ route('public.regulations') }}" class="transition-colors hover:text-senat-primary">Produk Hukum &amp; Peraturan</a></li>
                <li><a href="{{ route('public.regulations.decrees') }}" class="transition-colors hover:text-senat-primary">Surat Keputusan Senat</a></li>
                <li><a href="{{ route('public.aspirations') }}" class="transition-colors hover:text-senat-primary">Kanal Aspirasi Civitas</a></li>
                <li><a href="{{ route('public.aspirations.track') }}" class="transition-colors hover:text-senat-primary">Lacak Status Tiket</a></li>
                <li><a href="{{ route('public.archives') }}" class="transition-colors hover:text-senat-primary">Arsip Kegiatan &amp; LPJ</a></li>
            </ul>
        </div>

        {{-- Kolom 4: kontak --}}
        <div>
            <h3 class="text-[13px] font-bold text-[#0f172a]">Kontak &amp; Jam Operasional</h3>
            <ul class="mt-5 space-y-[6px] text-[12px] text-senat-muted">
                <li>senat@polines.ac.id</li>
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
