@extends('layouts.public')

@section('title', 'Direktori Komisi & Anggota — Senat Akademik Politeknik Negeri Semarang')
@section('description', 'Direktori komisi kerja dan anggota Senat Akademik Politeknik Negeri Semarang periode '.$period.' — pimpinan komisi, sekretaris, anggota komisi, fokus kerja, dan tupoksi.')

@php
    /**
     * 03 — Direktori Komisi & Anggota — Figma node 2075:2 (1440×2532).
     * Data dari PublicController@directory: $committees, $members, $leadership, $period, $direktori.
     */
    $komisiLabel = fn (string $code): string => str_replace('KOM-', 'Komisi ', $code);
    $komisiShort = fn (string $name): string => preg_replace('/^Komisi\s+/', '', $name);
    $initialsOf = function (string $name): string {
        $parts = collect(explode(' ', preg_replace('/,.*$/', '', $name)))
            ->filter(fn ($part) => $part !== '' && ! str_contains($part, '.'))
            ->values();
        $out = $parts->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');

        return $out !== '' ? $out : mb_strtoupper(mb_substr($name, 0, 1));
    };

    $memberGroups = collect($members)->groupBy(fn (array $person) => $person['committee_code'] ?? '__senat');

    $panels = [];
    foreach ($committees as $committee) {
        $rows = collect($memberGroups[$committee['code']] ?? []);

        $panels[] = [
            'code' => $committee['code'],
            'label' => $komisiLabel($committee['code']),
            'short' => $komisiShort($committee['name']),
            'name' => $committee['name'],
            'focus' => $committee['focus'],
            'tupoksi' => $committee['tupoksi'] ?? null,
            'total' => $committee['members_count'],
            'lead' => $rows->first(fn (array $person) => $person['name'] === $committee['lead']),
            'secretary' => $rows->first(fn (array $person) => $person['name'] === $committee['secretary']),
            'anggota' => $rows->filter(fn (array $person) => str_starts_with($person['role'], 'Anggota '))->values()->all(),
        ];
    }

    $heroPanels = array_map(fn (array $panel) => [
        'code' => $panel['code'],
        'title' => $panel['label'].' · '.$panel['short'],
        'focus' => $panel['focus'],
        'badge' => $panel['total'].' Anggota Komisi',
        'composition' => '1 Ketua • 1 Sekretaris • '.count($panel['anggota']).' Anggota',
        'count' => count($panel['anggota']),
    ], $panels);

    $leaderRows = collect([$leadership['chair'] ?? null, $leadership['secretary'] ?? null])
        ->merge($leadership['bph'] ?? [])
        ->filter()
        ->values();
@endphp

@section('content')
    <div x-data="{
        active: 0,
        panels: @js($heroPanels),
        modalOpen: false,
        editMode: false,
        submitting: false,
        form: {
            id: '',
            nama: '',
            nama_lama: '',
            nim_nip: '',
            email: '',
            committee_code: '{{ $panels[0]['code'] ?? 'KOM-I' }}',
            role: 'Anggota',
            organ: '',
            focus: ''
        },
        deleteModalOpen: false,
        deleteTarget: {
            id: '',
            nama: '',
            committee_code: ''
        },
        toast: {
            show: false,
            message: '',
            type: 'success'
        },
        showToast(message, type = 'success') {
            this.toast.message = message;
            this.toast.type = type;
            this.toast.show = true;
            setTimeout(() => { this.toast.show = false; }, 4000);
        },
        openCreate(code) {
            this.editMode = false;
            const currentCode = code || (this.panels[this.active] ? this.panels[this.active].code : 'KOM-I');
            this.form = {
                id: '',
                nama: '',
                nama_lama: '',
                nim_nip: '',
                email: '',
                committee_code: currentCode,
                role: 'Anggota',
                organ: '',
                focus: ''
            };
            this.modalOpen = true;
        },
        openEdit(person, code) {
            if (!person || !person.name) return;
            this.editMode = true;
            let role = 'Anggota';
            const roleStr = (person.role || '').toLowerCase();
            if (roleStr.includes('ketua')) {
                role = 'Ketua';
            } else if (roleStr.includes('sekretaris')) {
                role = 'Sekretaris';
            }
            const currentCode = code || person.committee_code || (this.panels[this.active] ? this.panels[this.active].code : 'KOM-I');
            this.form = {
                id: person.slug || person.id_enrollment || person.id_user || encodeURIComponent(person.name),
                nama: person.name,
                nama_lama: person.name,
                nim_nip: person.nip || person.nim_nip || '',
                email: person.email || '',
                committee_code: currentCode,
                role: role,
                organ: person.organ || '',
                focus: person.focus || ''
            };
            this.modalOpen = true;
        },
        openDelete(person, code) {
            if (!person || !person.name) return;
            this.deleteTarget = {
                id: person.slug || person.id_enrollment || person.id_user || encodeURIComponent(person.name),
                nama: person.name,
                committee_code: code || person.committee_code || (this.panels[this.active] ? this.panels[this.active].code : 'KOM-I')
            };
            this.deleteModalOpen = true;
        },
        async submitForm() {
            if (!this.form.nama || !this.form.committee_code) {
                this.showToast('Nama anggota dan Komisi wajib diisi.', 'error');
                return;
            }
            this.submitting = true;
            try {
                const csrfToken = document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '';
                const url = this.editMode
                    ? '{{ url('/direktori-komisi/anggota') }}/' + encodeURIComponent(this.form.id)
                    : '{{ route('public.directory.store') }}';

                const res = await fetch(url, {
                    method: this.editMode ? 'PUT' : 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(this.form)
                });
                const data = await res.json();
                if (res.ok && data.status === 'success') {
                    this.modalOpen = false;
                    this.showToast(data.message || (this.editMode ? 'Data anggota berhasil diperbarui!' : 'Anggota baru berhasil ditambahkan!'));
                    setTimeout(() => { window.location.reload(); }, 800);
                } else {
                    this.showToast(data.message || 'Terjadi kesalahan saat memproses data.', 'error');
                }
            } catch (e) {
                this.showToast('Gagal menghubungi server.', 'error');
            } finally {
                this.submitting = false;
            }
        },
        async confirmDelete() {
            this.submitting = true;
            try {
                const csrfToken = document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '';
                const url = '{{ url('/direktori-komisi/anggota') }}/' + encodeURIComponent(this.deleteTarget.id);
                const res = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(this.deleteTarget)
                });
                const data = await res.json();
                if (res.ok && data.status === 'success') {
                    this.deleteModalOpen = false;
                    this.showToast(data.message || 'Anggota berhasil dihapus.');
                    setTimeout(() => { window.location.reload(); }, 800);
                } else {
                    this.showToast(data.message || 'Gagal menghapus anggota.', 'error');
                }
            } catch (e) {
                this.showToast('Gagal menghubungi server.', 'error');
            } finally {
                this.submitting = false;
            }
        }
    }">

        {{-- Breadcrumb bar --}}
        <div class="border-b border-[#d6e0ed] bg-white">
            <div class="senat-shell flex h-[62px] flex-wrap items-center gap-x-3 gap-y-1 text-[11px]">
                <a href="{{ route('public.profile') }}" class="font-bold text-senat-link hover:underline">‹&nbsp; Profil &amp; Komisi</a>
                <span class="font-medium text-senat-body">/</span>
                <span class="font-medium text-senat-body">Direktori Komisi</span>
            </div>
        </div>

        {{-- Hero --}}
        <section class="relative overflow-hidden bg-senat-hero">
            <div class="pointer-events-none absolute inset-y-0 right-0 hidden w-[32%] bg-[#052e59] lg:block" aria-hidden="true"></div>
            <div class="pointer-events-none absolute inset-y-0 right-0 hidden w-[26px] bg-senat-orange lg:block" aria-hidden="true"></div>

            <div class="senat-shell relative flex min-h-[288px] flex-col justify-center py-10">
                <div class="lg:flex lg:items-center lg:gap-10">
                    <div class="lg:max-w-[730px]">
                        <p class="text-[11px] font-bold uppercase tracking-[0.1em] text-senat-sky">Direktori Anggota Senat</p>
                        <h1 class="mt-3 text-[26px] font-bold leading-[1.08] text-white sm:text-[35px]"
                            x-text="panels[active].title">{{ $panels[0]['label'] ?? 'Direktori Komisi' }}@if (isset($panels[0])) · {{ $panels[0]['short'] }}@endif</h1>
                        <p class="mt-4 max-w-[690px] text-[13px] leading-relaxed text-senat-skySoft"
                           x-text="panels[active].focus">{{ $panels[0]['focus'] ?? $period }}</p>
                    </div>

                    <div class="mt-8 lg:mt-0 lg:ml-auto lg:w-[330px]">
                        <div class="flex items-center justify-between gap-3">
                            <span class="inline-flex items-center rounded-full bg-[#e8fcf5] px-3.5 py-1 text-[9px] font-bold uppercase tracking-[0.06em] text-[#008c66]"
                                  x-text="panels[active].badge">{{ $panels[0]['total'] ?? 0 }} Anggota Komisi</span>
                            @if (session('auth_user_role') === 'admin')
                                <button type="button"
                                        @click="openCreate(panels[active]?.code)"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-senat-orange px-3.5 py-1.5 text-[10px] font-bold text-senat-ink shadow-sm transition-all duration-150 hover:bg-senat-orangeHover hover:shadow-md cursor-pointer">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                    </svg>
                                    <span>Tambah Anggota</span>
                                </button>
                            @endif
                        </div>
                        <p class="mt-5 text-[10px] font-bold uppercase tracking-[0.08em] text-[#94c4f5]">Masa Bakti</p>
                        <p class="mt-1 text-[18px] font-bold text-white">{{ $period }}</p>
                        <p class="mt-2 text-[10px] text-[#b8d6f5]">Struktur dapat diperbarui Sekretariat</p>
                    </div>
                </div>
            </div>
        </section>

        <div class="senat-shell pb-16 pt-10">

            {{-- Selector surface: pilih komisi --}}
            <section class="rounded-[13px] border border-[#d6e0ed] bg-white p-5 sm:p-6" aria-label="Pilih komisi">
                <p class="text-[10px] font-bold uppercase tracking-[0.08em] text-senat-body">Pilih Komisi</p>

                @if (count($panels))
                    <div class="mt-4 flex flex-wrap gap-3">
                        @foreach ($panels as $i => $panel)
                            <button type="button"
                                    @click="active = {{ $i }}"
                                    :class="active === {{ $i }} ? 'border-senat-hero bg-senat-hero' : 'border-[#d6e0ed] bg-white hover:border-senat-border'"
                                    :aria-pressed="(active === {{ $i }}).toString()"
                                    class="flex min-w-[150px] flex-1 flex-col items-center justify-center gap-1 rounded-lg border px-4 py-2.5 text-center transition-colors duration-150">
                                <span class="text-[9px] font-bold uppercase tracking-[0.08em]"
                                      :class="active === {{ $i }} ? 'text-[#abd1ff]' : 'text-senat-body'">{{ strtoupper($panel['label']) }}</span>
                                <span class="text-[10px] font-bold leading-snug"
                                      :class="active === {{ $i }} ? 'text-white' : 'text-senat-ink'">{{ $panel['short'] }}</span>
                            </button>
                        @endforeach
                    </div>
                @else
                    <x-senat.empty class="mt-4"
                                   title="Data komisi belum tersedia"
                                   message="Struktur komisi Senat Akademik akan ditampilkan setelah Sekretariat memperbarui data keanggotaan." />
                @endif
            </section>

            {{-- Panel per komisi --}}
            @foreach ($panels as $i => $panel)
                <div @if ($i > 0) x-cloak @endif x-show="active === {{ $i }}" class="mt-12 space-y-8">

                    {{-- Susunan keanggotaan --}}
                    <section>
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-[0.08em] text-senat-orange">Susunan Keanggotaan</p>
                                <h2 class="mt-2 text-[26px] font-bold leading-tight text-senat-ink">Anggota {{ $panel['label'] }}</h2>
                                <p class="mt-2 max-w-[800px] text-[11px] leading-relaxed text-senat-body">
                                    @if (session('auth_user_role') === 'admin')
                                        Pilih kartu anggota untuk membuka profil jabatan atau gunakan tombol Tambah/Edit untuk mengelola direktori.
                                    @else
                                        Pilih kartu anggota untuk membuka rincian profil jabatan dan mandat keanggotaan komisi.
                                    @endif
                                </p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2.5">
                                @if (session('auth_user_role') === 'admin')
                                    <button type="button"
                                            @click="openCreate('{{ $panel['code'] }}')"
                                            class="inline-flex items-center gap-1.5 rounded-lg border border-senat-hero bg-senat-hero px-3.5 py-1.5 text-[11px] font-bold text-white shadow-xs transition-colors hover:bg-senat-primary cursor-pointer">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                        </svg>
                                        Tambah Anggota
                                    </button>
                                @endif
                                <span class="shrink-0 rounded-full bg-senat-badgeBg px-3.5 py-1 text-[9px] font-bold uppercase tracking-[0.06em] text-senat-hero"
                                      x-text="panels[{{ $i }}].composition">1 Ketua • 1 Sekretaris • {{ count($panel['anggota']) }} Anggota</span>
                            </div>
                        </div>
                    </section>

                    {{-- Leadership zone: pimpinan komisi --}}
                    <section class="rounded-[15px] border border-[#d6e0ed] bg-white p-6 sm:p-8">
                        <p class="text-[10px] font-bold uppercase tracking-[0.08em] text-senat-orange">Pimpinan Komisi</p>

                        <div class="mt-6 grid gap-5 lg:grid-cols-2">
                            @if (! empty($panel['lead']))
                                <div class="group relative flex items-center gap-5 rounded-[12px] border border-[#a3c2e3] bg-senat-badgeBg p-6 transition-colors duration-150 hover:border-senat-link">
                                    <a href="{{ route('public.directory.member', $panel['lead']['slug']) }}" class="relative flex h-[82px] w-[82px] shrink-0 items-center justify-center rounded-full border border-[#9ebfe0] bg-[#e5f5ff] text-[16px] font-bold text-senat-link hover:scale-105 transition-transform">
                                        <span class="flex h-[74px] w-[74px] items-center justify-center rounded-full border border-[#bdd4eb] bg-senat-badgeBg">{{ $initialsOf($panel['lead']['name']) }}</span>
                                    </a>
                                    <span class="min-w-0 flex-1">
                                        <span class="flex items-center justify-between gap-2">
                                            <span class="block text-[10px] font-bold uppercase tracking-[0.06em] text-senat-link">Ketua {{ $panel['label'] }}</span>
                                            @if (session('auth_user_role') === 'admin')
                                                <button type="button"
                                                        @click="openEdit(@js($panel['lead']), '{{ $panel['code'] }}')"
                                                        title="Edit Ketua Komisi"
                                                        class="inline-flex items-center gap-1 rounded-md border border-[#9ebfe0] bg-white px-2 py-0.5 text-[10px] font-bold text-senat-link hover:bg-[#e5f5ff] transition-colors cursor-pointer">
                                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" />
                                                    </svg>
                                                    Edit
                                                </button>
                                            @endif
                                        </span>
                                        <a href="{{ route('public.directory.member', $panel['lead']['slug']) }}" class="mt-1.5 block text-[16px] font-bold leading-snug text-senat-ink hover:text-senat-link transition-colors">{{ $panel['lead']['name'] }}</a>
                                        @if (! empty($panel['lead']['nip']))
                                            <span class="mt-1 block font-mono text-[11px] font-medium text-slate-500">NIP {{ $panel['lead']['nip'] }}</span>
                                        @endif
                                        @if (! empty($panel['lead']['jurusan']))
                                            <span class="mt-0.5 block text-[10.5px] font-medium text-senat-primary">{{ $panel['lead']['jurusan'] }}</span>
                                        @endif
                                        <span class="mt-1 block text-[11px] text-senat-body">{{ $panel['lead']['organ'] }}</span>
                                        <a href="{{ route('public.directory.member', $panel['lead']['slug']) }}" class="mt-3 inline-block text-[11px] font-bold text-senat-link hover:underline">Lihat profil anggota &nbsp;›</a>
                                    </span>
                                </div>
                            @else
                                <div class="rounded-[12px] border border-dashed border-senat-border bg-white p-6 text-[11px] text-senat-muted">
                                    Data ketua {{ $panel['label'] }} belum diisi pada dokumen Sekretariat.
                                </div>
                            @endif

                            @if (! empty($panel['secretary']))
                                <div class="group relative flex items-center gap-5 rounded-[12px] border border-[#d6e0ed] bg-white p-6 transition-colors duration-150 hover:border-senat-link">
                                    <a href="{{ route('public.directory.member', $panel['secretary']['slug']) }}" class="relative flex h-[82px] w-[82px] shrink-0 items-center justify-center rounded-full border border-[#9ebfe0] bg-[#e5f5ff] text-[16px] font-bold text-senat-link hover:scale-105 transition-transform">
                                        <span class="flex h-[74px] w-[74px] items-center justify-center rounded-full border border-[#bdd4eb] bg-senat-badgeBg">{{ $initialsOf($panel['secretary']['name']) }}</span>
                                    </a>
                                    <span class="min-w-0 flex-1">
                                        <span class="flex items-center justify-between gap-2">
                                            <span class="block text-[10px] font-bold uppercase tracking-[0.06em] text-senat-link">Sekretaris {{ $panel['label'] }}</span>
                                            @if (session('auth_user_role') === 'admin')
                                                <button type="button"
                                                        @click="openEdit(@js($panel['secretary']), '{{ $panel['code'] }}')"
                                                        title="Edit Sekretaris Komisi"
                                                        class="inline-flex items-center gap-1 rounded-md border border-[#d6e0ed] bg-senat-badgeBg px-2 py-0.5 text-[10px] font-bold text-senat-link hover:bg-blue-100 transition-colors cursor-pointer">
                                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" />
                                                    </svg>
                                                    Edit
                                                </button>
                                            @endif
                                        </span>
                                        <a href="{{ route('public.directory.member', $panel['secretary']['slug']) }}" class="mt-1.5 block text-[16px] font-bold leading-snug text-senat-ink hover:text-senat-link transition-colors">{{ $panel['secretary']['name'] }}</a>
                                        @if (! empty($panel['secretary']['nip']))
                                            <span class="mt-1 block font-mono text-[11px] font-medium text-slate-500">NIP {{ $panel['secretary']['nip'] }}</span>
                                        @endif
                                        @if (! empty($panel['secretary']['jurusan']))
                                            <span class="mt-0.5 block text-[10.5px] font-medium text-senat-primary">{{ $panel['secretary']['jurusan'] }}</span>
                                        @endif
                                        <span class="mt-1 block text-[11px] text-senat-body">{{ $panel['secretary']['organ'] }}</span>
                                        <a href="{{ route('public.directory.member', $panel['secretary']['slug']) }}" class="mt-3 inline-block text-[11px] font-bold text-senat-link hover:underline">Lihat profil anggota &nbsp;›</a>
                                    </span>
                                </div>
                            @else
                                <div class="rounded-[12px] border border-[#d6e0ed] bg-white p-6">
                                    <p class="text-[10px] font-bold uppercase tracking-[0.06em] text-senat-link">Sekretaris {{ $panel['label'] }}</p>
                                    <p class="mt-1.5 text-[16px] font-bold leading-snug text-senat-ink">Nama Sekretaris Komisi</p>
                                    <p class="mt-1.5 text-[11px] text-senat-body">Data Sekretariat belum diisi</p>
                                    <span class="mt-3 inline-flex items-center rounded-full bg-senat-badgeBg px-3 py-1 text-[9px] font-bold uppercase tracking-[0.06em] text-senat-orange">Data menunggu pembaruan</span>
                                </div>
                            @endif
                        </div>
                    </section>

                    {{-- Daftar anggota komisi --}}
                    <section>
                        <div class="flex flex-wrap items-end justify-between gap-3">
                            <h2 class="text-[21px] font-bold text-senat-ink">Daftar Anggota Komisi</h2>
                            <span class="text-[11px] font-medium text-senat-body"
                                  x-text="panels[{{ $i }}].count + ' anggota komisi'">{{ count($panel['anggota']) }} anggota komisi</span>
                        </div>

                        @if (count($panel['anggota']))
                            <div class="mt-5 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                                @foreach ($panel['anggota'] as $person)
                                    <article class="flex flex-col rounded-[13px] border border-[#d6e0ed] bg-white p-6 shadow-2xs hover:shadow-xs transition-shadow">
                                        <div class="flex items-start gap-4">
                                            <a href="{{ route('public.directory.member', $person['slug']) }}" class="relative flex h-[70px] w-[70px] shrink-0 items-center justify-center rounded-full border border-[#9ebfe0] bg-[#e5f5ff] text-[14px] font-bold text-senat-link hover:scale-105 transition-transform">
                                                <span class="flex h-[62px] w-[62px] items-center justify-center rounded-full border border-[#bdd4eb] bg-senat-badgeBg">{{ $initialsOf($person['name']) }}</span>
                                            </a>
                                            <div class="min-w-0">
                                                <p class="text-[10px] font-bold uppercase tracking-[0.06em] text-senat-link">{{ strtoupper($person['role']) }}</p>
                                                <a href="{{ route('public.directory.member', $person['slug']) }}" class="mt-1.5 block text-[15px] font-bold leading-snug text-senat-ink hover:text-senat-link transition-colors">{{ $person['name'] }}</a>
                                                @if (! empty($person['nip']))
                                                    <p class="mt-1 font-mono text-[11px] font-medium text-slate-500">NIP {{ $person['nip'] }}</p>
                                                @endif
                                                @if (! empty($person['jurusan']))
                                                    <p class="mt-0.5 text-[10.5px] font-medium text-senat-primary">{{ $person['jurusan'] }}</p>
                                                @endif
                                                <p class="mt-1 text-[10px] text-senat-body">{{ $person['organ'] }}</p>
                                            </div>
                                        </div>

                                        <p class="mt-4 text-[10px] leading-relaxed text-senat-body">Data jabatan dikelola Sekretariat Senat.</p>

                                        <div class="mt-auto flex items-center gap-2 pt-4 border-t border-slate-100">
                                            <a href="{{ route('public.directory.member', $person['slug']) }}"
                                               class="flex h-[32px] flex-1 items-center justify-center rounded-[7px] border border-[#bdd1e5] bg-senat-badgeBg text-[9px] font-bold uppercase tracking-[0.06em] text-senat-link transition-colors duration-150 hover:border-senat-link">
                                                Profil &nbsp;›
                                            </a>
                                            @if (session('auth_user_role') === 'admin')
                                                <button type="button"
                                                        @click="openEdit(@js($person), '{{ $panel['code'] }}')"
                                                        title="Edit anggota"
                                                        class="flex h-[32px] w-[32px] shrink-0 items-center justify-center rounded-[7px] border border-[#d6e0ed] bg-white text-senat-body transition-colors hover:border-senat-link hover:text-senat-link hover:bg-senat-badgeBg cursor-pointer">
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" />
                                                    </svg>
                                                </button>
                                                <button type="button"
                                                        @click="openDelete(@js($person), '{{ $panel['code'] }}')"
                                                        title="Hapus anggota"
                                                        class="flex h-[32px] w-[32px] shrink-0 items-center justify-center rounded-[7px] border border-red-200 bg-white text-red-500 transition-colors hover:border-red-400 hover:bg-red-50 cursor-pointer">
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        @else
                            <x-senat.empty class="mt-5"
                                           title="Data anggota belum diisi"
                                           message="Nama anggota {{ $panel['label'] }} akan ditampilkan setelah Sekretariat Senat memperbarui data keanggotaan." />
                        @endif
                    </section>

                    {{-- Tupoksi komisi --}}
                    <section class="rounded-[8px] border border-[#e2e8f0] bg-[#f8fafc] p-6 sm:p-7">
                        <p class="text-[11px] font-bold uppercase tracking-[0.06em] text-senat-hero">Panduan Kinerja Komisi</p>
                        <h2 class="mt-2 text-[18px] font-bold text-[#0f172a]">Tugas Pokok &amp; Fungsi (TUPOKSI) {{ $panel['label'] }}</h2>

                        <div class="mt-5 grid gap-5 lg:grid-cols-2">
                            <div class="rounded-[6px] border border-[#e2e8f0] bg-white p-5">
                                <p class="text-[11px] font-bold uppercase tracking-[0.06em] text-senat-orange">Fokus Kerja Komisi</p>
                                <p class="mt-3 text-[12.5px] leading-relaxed text-[#475569]">{{ $panel['focus'] }}</p>
                            </div>

                            <div class="rounded-[6px] border border-[#e2e8f0] bg-white p-5">
                                <p class="text-[11px] font-bold uppercase tracking-[0.06em] text-senat-orange">Pimpinan Komisi</p>
                                <dl class="mt-3 space-y-3 text-[12.5px] leading-relaxed text-[#475569]">
                                    <div>
                                        <dt class="text-[10px] font-bold uppercase tracking-[0.06em] text-senat-muted">Ketua</dt>
                                        <dd class="mt-0.5 font-semibold text-senat-ink">{{ $panel['lead']['name'] ?? 'Belum tersedia' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-[10px] font-bold uppercase tracking-[0.06em] text-senat-muted">Sekretaris</dt>
                                        <dd class="mt-0.5 font-semibold text-senat-ink">{{ $panel['secretary']['name'] ?? 'Belum tersedia' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-[10px] font-bold uppercase tracking-[0.06em] text-senat-muted">Jumlah Anggota</dt>
                                        <dd class="mt-0.5 font-semibold text-senat-ink">{{ $panel['total'] }} orang &middot; {{ count($panel['anggota']) }} anggota komisi</dd>
                                    </div>
                                </dl>
                            </div>

                            <div class="rounded-[6px] border border-[#e2e8f0] bg-white p-5 lg:col-span-2">
                                <p class="text-[11px] font-bold uppercase tracking-[0.06em] text-senat-orange">Tupoksi Komisi</p>
                                <p class="mt-3 max-w-[1100px] text-[12.5px] leading-relaxed text-[#475569]">
                                    {{ $panel['tupoksi'] ?: 'Rincian tugas pokok dan fungsi komisi ini belum tersedia pada dokumen Sekretariat Senat.' }}
                                </p>
                            </div>
                        </div>
                    </section>
                </div>
            @endforeach

            {{-- Pimpinan Senat & BPH --}}
            @if ($leaderRows->isNotEmpty())
                <section class="mt-14 rounded-[15px] border border-[#d6e0ed] bg-white p-6 sm:p-8">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.08em] text-senat-orange">Pimpinan Senat &amp; BPH</p>
                            <h2 class="mt-2 text-[26px] font-bold leading-tight text-senat-ink">Pimpinan Senat Akademik</h2>
                            <p class="mt-2 text-[11px] leading-relaxed text-senat-body">
                                Ketua, Sekretaris, dan Badan Pekerja Harian Senat Akademik periode {{ $period }}.
                            </p>
                        </div>
                        <span class="shrink-0 self-start rounded-full bg-senat-badgeBg px-3.5 py-1 text-[9px] font-bold uppercase tracking-[0.06em] text-senat-hero lg:self-end">
                            Masa Bakti {{ $period }}
                        </span>
                    </div>

                    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        @foreach ($leaderRows as $leader)
                            <a href="{{ route('public.directory.member', $leader['slug']) }}"
                               class="group rounded-[12px] border border-[#d6e0ed] bg-white p-5 transition-colors duration-150 hover:border-senat-link">
                                <span class="flex items-center gap-3">
                                    @if (! empty($leader['photo']))
                                        <img src="{{ asset($leader['photo']) }}" alt="{{ $leader['name'] }}" class="h-[44px] w-[44px] shrink-0 rounded-full border border-senat-badgeLine/60 object-cover object-top shadow-sm">
                                    @else
                                        <span class="flex h-[44px] w-[44px] shrink-0 items-center justify-center rounded-full border border-[#bdd4eb] bg-senat-badgeBg text-[12px] font-bold text-senat-link">{{ $initialsOf($leader['name']) }}</span>
                                    @endif
                                    <span class="min-w-0">
                                        <span class="block text-[9px] font-bold uppercase tracking-[0.06em] text-senat-link">{{ $leader['role'] }}</span>
                                        <span class="mt-1 block text-[13px] font-bold leading-snug text-senat-ink group-hover:text-senat-link">{{ $leader['name'] }}</span>
                                    </span>
                                </span>
                                <span class="mt-3 block text-[10px] text-senat-body">{{ $leader['organ'] }}</span>
                            </a>
                        @endforeach
                    </div>

                    <div class="mt-6 grid gap-5 sm:grid-cols-3">
                        <x-senat.stat label="Total Anggota" :value="$direktori['members']" hint="Pimpinan, BPH, dan anggota komisi" icon="users" />
                        <x-senat.stat label="Komisi Kerja" :value="$direktori['committees']" hint="Komisi I–V Senat Akademik" icon="dashboard" />
                        <x-senat.stat label="Masa Bakti" :value="$direktori['period']" hint="Periode kepengurusan Senat" icon="calendar" />
                    </div>
                </section>
            @endif

            {{-- Bottom bar --}}
            <div class="mt-14 flex flex-col gap-3 border-t border-senat-line pt-6 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-[11px] text-senat-body">Direktori keanggotaan Senat Akademik &bull; Masa Bakti {{ $period }}</p>
                <a href="{{ route('public.profile') }}" class="text-[11px] font-bold text-senat-link hover:underline">Kembali ke Profil &amp; Komisi &nbsp;›</a>
            </div>
        </div>

        @if (session('auth_user_role') === 'admin')
        {{-- ========================================================================= --}}
        {{-- MODAL FORM: TAMBAH & EDIT ANGGOTA KOMISI (CRUD PROFESIONAL)              --}}
        {{-- ========================================================================= --}}
        <div x-show="modalOpen"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             aria-labelledby="modal-title"
             role="dialog"
             aria-modal="true">

            {{-- Backdrop blur overlay --}}
            <div x-show="modalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="if (!submitting) modalOpen = false"
                 class="fixed inset-0 bg-[#0f172a]/60 backdrop-blur-xs transition-opacity"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-6">
                <div x-show="modalOpen"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative w-full max-w-[580px] transform overflow-hidden rounded-[16px] border border-[#d6e0ed] bg-white text-left shadow-2xl transition-all">

                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between border-b border-[#e2e8f0] bg-[#f8fafc] px-6 py-4">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-senat-badgeBg text-senat-hero">
                                <svg x-show="!editMode" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                                <svg x-show="editMode" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </span>
                            <div>
                                <h3 class="text-[15px] font-bold text-senat-ink"
                                    x-text="editMode ? 'Edit Data Anggota Senat' : 'Tambah Anggota Senat Baru'"></h3>
                                <p class="text-[11px] text-senat-body">Direktori Keanggotaan & Komisi Kerja Senat Akademik</p>
                            </div>
                        </div>
                        <button type="button"
                                @click="if (!submitting) modalOpen = false"
                                class="rounded-lg p-1 text-slate-400 transition-colors hover:bg-slate-200 hover:text-slate-700 cursor-pointer">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Form Body --}}
                    <form @submit.prevent="submitForm()" class="p-6 space-y-4">
                        {{-- Komisi Selection --}}
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-senat-body">
                                Komisi Penugasan <span class="text-red-500">*</span>
                            </label>
                            <select x-model="form.committee_code"
                                    required
                                    class="mt-1.5 block w-full rounded-lg border border-[#d6e0ed] bg-white px-3.5 py-2.5 text-[12px] font-semibold text-senat-ink shadow-2xs focus:border-senat-link focus:outline-none focus:ring-1 focus:ring-senat-link">
                                @foreach ($panels as $p)
                                    <option value="{{ $p['code'] }}">{{ $p['code'] }} — {{ $p['name'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Jabatan Komisi --}}
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-senat-body">
                                Jabatan di Komisi <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1.5 grid grid-cols-3 gap-2">
                                <label class="flex cursor-pointer flex-col items-center justify-center rounded-lg border p-2.5 text-center transition-colors"
                                       :class="form.role === 'Ketua' ? 'border-senat-link bg-senat-badgeBg text-senat-link font-bold' : 'border-[#d6e0ed] bg-white text-senat-body hover:bg-slate-50'">
                                    <input type="radio" x-model="form.role" value="Ketua" class="sr-only">
                                    <span class="text-[12px]">Ketua Komisi</span>
                                </label>
                                <label class="flex cursor-pointer flex-col items-center justify-center rounded-lg border p-2.5 text-center transition-colors"
                                       :class="form.role === 'Sekretaris' ? 'border-senat-link bg-senat-badgeBg text-senat-link font-bold' : 'border-[#d6e0ed] bg-white text-senat-body hover:bg-slate-50'">
                                    <input type="radio" x-model="form.role" value="Sekretaris" class="sr-only">
                                    <span class="text-[12px]">Sekretaris Komisi</span>
                                </label>
                                <label class="flex cursor-pointer flex-col items-center justify-center rounded-lg border p-2.5 text-center transition-colors"
                                       :class="form.role === 'Anggota' ? 'border-senat-link bg-senat-badgeBg text-senat-link font-bold' : 'border-[#d6e0ed] bg-white text-senat-body hover:bg-slate-50'">
                                    <input type="radio" x-model="form.role" value="Anggota" class="sr-only">
                                    <span class="text-[12px]">Anggota Komisi</span>
                                </label>
                            </div>
                        </div>

                        {{-- Nama Lengkap --}}
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-senat-body">
                                Nama Lengkap beserta Gelar <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   x-model="form.nama"
                                   required
                                   placeholder="mis. Dr. Ir. Budi Santoso, S.T., M.T."
                                   class="mt-1.5 block w-full rounded-lg border border-[#d6e0ed] bg-white px-3.5 py-2.5 text-[12px] text-senat-ink shadow-2xs focus:border-senat-link focus:outline-none focus:ring-1 focus:ring-senat-link">
                        </div>

                        {{-- NIP / NIDN & Email --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-senat-body">
                                    NIP / NIDN
                                </label>
                                <input type="text"
                                       x-model="form.nim_nip"
                                       placeholder="mis. 197508121999031002"
                                       class="mt-1.5 block w-full rounded-lg border border-[#d6e0ed] bg-white px-3.5 py-2.5 text-[12px] text-senat-ink shadow-2xs focus:border-senat-link focus:outline-none focus:ring-1 focus:ring-senat-link">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-senat-body">
                                    Email Resmi (@polines.ac.id)
                                </label>
                                <input type="email"
                                       x-model="form.email"
                                       placeholder="mis. budi@polines.ac.id"
                                       class="mt-1.5 block w-full rounded-lg border border-[#d6e0ed] bg-white px-3.5 py-2.5 text-[12px] text-senat-ink shadow-2xs focus:border-senat-link focus:outline-none focus:ring-1 focus:ring-senat-link">
                            </div>
                        </div>

                        {{-- Unit Kerja / Asal Jurusan --}}
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-senat-body">
                                Asal Jurusan / Organ / Unit Kerja
                            </label>
                            <input type="text"
                                   x-model="form.organ"
                                   placeholder="mis. Jurusan Teknik Elektro / Teknik Mesin / Akuntansi"
                                   class="mt-1.5 block w-full rounded-lg border border-[#d6e0ed] bg-white px-3.5 py-2.5 text-[12px] text-senat-ink shadow-2xs focus:border-senat-link focus:outline-none focus:ring-1 focus:ring-senat-link">
                        </div>

                        {{-- Catatan / Fokus Keahlian --}}
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-senat-body">
                                Catatan / Fokus Keahlian (Opsional)
                            </label>
                            <textarea x-model="form.focus"
                                      rows="2"
                                      placeholder="Keahlian akademik atau catatan penugasan komisi..."
                                      class="mt-1.5 block w-full rounded-lg border border-[#d6e0ed] bg-white px-3.5 py-2 text-[12px] text-senat-ink shadow-2xs focus:border-senat-link focus:outline-none focus:ring-1 focus:ring-senat-link"></textarea>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="mt-6 flex items-center justify-end gap-3 border-t border-[#e2e8f0] pt-4">
                            <button type="button"
                                    @click="if (!submitting) modalOpen = false"
                                    class="rounded-lg border border-[#d6e0ed] bg-white px-4 py-2 text-[12px] font-bold text-senat-body transition-colors hover:bg-slate-100 cursor-pointer">
                                Batal
                            </button>
                            <button type="submit"
                                    :disabled="submitting"
                                    class="inline-flex items-center gap-2 rounded-lg bg-senat-hero px-5 py-2 text-[12px] font-bold text-white shadow-xs transition-colors hover:bg-senat-primary disabled:opacity-60 cursor-pointer">
                                <svg x-show="submitting" class="h-3.5 w-3.5 animate-spin" viewBox="0 0 24 24" fill="none">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span x-text="editMode ? (submitting ? 'Memperbarui...' : 'Simpan Perubahan') : (submitting ? 'Menyimpan...' : 'Simpan Anggota')"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- MODAL KONFIRMASI HAPUS ANGGOTA                                            --}}
        {{-- ========================================================================= --}}
        <div x-show="deleteModalOpen"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             role="dialog"
             aria-modal="true">

            <div x-show="deleteModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="if (!submitting) deleteModalOpen = false"
                 class="fixed inset-0 bg-[#0f172a]/60 backdrop-blur-xs transition-opacity"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center">
                <div x-show="deleteModalOpen"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="relative w-full max-w-[420px] transform overflow-hidden rounded-[16px] border border-[#d6e0ed] bg-white p-6 text-left shadow-2xl transition-all">

                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </span>
                        <div>
                            <h3 class="text-[15px] font-bold text-senat-ink">Hapus Anggota Senat</h3>
                            <p class="text-[11px] text-senat-body">Konfirmasi pencopotan keanggotaan komisi</p>
                        </div>
                    </div>

                    <p class="mt-4 text-[12px] leading-relaxed text-slate-600">
                        Apakah Anda yakin ingin menghapus <strong class="text-senat-ink" x-text="deleteTarget.nama"></strong> dari <span class="font-bold text-senat-link" x-text="deleteTarget.committee_code"></span>? Data ini akan diperbarui dari daftar direktori.
                    </p>

                    <div class="mt-6 flex items-center justify-end gap-3">
                        <button type="button"
                                @click="if (!submitting) deleteModalOpen = false"
                                class="rounded-lg border border-[#d6e0ed] bg-white px-4 py-2 text-[12px] font-bold text-senat-body transition-colors hover:bg-slate-100 cursor-pointer">
                            Batal
                        </button>
                        <button type="button"
                                @click="confirmDelete()"
                                :disabled="submitting"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-4 py-2 text-[12px] font-bold text-white shadow-xs transition-colors hover:bg-red-700 disabled:opacity-60 cursor-pointer">
                            <svg x-show="submitting" class="h-3.5 w-3.5 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span x-text="submitting ? 'Menghapus...' : 'Ya, Hapus Anggota'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- ========================================================================= --}}
        {{-- TOAST NOTIFICATION                                                        --}}
        {{-- ========================================================================= --}}
        <div x-show="toast.show"
             x-cloak
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-2"
             class="fixed bottom-6 right-6 z-50 flex items-center gap-3 rounded-xl border border-[#bdd4eb] bg-white px-4 py-3 shadow-xl">
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full"
                  :class="toast.type === 'error' ? 'bg-red-100 text-red-600' : 'bg-emerald-100 text-emerald-600'">
                <svg x-show="toast.type !== 'error'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <svg x-show="toast.type === 'error'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </span>
            <p class="text-[12px] font-semibold text-senat-ink" x-text="toast.message"></p>
        </div>
    </div>
@endsection
