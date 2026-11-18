<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * Penyimpanan foto profil anggota Senat.
 *
 * Foto disimpan pada folder publik frontend (public/images/members) mengikuti
 * konvensi yang sudah dipakai halaman direktori: {nip}.{ext} atau {slug}.{ext}.
 */
class MemberPhotoStorage
{
    public const DIRECTORY = 'images/members';

    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    /**
     * Simpan file foto unggahan dan kembalikan path relatifnya
     * (contoh: "images/members/197101172003121001.jpg").
     */
    public static function store(UploadedFile $file, ?string $nip, string $name): string
    {
        $directory = public_path(self::DIRECTORY);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $base = self::baseName($nip, $name);
        $extension = self::extension($file);

        // Hapus versi lama dengan nama dasar yang sama (ekstensi bisa berbeda).
        foreach (self::ALLOWED_EXTENSIONS as $oldExtension) {
            if ($oldExtension === $extension) {
                continue;
            }
            $oldFile = $directory . DIRECTORY_SEPARATOR . $base . '.' . $oldExtension;
            if (is_file($oldFile)) {
                @unlink($oldFile);
            }
        }

        // Salin file ke folder publik. move_uploaded_file() tidak dapat dipakai
        // untuk file sementara Livewire karena bukan upload pada request ini.
        // File sementara TIDAK dihapus: Livewire mengelola pembersihannya sendiri.
        $source = $file->getRealPath();
        $target = $directory . DIRECTORY_SEPARATOR . $base . '.' . $extension;

        $stored = @copy($source, $target);
        if (! $stored) {
            $stored = @rename($source, $target);
        }

        if (! $stored) {
            throw new \RuntimeException('Gagal menyimpan foto profil ke folder publik: ' . $target);
        }

        return self::DIRECTORY . '/' . $base . '.' . $extension;
    }

    /**
     * Hapus foto lama berdasarkan path relatif (jika ada di folder publik).
     */
    public static function delete(?string $path): void
    {
        if (empty($path) || ! str_starts_with($path, self::DIRECTORY . '/')) {
            return;
        }

        $file = public_path($path);
        if (is_file($file)) {
            @unlink($file);
        }
    }

    private static function baseName(?string $nip, string $name): string
    {
        $nip = preg_replace('/[^0-9A-Za-z_-]/', '', (string) $nip);

        if (! empty($nip)) {
            return $nip;
        }

        $slug = Str::slug($name);

        return $slug !== '' ? $slug : 'anggota-' . time();
    }

    private static function extension(UploadedFile $file): string
    {
        $extension = strtolower((string) $file->getClientOriginalExtension());

        return in_array($extension, self::ALLOWED_EXTENSIONS, true) ? $extension : 'jpg';
    }
}
