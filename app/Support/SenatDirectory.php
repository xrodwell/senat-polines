<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Sumber tunggal data kepengurusan Senat Akademik Polines periode 2026–2029.
 * Data berasal dari dokumen resmi: Struktur Organisasi Senat 2026–2029 (Excel),
 * Draft OTK Senat 2026, dan Notulen Rapat Pleno Khusus 09 Oktober 2026.
 */
class SenatDirectory
{
    public const PERIOD = '2026–2029';

    /** @var array<string, string> */
    public const COMMITTEE_LABELS = [
        'KOM-I' => 'Komisi I',
        'KOM-II' => 'Komisi II',
        'KOM-III' => 'Komisi III',
        'KOM-IV' => 'Komisi IV',
        'KOM-V' => 'Komisi V',
    ];

    public static function chair(): array
    {
        return [
            'slug' => 'sukamto',
            'name' => 'Dr. Sukamto, S.Kom., M.T.',
            'role' => 'Ketua Senat',
            'nip' => '197101172003121001',
            'organ' => 'Pimpinan Senat',
            'focus' => 'Memimpin persidangan pleno, menetapkan agenda normatif, dan merepresentasikan Senat kepada Direktur serta Kemendiktisaintek.',
        ];
    }

    public static function secretary(): array
    {
        return [
            'slug' => 'andi-setiawan',
            'name' => 'Andi Setiawan, S.E., M.M.',
            'role' => 'Sekretaris Senat',
            'nip' => '197607202008011011',
            'organ' => 'Pimpinan Senat',
            'focus' => 'Mengelola persidangan, verifikasi risalah keputusan pleno, dan pengelolaan arsip normatif Senat.',
        ];
    }

    /** @return array<int, array<string, mixed>> */
    public static function bph(): array
    {
        return [
            [
                'slug' => 'bph-andi-setiawan',
                'name' => 'Andi Setiawan, S.E., M.M.',
                'role' => 'Ketua BPH',
                'organ' => 'Badan Pekerja Harian',
                'focus' => 'Mengoordinasikan kerja antar-komisi dan menyiapkan berkas sidang.',
            ],
            [
                'slug' => 'vinda-setya-kartika',
                'name' => 'Vinda Setya Kartika, S.T., M.T.',
                'role' => 'Sekretaris BPH',
                'organ' => 'Badan Pekerja Harian',
                'focus' => 'Menyusun rancangan agenda persidangan berkala dan risalah tindak lanjut.',
            ],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    public static function committees(): array
    {
        $path = storage_path('mock/committees.json');

        if (! is_file($path)) {
            return [];
        }

        return json_decode(file_get_contents($path), true) ?: [];
    }

    public static function committeeLabel(string $code): string
    {
        return self::COMMITTEE_LABELS[$code] ?? $code;
    }

    public static function committeeByCode(string $code): ?array
    {
        foreach (self::committees() as $committee) {
            if ($committee['code'] === $code) {
                return $committee;
            }
        }

        return null;
    }

    public static function slug(string $name): string
    {
        return Str::slug(str_replace(['.', ','], '', $name));
    }

    /**
     * Direktori anggota lengkap: pimpinan, BPH, ketua/sekretaris komisi, dan anggota komisi.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function members(): array
    {
        $members = [];
        $seen = [];

        $push = function (array $person) use (&$members, &$seen) {
            $slug = ($person['slug'] ?? '') ?: self::slug($person['name']);
            if (isset($seen[$person['name'] . '|' . $person['role']])) {
                return;
            }
            $seen[$person['name'] . '|' . $person['role']] = true;
            $members[] = array_merge($person, ['slug' => $slug]);
        };

        $chair = self::chair();
        $chair['slug'] = 'sukamto';
        $push($chair);

        $secretary = self::secretary();
        $secretary['slug'] = 'andi-setiawan';
        $push($secretary);

        foreach (self::bph() as $person) {
            $push($person);
        }

        foreach (self::committees() as $committee) {
            $push([
                'name' => $committee['lead'],
                'role' => 'Ketua '.self::committeeLabel($committee['code']),
                'organ' => self::committeeLabel($committee['code']).' — '.$committee['name'],
                'committee_code' => $committee['code'],
                'committee_name' => $committee['name'],
                'focus' => $committee['focus'],
            ]);
            $push([
                'name' => $committee['secretary'],
                'role' => 'Sekretaris '.self::committeeLabel($committee['code']),
                'organ' => self::committeeLabel($committee['code']).' — '.$committee['name'],
                'committee_code' => $committee['code'],
                'committee_name' => $committee['name'],
                'focus' => $committee['focus'],
            ]);
            foreach ($committee['members'] ?? [] as $member) {
                $push([
                    'name' => $member,
                    'role' => 'Anggota '.self::committeeLabel($committee['code']),
                    'organ' => self::committeeLabel($committee['code']).' — '.$committee['name'],
                    'committee_code' => $committee['code'],
                    'committee_name' => $committee['name'],
                    'focus' => $committee['focus'],
                ]);
            }
        }

        return $members;
    }

    public static function findBySlug(string $slug): ?array
    {
        foreach (self::members() as $member) {
            if ($member['slug'] === $slug) {
                return $member;
            }
        }

        return null;
    }

    /** @return array<string, mixed> */
    public static function stats(): array
    {
        $committees = self::committees();

        return [
            'committees' => count($committees),
            'members' => count(self::members()),
            'period' => self::PERIOD,
        ];
    }
}
