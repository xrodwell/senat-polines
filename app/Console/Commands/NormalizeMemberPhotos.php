<?php

namespace App\Console\Commands;

use App\Support\MemberPhotoStorage;
use Illuminate\Console\Command;

class NormalizeMemberPhotos extends Command
{
    protected $signature = 'photos:normalize
                            {--dry-run : Tampilkan rencana perubahan tanpa menulis file}';

    protected $description = 'Seragamkan resolusi foto anggota ke ukuran acuan (mendekati 200x300, tanpa distorsi)';

    public function handle(): int
    {
        if ($this->option('dry-run')) {
            $this->warn('Mode dry-run belum mengubah file. Jalankan tanpa --dry-run untuk menerapkan.');
        }

        $results = MemberPhotoStorage::normalizeAll();

        if ($results === []) {
            $this->info('Tidak ada foto yang perlu disesuaikan.');
            return self::SUCCESS;
        }

        $this->table(
            ['File', 'Sebelum', 'Sesudah'],
            array_map(fn (array $row): array => [$row['file'], $row['from'], $row['to']], $results)
        );

        $this->info(count($results) . ' foto disesuaikan ke resolusi acuan ' . MemberPhotoStorage::TARGET_WIDTH . 'x' . MemberPhotoStorage::TARGET_HEIGHT . '.');

        return self::SUCCESS;
    }
}
