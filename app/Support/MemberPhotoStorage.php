<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * Penyimpanan foto profil anggota Senat.
 *
 * Foto disimpan pada folder publik frontend (public/images/members) mengikuti
 * konvensi yang sudah dipakai halaman direktori: {nip}.{ext} atau {slug}.{ext}.
 *
 * Semua foto diseragamkan mendekati resolusi acuan foto
 * Prof. Dr. Ir. Dwiana Hendrawati, S.T., M.T. (200 x 300 px). Gambar TIDAK
 * dipaksa ke rasio 2:3 (tidak dibuat gepeng): sisi yang lebih panjang
 * diskalakan ke ukuran acuan sehingga salah satu dimensi mendekati 200/300.
 */
class MemberPhotoStorage
{
    public const DIRECTORY = 'images/members';

    /** Lebar acuan (px) untuk foto orientasi mendatar/persegi. */
    public const TARGET_WIDTH = 200;

    /** Tinggi acuan (px) untuk foto orientasi tegak. */
    public const TARGET_HEIGHT = 300;

    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    /**
     * Simpan file foto unggahan dan kembalikan path relatifnya
     * (contoh: "images/members/197101172003121001.jpg").
     *
     * Foto langsung ditulis dalam resolusi standar (mendekati 200x300)
     * dengan penskalaan proporsional agar tidak gepeng.
     */
    public static function store(UploadedFile $file, ?string $nip, string $name): string
    {
        $directory = public_path(self::DIRECTORY);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $base = self::baseName($nip, $name);
        $source = (string) $file->getRealPath();

        // Format ditentukan dari isi gambar (bukan sekadar nama file), supaya
        // PNG yang dinamai .jpg tetap tersimpan dengan ekstensi yang benar.
        $extension = self::detectFormat($source) ?? self::extension($file);

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

        $target = $directory . DIRECTORY_SEPARATOR . $base . '.' . $extension;

        // Skalakan proporsional ke resolusi standar. Jika GD tidak tersedia
        // atau gambar tidak terbaca, fallback ke penyalinan apa adanya.
        // File sementara TIDAK dihapus: Livewire mengelola pembersihannya sendiri.
        if (! self::resizeToTarget($source, $target, $extension)) {
            $stored = @copy($source, $target);
            if (! $stored) {
                $stored = @rename($source, $target);
            }

            if (! $stored) {
                throw new \RuntimeException('Gagal menyimpan foto profil ke folder publik: ' . $target);
            }
        }

        return self::DIRECTORY . '/' . $base . '.' . $extension;
    }

    /**
     * Seragamkan semua foto yang sudah ada di folder publik ke resolusi
     * standar (mendekati 200x300). Hasil: statistik per file.
     *
     * @return array<int, array{file: string, from: string, to: string}>
     */
    public static function normalizeAll(): array
    {
        $directory = public_path(self::DIRECTORY);
        if (! is_dir($directory)) {
            return [];
        }

        $results = [];

        foreach (glob($directory . DIRECTORY_SEPARATOR . '*') ?: [] as $path) {
            if (! is_file($path)) {
                continue;
            }

            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
                continue;
            }

            $before = @getimagesize($path);
            if (! $before) {
                continue;
            }

            // Tulis ke file sementara dulu agar aman bila proses gagal.
            $temp = $path . '.tmp.' . $extension;
            if (! self::resizeToTarget($path, $temp, $extension)) {
                @unlink($temp);
                continue;
            }

            if (! @rename($temp, $path)) {
                @copy($temp, $path);
                @unlink($temp);
            }

            $after = @getimagesize($path);

            $results[] = [
                'file' => basename($path),
                'from' => $before[0] . 'x' . $before[1],
                'to' => $after ? ($after[0] . 'x' . $after[1]) : '?',
            ];
        }

        return $results;
    }

    /**
     * Tulis gambar ke $target dengan penskalaan proporsional mendekati
     * 200x300. Mengembalikan false bila gambar sudah sesuai ukuran atau
     * GD tidak dapat memprosesnya.
     */
    public static function resizeToTarget(string $source, string $target, ?string $format = null): bool
    {
        if (! function_exists('imagecreatetruecolor')) {
            return false;
        }

        $info = @getimagesize($source);
        if (! $info || empty($info[0]) || empty($info[1])) {
            return false;
        }

        [$width, $height] = $info;
        $format = $format ?: self::formatForType($info[2] ?? null);

        if ($format === null) {
            return false;
        }

        [$newWidth, $newHeight] = self::fitToTarget($width, $height);

        // Sudah sesuai acuan: tidak perlu diolah ulang.
        if ($newWidth === $width && $newHeight === $height) {
            return false;
        }

        $image = self::readImage($source, $format);
        if (! $image) {
            return false;
        }

        $canvas = imagecreatetruecolor($newWidth, $newHeight);

        // Pertahankan transparansi untuk PNG/WebP.
        if ($format === 'png' || $format === 'webp') {
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
            imagefilledrectangle($canvas, 0, 0, $newWidth, $newHeight, $transparent);
        }

        imagecopyresampled(
            $canvas,
            $image,
            0,
            0,
            0,
            0,
            $newWidth,
            $newHeight,
            $width,
            $height
        );

        return self::writeImage($canvas, $target, $format);
    }

    /**
     * Hitung dimensi baru tanpa mengubah rasio (tidak gepeng).
     *
     * - Foto tegak (tinggi > lebar): tinggi dijadikan 300 px.
     * - Foto mendatar/persegi: lebar dijadikan 200 px.
     *
     * @return array{0: int, 1: int}
     */
    private static function fitToTarget(int $width, int $height): array
    {
        $targetWidth = self::TARGET_WIDTH;
        $targetHeight = self::TARGET_HEIGHT;

        if ($height > $width) {
            $scale = $targetHeight / $height;
        } else {
            $scale = $targetWidth / $width;
        }

        $newWidth = max(1, (int) round($width * $scale));
        $newHeight = max(1, (int) round($height * $scale));

        return [$newWidth, $newHeight];
    }

    private static function readImage(string $source, string $format): \GdImage|false
    {
        return match ($format) {
            'png' => @imagecreatefrompng($source),
            'webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($source) : false,
            default => @imagecreatefromjpeg($source),
        };
    }

    private static function writeImage(\GdImage $image, string $target, string $format): bool
    {
        return match ($format) {
            'png' => @imagepng($image, $target, 6),
            'webp' => function_exists('imagewebp') ? @imagewebp($image, $target, 90) : false,
            default => @imagejpeg($image, $target, 90),
        };
    }

    /**
     * Deteksi format gambar dari isi file. Mengembalikan null bila bukan
     * format yang didukung.
     */
    private static function detectFormat(string $path): ?string
    {
        $info = @getimagesize($path);

        return $info ? self::formatForType($info[2] ?? null) : null;
    }

    private static function formatForType(?int $type): ?string
    {
        return match ($type) {
            IMAGETYPE_JPEG => 'jpg',
            IMAGETYPE_PNG => 'png',
            IMAGETYPE_WEBP => 'webp',
            default => null,
        };
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
