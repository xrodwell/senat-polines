@extends('layouts.public')

@section('title', 'Struktur Organisasi — Senat Akademik Politeknik Negeri Semarang')
@section('description', 'Bagan organisasi Senat Akademik Politeknik Negeri Semarang: susunan pimpinan kolegial, BP (Badan Pekerja), dan lima komisi fungsional beserta profil dan tupoksinya.')

@section('content')

    {{-- 02C — Profil · Struktur Organisasi (Figma node 2204:304) --}}
    <x-senat.page-hero
        eyebrow="Bagan Kelembagaan & Struktur"
        title="Struktur Organisasi Senat Akademik"
        subtitle="Susunan kepemimpinan kolegial, komisi fungsional I–V, BP (Badan Pekerja), dan sekretariat Senat Akademik Politeknik Negeri Semarang periode aktif."
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

        // Pejabat di bawah Ketua: Sekretaris Senat + BP (Badan Pekerja)
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
                'role' => $person['role'] ?? 'BP (Badan Pekerja)',
                'name' => $person['name'] ?? '—',
                'organ' => $person['organ'] ?? 'BP (Badan Pekerja)',
                'nip' => $person['nip'] ?? (\App\Support\SenatDirectory::LECTURER_DATA[$person['name'] ?? '']['nip'] ?? null),
                'focus' => $person['focus'] ?? 'Mengoordinasikan kerja antar-komisi dan menyiapkan berkas sidang.',
                'slug' => $person['slug'] ?? \App\Support\SenatDirectory::slug($person['name'] ?? ''),
            ];
        }
    @endphp

    {{-- ================= BAGAN FORMAL ================= --}}
    <section class="senat-shell pt-14 lg:pt-16">

        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.1em] text-senat-link">Bagan Formal</p>
            <h2 class="mt-3 text-[28px] font-bold leading-[1.15] text-senat-ink">Struktur Pimpinan, Komisi, dan Anggota</h2>
        </div>

        <div class="mt-8 rounded-[14px] border border-senat-line bg-white p-6 lg:p-8">

            {{-- 1. KETUA SENAT (KLIK LANGSUNG KE DETAIL PROFIL) --}}
            @if (! empty($chair))
                <a href="{{ route('public.directory.member', $chair['slug'] ?? 'sukamto') }}"
                   class="group mx-auto block w-full max-w-[500px] cursor-pointer overflow-hidden rounded-[14px] border border-[#144f8c] bg-senat-hero transition-all duration-200 hover:scale-[1.015] hover:shadow-xl hover:border-senat-orange">
                    <div class="h-[5px] w-full bg-senat-orange" aria-hidden="true"></div>
                    <div class="flex items-center justify-between gap-4 px-[22px] py-5">
                        <div class="flex items-center gap-5 min-w-0">
                            @php
                                $chairPhoto = $chair['photo'] ?? (! empty($chair['nip']) ? 'images/members/' . $chair['nip'] . '.jpg' : null);
                                $hasChairPhoto = ! empty($chairPhoto) && (
                                    file_exists(public_path($chairPhoto)) || 
                                    file_exists(base_path('../Backend-Senat-Polines/public/' . $chairPhoto))
                                );
                            @endphp
                            @if ($hasChairPhoto)
                                <div class="h-[72px] w-[72px] shrink-0 overflow-hidden rounded-full border-2 border-senat-orange/70 shadow-md">
                                    <img src="{{ asset($chairPhoto) }}" alt="{{ $chair['name'] }}"
                                         class="h-full w-full object-cover object-top scale-110 origin-top transition-transform duration-200 group-hover:scale-120">
                                </div>
                            @else
                                <span class="flex h-[72px] w-[72px] shrink-0 items-center justify-center rounded-full border border-[#a3bdd6] bg-[#0a4a86] text-[18px] font-bold text-[#add4ff] transition-transform duration-200 group-hover:scale-105" aria-hidden="true">
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
                </a>
            @endif

            {{-- Penghubung: Ketua Senat → Sekretaris Senat --}}
            <div class="flex justify-center" aria-hidden="true">
                <div class="h-6 w-[2px] rounded-[1px] bg-[#8cabc9]"></div>
            </div>

            {{-- 2. SEKRETARIS SENAT (KLIK LANGSUNG KE DETAIL PROFIL) --}}
            @if (! empty($secretary))
                @php
                    $secretarySlug = $secretary['slug'] ?? \App\Support\SenatDirectory::slug($secretary['name'] ?? '');
                    $secNip = $secretary['nip'] ?? (\App\Support\SenatDirectory::LECTURER_DATA[$secretary['name'] ?? '']['nip'] ?? null);
                    $secPhoto = $secretary['photo'] ?? (! empty($secNip) ? 'images/members/' . $secNip . '.jpg' : null);
                    $hasSecPhoto = ! empty($secPhoto) && (
                        file_exists(public_path($secPhoto)) || 
                        file_exists(base_path('../Backend-Senat-Polines/public/' . $secPhoto))
                    );
                @endphp
                <a href="{{ route('public.directory.member', $secretarySlug) }}"
                   class="group mx-auto flex w-full max-w-[440px] cursor-pointer items-center justify-between rounded-[12px] border border-[#d9e3f0] bg-white px-[18px] py-4 transition-all duration-200 hover:scale-[1.015] hover:border-senat-primary hover:shadow-md">
                    <div class="flex items-center min-w-0">
                        <div class="flex h-[62px] w-[62px] shrink-0 items-center justify-center overflow-hidden rounded-full border-2 border-[#a3bdd6] shadow-sm">
                            @if ($hasSecPhoto)
                                <img src="{{ asset($secPhoto) }}" alt="{{ $secretary['name'] }}" class="h-full w-full object-cover object-top scale-110 origin-top transition-transform duration-200 group-hover:scale-120">
                            @else
                                <span class="flex h-full w-full items-center justify-center bg-[#d6e5f5] text-[16px] font-bold text-senat-link" aria-hidden="true">
                                    {{ $initials($secretary['name'] ?? null) }}
                                </span>
                            @endif
                        </div>
                        <div class="ml-4 min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-[0.06em] text-senat-link">{{ $secretary['role'] ?? 'Sekretaris Senat' }}</p>
                            <p class="mt-1 text-[14px] font-bold leading-snug text-senat-ink group-hover:text-senat-primary transition-colors truncate">{{ $secretary['name'] ?? '—' }}</p>
                            <p class="mt-0.5 text-[9px] text-senat-body">Masa Bakti {{ $period }}</p>
                        </div>
                    </div>
                    <span class="hidden sm:block text-senat-muted group-hover:text-senat-primary transition-colors pl-2" aria-hidden="true">›</span>
                </a>
            @endif

            {{-- Penghubung: Sekretaris Senat → Sekretariat --}}
            <div class="flex justify-center" aria-hidden="true">
                <div class="h-6 w-[2px] rounded-[1px] bg-[#8cabc9]"></div>
            </div>

            {{-- 3. SEKRETARIAT --}}
            <div class="mx-auto flex w-full max-w-[340px] items-center justify-center gap-3 rounded-[10px] border border-[#a3c2e0] bg-senat-badgeBg px-5 py-3 text-center">
                <span class="flex h-[26px] w-[26px] shrink-0 items-center justify-center rounded-[7px] bg-senat-hero text-white" aria-hidden="true">
                    <x-senat.icon name="minutes" class="h-[16px] w-[16px]" />
                </span>
                <div class="text-left">
                    <p class="text-[11px] font-bold uppercase tracking-[0.08em] text-senat-hero">Sekretariat</p>
                    <p class="text-[9px] text-senat-body">Koordinasi Komisi I–V &amp; BP (Badan Pekerja)</p>
                </div>
            </div>

            {{-- Penghubung: Sekretariat → 6 Kolom Komisi & BP --}}
            <div class="flex justify-center" aria-hidden="true">
                <div class="h-6 w-[2px] rounded-[1px] bg-[#8cabc9]"></div>
            </div>

            <div class="hidden xl:grid xl:grid-cols-6 xl:gap-2.5" aria-hidden="true">
                @for ($i = 0; $i < 6; $i++)
                    <div class="relative h-6">
                        <div class="absolute left-1/2 top-0 h-full w-[2px] -translate-x-1/2 rounded-b-[1px] bg-[#8cabc9]"></div>
                        @if ($i === 0)
                            <div class="absolute -right-2.5 left-1/2 top-0 h-[2px] bg-[#8cabc9]"></div>
                        @elseif ($i === 5)
                            <div class="absolute left-0 right-1/2 top-0 h-[2px] bg-[#8cabc9]"></div>
                        @else
                            <div class="absolute -right-2.5 left-0 top-0 h-[2px] bg-[#8cabc9]"></div>
                        @endif
                    </div>
                @endfor
            </div>

            {{-- 4. ENAM KOLOM SEJAJAR: BP DI KIRI AWAL, DIIKUTI KOMISI I–V (KETUA SAJA, KLIK KE /direktori-komisi) --}}
            @php
                $excelOrder = ['BP' => 1, 'KOM-I' => 2, 'KOM-II' => 3, 'KOM-III' => 4, 'KOM-IV' => 5, 'KOM-V' => 6];
                $orderedCommittees = collect($committees)
                    ->sortBy(fn ($item) => $excelOrder[$item['code'] ?? ''] ?? 99)
                    ->values();
            @endphp

            <div class="grid gap-2.5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                @forelse ($orderedCommittees as $committee)
                    @php
                        $leadName = $committee['lead'] ?? '—';
                        $committeeLabel = \App\Support\SenatDirectory::committeeLabel($committee['code'] ?? '');
                        $directoryUrl = route('public.directory', ['komisi' => $committee['code'] ?? '']);

                        // Cari NIP & foto Ketua Komisi
                        $leadNip = \App\Support\SenatDirectory::LECTURER_DATA[$leadName]['nip'] ?? null;
                        if (! $leadNip) {
                            foreach (\App\Support\SenatDirectory::members() as $m) {
                                if (! empty($m['name']) && (str_contains($m['name'], $leadName) || str_contains($leadName, $m['name']))) {
                                    $leadNip = $m['nip'] ?? null;
                                    break;
                                }
                            }
                        }
                        $leadPhoto = ! empty($leadNip) ? 'images/members/' . $leadNip . '.jpg' : null;
                        $hasLeadPhoto = ! empty($leadPhoto) && (
                            file_exists(public_path($leadPhoto)) || 
                            file_exists(base_path('../Backend-Senat-Polines/public/' . $leadPhoto))
                        );
                    @endphp
                    <a href="{{ $directoryUrl }}"
                       class="group flex flex-col overflow-hidden rounded-[12px] border border-[#d9e3f0] bg-white transition-all duration-200 hover:scale-[1.015] hover:border-senat-primary hover:shadow-lg">
                        <div class="h-1 w-full bg-senat-hero" aria-hidden="true"></div>

                        <div class="flex flex-1 flex-col p-4 text-center">
                            <div class="mx-auto flex h-[66px] w-[66px] shrink-0 items-center justify-center overflow-hidden rounded-full border-2 border-[#a3bdd6] shadow-sm">
                                @if ($hasLeadPhoto)
                                    <img src="{{ asset($leadPhoto) }}" alt="{{ $leadName }}" class="h-full w-full object-cover object-top scale-110 origin-top transition-transform duration-200 group-hover:scale-120">
                                @else
                                    <span class="flex h-full w-full items-center justify-center bg-senat-badgeBg text-[16px] font-bold text-senat-link" aria-hidden="true">
                                        {{ $initials($leadName) }}
                                    </span>
                                @endif
                            </div>

                            <p class="mt-2.5 text-[10px] font-bold uppercase tracking-[0.06em] text-senat-link">
                                {{ $committeeLabel }}
                            </p>
                            <p class="mt-0.5 flex min-h-[34px] items-start justify-center text-[11px] font-semibold leading-snug text-senat-body transition-colors group-hover:text-senat-primary">
                                {{ $leadName }}
                            </p>

                            <p class="mt-2.5 flex min-h-[54px] items-start justify-center text-[12.5px] font-bold leading-snug text-senat-ink transition-colors group-hover:text-senat-primary">
                                {{ $committee['name'] }}
                            </p>
                            <p class="mt-2 flex-1 text-[11px] leading-[1.45] text-senat-body">
                                {{ $committee['focus'] }}
                            </p>

                            <div class="mt-3.5 h-px w-full bg-[#d9e3f0]" aria-hidden="true"></div>

                            <span class="mt-2.5 block text-[10px] font-bold text-senat-link transition-colors group-hover:text-senat-primary">
                                Lihat anggota &amp; mandat <span aria-hidden="true">›</span>
                            </span>
                        </div>
                    </a>
                @empty
                    <div class="sm:col-span-2 lg:col-span-3 xl:col-span-6">
                        <x-senat.empty title="Data komisi belum dimuat" />
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- ================= RINCIAN TUPOKSI KOMISI & BP ================= --}}
    <section id="tupoksi-komisi" class="senat-shell scroll-mt-24 py-14 lg:py-16">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.1em] text-senat-link">Tugas Pokok &amp; Fungsi</p>
            <h2 class="mt-3 text-[28px] font-bold leading-[1.15] text-senat-ink">Rincian Tupoksi Komisi &amp; Badan Pekerja (BP)</h2>
            <p class="mt-3 max-w-[1264px] text-[14px] leading-[1.45] text-senat-body">
                Setiap komisi dan Badan Pekerja (BP) menjalankan mandat normatif yang ditetapkan Senat Akademik untuk periode {{ $period }}, mulai dari koordinasi persidangan hingga perumusan kebijakan dan pengawasan.
            </p>
        </div>

        <div class="mt-8 space-y-4">
            @forelse ($committees as $committee)
                @php
                    $isBp = ($committee['code'] ?? '') === 'BP';
                @endphp
                <article id="tupoksi-{{ $committee['code'] }}" class="scroll-mt-24 overflow-hidden rounded-[12px] border border-senat-line bg-white">
                    <div class="h-1 w-full bg-senat-hero" aria-hidden="true"></div>

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
                                <dt class="text-[10px] font-bold uppercase tracking-[0.06em] text-senat-muted">{{ $isBp ? 'Ketua BP' : 'Ketua Komisi' }}</dt>
                                <dd class="mt-1 text-[13px] font-bold text-senat-ink">{{ $committee['lead'] ?? '—' }}</dd>
                            </div>
                            <div class="rounded-[10px] border border-[#f1f5f9] bg-[#f8fafc] px-4 py-3">
                                <dt class="text-[10px] font-bold uppercase tracking-[0.06em] text-senat-muted">{{ $isBp ? 'Sekretaris BP' : 'Sekretaris Komisi' }}</dt>
                                <dd class="mt-1 text-[13px] font-bold text-senat-ink">{{ $committee['secretary'] ?? '—' }}</dd>
                            </div>
                        </dl>

                        <div class="mt-5">
                            <p class="text-[10px] font-bold uppercase tracking-[0.06em] text-senat-muted">Tugas Pokok &amp; Fungsi</p>
                            @if (! empty($committee['tupoksi_items']))
                                <ol class="mt-2 list-decimal space-y-1.5 pl-4 text-[12.5px] leading-[1.6] text-senat-body">
                                    @foreach ($committee['tupoksi_items'] as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ol>
                            @else
                                <p class="mt-2 text-[12.5px] leading-[1.6] text-senat-body">{{ $committee['tupoksi'] ?? $committee['focus'] ?? '—' }}</p>
                            @endif
                        </div>

                        @if (! empty($committee['members']))
                            <div class="mt-5">
                                <p class="text-[10px] font-bold uppercase tracking-[0.06em] text-senat-muted">Anggota</p>
                                <ul class="mt-2 flex flex-wrap gap-2">
                                    @foreach ($committee['members'] as $member)
                                        @php
                                            $memberName = is_array($member) ? ($member['nama'] ?? $member['name'] ?? '—') : $member;
                                            $memberSlug = is_array($member)
                                                ? ($member['slug'] ?? \App\Support\SenatDirectory::slug($memberName))
                                                : \App\Support\SenatDirectory::slug($memberName);
                                        @endphp
                                        <li class="rounded-full border border-senat-line bg-senat-surface px-3 py-1 text-[11px] text-senat-body hover:border-senat-primary hover:text-senat-primary transition-colors">
                                            <a href="{{ route('public.directory.member', $memberSlug) }}" class="transition-colors">
                                                {{ $memberName }}
                                            </a>
                                        </li>
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
                <x-senat.stat label="Komisi & Badan Pekerja" :value="count($committees) ?: ($direktori['committees'] ?? 6)"
                              hint="Komisi I–V & BP (Badan Pekerja)" icon="dashboard" />
                <x-senat.stat label="Anggota Senat" :value="$direktori['members'] ?? 0"
                              hint="Pimpinan, BP, dan anggota komisi" icon="users" />
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
