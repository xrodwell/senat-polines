<footer class="border-t border-senat-line bg-white">
    <div class="senat-shell grid grid-cols-1 items-start gap-8 py-10 text-sm leading-6 text-senat-muted sm:grid-cols-2 xl:grid-cols-[minmax(0,1.15fr)_minmax(0,1.4fr)_minmax(0,1fr)_minmax(0,1fr)] lg:py-12">

        {{-- Kolom 1: identitas --}}
        <div class="min-w-0">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo-polines.png') }}" alt="Logo Politeknik Negeri Semarang"
                     class="h-10 w-10 shrink-0 rounded-md object-contain">
                <span class="leading-none">
                    <span class="block text-sm font-bold leading-6 text-[#0f172a]">SENAT AKADEMIK</span>
                    <span class="mt-1 block text-xs font-medium leading-5 text-senat-muted">Politeknik Negeri Semarang</span>
                </span>
            </div>
            <p class="senat-prose mt-4 max-w-sm">
                Lembaga normatif tertinggi di bidang akademik Politeknik Negeri Semarang yang merumuskan kebijakan dan menjaga mutu akademik institusi.
            </p>
        </div>

        {{-- Kolom 2: komisi & BP --}}
        <div class="min-w-0">
            <h2 class="text-sm font-bold leading-6 text-[#0f172a]">Komisi &amp; BP Senat</h2>
            <ul class="mt-3 space-y-1">
                @forelse (\App\Support\SenatDirectory::committees() as $committee)
                    @php
                        $isBp = ($committee['code'] ?? '') === 'BP';
                        $footerLabel = $isBp
                            ? 'BP — Badan Pekerja'
                            : (\App\Support\SenatDirectory::committeeLabel($committee['code']) . ' — ' . \Illuminate\Support\Str::after($committee['name'], 'Komisi '));
                    @endphp
                    <li>
                        <a href="{{ route('public.profile.structure') }}#tupoksi-{{ $committee['code'] }}"
                           class="senat-tap grid min-h-[36px] grid-cols-[max-content_minmax(0,1fr)] items-baseline gap-x-2 py-1.5 transition-colors hover:text-senat-primary">
                            <span class="whitespace-nowrap">{{ \Illuminate\Support\Str::before($footerLabel, ' — ') }} —</span>
                            <span>{{ \Illuminate\Support\Str::after($footerLabel, ' — ') }}</span>
                        </a>
                    </li>
                @empty
                    <li class="grid grid-cols-[max-content_minmax(0,1fr)] gap-x-2 py-1.5"><span class="whitespace-nowrap">Komisi I —</span><span>Pendidikan dan Pengajaran</span></li>
                    <li class="grid grid-cols-[max-content_minmax(0,1fr)] gap-x-2 py-1.5"><span class="whitespace-nowrap">Komisi II —</span><span>Penelitian dan Pengabdian</span></li>
                    <li class="grid grid-cols-[max-content_minmax(0,1fr)] gap-x-2 py-1.5"><span class="whitespace-nowrap">Komisi III —</span><span>Kelembagaan dan SDM</span></li>
                    <li class="grid grid-cols-[max-content_minmax(0,1fr)] gap-x-2 py-1.5"><span class="whitespace-nowrap">Komisi IV —</span><span>Pengembangan dan Kerja Sama</span></li>
                    <li class="grid grid-cols-[max-content_minmax(0,1fr)] gap-x-2 py-1.5"><span class="whitespace-nowrap">Komisi V —</span><span>Komisi Etik</span></li>
                    <li class="grid grid-cols-[max-content_minmax(0,1fr)] gap-x-2 py-1.5"><span class="whitespace-nowrap">BP —</span><span>Badan Pekerja</span></li>
                @endforelse
            </ul>
        </div>

        {{-- Kolom 3: layanan --}}
        <div class="min-w-0">
            <h2 class="text-sm font-bold leading-6 text-[#0f172a]">Layanan</h2>
            <ul class="mt-3 space-y-1">
                <li><a href="{{ route('public.regulations') }}" class="senat-tap flex min-h-[36px] items-center py-1.5 transition-colors hover:text-senat-primary">Produk Hukum &amp; Peraturan</a></li>
                <li><a href="{{ route('public.regulations.decrees') }}" class="senat-tap flex min-h-[36px] items-center py-1.5 transition-colors hover:text-senat-primary">Surat Keputusan Senat</a></li>
                <li><a href="{{ route('public.aspirations') }}" class="senat-tap flex min-h-[36px] items-center py-1.5 transition-colors hover:text-senat-primary">Kanal Aspirasi Civitas</a></li>
                <li><a href="{{ route('public.aspirations.track') }}" class="senat-tap flex min-h-[36px] items-center py-1.5 transition-colors hover:text-senat-primary">Lacak Status Tiket</a></li>
                <li><a href="{{ route('public.archives') }}" class="senat-tap flex min-h-[36px] items-center py-1.5 transition-colors hover:text-senat-primary">Arsip Kegiatan &amp; LPJ</a></li>
            </ul>
        </div>

        {{-- Kolom 4: kontak --}}
        <div class="min-w-0">
            <h2 class="text-sm font-bold leading-6 text-[#0f172a]">Kontak &amp; Jam Operasional</h2>
            <ul class="mt-3 space-y-1.5">
                <li>Telp. +62 24 7473417, 7499585, 7499586</li>
                <li>Fax. +62 24 7472396</li>
                <li>Email: <a href="mailto:sekretariat@polines.ac.id" class="senat-tap inline-flex min-h-[36px] items-center break-all py-1.5 transition-colors hover:text-senat-primary">sekretariat@polines.ac.id</a></li>
                <li>Website: <a href="https://www.polines.ac.id" target="_blank" rel="noopener noreferrer" class="senat-tap inline-flex min-h-[36px] items-center break-all py-1.5 transition-colors hover:text-senat-primary">www.polines.ac.id</a></li>
                <li class="pt-1">Senin – Jumat · 08.00 – 16.00 WIB</li>
                <li class="pt-1">
                    Jl. Prof. H. Soedarto S.H.,<br>
                    Tembalang, Semarang<br>
                    Kode Pos 50275
                </li>
            </ul>
        </div>
    </div>

    <div class="senat-shell">
        <div class="h-px w-full bg-senat-line"></div>
        <p class="py-5 text-center text-xs font-medium leading-5 text-senat-muted sm:text-left">
            © {{ date('Y') }} Senat Akademik Politeknik Negeri Semarang. Seluruh hak cipta dilindungi.
        </p>
    </div>
</footer>
