@extends('layouts.portal')

@section('title', 'Dashboard Anggota — Portal Internal Senat Akademik Polines')

@section('content')
@php
    /**
     * 12A — Dashboard Anggota · Ringkasan (Figma node 2021:2)
     * Topbar + sidebar disediakan layouts.portal; bagian ini hanya area konten 1116px.
     */
    $bulanId = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $tanggal = function (string $value) use ($bulanId): string {
        $ts = strtotime($value);
        return $ts ? date('j', $ts) . ' ' . $bulanId[(int) date('n', $ts)] . ' ' . date('Y', $ts) : $value;
    };

    $active = $stats['active'] ?? null;
    $upcoming = array_values($stats['upcoming'] ?? []);
    $perluKonfirmasi = count($upcoming) + ($active ? 1 : 0);
    $perhatian = array_slice($upcoming ?: $meetings, 0, 2);
    $agendaTerdekat = $active ?: ($upcoming[0] ?? ($meetings[0] ?? null));
@endphp

<div class="mx-auto w-full max-w-[1116px]" x-data="{ showNewsModal: false, postStatus: 'Terpublikasi' }">

    {{-- ================= FLASH ALERT NOTIFICATION ================= --}}
    @if (session('success'))
        <div class="mb-6 flex items-center justify-between gap-3 rounded-[14px] bg-emerald-50 border border-emerald-200 px-5 py-3.5 text-emerald-800 shadow-xs">
            <div class="flex items-center gap-2.5">
                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">✓</span>
                <span class="text-[13px] font-semibold">{{ session('success') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 text-sm font-bold">✕</button>
        </div>
    @endif

    {{-- ================= PAGE HEADER ================= --}}
    <header class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-[25px] font-bold leading-tight tracking-tight text-senat-ink">Dashboard Anggota</h1>
            <p class="mt-1.5 text-[13px] text-senat-muted">Ringkasan aktivitas persidangan, warta terbitan, dan keputusan terkini.</p>
        </div>
        <div class="flex items-center gap-2.5">
            <button type="button"
                    @click="showNewsModal = true"
                    class="inline-flex h-9 items-center justify-center gap-1.5 rounded-md bg-senat-primary px-3.5 text-[12px] font-bold text-white shadow-xs transition-colors hover:bg-senat-primaryHover">
                <x-senat.icon name="plus" class="h-4 w-4" />
                <span>+ Tulis Berita</span>
            </button>
            <a href="{{ route('admin.preview', ['tab' => 'Manajemen Berita & Siaran Pers']) }}"
               class="inline-flex h-9 items-center justify-center gap-1 rounded-md border border-senat-border bg-white px-3 text-[12px] font-semibold text-senat-ink shadow-xs transition-colors hover:bg-slate-50">
                <span>Kelola Warta</span>
                <span aria-hidden="true">›</span>
            </a>
        </div>
    </header>

    {{-- ================= NOTICE ================= --}}
    @if ($perluKonfirmasi > 0)
        <div class="mb-6 flex flex-wrap items-start gap-x-4 gap-y-3 rounded-[14px] bg-[#fff4df] px-5 py-3 shadow-[0_3px_12px_rgba(13,26,41,0.07)]">
            <x-senat.icon name="warning" class="h-[18px] w-[18px] shrink-0 text-[#e88a00]" />
            <p class="min-w-0 flex-1 text-[13px] font-medium text-[#975c00]">
                {{ $perluKonfirmasi }} sidang membutuhkan konfirmasi presensi sebelum waktu dimulai.
            </p>
            <x-senat.button variant="secondary" size="sm" :href="route('portal.agenda')">Lihat Agenda</x-senat.button>
        </div>
    @endif

    {{-- ================= KPI ================= --}}
    <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
        <x-senat.stat label="Agenda" :value="$stats['total']" :hint="count($upcoming) . ' sidang terjadwal'" icon="folder" />
        <x-senat.stat label="E-Voting" value="—" hint="Belum ada voting dibuka" icon="check" />
        <x-senat.stat label="Kehadiran" :value="$stats['attendance_rate'] . '%'" hint="Periode berjalan" icon="clock" />
        <x-senat.stat label="Tindak lanjut" :value="count($aspirations)" hint="Aspirasi masuk komisi" icon="warning" />
    </div>

    {{-- ================= PRIORITAS + AGENDA TERDEKAT ================= --}}
    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- Tindakan Prioritas --}}
        <section class="rounded-[14px] border border-senat-line bg-white p-5 shadow-[0_3px_12px_rgba(13,26,41,0.07)] lg:col-span-2">
            <div class="flex flex-wrap items-center gap-3">
                <h2 class="text-[18px] font-bold text-senat-ink">Tindakan Prioritas</h2>
                <x-senat.badge state="warning" :dot="false">Perlu respons</x-senat.badge>
            </div>

            <ul class="mt-5 divide-y divide-senat-line">
                @forelse ($perhatian as $meeting)
                    @php
                        $sedang = ($meeting['status'] ?? '') === 'Sedang Berlangsung';
                        $dot = $sedang ? 'bg-[#e88a00]' : 'bg-senat-link';
                    @endphp
                    <li class="flex items-start gap-4 py-4 first:pt-0 last:pb-0">
                        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ $dot }}" aria-hidden="true"></span>
                        <div class="min-w-0 flex-1">
                            <p class="text-[14px] font-semibold text-senat-ink">{{ $sedang ? 'Konfirmasi presensi' : 'Agenda menunggu' }}</p>
                            <p class="mt-0.5 text-[11px] text-senat-muted">
                                {{ $meeting['type'] ?? 'Agenda' }} · {{ $tanggal($meeting['date']) }}
                            </p>
                        </div>
                        <x-senat.badge :state="$sedang ? 'success' : 'info'" :dot="false">
                            {{ $meeting['status'] ?? 'Terjadwal' }}
                        </x-senat.badge>
                    </li>
                @empty
                    <li class="py-4">
                        <p class="text-[13px] text-senat-muted">Tidak ada tindakan yang menunggu respons.</p>
                    </li>
                @endforelse

                @foreach ($aspirations as $aspiration)
                    <li class="flex items-start gap-4 py-4 first:pt-0 last:pb-0">
                        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-[#7048e8]" aria-hidden="true"></span>
                        <div class="min-w-0 flex-1">
                            <p class="text-[14px] font-semibold text-senat-ink">Tinjau aspirasi konstituen</p>
                            <p class="mt-0.5 text-[11px] text-senat-muted">
                                {{ $aspiration['subject'] }} · {{ $aspiration['committee_target'] }}
                            </p>
                        </div>
                        <span class="shrink-0 whitespace-nowrap text-[11px] font-semibold text-senat-muted">
                            {{ $aspiration['status'] }}
                        </span>
                    </li>
                @endforeach
            </ul>
        </section>

        {{-- Agenda Terdekat --}}
        <section class="rounded-[14px] border border-senat-line bg-white p-5 shadow-[0_3px_12px_rgba(13,26,41,0.07)]">
            <h2 class="text-[18px] font-bold text-senat-ink">Agenda Terdekat</h2>

            @if ($agendaTerdekat)
                <div class="mt-4">
                    <x-senat.badge state="info" :dot="false">{{ $agendaTerdekat['type'] ?? 'Agenda' }}</x-senat.badge>
                    <h3 class="mt-3 text-[16px] font-semibold leading-snug text-senat-ink">{{ $agendaTerdekat['title'] }}</h3>
                    <p class="mt-2 text-[12px] leading-relaxed text-senat-muted">{{ $agendaTerdekat['room'] }}</p>
                    <div class="mt-4 space-y-1">
                        <p class="text-[12px] font-medium text-senat-muted">{{ $tanggal($agendaTerdekat['date']) }}</p>
                        <p class="text-[12px] font-semibold text-senat-ink">
                            {{ $agendaTerdekat['time'] }} · {{ $agendaTerdekat['session_code'] }}
                        </p>
                    </div>
                    <x-senat.button class="mt-5" :href="route('portal.agenda')">Buka Agenda</x-senat.button>
                </div>
            @else
                <p class="mt-4 text-[13px] text-senat-muted">Belum ada agenda terjadwal.</p>
            @endif
        </section>
    </div>

    {{-- ================= AKTIVITAS TERKINI ================= --}}
    <section class="mt-6 rounded-[14px] border border-senat-line bg-white p-5 shadow-[0_3px_12px_rgba(13,26,41,0.07)]">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-[18px] font-bold text-senat-ink">Aktivitas &amp; Warta Terkini</h2>
                <p class="text-[11.5px] text-senat-muted">Update berita dan persidangan yang baru saja dipublikasikan</p>
            </div>
            <span class="rounded-full bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 text-[10.5px] font-bold text-emerald-700">
                Sinkron Otomatis
            </span>
        </div>

        <ul class="mt-4 divide-y divide-senat-line">
            @forelse (array_slice($announcements, 0, 5) as $announcement)
                <li class="flex flex-wrap items-start gap-x-4 gap-y-1 py-3.5 first:pt-1 last:pb-1">
                    <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-[5px] bg-senat-link" aria-hidden="true"></span>
                    <div class="min-w-0 flex-1">
                        <a href="{{ route('public.news.show', $announcement['slug']) }}"
                           class="text-[13.5px] font-semibold text-senat-ink hover:text-senat-primary transition-colors">
                            {{ $announcement['title'] }}
                        </a>
                        <p class="mt-0.5 text-[11.5px] text-senat-muted line-clamp-1">{{ $announcement['excerpt'] }}</p>
                    </div>
                    <span class="shrink-0 whitespace-nowrap text-[11px] font-medium text-senat-muted">
                        {{ $tanggal($announcement['date']) }}
                    </span>
                </li>
            @empty
                <li class="py-4"><p class="text-[13px] text-senat-muted">Belum ada warta yang diterbitkan.</p></li>
            @endforelse
        </ul>
    </section>

    {{-- ================= WARTA & PENGUMUMAN ================= --}}
    <section class="mt-6">
        <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-[18px] font-bold text-senat-ink">Warta &amp; Pengumuman</h2>
                    <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-[10.5px] font-bold text-emerald-800">
                        Up-to-date
                    </span>
                </div>
                <p class="text-[11.5px] text-senat-muted">Warta resmi dan siaran pers Senat Akademik yang siap diakses publik</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button"
                        @click="showNewsModal = true"
                        class="inline-flex items-center gap-1.5 rounded-md bg-senat-primary/10 px-3 py-1.5 text-[11.5px] font-semibold text-senat-primary hover:bg-senat-primary/20 transition-colors">
                    <x-senat.icon name="plus" class="h-3.5 w-3.5" />
                    <span>Tulis Berita</span>
                </button>
                <x-senat.button variant="ghost" size="sm" :trailing="'›'" :href="route('public.news')">Kanal berita publik</x-senat.button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            @forelse (array_slice($announcements, 0, 8) as $announcement)
                <a href="{{ route('public.news.show', $announcement['slug']) }}"
                   class="group flex h-full flex-col overflow-hidden rounded-[14px] border border-senat-line bg-white shadow-[0_3px_12px_rgba(13,26,41,0.06)] transition-all hover:border-senat-primary/40 hover:shadow-[0_6px_20px_rgba(13,26,41,0.1)]">
                    @if (!empty($announcement['thumbnail']))
                        <div class="h-32 w-full overflow-hidden bg-slate-100">
                            <img src="{{ $announcement['thumbnail'] }}" alt="{{ $announcement['title'] }}" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105" loading="lazy">
                        </div>
                    @else
                        <div class="flex h-32 w-full items-center justify-center bg-senat-surface text-senat-muted">
                            <x-senat.icon name="image" class="h-8 w-8" />
                        </div>
                    @endif
                    <div class="flex flex-1 flex-col p-4">
                        <div class="flex items-center justify-between gap-1.5">
                            <x-senat.badge state="neutral" :dot="false">{{ $announcement['category'] }}</x-senat.badge>
                            @if (($announcement['date'] ?? '') === date('Y-m-d'))
                                <span class="rounded bg-emerald-100 px-1.5 py-0.5 text-[9.5px] font-bold text-emerald-800 uppercase tracking-wider">Baru</span>
                            @endif
                        </div>
                        <h3 class="mt-2.5 text-[13.5px] font-semibold leading-snug text-senat-ink group-hover:text-senat-primary line-clamp-2">
                            {{ $announcement['title'] }}
                        </h3>
                        <p class="mt-1.5 line-clamp-2 text-[11.5px] leading-relaxed text-senat-muted">
                            {{ $announcement['excerpt'] }}
                        </p>
                        <div class="mt-auto pt-3 flex items-center justify-between text-[11px] font-medium text-senat-muted border-t border-slate-100">
                            <span>{{ $tanggal($announcement['date']) }}</span>
                            <span class="text-senat-primary font-bold group-hover:underline">Baca ›</span>
                        </div>
                    </div>
                </a>
            @empty
                <div class="md:col-span-2 xl:col-span-4">
                    <x-senat.empty title="Belum ada warta terpublikasi" message="Warta terbaru Senat akan muncul di area ini setelah dipublikasikan." />
                </div>
            @endforelse
        </div>
    </section>

    {{-- ================= MODAL: TULIS & PUBLIKASIKAN BERITA DARI DASHBOARD ================= --}}
    <div x-show="showNewsModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-xs overflow-y-auto"
         @click.self="showNewsModal = false"
         @keydown.escape.window="showNewsModal = false">
        <div class="relative w-full max-w-2xl rounded-[16px] border border-senat-border bg-white shadow-2xl overflow-hidden my-8"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            {{-- Header Modal --}}
            <div class="flex items-center justify-between border-b border-senat-line bg-[#f8fafc] px-6 py-4">
                <div>
                    <h3 class="text-[17px] font-bold text-senat-ink">Tulis &amp; Publikasikan Berita Baru</h3>
                    <p class="text-[11.5px] text-senat-muted">Naskah langsung tersimpan ke database &amp; tampil up-to-date di portal</p>
                </div>
                <button type="button" @click="showNewsModal = false" class="rounded-full p-1 text-slate-400 hover:bg-slate-200 hover:text-slate-700">
                    <x-senat.icon name="close" class="h-5 w-5" />
                </button>
            </div>

            {{-- Form Input Berita --}}
            <form action="{{ route('portal.news.store') }}" method="POST" class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                @csrf

                {{-- Judul Berita --}}
                <div>
                    <label class="block text-[12px] font-bold text-senat-ink">Judul Berita / Siaran Pers <span class="text-red-500">*</span></label>
                    <input type="text"
                           name="title"
                           required
                           placeholder="Contoh: Sidang Pleno Penetapan Standar Mutu Akademik Vokasi 2026"
                           class="mt-1 w-full rounded-lg border border-senat-border px-3.5 py-2 text-[13px] text-senat-ink placeholder-senat-muted focus:border-senat-primary focus:outline-hidden">
                </div>

                {{-- Baris 2 Kolom: Kategori & Status --}}
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-[12px] font-bold text-senat-ink">Kategori Berita <span class="text-red-500">*</span></label>
                        <select name="category" required class="mt-1 w-full rounded-lg border border-senat-border bg-white px-3.5 py-2 text-[13px] text-senat-ink focus:border-senat-primary focus:outline-hidden">
                            <option value="Warta Senat">Warta Senat</option>
                            <option value="Sidang Pleno">Sidang Pleno</option>
                            <option value="Pengawasan Akademik">Pengawasan Akademik</option>
                            <option value="Kerja Sama">Kerja Sama</option>
                            <option value="Regulasi">Regulasi</option>
                            <option value="Siaran Pers">Siaran Pers</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[12px] font-bold text-senat-ink">Status Publikasi</label>
                        <select name="status" x-model="postStatus" class="mt-1 w-full rounded-lg border border-senat-border bg-white px-3.5 py-2 text-[13px] text-senat-ink focus:border-senat-primary focus:outline-hidden">
                            <option value="Terpublikasi">Terpublikasi (Langsung tayang)</option>
                            <option value="Draf">Draf (Simpan internal)</option>
                        </select>
                    </div>
                </div>

                {{-- Gambar / Thumbnail --}}
                <div>
                    <label class="block text-[12px] font-bold text-senat-ink">Gambar / Thumbnail Berita (URL)</label>
                    <input type="text"
                           name="thumbnail"
                           placeholder="https://images.unsplash.com/... (Kosongkan untuk gambar default Senat)"
                           class="mt-1 w-full rounded-lg border border-senat-border px-3.5 py-2 text-[13px] text-senat-ink placeholder-senat-muted focus:border-senat-primary focus:outline-hidden">
                </div>

                {{-- Isi Berita Lengkap --}}
                <div>
                    <label class="block text-[12px] font-bold text-senat-ink">Isi Berita Lengkap <span class="text-red-500">*</span></label>
                    <textarea name="body"
                              rows="6"
                              required
                              placeholder="Tuliskan naskah lengkap berita kegiatan, risalah rapat senat, atau rilis pers di sini..."
                              class="mt-1 w-full rounded-lg border border-senat-border px-3.5 py-2.5 text-[13px] text-senat-ink placeholder-senat-muted leading-relaxed focus:border-senat-primary focus:outline-hidden"></textarea>
                </div>

                {{-- Footer Tombol Aksi --}}
                <div class="flex items-center justify-between border-t border-senat-line pt-4">
                    <button type="button" @click="showNewsModal = false" class="rounded-md border border-senat-border bg-white px-4 py-2 text-[12px] font-semibold text-senat-body hover:bg-slate-50">
                        Batal
                    </button>
                    <div class="flex items-center gap-2">
                        <button type="submit"
                                @click="postStatus = 'Draf'"
                                class="rounded-md border border-senat-border bg-[#f8fafc] px-4 py-2 text-[12px] font-semibold text-senat-primary hover:border-senat-primary">
                            Simpan sebagai Draf
                        </button>
                        <button type="submit"
                                @click="postStatus = 'Terpublikasi'"
                                class="rounded-md bg-senat-primary px-5 py-2 text-[12px] font-bold text-white shadow-xs hover:bg-senat-primaryHover">
                            Publikasikan Sekarang
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
