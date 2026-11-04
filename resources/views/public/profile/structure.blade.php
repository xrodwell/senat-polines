@extends('layouts.public')

@section('title', 'Struktur Organisasi — Senat Akademik Politeknik Negeri Semarang')
@section('description', 'Bagan organisasi Senat Akademik Politeknik Negeri Semarang: susunan pimpinan kolegial, Badan Pekerja Harian, dan lima komisi fungsional beserta profil dan tupoksinya.')

@section('content')

    {{-- 02C — Profil · Struktur Organisasi (Figma node 2204:304) --}}
    <x-senat.page-hero
        eyebrow="Bagan Kelembagaan & Struktur"
        title="Struktur Organisasi Senat Akademik"
        subtitle="Susunan kepemimpinan kolegial, komisi fungsional I–V, dan sekretariat Senat Akademik Politeknik Negeri Semarang periode aktif."
    />

    @php
        $chair = $leadership['chair'] ?? [];
        $secretary = $leadership['secretary'] ?? [];
        $bph = $leadership['bph'] ?? [];

        // Inisial nama untuk lingkar avatar
        $initials = function (?string $name): string {
            if (! $name) {
                return 'SA';
            }

            $name = preg_replace('/\b(Dr|Drs|Dra|Ir|Prof|Hj|H)\.\s*/i', '', $name);
            $letters = [];

            foreach (preg_split('/\s+/', (string) $name) as $token) {
                $token = trim($token);
                if ($token === '' || str_contains($token, '.')) {
                    continue;
                }
                $letters[] = strtoupper(substr($token, 0, 1));
                if (count($letters) === 2) {
                    break;
                }
            }

            return $letters ? implode('', $letters) : 'SA';
        };

        // Pejabat di bawah Ketua: Sekretaris Senat + Badan Pekerja Harian
        $officers = [];

        if (! empty($secretary)) {
            $officers[] = [
                'role' => $secretary['role'] ?? 'Sekretaris Senat',
                'name' => $secretary['name'] ?? '—',
                'organ' => $secretary['organ'] ?? 'Pimpinan Senat',
                'nip' => $secretary['nip'] ?? '197607202008011011',
                'focus' => $secretary['focus'] ?? 'Mengelola persidangan, verifikasi risalah keputusan pleno, dan pengelolaan arsip normatif Senat.',
                'slug' => $secretary['slug'] ?? 'andi-setiawan',
            ];
        }

        foreach ($bph as $person) {
            $officers[] = [
                'role' => $person['role'] ?? 'Badan Pekerja Harian',
                'name' => $person['name'] ?? '—',
                'organ' => $person['organ'] ?? 'Badan Pekerja Harian',
                'nip' => $person['nip'] ?? null,
                'focus' => $person['focus'] ?? 'Mengoordinasikan kerja antar-komisi dan menyiapkan berkas sidang.',
                'slug' => $person['slug'] ?? \App\Support\SenatDirectory::slug($person['name'] ?? ''),
            ];
        }

        $chairModalData = [
            'name' => $chair['name'] ?? 'Dr. Sukamto, S.Kom., M.T.',
            'role' => ($chair['role'] ?? 'Ketua Senat') . ' Akademik',
            'organ' => $chair['organ'] ?? 'Pimpinan Senat',
            'nip' => $chair['nip'] ?? '197101172003121001',
            'period' => $period,
            'focus' => $chair['focus'] ?? 'Memimpin persidangan pleno, menetapkan agenda normatif, dan merepresentasikan Senat kepada Direktur serta Kemendiktisaintek.',
            'slug' => $chair['slug'] ?? 'sukamto',
            'initials' => $initials($chair['name'] ?? 'Dr. Sukamto'),
            'photo' => ! empty($chair['photo']) ? asset($chair['photo']) : null,
        ];
    @endphp

    {{-- ================= BAGAN FORMAL DENGAN MODAL PROFIL INTERAKTIF ================= --}}
    <section class="senat-shell pt-14 lg:pt-16"
             x-data="{
                 openModal: false,
                 profile: {
                     name: '',
                     role: '',
                     organ: '',
                     nip: '',
                     period: '{{ $period }}',
                     focus: '',
                     slug: '',
                     initials: '',
                     photo: null,
                     secretary: '',
                     members_count: null
                 },
                 showPerson(data) {
                     this.profile = data;
                     this.openModal = true;
                 }
             }"
             @keydown.escape.window="openModal = false">

        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.1em] text-senat-link">Bagan Formal</p>
                <h2 class="mt-3 text-[28px] font-bold leading-[1.15] text-senat-ink">Struktur Pimpinan, Komisi, dan Anggota</h2>
            </div>
            <p class="text-xs text-senat-muted italic">
                * Klik pada setiap bagan untuk melihat rincian profil jabatan &amp; mandatnya.
            </p>
        </div>

        <div class="mt-8 rounded-[14px] border border-senat-line bg-white p-6 lg:p-8">

            {{-- Judul bagan --}}
            <div class="rounded-[7px] bg-senat-badgeBg px-4 py-2 text-center">
                <span class="mx-auto mb-1 block h-[3px] w-[38px] rounded-[2px] bg-senat-orange" aria-hidden="true"></span>
                <p class="text-[10px] font-bold uppercase tracking-[0.08em] text-senat-hero">Struktur Organisasi Senat Akademik</p>
            </div>

            {{-- 1. KETUA SENAT (BISA DIKLIK) --}}
            @if (! empty($chair))
                <div @click="showPerson({{ json_encode($chairModalData) }})"
                     class="group mx-auto mt-8 w-full max-w-[500px] cursor-pointer overflow-hidden rounded-[14px] border border-[#144f8c] bg-senat-hero transition-all duration-200 hover:scale-[1.015] hover:shadow-xl hover:border-senat-orange">
                    <div class="h-[5px] w-full bg-senat-orange" aria-hidden="true"></div>
                    <div class="flex items-center justify-between gap-4 px-[22px] py-5">
                        <div class="flex items-center gap-5 min-w-0">
                            @if (! empty($chair['photo']))
                                <img src="{{ asset($chair['photo']) }}" alt="{{ $chair['name'] }}"
                                     class="h-[62px] w-[62px] shrink-0 rounded-full border-2 border-senat-orange/60 object-cover object-top shadow-md transition-transform duration-200 group-hover:scale-105">
                            @else
                                <span class="flex h-[62px] w-[62px] shrink-0 items-center justify-center rounded-full border border-[#a3bdd6] bg-[#0a4a86] text-[16px] font-bold text-[#add4ff] transition-transform duration-200 group-hover:scale-105" aria-hidden="true">
                                    {{ $initials($chair['name'] ?? null) }}
                                </span>
                            @endif
                            <div class="min-w-0">
                                <p class="text-[10px] font-bold uppercase tracking-[0.06em] text-[#add4ff]">{{ $chair['role'] ?? 'Ketua Senat' }} Akademik</p>
                                <p class="mt-1 text-[18px] font-bold leading-tight text-white group-hover:text-senat-orange transition-colors truncate sm:overflow-visible sm:whitespace-normal">{{ $chair['name'] ?? '—' }}</p>
                                <p class="mt-1 text-[10px] font-medium text-[#c2deff]">Masa Bakti {{ $period }}</p>
                            </div>
                        </div>
                        <span class="hidden sm:inline-flex shrink-0 items-center gap-1 rounded-full bg-white/10 px-2.5 py-1 text-[9px] font-bold text-senat-skySoft group-hover:bg-senat-orange group-hover:text-senat-primary transition-colors">
                            <span>Profil</span>
                            <span aria-hidden="true">›</span>
                        </span>
                    </div>
                </div>
            @endif

            {{-- Penghubung: Ketua → Pejabat --}}
            <div class="flex justify-center" aria-hidden="true">
                <div class="h-6 w-[2px] rounded-[1px] bg-[#8cabc9]"></div>
            </div>

            <div class="hidden lg:block" aria-hidden="true">
                <div class="mx-auto h-[2px] w-[69.3%] rounded-[1px] bg-[#8cabc9]"></div>
                <div class="mx-auto grid w-[69.3%] grid-cols-3">
                    @for ($i = 0; $i < 3; $i++)
                        <div class="mx-auto h-[18px] w-[2px] rounded-[1px] bg-[#8cabc9]"></div>
                    @endfor
                </div>
            </div>

            {{-- 2. SEKRETARIS SENAT & BADAN PEKERJA HARIAN (BISA DIKLIK) --}}
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($officers as $officer)
                    @php
                        $officerModalData = [
                            'name' => $officer['name'],
                            'role' => $officer['role'],
                            'organ' => $officer['organ'] ?? 'Pimpinan & Badan Pekerja Harian',
                            'nip' => $officer['nip'] ?? null,
                            'period' => $period,
                            'focus' => $officer['focus'] ?? 'Mengoordinasikan kerja kelembagaan dan kelancaran persidangan Senat.',
                            'slug' => $officer['slug'] ?? null,
                            'initials' => $initials($officer['name']),
                        ];
                    @endphp
                    <div @click="showPerson({{ json_encode($officerModalData) }})"
                         class="group flex h-[110px] cursor-pointer items-center justify-between rounded-[12px] border border-[#d9e3f0] bg-white px-[18px] transition-all duration-200 hover:scale-[1.015] hover:border-senat-primary hover:shadow-md">
                        <div class="flex items-center min-w-0">
                            <span class="flex h-[58px] w-[58px] shrink-0 items-center justify-center rounded-full border border-[#a3bdd6] bg-[#d6e5f5] transition-transform duration-200 group-hover:scale-105">
                                <span class="flex h-[50px] w-[50px] items-center justify-center rounded-full border border-[#bdd1e5] bg-senat-badgeBg text-[15px] font-bold text-senat-link" aria-hidden="true">
                                    {{ $initials($officer['name']) }}
                                </span>
                            </span>
                            <div class="ml-4 min-w-0">
                                <p class="text-[10px] font-bold uppercase tracking-[0.06em] text-senat-link">{{ $officer['role'] }}</p>
                                <p class="mt-1 text-[13px] font-bold leading-snug text-senat-ink group-hover:text-senat-primary transition-colors truncate">{{ $officer['name'] }}</p>
                                <p class="mt-1 text-[9px] text-senat-body">Masa Bakti {{ $period }}</p>
                            </div>
                        </div>
                        <span class="hidden sm:block text-senat-muted group-hover:text-senat-primary transition-colors pl-2" aria-hidden="true">›</span>
                    </div>
                @empty
                    <div class="sm:col-span-2 lg:col-span-3">
                        <x-senat.empty title="Data pimpinan belum dimuat" />
                    </div>
                @endforelse
            </div>

            {{-- Sub-judul: Pimpinan dan Lima Komisi --}}
            <div class="mt-12 border-t border-senat-line pt-8">
                <p class="text-[11px] font-bold uppercase tracking-[0.1em] text-senat-link">Struktur Organisasi</p>
                <h3 class="mt-3 text-[28px] font-bold leading-[1.15] text-senat-ink">Pimpinan dan Lima Komisi Senat</h3>
            </div>

            {{-- Penghubung: Pejabat → Komisi --}}
            <div class="mt-6 flex justify-center" aria-hidden="true">
                <div class="h-[30px] w-[2px] rounded-[1px] bg-[#8cabc9]"></div>
            </div>

            {{-- Panel koordinasi komisi --}}
            <div class="mx-auto flex w-full max-w-[400px] items-center gap-4 rounded-[10px] border border-[#a3c2e0] bg-senat-badgeBg px-5 py-3">
                <span class="flex h-[26px] w-[26px] shrink-0 items-center justify-center rounded-[7px] bg-senat-hero text-[13px] font-bold leading-none text-white" aria-hidden="true"><x-senat.icon name="minutes" class="h-[18px] w-[18px]" /></span>
                <div class="min-w-0">
                    <p class="text-[10px] font-bold uppercase tracking-[0.06em] text-senat-hero">Koordinasi Komisi Senat Akademik</p>
                    <p class="mt-1 text-[9px] text-senat-body">Telaah kebijakan melalui lima bidang mandat.</p>
                </div>
            </div>

            {{-- Penghubung: Koordinasi → Lima Komisi --}}
            <div class="flex justify-center" aria-hidden="true">
                <div class="h-[30px] w-[2px] rounded-[1px] bg-[#8cabc9]"></div>
            </div>

            <div class="hidden lg:block" aria-hidden="true">
                <div class="mx-auto h-[2px] w-[81.3%] rounded-[1px] bg-[#8cabc9]"></div>
                <div class="mx-auto grid w-[81.3%] grid-cols-5">
                    @for ($i = 0; $i < 5; $i++)
                        <div class="mx-auto h-[24px] w-[2px] rounded-[1px] bg-[#8cabc9]"></div>
                    @endfor
                </div>
            </div>

            {{-- 3. KARTU LIMA KOMISI (BISA DIKLIK KETUA & DETAIL KOMISINYA) --}}
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-5">
                @forelse ($committees as $committee)
                    @php
                        $leadSlug = \App\Support\SenatDirectory::slug($committee['lead'] ?? '');
                        $committeeLabel = \App\Support\SenatDirectory::committeeLabel($committee['code']);
                        $committeeModalData = [
                            'name' => $committee['lead'] ?? '—',
                            'role' => 'Ketua ' . $committeeLabel,
                            'organ' => $committeeLabel . ' — ' . $committee['name'],
                            'nip' => null,
                            'period' => $period,
                            'secretary' => $committee['secretary'] ?? null,
                            'members_count' => $committee['members_count'] ?? (count($committee['members'] ?? []) + 2),
                            'focus' => $committee['focus'] ?? '',
                            'slug' => $leadSlug,
                            'initials' => $initials($committee['lead'] ?? null),
                        ];
                    @endphp
                    <article @click="showPerson({{ json_encode($committeeModalData) }})"
                             class="group flex flex-col cursor-pointer overflow-hidden rounded-[12px] border border-[#d9e3f0] bg-white transition-all duration-200 hover:scale-[1.02] hover:border-senat-primary hover:shadow-lg">
                        <div class="h-1 w-full {{ $loop->iteration === 3 ? 'bg-senat-orange' : 'bg-senat-hero' }}" aria-hidden="true"></div>

                        <div class="flex flex-1 flex-col p-5">
                            <div class="flex items-center gap-3">
                                <span class="flex h-[50px] w-[50px] shrink-0 items-center justify-center rounded-full border border-[#a3bdd6] bg-[#d6e5f5] transition-transform duration-200 group-hover:scale-105">
                                    <span class="flex h-[42px] w-[42px] items-center justify-center rounded-full border border-[#bdd1e5] bg-senat-badgeBg text-[14px] font-bold text-senat-link" aria-hidden="true">
                                        {{ $initials($committee['lead'] ?? null) }}
                                    </span>
                                </span>
                                <div class="min-w-0">
                                    <p class="text-[10px] font-bold uppercase tracking-[0.06em] text-senat-link">
                                        {{ $committeeLabel }}
                                    </p>
                                    <p class="mt-0.5 text-[8px] font-medium uppercase leading-tight text-senat-body group-hover:text-senat-primary transition-colors truncate">{{ $committee['lead'] ?? '—' }}</p>
                                </div>
                            </div>

                            <p class="mt-4 text-center text-[14px] font-bold leading-snug text-senat-ink group-hover:text-senat-primary transition-colors">{{ $committee['name'] }}</p>
                            <p class="mt-2 flex-1 text-center text-[10px] leading-[1.3] text-senat-body line-clamp-3">{{ $committee['focus'] }}</p>

                            <div class="mt-4 h-px w-full bg-[#d9e3f0]" aria-hidden="true"></div>

                            <button type="button"
                                    class="mt-3 block text-center text-[10px] font-bold text-senat-link group-hover:text-senat-primary transition-colors">
                                Lihat profil &amp; mandat <span aria-hidden="true">›</span>
                            </button>
                        </div>
                    </article>
                @empty
                    <div class="sm:col-span-2 lg:col-span-5">
                        <x-senat.empty title="Data komisi belum dimuat" />
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ================= MODAL POP-UP PROFIL INTERAKTIF ================= --}}
        <div x-cloak x-show="openModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#181a58]/60 backdrop-blur-sm"
             role="dialog" aria-modal="true" @click.self="openModal = false">

            <div x-show="openModal"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative w-full max-w-lg overflow-hidden rounded-2xl border border-senat-line bg-white shadow-2xl">

                {{-- Strip gradasi atas sesuai warna brand baru --}}
                <div class="h-2 w-full bg-gradient-to-r from-senat-primary via-senat-hero to-senat-orange"></div>

                <button type="button" @click="openModal = false"
                        aria-label="Tutup modal"
                        class="absolute right-4 top-5 flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-800 transition">
                    <x-senat.icon name="x" class="h-4 w-4" />
                </button>

                <div class="p-6 sm:p-7">
                    {{-- Header Profil --}}
                    <div class="flex items-start gap-4">
                        <template x-if="profile.photo">
                            <img :src="profile.photo" :alt="profile.name" class="h-16 w-16 shrink-0 rounded-2xl border-2 border-senat-orange/40 object-cover object-top shadow-md">
                        </template>
                        <template x-if="!profile.photo">
                            <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl border-2 border-senat-orange/40 bg-senat-hero text-2xl font-bold text-white shadow-md">
                                <span x-text="profile.initials"></span>
                            </span>
                        </template>
                        <div class="min-w-0 pr-6">
                            <span class="inline-block rounded-md bg-senat-badgeBg px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-senat-link border border-senat-badgeLine/40"
                                  x-text="profile.organ"></span>
                            <h3 class="mt-1.5 text-lg font-bold leading-snug text-senat-ink" x-text="profile.name"></h3>
                            <p class="text-xs font-semibold text-senat-orange" x-text="profile.role"></p>
                        </div>
                    </div>

                    {{-- Detail Box --}}
                    <div class="mt-5 space-y-3 rounded-xl border border-slate-100 bg-slate-50/75 p-4 text-xs">
                        <template x-if="profile.nip">
                            <div class="flex items-center justify-between border-b border-slate-200/60 pb-2">
                                <span class="font-medium text-slate-500">NIP</span>
                                <span class="font-mono font-bold text-slate-800" x-text="profile.nip"></span>
                            </div>
                        </template>
                        <div class="flex items-center justify-between border-b border-slate-200/60 pb-2">
                            <span class="font-medium text-slate-500">Masa Bakti</span>
                            <span class="font-semibold text-slate-800" x-text="profile.period"></span>
                        </div>
                        <template x-if="profile.secretary">
                            <div class="flex items-center justify-between border-b border-slate-200/60 pb-2">
                                <span class="font-medium text-slate-500">Sekretaris Komisi</span>
                                <span class="font-semibold text-slate-800 text-right" x-text="profile.secretary"></span>
                            </div>
                        </template>
                        <template x-if="profile.members_count">
                            <div class="flex items-center justify-between border-b border-slate-200/60 pb-2">
                                <span class="font-medium text-slate-500">Kekuatan Anggota</span>
                                <span class="font-bold text-senat-link" x-text="profile.members_count + ' Personel'"></span>
                            </div>
                        </template>
                        <div>
                            <span class="block font-medium text-slate-500 mb-1">Tugas &amp; Fokus Mandat:</span>
                            <p class="leading-relaxed text-slate-700 text-[11.5px]" x-text="profile.focus"></p>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="mt-6 flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="openModal = false"
                                class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                            Tutup
                        </button>
                        <template x-if="profile.slug">
                            <a :href="'{{ url('/direktori-komisi') }}/' + profile.slug"
                               class="inline-flex items-center gap-1.5 rounded-lg bg-senat-primary px-4 py-2 text-xs font-bold text-white hover:bg-senat-primaryHover transition shadow-sm">
                                <span>Lihat Profil Lengkap</span>
                                <span aria-hidden="true">›</span>
                            </a>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= RINCIAN TUPOKSI KOMISI ================= --}}
    <section id="tupoksi-komisi" class="senat-shell scroll-mt-24 py-14 lg:py-16">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.1em] text-senat-link">Tugas Pokok &amp; Fungsi</p>
            <h2 class="mt-3 text-[28px] font-bold leading-[1.15] text-senat-ink">Rincian Tupoksi Lima Komisi Senat</h2>
            <p class="mt-3 max-w-[1264px] text-[14px] leading-[1.45] text-senat-body">
                Setiap komisi menjalankan mandat normatif yang ditetapkan Senat Akademik untuk periode {{ $period }}, mulai dari perumusan kebijakan hingga pengawasan pelaksanaannya.
            </p>
        </div>

        <div class="mt-8 space-y-4">
            @forelse ($committees as $committee)
                <article id="tupoksi-{{ $committee['code'] }}" class="scroll-mt-24 overflow-hidden rounded-[12px] border border-senat-line bg-white">
                    <div class="h-1 w-full {{ $loop->iteration === 3 ? 'bg-senat-orange' : 'bg-senat-hero' }}" aria-hidden="true"></div>

                    <div class="p-6 lg:p-8">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="min-w-0">
                                <span class="inline-flex items-center rounded-full border border-senat-badgeLine bg-senat-badgeBg px-2.5 py-[3px] text-[10px] font-bold uppercase tracking-[0.06em] text-senat-link">
                                    {{ \App\Support\SenatDirectory::committeeLabel($committee['code']) }}
                                </span>
                                <h3 class="mt-3 text-[18px] font-bold leading-snug text-senat-ink">{{ $committee['name'] }}</h3>
                            </div>
                            <x-senat.badge state="neutral" :dot="false">{{ $committee['members_count'] ?? count($committee['members'] ?? []) }} Anggota</x-senat.badge>
                        </div>

                        <dl class="mt-5 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-[10px] border border-[#f1f5f9] bg-[#f8fafc] px-4 py-3">
                                <dt class="text-[10px] font-bold uppercase tracking-[0.06em] text-senat-muted">Ketua Komisi</dt>
                                <dd class="mt-1 text-[13px] font-bold text-senat-ink">{{ $committee['lead'] ?? '—' }}</dd>
                            </div>
                            <div class="rounded-[10px] border border-[#f1f5f9] bg-[#f8fafc] px-4 py-3">
                                <dt class="text-[10px] font-bold uppercase tracking-[0.06em] text-senat-muted">Sekretaris Komisi</dt>
                                <dd class="mt-1 text-[13px] font-bold text-senat-ink">{{ $committee['secretary'] ?? '—' }}</dd>
                            </div>
                        </dl>

                        <div class="mt-5">
                            <p class="text-[10px] font-bold uppercase tracking-[0.06em] text-senat-muted">Tugas Pokok &amp; Fungsi</p>
                            <p class="mt-2 text-[12.5px] leading-[1.6] text-senat-body">{{ $committee['tupoksi'] ?? $committee['focus'] ?? '—' }}</p>
                        </div>

                        @if (! empty($committee['members']))
                            <div class="mt-5">
                                <p class="text-[10px] font-bold uppercase tracking-[0.06em] text-senat-muted">Anggota</p>
                                <ul class="mt-2 flex flex-wrap gap-2">
                                    @foreach ($committee['members'] as $member)
                                        <li class="rounded-full border border-senat-line bg-senat-surface px-3 py-1 text-[11px] text-senat-body">{{ $member }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </article>
            @empty
                <x-senat.empty title="Data komisi belum dimuat"
                               message="Rincian tupoksi komisi akan tampil setelah data kelembagaan tersedia." />
            @endforelse
        </div>
    </section>

    {{-- ================= RINGKASAN KELEMBAGAAN ================= --}}
    <section class="border-t border-senat-line bg-white">
        <div class="senat-shell py-14">
            <p class="text-[11px] font-bold uppercase tracking-[0.1em] text-senat-link">Ringkasan Kelembagaan</p>
            <h2 class="mt-3 text-[22px] font-bold leading-[1.2] text-[#0f172a]">Senat Akademik dalam Angka</h2>

            <div class="mt-6 grid gap-4 sm:grid-cols-3">
                <x-senat.stat label="Komisi Fungsional" :value="$direktori['committees'] ?? count($committees)"
                              hint="Komisi I s.d. Komisi V" icon="dashboard" />
                <x-senat.stat label="Anggota Senat" :value="$direktori['members'] ?? 0"
                              hint="Pimpinan, BPH, dan anggota komisi" icon="users" />
                <x-senat.stat label="Periode Jabatan" :value="$direktori['period'] ?? $period"
                              hint="Masa bakti kolegial Senat" icon="calendar" />
            </div>

            <div class="mt-6 flex flex-wrap gap-3">
                <x-senat.button variant="primary" size="md" :href="route('public.directory')" trailing="›">
                    Direktori Komisi &amp; Anggota
                </x-senat.button>
                <x-senat.button variant="secondary" size="md" :href="route('public.profile.tasks')">
                    Tugas &amp; Wewenang Senat
                </x-senat.button>
            </div>
        </div>
    </section>

@endsection
