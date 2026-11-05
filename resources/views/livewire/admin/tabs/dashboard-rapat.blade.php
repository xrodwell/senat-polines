<div wire:key="screen-dashboard-rapat" class="space-y-6">
                {{-- Info Banner Khas Portal --}}
                <div class="flex items-start gap-3 rounded-[14px] bg-senat-badgeBg px-4 py-3.5 text-senat-link shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
                    <span class="text-[18px] font-bold leading-none" aria-hidden="true">i</span>
                    <p class="text-[13px] font-medium leading-snug">
                        Panel administrasi terpadu: kelola jadwal sidang, kuorum presensi, risalah, serta pembaharuan konten situs resmi.
                    </p>
                </div>

                {{-- Stat / KPI Khas Portal --}}
                <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
                    <x-senat.stat label="Sidang terjadwal" :value="str_pad((string) $stats['meetings_count'], 2, '0', STR_PAD_LEFT)"
                                  :hint="count($meetings).' agenda terdaftar'" icon="calendar" />
                    <x-senat.stat label="Notulensi draft" :value="str_pad((string) ($notulenItem ? 1 : 0), 2, '0', STR_PAD_LEFT)"
                                  hint="Perlu verifikasi sekretariat" icon="minutes" />
                    <x-senat.stat label="Voting dibuka" :value="str_pad((string) ($activeMeeting ? 1 : 0), 2, '0', STR_PAD_LEFT)"
                                  :hint="$activeMeeting ? 'Sesi sedang berlangsung' : 'Belum ada voting aktif'" icon="check" />
                    <x-senat.stat label="Aspirasi kajian" :value="str_pad((string) $stats['pending_aspirations'], 2, '0', STR_PAD_LEFT)"
                                  hint="Menunggu telaah komisi" icon="aspiration" />
                </div>

                <div class="grid gap-5 lg:grid-cols-2">
                    {{-- Aksi Cepat --}}
                    <section class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
                        <h2 class="text-[18px] font-bold text-senat-ink">Aksi Cepat</h2>
                        <div class="mt-4 space-y-3">
                            @foreach ($aksiCepat as $aksi)
                                <a href="{{ route('admin.preview', ['tab' => $aksi['action']]) }}"
                                   class="flex w-full items-center gap-3 rounded-[10px] px-4 py-3 text-left transition-colors {{ $aksi['accent'] ? 'bg-senat-badgeBg' : 'border border-senat-sidebarLine bg-white hover:border-senat-badgeLine' }}">
                                    <x-senat.icon :name="$aksi['icon']" class="h-4 w-4 shrink-0 {{ $aksi['accent'] ? 'text-senat-link' : 'text-senat-muted' }}" />
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-[14px] font-semibold text-senat-ink">{{ $aksi['title'] }}</span>
                                        <span class="mt-0.5 block truncate text-[11px] text-senat-body">{{ $aksi['desc'] }}</span>
                                    </span>
                                    <span class="text-[20px] font-bold leading-none text-senat-link" aria-hidden="true">›</span>
                                </a>
                            @endforeach
                        </div>
                    </section>

                    {{-- Antrean Tindakan --}}
                    <section class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
                        <h2 class="text-[18px] font-bold text-senat-ink">Antrean Tindakan</h2>
                        @if ($antrean->isEmpty())
                            <x-senat.empty class="mt-4" title="Tidak ada tindakan tertunda"
                                           message="Tidak ada berkas yang menunggu verifikasi sekretariat saat ini." />
                        @else
                            <ul class="mt-4 divide-y divide-senat-sidebarLine">
                                @foreach ($antrean as $item)
                                    <li class="flex items-start gap-3 py-3 first:pt-1 last:pb-1">
                                        <span class="mt-1.5 h-[9px] w-[9px] shrink-0 rounded-[5px] {{ $item['tone'] }}" aria-hidden="true"></span>
                                        <span class="min-w-0">
                                            <span class="block text-[13px] font-semibold text-senat-ink">{{ $item['title'] }}</span>
                                            <span class="mt-1 block text-[11px] leading-snug text-senat-body">{{ $item['meta'] }}</span>
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </section>
                </div>

                {{-- Agenda Sidang --}}
                <section class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
                    <div class="flex items-center justify-between">
                        <h2 class="text-[18px] font-bold text-senat-ink">Agenda Sidang Terjadwal</h2>
                        <a href="{{ route('admin.preview', ['tab' => 'Jadwal & Agenda Sidang']) }}"
                           class="text-[12px] font-semibold text-senat-link hover:underline">
                            Kelola Jadwal →
                        </a>
                    </div>
                    @if (empty($meetings))
                        <x-senat.empty class="mt-4" title="Belum ada agenda sidang"
                                       message="Agenda akan tampil di sini setelah sidang dijadwalkan." />
                    @else
                        <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                            @foreach ($meetings as $meeting)
                                <article class="flex flex-col justify-between rounded-[12px] border border-senat-sidebarLine bg-senat-surface p-4">
                                    <div>
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="text-[11px] font-bold uppercase text-senat-muted">{{ $meeting['session_code'] }}</span>
                                            <x-senat.badge :state="$this->chipState($meeting['type'])">{{ $this->chipLabel($meeting['type']) }}</x-senat.badge>
                                        </div>
                                        <h3 class="mt-2 text-[14px] font-bold leading-snug text-senat-ink">{{ $meeting['title'] }}</h3>
                                        <p class="mt-2 text-[12px] text-senat-body">
                                            {{ \Illuminate\Support\Carbon::parse($meeting['date'])->format('d M Y') }} · {{ $meeting['time'] }}
                                        </p>
                                        <p class="mt-1 text-[11px] text-senat-muted">{{ $meeting['room'] }}</p>
                                    </div>
                                    <div class="mt-4 flex items-center justify-between border-t border-senat-sidebarLine pt-3 text-[11px]">
                                        <span class="font-medium text-senat-body">Hadir: {{ $meeting['present_count'] }}/{{ $meeting['total_invited'] }}</span>
                                        <a href="{{ route('admin.preview', ['tab' => 'Presensi & Verifikasi QR']) }}"
                                           class="font-semibold text-senat-link hover:underline">Presensi ›</a>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </section>
                </div>
