<div wire:key="screen-berita-siaran-pers" class="grid gap-5 xl:grid-cols-[730px_minmax(0,1fr)]">
                    {{-- Kolom Kiri: Publikasi Warta & Berita Senat --}}
                    <section class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b border-senat-sidebarLine pb-4">
                            <div>
                                <h2 class="text-[18px] font-bold text-senat-ink">Publikasi Warta & Berita Senat</h2>
                                <p class="text-[12px] text-senat-body">Kanal siaran pers dan transparansi kegiatan persidangan</p>
                            </div>
                            <x-senat.button variant="primary" size="sm" type="button" wire:click="openCreatePostModal">
                                <x-senat.icon name="plus" class="h-4 w-4" />
                                <span>Tulis Berita</span>
                            </x-senat.button>
                        </div>

                        {{-- Bar Filter & Pencarian --}}
                        <div class="mt-4 flex flex-col gap-2.5 sm:flex-row sm:items-center sm:justify-between">
                            <div class="relative flex-1">
                                <x-senat.icon name="search" class="absolute left-3 top-2.5 h-4 w-4 text-senat-muted" />
                                <input type="text"
                                       wire:model.live.debounce.300ms="postSearch"
                                       placeholder="Cari berita berdasarkan judul atau kategori..."
                                       class="w-full rounded-lg border border-senat-border bg-senat-surface py-2 pl-9 pr-3 text-[12.5px] text-senat-ink placeholder-senat-muted focus:border-senat-primary focus:bg-white focus:outline-hidden">
                            </div>
                            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0">
                                @foreach (['Semua', 'Warta Senat', 'Sidang Pleno', 'Pengawasan Akademik', 'Kerja Sama', 'Regulasi'] as $catOption)
                                    <button type="button"
                                            wire:click="$set('postFilterCategory', '{{ $catOption }}')"
                                            class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-semibold transition-colors {{ $postFilterCategory === $catOption ? 'bg-senat-primary text-white' : 'bg-senat-surface text-senat-body hover:bg-slate-200' }}">
                                        {{ $catOption }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Daftar Berita --}}
                        <ul class="mt-4 divide-y divide-senat-sidebarLine">
                            @forelse ($posts as $post)
                                <li wire:key="post-item-{{ $post['id'] }}" class="py-4 first:pt-1">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-2">
                                            <x-senat.badge state="neutral">{{ $post['category'] }}</x-senat.badge>
                                            @if (($post['status'] ?? 'Terpublikasi') === 'Terpublikasi')
                                                <x-senat.badge state="success">Terpublikasi</x-senat.badge>
                                            @else
                                                <x-senat.badge state="warning">Draf</x-senat.badge>
                                            @endif
                                        </div>
                                        <span class="text-[11px] text-senat-muted">{{ $post['date'] }}</span>
                                    </div>

                                    <div class="mt-3 flex items-start gap-5">
                                        @if (!empty($post['thumbnail']))
                                            <img src="{{ $post['thumbnail'] }}"
                                                 alt="{{ $post['title'] }}"
                                                 class="h-16 w-20 shrink-0 rounded-lg border border-senat-border object-cover">
                                        @else
                                            <div class="flex h-16 w-20 shrink-0 items-center justify-center rounded-lg border border-senat-border bg-senat-surface text-senat-muted">
                                                <x-senat.icon name="image" class="h-6 w-6" />
                                            </div>
                                        @endif
                                        <div class="min-w-0 flex-1">
                                            <h3 class="text-[15px] font-semibold text-senat-ink leading-snug">{{ $post['title'] }}</h3>
                                            <p class="mt-1 text-[12.5px] leading-relaxed text-senat-body line-clamp-2">{{ $post['excerpt'] }}</p>
                                            <div class="mt-1 flex items-center gap-2 text-[11px] text-senat-muted">
                                                <span>Penulis: {{ $post['author'] ?? 'Sekretariat Senat' }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-3 flex flex-wrap items-center justify-end gap-2">
                                        {{-- Tombol Edit --}}
                                        <x-senat.button variant="secondary" size="sm" type="button" wire:click="openEditPostModal({{ $post['id'] }})">
                                            <x-senat.icon name="edit" class="h-3.5 w-3.5" />
                                            <span>Edit</span>
                                        </x-senat.button>

                                        {{-- Tombol Publikasikan / Tarik ke Draf --}}
                                        @if (($post['status'] ?? 'Terpublikasi') === 'Terpublikasi')
                                            <x-senat.button variant="secondary" size="sm" type="button" wire:click="togglePublishPost({{ $post['id'] }})">
                                                <span class="text-amber-700">Tarik ke Draf</span>
                                            </x-senat.button>
                                        @else
                                            <x-senat.button variant="primary" size="sm" type="button" wire:click="togglePublishPost({{ $post['id'] }})">
                                                <x-senat.icon name="check" class="h-3.5 w-3.5" />
                                                <span>Publikasikan</span>
                                            </x-senat.button>
                                        @endif

                                        {{-- Tombol Hapus Berita --}}
                                        <button type="button"
                                                wire:click="deletePost({{ $post['id'] }})"
                                                wire:confirm="Apakah Anda yakin ingin menghapus berita ini?"
                                                class="inline-flex h-9 items-center justify-center rounded-md border border-red-200 bg-red-50 px-2.5 text-[11.5px] font-semibold text-red-700 hover:bg-red-100 transition-colors">
                                            <x-senat.icon name="trash" class="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                </li>
                            @empty
                                <li class="py-8 text-center">
                                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-senat-surface text-senat-muted">
                                        <x-senat.icon name="file-text" class="h-6 w-6" />
                                    </div>
                                    <p class="mt-2 text-[13px] font-medium text-senat-ink">Tidak ada berita yang ditemukan</p>
                                    <p class="text-[11px] text-senat-muted">Coba ubah kata kunci pencarian atau buat berita baru.</p>
                                </li>
                            @endforelse
                        </ul>
                    </section>

                    {{-- Kolom Kanan: Antrean Unggah Media & Berkas --}}
                    <section class="space-y-5">
                        <div class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
                            <h2 class="text-[18px] font-bold text-senat-ink">Antrean Unggah Media</h2>
                            <p class="mt-1 text-[12px] text-senat-body">Format PDF atau gambar dokumentasi maksimal 10MB</p>

                            @if ($mediaFile)
                                {{-- Berkas Terpilih & Siap Disimpan --}}
                                <div class="mt-5 rounded-[12px] border-2 border-senat-primary/30 bg-[#f4f8fc] p-5 text-center">
                                    @php
                                        $isImg = in_array($mediaFile->getMimeType(), ['image/jpeg', 'image/png', 'image/webp'], true);
                                    @endphp

                                    @if ($isImg)
                                        <div class="relative mx-auto max-w-[200px] overflow-hidden rounded-lg border border-senat-border bg-white shadow-xs">
                                            <img src="{{ $mediaFile->temporaryUrl() }}" alt="Preview" class="h-28 w-full object-cover">
                                        </div>
                                    @else
                                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-xl bg-senat-surface text-senat-link">
                                            <x-senat.icon name="file-text" class="h-8 w-8 text-senat-link" />
                                        </div>
                                    @endif

                                    <p class="mt-3 text-[13px] font-semibold text-senat-ink truncate px-2">
                                        {{ $mediaFile->getClientOriginalName() }}
                                    </p>
                                    <p class="text-[11px] text-senat-muted">
                                        {{ number_format($mediaFile->getSize() / 1024, 0) }} KB · Siap disimpan ke server
                                    </p>

                                    <div class="mt-4 flex items-center justify-center gap-2">
                                        <x-senat.button variant="primary" size="sm" type="button" wire:click="saveMediaFile" wire:loading.attr="disabled">
                                            <span wire:loading.remove wire:target="saveMediaFile">Simpan Berkas</span>
                                            <span wire:loading wire:target="saveMediaFile">Menyimpan...</span>
                                        </x-senat.button>
                                        <x-senat.button variant="secondary" size="sm" type="button" wire:click="cancelMediaFile">
                                            Batal
                                        </x-senat.button>
                                    </div>
                                </div>
                            @else
                                <div x-data="{ isDragging: false }"
                                     @dragenter.prevent="isDragging = true"
                                     @dragover.prevent="isDragging = true"
                                     @dragleave.prevent="if (!$event.currentTarget.contains($event.relatedTarget)) isDragging = false"
                                     @drop.prevent.stop="isDragging = false; if ($event.dataTransfer.files.length) $wire.upload('mediaFile', $event.dataTransfer.files[0])"
                                     :class="isDragging ? 'border-senat-primary bg-blue-50 ring-2 ring-senat-primary/20' : ''"
                                     class="mt-5 rounded-[12px] border-2 border-dashed border-senat-border bg-senat-surface p-6 text-center transition-colors hover:border-senat-primary hover:bg-[#f6f9fc]">
                                    <input type="file"
                                           wire:model="mediaFile"
                                           id="media-file-input"
                                           accept=".png,.jpg,.jpeg,.webp,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx"
                                           class="hidden">
                                    <label for="media-file-input" class="block cursor-pointer">
                                        <span class="text-senat-link inline-block">
                                            <x-senat.icon name="upload" class="mx-auto h-8 w-8 text-senat-link" />
                                        </span>
                                        <p class="mt-2 text-[13px] font-medium text-senat-ink">Tarik satu berkas ke sini</p>
                                        <p class="mt-1 text-[11px] text-senat-muted">atau klik untuk memilih dari komputer · maksimal 10MB</p>
                                        <div class="mt-3 flex flex-wrap items-center justify-center gap-1.5 text-[10px] font-bold text-senat-muted uppercase">
                                            @foreach (['PNG', 'JPG', 'WEBP', 'PDF', 'DOC', 'DOCX', 'XLS', 'XLSX', 'PPT', 'PPTX'] as $format)
                                                <span class="rounded border border-senat-border bg-white px-2 py-0.5">{{ $format }}</span>
                                            @endforeach
                                        </div>
                                        <div class="mt-4">
                                            <span class="inline-flex items-center justify-center gap-1.5 rounded-md border border-senat-border bg-[#f8fafc] px-4 py-2 text-[11.5px] font-semibold text-senat-primary shadow-2xs hover:border-senat-primary">
                                                <x-senat.icon name="file-text" class="h-3.5 w-3.5" />
                                                <span>Pilih Berkas</span>
                                            </span>
                                        </div>
                                    </label>
                                </div>
                            @endif

                            {{-- Loading Indikator File Livewire --}}
                            <div wire:loading wire:target="mediaFile" class="mt-3 text-center text-[12px] font-medium text-senat-link">
                                Membaca dan memproses berkas...
                            </div>
                            @error('mediaFile')
                                <p class="mt-2 text-center text-[12px] font-semibold text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Repositori Berkas & Media Terunggah --}}
                        <div class="rounded-[14px] border border-senat-sidebarLine bg-white p-5 shadow-[0_3px_12px_0_rgba(13,26,41,0.07)]">
                            <div class="flex items-center justify-between border-b border-senat-sidebarLine pb-3">
                                <div>
                                    <h3 class="text-[14px] font-bold text-senat-ink">Media & Berkas Tersimpan</h3>
                                    <p class="text-[11px] text-senat-muted">Dapat ditautkan ke warta &amp; berita</p>
                                </div>
                                <span class="rounded-full bg-senat-surface px-2 py-0.5 text-[10.5px] font-bold text-senat-ink border border-senat-border">
                                    {{ count($mediaList) }} Berkas
                                </span>
                            </div>

                            <div class="mt-3 max-h-[360px] overflow-y-auto space-y-2.5 pr-1">
                                @forelse ($mediaList as $m)
                                    <div wire:key="media-item-{{ $m['id'] }}" class="flex items-center justify-between gap-3 rounded-lg border border-senat-border bg-senat-surface p-2.5 transition-colors hover:bg-[#f6f9fc]">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            @if (($m['type'] ?? '') === 'image')
                                                <button type="button"
                                                        wire:click="openMediaPreview('{{ $m['id'] }}')"
                                                        class="shrink-0 rounded-md focus:outline-hidden focus:ring-2 focus:ring-senat-primary"
                                                        aria-label="Pratinjau {{ $m['original_name'] ?? $m['name'] }}">
                                                    <img src="{{ $m['url'] }}" alt="" class="h-10 w-10 rounded-md border border-senat-border object-cover">
                                                </button>
                                            @else
                                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-white border border-senat-border text-senat-link">
                                                    <x-senat.icon name="file-text" class="h-5 w-5" />
                                                </div>
                                            @endif
                                            <div class="min-w-0">
                                                <p class="truncate text-[12px] font-semibold text-senat-ink">{{ $m['original_name'] ?? $m['name'] }}</p>
                                                <p class="text-[10px] text-senat-muted">{{ $m['size'] ?? '-' }} · {{ $m['uploaded_at'] ?? 'Baru saja' }}</p>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-1 shrink-0">
                                            <button type="button"
                                                    wire:click="openMediaPreview('{{ $m['id'] }}')"
                                                    title="Pratinjau berkas"
                                                    class="rounded-md border border-senat-border bg-white px-2 py-1 text-[10.5px] font-semibold text-senat-primary hover:border-senat-primary transition-colors">
                                                Pratinjau
                                            </button>
                                            @if (($m['type'] ?? '') === 'image')
                                                <button type="button"
                                                        wire:click="useMediaAsPostImage('{{ $m['id'] }}')"
                                                        title="Gunakan sebagai gambar berita"
                                                        class="rounded-md border border-senat-border bg-white px-2 py-1 text-[10.5px] font-semibold text-senat-primary hover:border-senat-primary transition-colors">
                                                    Pilih Gambar
                                                </button>
                                            @endif

                                            @if (!empty($m['url']) && $m['url'] !== '#')
                                                <a href="{{ $m['url'] }}" target="_blank" title="Buka berkas" class="rounded p-1 text-senat-muted hover:text-senat-primary">
                                                    <x-senat.icon name="external" class="h-3.5 w-3.5" />
                                                </a>
                                            @endif

                                            <button type="button"
                                                    wire:click="deleteMediaFile('{{ $m['id'] }}')"
                                                    title="Hapus berkas"
                                                    class="rounded p-1 text-senat-muted hover:text-red-600 transition-colors">
                                                <x-senat.icon name="trash" class="h-3.5 w-3.5" />
                                            </button>
                                        </div>
                                    </div>
                                @empty
                                    <p class="py-4 text-center text-[12px] text-senat-muted">Belum ada berkas media terunggah.</p>
                                @endforelse
                            </div>
                        </div>
                    </section>
                </div>

                {{-- MODAL DIALOG: Tulis & Edit Berita --}}
                @if ($showPostModal)
                    <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/50 p-2 backdrop-blur-xs sm:p-4"
                         wire:click.self="closePostModal"
                         wire:keydown.escape="closePostModal">
                        <div class="relative my-auto flex max-h-[calc(100dvh-1rem)] min-h-0 w-full max-w-3xl flex-col overflow-hidden rounded-[16px] border border-senat-border bg-white shadow-2xl sm:max-h-[calc(100dvh-2rem)]">
                            {{-- Header Modal --}}
                            <div class="flex shrink-0 items-center justify-between border-b border-senat-sidebarLine bg-[#f8fafc] px-4 py-4 sm:px-6">
                                <div>
                                    <h3 class="text-[17px] font-bold text-senat-ink">
                                        {{ $editingPostId ? 'Edit Warta & Berita Senat' : 'Tulis Berita & Siaran Pers Baru' }}
                                    </h3>
                                    <p class="text-[11.5px] text-senat-muted">
                                        {{ $editingPostId ? 'Perbarui konten berita dan status publikasi' : 'Buat naskah rilis pers resmi Senat Akademik Polines' }}
                                    </p>
                                </div>
                                <button type="button" wire:click="closePostModal" class="rounded-full p-1.5 text-slate-400 hover:bg-slate-200 hover:text-slate-700 transition-colors">
                                    <x-senat.icon name="close" class="h-5 w-5" />
                                </button>
                            </div>

                            {{-- Form Isi Modal --}}
                            <div class="min-h-0 flex-1 space-y-4 overflow-y-auto p-4 sm:p-6">
                                {{-- Alert jika ada validasi yang belum lengkap --}}
                                @if ($errors->any())
                                    <div class="rounded-lg bg-red-50 border border-red-200 p-3 text-[12px] text-red-700">
                                        <p class="font-bold flex items-center gap-1.5">
                                            <x-senat.icon name="warning" class="h-4 w-4 text-red-600" />
                                            <span>Mohon lengkapi kolom yang wajib diisi:</span>
                                        </p>
                                        <ul class="list-disc list-inside mt-1.5 text-[11.5px] space-y-0.5">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                {{-- Judul Berita --}}
                                <div>
                                    <label class="block text-[12px] font-bold text-senat-ink">Judul Berita <span class="text-red-500">*</span></label>
                                    <input type="text"
                                           wire:model="postTitle"
                                           placeholder="Contoh: Sidang Pleno Penetapan Standar Kompetensi Mahasiswa Vokasi 2026"
                                           class="mt-1 w-full rounded-lg border border-senat-border px-3.5 py-2 text-[13px] text-senat-ink placeholder-senat-muted focus:border-senat-primary focus:outline-hidden">
                                    @error('postTitle') <span class="text-[11px] font-semibold text-red-500">{{ $message }}</span> @enderror
                                </div>

                                {{-- Baris 2 Kolom: Kategori & Tanggal --}}
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="block text-[12px] font-bold text-senat-ink">Kategori Berita <span class="text-red-500">*</span></label>
                                        <select wire:model="postCategory" class="mt-1 w-full rounded-lg border border-senat-border bg-white px-3.5 py-2 text-[13px] text-senat-ink focus:border-senat-primary focus:outline-hidden">
                                            <option value="Warta Senat">Warta Senat</option>
                                            <option value="Sidang Pleno">Sidang Pleno</option>
                                            <option value="Pengawasan Akademik">Pengawasan Akademik</option>
                                            <option value="Kerja Sama">Kerja Sama</option>
                                            <option value="Regulasi">Regulasi</option>
                                            <option value="Siaran Pers">Siaran Pers</option>
                                        </select>
                                        @error('postCategory') <span class="text-[11px] font-semibold text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[12px] font-bold text-senat-ink">Tanggal Publikasi</label>
                                        <input type="date"
                                               wire:model="postDate"
                                               class="mt-1 w-full rounded-lg border border-senat-border px-3.5 py-2 text-[13px] text-senat-ink focus:border-senat-primary focus:outline-hidden">
                                    </div>
                                </div>

                                {{-- Ringkasan Berita --}}
                                <div>
                                    <label class="block text-[12px] font-bold text-senat-ink">Ringkasan Berita (Excerpt)</label>
                                    <textarea wire:model="postExcerpt"
                                              rows="2"
                                              placeholder="Ringkasan singkat 1-2 kalimat untuk kartu berita portal publik..."
                                              class="mt-1 w-full rounded-lg border border-senat-border px-3.5 py-2 text-[13px] text-senat-ink placeholder-senat-muted focus:border-senat-primary focus:outline-hidden"></textarea>
                                </div>

                                {{-- Isi Berita Lengkap --}}
                                <div>
                                    <label class="block text-[12px] font-bold text-senat-ink">Isi Berita Lengkap <span class="text-red-500">*</span></label>
                                    <textarea wire:model="postBody"
                                              rows="6"
                                              placeholder="Tuliskan naskah lengkap berita, hasil sidang, dokumentasi, atau siaran pers di sini..."
                                              class="mt-1 w-full rounded-lg border border-senat-border px-3.5 py-2.5 text-[13px] text-senat-ink placeholder-senat-muted leading-relaxed focus:border-senat-primary focus:outline-hidden"></textarea>
                                    @error('postBody') <span class="text-[11px] font-semibold text-red-500">{{ $message }}</span> @enderror
                                </div>

                                {{-- Galeri Gambar Berita --}}
                                <div>
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <div>
                                            <label class="block text-[12px] font-bold text-senat-ink">Galeri Gambar Berita</label>
                                            <p class="mt-0.5 text-[11px] text-senat-muted">Pilih beberapa gambar tersimpan atau tambahkan lewat tautan gambar.</p>
                                        </div>
                                        <button type="button" wire:click="openMediaPicker" class="rounded-md border border-senat-border bg-white px-3 py-2 text-[11px] font-semibold text-senat-primary hover:border-senat-primary">
                                            Pilih dari Media Tersimpan
                                        </button>
                                    </div>

                                    <div class="mt-3 flex flex-col gap-2 sm:flex-row">
                                        <input type="url" wire:model="postImageUrl" placeholder="https://alamat-gambar.jpg"
                                               class="min-w-0 flex-1 rounded-lg border border-senat-border px-3.5 py-2 text-[13px] text-senat-ink placeholder-senat-muted focus:border-senat-primary focus:outline-hidden">
                                        <button type="button" wire:click="addPostImageFromUrl" class="rounded-lg border border-senat-border bg-senat-surface px-4 py-2 text-[12px] font-semibold text-senat-primary hover:border-senat-primary">
                                            Tambahkan Tautan
                                        </button>
                                    </div>
                                    @error('postImageUrl') <span class="text-[11px] font-semibold text-red-500">{{ $message }}</span> @enderror

                                    @if (count($postImages) > 0)
                                        <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3">
                                            @foreach ($postImages as $index => $image)
                                                <div wire:key="post-image-{{ $index }}" class="overflow-hidden rounded-lg border border-senat-border bg-senat-surface">
                                                    <img src="{{ $image }}" alt="Gambar berita {{ $index + 1 }}" class="h-28 w-full object-cover">
                                                    <div class="flex items-center justify-between gap-2 p-2">
                                                        <span class="truncate text-[10px] font-medium text-senat-muted">{{ $index === 0 ? 'Gambar utama' : 'Gambar ' . ($index + 1) }}</span>
                                                        <button type="button" wire:click="removePostImage({{ $index }})" class="shrink-0 text-[10px] font-semibold text-red-600 hover:underline">Hapus</button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if ($errors->has('postImages') || $errors->has('postImages.*'))
                                        <p class="text-[11px] font-semibold text-red-600">{{ $errors->first('postImages') ?: $errors->first('postImages.*') }}</p>
                                    @endif
                                </div>

                                {{-- Lampiran berita --}}
                                <div class="rounded-lg border border-senat-border bg-senat-surface p-3.5">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <div>
                                            <h4 class="text-[12px] font-bold text-senat-ink">Lampiran Berita</h4>
                                            <p class="mt-0.5 text-[11px] text-senat-muted">Dokumen atau berkas lain dari media tersimpan.</p>
                                        </div>
                                        <button type="button" wire:click="openMediaPicker" class="rounded-md border border-senat-border bg-white px-3 py-1.5 text-[11px] font-semibold text-senat-primary hover:border-senat-primary">Pilih Berkas</button>
                                    </div>
                                    <div class="mt-3 flex flex-col gap-2 sm:flex-row">
                                        <input type="text" wire:model="postAttachmentName" placeholder="Nama lampiran (opsional)"
                                               class="min-w-0 rounded-lg border border-senat-border px-3 py-2 text-[12px] text-senat-ink sm:w-1/3">
                                        <input type="url" wire:model="postAttachmentUrl" placeholder="Tautan dokumen atau berkas"
                                               class="min-w-0 flex-1 rounded-lg border border-senat-border px-3 py-2 text-[12px] text-senat-ink">
                                        <button type="button" wire:click="addPostAttachmentFromUrl" class="rounded-lg border border-senat-border bg-white px-3 py-2 text-[11px] font-semibold text-senat-primary">Tambah</button>
                                    </div>
                                    @error('postAttachmentUrl') <span class="text-[11px] font-semibold text-red-500">{{ $message }}</span> @enderror
                                    @if (count($postAttachments) > 0)
                                        <ul class="mt-3 space-y-2">
                                            @foreach ($postAttachments as $index => $attachment)
                                                <li wire:key="post-attachment-{{ $index }}" class="flex items-center justify-between gap-3 rounded-md border border-senat-border bg-white px-3 py-2">
                                                    <a href="{{ $attachment['url'] }}" target="_blank" rel="noopener noreferrer" class="min-w-0 truncate text-[11px] font-medium text-senat-link hover:underline">{{ $attachment['name'] }}</a>
                                                    <button type="button" wire:click="removePostAttachment({{ $index }})" class="shrink-0 text-[10px] font-semibold text-red-600">Hapus</button>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>

                                {{-- Status Publikasi --}}
                                <div class="rounded-lg border border-senat-border bg-senat-surface p-3.5">
                                    <label class="block text-[12px] font-bold text-senat-ink">Status Publikasi</label>
                                    <div class="mt-2 flex items-center gap-5 text-[13px]">
                                        <label class="inline-flex items-center gap-2 cursor-pointer font-medium text-senat-ink">
                                            <input type="radio" wire:model="postStatus" value="Terpublikasi" class="text-senat-primary focus:ring-senat-primary">
                                            <span>Terpublikasi (Langsung tayang di portal)</span>
                                        </label>
                                        <label class="inline-flex items-center gap-2 cursor-pointer font-medium text-senat-ink">
                                            <input type="radio" wire:model="postStatus" value="Draf" class="text-senat-primary focus:ring-senat-primary">
                                            <span>Draf (Simpan internal terlebih dahulu)</span>
                                        </label>
                                    </div>
                                </div>

                                {{-- Footer Aksi Modal --}}
                                <div class="flex flex-col-reverse items-stretch justify-between gap-3 border-t border-senat-sidebarLine pt-4 sm:flex-row sm:items-center">
                                    <x-senat.button variant="secondary" size="md" type="button" wire:click="closePostModal">
                                        Batal
                                    </x-senat.button>

                                    <div class="flex flex-col gap-2 sm:flex-row">
                                        <x-senat.button variant="secondary" size="md" type="button" wire:click="savePost(false)" wire:loading.attr="disabled">
                                            <span wire:loading.remove wire:target="savePost(false)">Simpan sebagai Draf</span>
                                            <span wire:loading wire:target="savePost(false)">Menyimpan...</span>
                                        </x-senat.button>

                                        <x-senat.button variant="primary" size="md" type="button" wire:click="savePost(true)" wire:loading.attr="disabled">
                                            <span wire:loading.remove wire:target="savePost(true)">{{ $editingPostId ? 'Simpan Perubahan' : 'Publikasikan Sekarang' }}</span>
                                            <span wire:loading wire:target="savePost(true)">Memproses...</span>
                                        </x-senat.button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($showMediaPicker)
                    <div class="fixed inset-0 z-[60] flex items-center justify-center overflow-y-auto bg-black/60 p-2 sm:p-4"
                         wire:click.self="closeMediaPicker"
                         wire:keydown.escape="closeMediaPicker">
                        <section class="flex max-h-[calc(100dvh-1rem)] min-h-0 w-full max-w-4xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl sm:max-h-[calc(100dvh-2rem)]"
                                 role="dialog" aria-modal="true" aria-labelledby="media-picker-title">
                            <header class="flex shrink-0 items-center justify-between border-b border-senat-sidebarLine px-4 py-4 sm:px-6">
                                <div>
                                    <h3 id="media-picker-title" class="text-[16px] font-bold text-senat-ink">Pilih Media untuk Berita</h3>
                                    <p class="mt-1 text-[11px] text-senat-muted">Klik gambar untuk menambah atau menghapus dari galeri. Dokumen akan menjadi lampiran.</p>
                                </div>
                                <button type="button" wire:click="closeMediaPicker" class="rounded-md p-2 text-senat-muted hover:bg-senat-surface" aria-label="Tutup pemilih media">×</button>
                            </header>
                            <div class="min-h-0 flex-1 overflow-y-auto p-4 sm:p-6">
                                @if (count($mediaList) > 0)
                                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                                        @foreach ($mediaList as $m)
                                            @php
                                                $selectedInPost = ($m['type'] ?? '') === 'image'
                                                    ? in_array($m['url'] ?? '', $postImages, true)
                                                    : collect($postAttachments)->contains('url', $m['url'] ?? '');
                                            @endphp
                                            <button type="button"
                                                    wire:key="media-picker-item-{{ $m['id'] }}"
                                                    wire:click="toggleMediaInPost('{{ $m['id'] }}')"
                                                    class="overflow-hidden rounded-xl border text-left transition {{ $selectedInPost ? 'border-senat-primary ring-2 ring-senat-primary/20' : 'border-senat-border hover:border-senat-primary' }}">
                                                @if (($m['type'] ?? '') === 'image')
                                                    <img src="{{ $m['url'] }}" alt="{{ $m['original_name'] ?? $m['name'] }}" class="h-28 w-full bg-senat-surface object-cover">
                                                @else
                                                    <span class="flex h-28 items-center justify-center bg-senat-surface text-senat-link">
                                                        <x-senat.icon name="file-text" class="h-9 w-9" />
                                                    </span>
                                                @endif
                                                <span class="block p-2.5">
                                                    <span class="block truncate text-[11px] font-semibold text-senat-ink">{{ $m['original_name'] ?? $m['name'] }}</span>
                                                    <span class="mt-1 block text-[10px] font-medium {{ $selectedInPost ? 'text-senat-link' : 'text-senat-muted' }}">
                                                        {{ $selectedInPost ? 'Dipilih · klik untuk batal' : (($m['type'] ?? '') === 'image' ? 'Gambar' : 'Lampiran') }}
                                                    </span>
                                                </span>
                                            </button>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="rounded-lg border border-dashed border-senat-border p-8 text-center text-[12px] text-senat-muted">Belum ada media tersimpan. Unggah gambar atau dokumen terlebih dahulu.</p>
                                @endif
                            </div>
                            <footer class="flex shrink-0 items-center justify-between gap-3 border-t border-senat-sidebarLine px-4 py-3 sm:px-6">
                                <span class="text-[11px] text-senat-muted">{{ count($postImages) }} gambar · {{ count($postAttachments) }} lampiran dipilih</span>
                                <button type="button" wire:click="closeMediaPicker" class="rounded-lg bg-senat-primary px-4 py-2 text-[12px] font-semibold text-white hover:bg-senat-primaryHover">Selesai</button>
                            </footer>
                        </section>
                    </div>
                @endif

                @php
                    $previewMedia = collect($mediaList)->firstWhere('id', $mediaPreviewId);
                @endphp
                @if ($showMediaPreviewModal && $previewMedia)
                    <div class="fixed inset-0 z-[70] flex items-center justify-center overflow-y-auto bg-black/70 p-2 sm:p-4"
                         wire:click.self="closeMediaPreview"
                         wire:keydown.escape="closeMediaPreview">
                        <section class="flex max-h-[calc(100dvh-1rem)] min-h-0 w-full max-w-3xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl sm:max-h-[calc(100dvh-2rem)]"
                                 role="dialog" aria-modal="true" aria-labelledby="media-preview-title">
                            <header class="flex shrink-0 items-center justify-between gap-3 border-b border-senat-sidebarLine px-4 py-3 sm:px-6">
                                <div class="min-w-0">
                                    <h3 id="media-preview-title" class="truncate text-[14px] font-bold text-senat-ink">{{ $previewMedia['original_name'] ?? $previewMedia['name'] }}</h3>
                                    <p class="text-[10px] text-senat-muted">{{ $previewMedia['size'] ?? '-' }} · {{ $previewMedia['uploaded_at'] ?? 'Baru saja' }}</p>
                                </div>
                                <button type="button" wire:click="closeMediaPreview" class="rounded-md p-2 text-senat-muted hover:bg-senat-surface" aria-label="Tutup pratinjau">×</button>
                            </header>
                            <div class="min-h-0 flex-1 overflow-auto bg-senat-surface p-3 sm:p-5">
                                @if (($previewMedia['type'] ?? '') === 'image')
                                    <img src="{{ $previewMedia['url'] }}" alt="{{ $previewMedia['original_name'] ?? $previewMedia['name'] }}" class="mx-auto max-h-[65dvh] max-w-full rounded-lg object-contain">
                                @elseif (str_ends_with(strtolower($previewMedia['name'] ?? ''), '.pdf'))
                                    <iframe src="{{ $previewMedia['url'] }}" title="Pratinjau {{ $previewMedia['original_name'] ?? $previewMedia['name'] }}" class="h-[65dvh] min-h-[300px] w-full rounded-lg border border-senat-border bg-white"></iframe>
                                @else
                                    <div class="flex min-h-[240px] flex-col items-center justify-center gap-3 rounded-lg border border-senat-border bg-white p-6 text-center">
                                        <x-senat.icon name="file-text" class="h-12 w-12 text-senat-link" />
                                        <p class="text-[12px] font-semibold text-senat-ink">Pratinjau tidak tersedia untuk tipe berkas ini.</p>
                                        <a href="{{ $previewMedia['url'] }}" target="_blank" rel="noopener noreferrer" class="text-[12px] font-semibold text-senat-link hover:underline">Buka atau unduh berkas</a>
                                    </div>
                                @endif
                            </div>
                            <footer class="flex shrink-0 flex-wrap items-center justify-between gap-3 border-t border-senat-sidebarLine px-4 py-3 sm:px-6">
                                <a href="{{ $previewMedia['url'] }}" target="_blank" rel="noopener noreferrer" class="text-[11px] font-semibold text-senat-link hover:underline">Buka berkas</a>
                                <div class="flex items-center gap-2">
                                    <button type="button" wire:click="addMediaToPost('{{ $previewMedia['id'] }}')" class="rounded-lg border border-senat-border bg-white px-3 py-2 text-[11px] font-semibold text-senat-primary hover:border-senat-primary">Gunakan di Berita</button>
                                    <button type="button" wire:click="closeMediaPreview" class="rounded-lg bg-senat-primary px-4 py-2 text-[11px] font-semibold text-white hover:bg-senat-primaryHover">Tutup</button>
                                </div>
                            </footer>
                        </section>
                    </div>
                @endif
