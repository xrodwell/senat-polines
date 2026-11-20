<section wire:key="screen-arsip-dokumen" class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
                    <div class="flex items-center justify-between border-b border-senat-sidebarLine pb-4">
                        <div>
                            <h2 class="text-[18px] font-bold text-senat-ink">Repositori Dokumen & Laporan Tahunan</h2>
                            <p class="text-[12px] text-senat-body">Berkas LPJ, OTK, dan arsip kelembagaan Senat Polines</p>
                        </div>
                        <x-senat.button variant="primary" size="sm" type="button" wire:click="saveAction('Upload Arsip')">+ Unggah Berkas</x-senat.button>
                    </div>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($archives as $arc)
                            <div class="rounded-[12px] border border-senat-sidebarLine bg-senat-surface p-4 flex flex-col justify-between">
                                <div>
                                    <x-senat.badge state="neutral">{{ $arc['category'] }}</x-senat.badge>
                                    <h3 class="mt-2 text-[14px] font-bold text-senat-ink">{{ $arc['title'] }}</h3>
                                    <p class="mt-1 text-[11.5px] text-senat-muted">{{ $arc['year'] }} · {{ $arc['file_size'] }}</p>
                                </div>
                                <div class="mt-4 flex items-center justify-end border-t border-senat-sidebarLine pt-3">
                                    <x-senat.button variant="secondary" size="sm" type="button" wire:click="saveAction('Unduh Berkas {{ $arc['id'] }}')">Unduh Berkas</x-senat.button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
