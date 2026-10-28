@extends('layouts.portal', ['sidebar' => 'sekretariat'])

@section('title', 'Aspirasi & RUU Kampus — Portal Internal Senat Akademik Polines')

@section('content')
    @php
        /* 13F — Dashboard Sekretariat / Aspirasi & RUU Kampus (Figma node 2281:1259) */
        $tahapan = [
            ['key' => 'Draft', 'accent' => 'text-[#155ead]', 'chip' => 'info', 'statuses' => ['Diterima', 'Draft', 'Menunggu Verifikasi']],
            ['key' => 'Kajian', 'accent' => 'text-[#5131b4]', 'chip' => 'neutral', 'statuses' => ['Sedang Ditelaah', 'Perlu Klarifikasi']],
            ['key' => 'Sidang Pleno', 'accent' => 'text-[#a95100]', 'chip' => 'warning', 'statuses' => ['Ditindaklanjuti Komisi']],
            ['key' => 'Pengesahan', 'accent' => 'text-[#116a3c]', 'chip' => 'success', 'statuses' => ['Selesai', 'Disahkan', 'Ditutup']],
        ];

        $pipeline = collect($tahapan)->map(function ($stage) use ($aspirations) {
            $stage['items'] = collect($aspirations)->whereIn('status', $stage['statuses'])->values();

            return $stage;
        });

        $drafDibuka = $pipeline->firstWhere('key', 'Draft')['items']->count();
    @endphp

    <div class="mx-auto max-w-[1116px] space-y-6">
        <header>
            <h1 class="text-[25px] font-bold leading-tight text-senat-ink">Aspirasi &amp; RUU Kampus</h1>
            <p class="mt-2 text-[13px] leading-snug text-senat-body">
                Kelola aspirasi, kajian, Public Hearing Digital, dan tahap pembentukan RUU Kampus.
            </p>
        </header>

        {{-- Smart Legislative Pipeline --}}
        <section class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
            <h2 class="text-[18px] font-bold text-senat-ink">Smart Legislative Pipeline</h2>
            <p class="mt-1.5 text-[13px] text-senat-body">
                Aspirasi publik dan RUU Kampus dikelola melalui tahap yang dapat ditelusuri.
            </p>

            <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                @foreach ($pipeline as $stage)
                    <div class="rounded-[12px] bg-senat-surface p-3">
                        <div class="flex items-start justify-between gap-2 px-1">
                            <p class="text-[14px] font-bold {{ $stage['accent'] }}">{{ $stage['key'] }}</p>
                            <x-senat.badge :state="$stage['chip']" :dot="false">{{ $stage['items']->count() }}</x-senat.badge>
                        </div>

                        <div class="mt-3 space-y-3">
                            @forelse ($stage['items'] as $item)
                                <article class="rounded-[10px] border border-senat-sidebarLine bg-white p-3">
                                    <p class="line-clamp-2 text-[13px] font-semibold leading-snug text-senat-ink">{{ $item['subject'] }}</p>
                                    <p class="mt-2 text-[11px] text-senat-body">{{ $item['ticket_code'] }}</p>
                                    <p class="mt-1 text-[11px] text-senat-muted">{{ $item['status'] }}</p>
                                </article>
                            @empty
                                <p class="rounded-[10px] border border-dashed border-senat-border bg-white px-3 py-4 text-[11px] text-senat-muted">
                                    Belum ada aspirasi pada tahap ini.
                                </p>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Public Hearing Digital --}}
        <section class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
            <h2 class="text-[18px] font-bold text-senat-ink">Public Hearing Digital</h2>
            <p class="mt-1.5 text-[13px] text-senat-body">
                Buka periode tanggapan terbatas atau terbuka untuk draf aturan sebelum disahkan.
            </p>

            <div class="mt-5 grid gap-4 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
                <div class="grid gap-4 sm:grid-cols-3">
                    <x-senat.stat label="Draf dibuka" :value="str_pad((string) $drafDibuka, 2, '0', STR_PAD_LEFT)" icon="folder" />
                    <x-senat.stat label="Tanggapan" value="00" icon="aspiration" />
                    <x-senat.stat label="Perlu moderasi" value="00" icon="warning" />
                </div>
                <x-senat.button variant="primary" class="lg:mb-1" type="button">Atur Public Hearing</x-senat.button>
            </div>
        </section>
    </div>
@endsection
