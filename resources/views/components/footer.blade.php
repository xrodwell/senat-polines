<footer class="bg-white border-t border-slate-200/80 mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">

            {{-- Kolom 1: Profil & Alamat --}}
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-polines-navy flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 14v7" />
                        </svg>
                    </div>
                    <div class="leading-tight">
                        <p class="text-sm font-extrabold tracking-tight text-slate-900">SENAT AKADEMIK</p>
                        <p class="text-xs text-slate-500 font-medium">Politeknik Negeri Semarang</p>
                    </div>
                </div>
                <p class="text-sm text-slate-600 leading-relaxed">
                    Lembaga normatif tertinggi di bidang akademik Politeknik Negeri Semarang yang merumuskan kebijakan dan menjaga mutu akademik institusi.
                </p>
                <address class="not-italic text-sm text-slate-600 leading-relaxed">
                    Jl. Prof. Soedarto, S.H.<br>
                    Tembalang, Semarang<br>
                    Jawa Tengah 50275
                </address>
            </div>

            {{-- Kolom 2: Komisi I–V --}}
            <div>
                <h3 class="text-sm font-semibold text-slate-900 tracking-tight mb-4">Komisi Senat</h3>
                <ul class="space-y-2.5">
                    <li>
                        <a href="{{ route('public.profile') }}" class="text-sm text-slate-600 hover:text-polines-navy transition-colors duration-150">
                            Komisi I — Pengembangan Pendidikan
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('public.profile') }}" class="text-sm text-slate-600 hover:text-polines-navy transition-colors duration-150">
                            Komisi II — Penelitian &amp; Pengabdian
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('public.profile') }}" class="text-sm text-slate-600 hover:text-polines-navy transition-colors duration-150">
                            Komisi III — Perencanaan &amp; Anggaran
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('public.profile') }}" class="text-sm text-slate-600 hover:text-polines-navy transition-colors duration-150">
                            Komisi IV — Kerja Sama &amp; Sistem Informasi
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('public.profile') }}" class="text-sm text-slate-600 hover:text-polines-navy transition-colors duration-150">
                            Komisi V — Etika &amp; Keilmuan
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Kolom 3: Layanan & Produk Hukum --}}
            <div>
                <h3 class="text-sm font-semibold text-slate-900 tracking-tight mb-4">Layanan</h3>
                <ul class="space-y-2.5">
                    <li>
                        <a href="{{ route('public.regulations') }}" class="text-sm text-slate-600 hover:text-polines-navy transition-colors duration-150">
                            Produk Hukum &amp; Peraturan
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('public.regulations') }}" class="text-sm text-slate-600 hover:text-polines-navy transition-colors duration-150">
                            Surat Keputusan Senat
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('public.aspirations') }}" class="text-sm text-slate-600 hover:text-polines-navy transition-colors duration-150">
                            Kanal Aspirasi Civitas
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('public.aspirations.track') }}" class="text-sm text-slate-600 hover:text-polines-navy transition-colors duration-150">
                            Lacak Status Tiket
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('public.archives') }}" class="text-sm text-slate-600 hover:text-polines-navy transition-colors duration-150">
                            Arsip Kegiatan &amp; LPJ
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('public.aspirations') }}" class="text-sm text-slate-600 hover:text-polines-navy transition-colors duration-150">
                            Kanal Aspirasi Civitas
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Kolom 4: Kontak & Jam Operasional --}}
            <div>
                <h3 class="text-sm font-semibold text-slate-900 tracking-tight mb-4">Kontak &amp; Jam Operasional</h3>
                <ul class="space-y-3">
                    <li class="flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-slate-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <a href="mailto:senat@polines.ac.id" class="text-sm text-slate-600 hover:text-polines-navy transition-colors duration-150">
                            senat@polines.ac.id
                        </a>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-slate-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        <span class="text-sm text-slate-600">(024) 7473417</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-slate-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="text-sm text-slate-600">
                            <p>Senin – Jumat</p>
                            <p class="tabular-nums">08.00 – 16.00 WIB</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-slate-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="text-sm text-slate-600">Gedung Rektorat Lt. 2, Kampus Tembalang</span>
                    </li>
                </ul>
            </div>

        </div>
    </div>

    {{-- Bottom Bar --}}
    <div class="border-t border-slate-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col sm:flex-row items-center justify-between gap-2">
            <p class="text-xs text-slate-500">
                &copy; 2026 Senat Akademik Politeknik Negeri Semarang. Seluruh hak cipta dilindungi.
            </p>
            <p class="text-xs text-slate-400">
                Dikelola oleh Sekretariat Senat Akademik
            </p>
        </div>
    </div>
</footer>
