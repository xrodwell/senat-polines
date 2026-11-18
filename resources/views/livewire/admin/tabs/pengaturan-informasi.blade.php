<div wire:key="screen-pengaturan-hero" class="rounded-xl border border-slate-200/80 bg-white p-6 shadow-xs">
    <h2 class="text-[16px] font-bold text-slate-900">Editor Hero Banner & Informasi Beranda Publik</h2>
    <p class="text-[12px] text-slate-500">Konfigurasi visual dan pesan sambutan pada halaman utama portal Senat</p>
    <div role="status" aria-live="polite">
        @if ($heroError)<p class="mt-3 text-red-700">{{ $heroError }}</p>@endif
        @if ($heroMessage)<p class="mt-3 text-green-700">{{ $heroMessage }}</p>@endif
        <p wire:loading wire:target="reloadHeroSettings,saveHeroSettings">Memproses pengaturan hero…</p>
    </div>
    <p class="mt-3 text-[12px] text-slate-500">Muat ulang akan mengganti draft. Salin perubahan yang belum disimpan terlebih dahulu.</p>
    <button type="button" wire:click="reloadHeroSettings" wire:confirm="Draft akan diganti dengan nilai tersimpan. Sudah menyalin draft?" wire:loading.attr="disabled" class="mt-2 rounded border px-3 py-2">Muat ulang nilai tersimpan</button>
    <form class="mt-5 space-y-4" wire:submit="saveHeroSettings">
        @foreach (['eyebrow' => 'Teks Pengantar (Eyebrow)', 'title' => 'Judul Utama (Display Headline)', 'description' => 'Sub-deskripsi Hero', 'background_image' => 'Gambar Latar Hero'] as $field => $label)
            <div>
                <label for="hero-{{ $field }}" class="text-[11.5px] font-semibold text-slate-700">{{ $label }}</label>
                @if (in_array($field, ['title', 'description']))
                    <textarea id="hero-{{ $field }}" wire:model="hero.{{ $field }}" rows="{{ $field === 'title' ? 2 : 3 }}" aria-describedby="hero-{{ $field }}-help hero-{{ $field }}-error" @error('hero.'.$field) aria-invalid="true" @enderror class="mt-1 w-full rounded-lg border border-slate-300 p-3 text-[12px] text-slate-900"></textarea>
                @else
                    <input id="hero-{{ $field }}" type="text" wire:model="hero.{{ $field }}" aria-describedby="hero-{{ $field }}-help hero-{{ $field }}-error" @error('hero.'.$field) aria-invalid="true" @enderror class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-[12px] text-slate-900">
                @endif
                <p id="hero-{{ $field }}-help" class="text-[12px] text-slate-500">{{ match ($field) { 'title' => 'Maksimal 160 karakter dan dua baris tanpa baris kosong. Baris kedua berwarna oranye.', 'description' => 'Maksimal 500 karakter.', 'background_image' => 'URL http/https atau path relatif terhadap root yang diawali /.', default => 'Teks pengantar singkat, satu baris.' } }}</p>
                <p id="hero-{{ $field }}-error" class="text-red-700">@error('hero.'.$field) {{ $message }} @enderror</p>
            </div>
        @endforeach
        <button type="submit" @disabled(!$heroLoaded || $heroSaving) wire:loading.attr="disabled" wire:target="saveHeroSettings,reloadHeroSettings" class="rounded-lg bg-senat-primary px-5 py-2.5 text-[12px] font-semibold text-white hover:bg-senat-primaryHover disabled:opacity-50">Publikasikan Pembaruan ke Beranda</button>
    </form>
</div>
