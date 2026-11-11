<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Sumber tunggal data kepengurusan Senat Akademik Polines periode 2026–2029.
 * Data resmi diverifikasi berdasarkan data kepegawaian resmi Politeknik Negeri Semarang (Polines).
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

    /**
     * Data NIP, Jurusan, dan Email resmi Dosen Senat Akademik Polines.
     *
     * @var array<string, array{nip: string, jurusan: string, email: string}>
     */
    public const LECTURER_DATA = [
        'Dr. Sukamto, S.Kom., M.T.' => [
            'nip' => '197101172003121001',
            'jurusan' => 'Teknik Elektro',
            'email' => 'sukamto@polines.ac.id',
        ],
        'Andi Setiawan, S.E., M.M.' => [
            'nip' => '197607202008011011',
            'jurusan' => 'Administrasi Bisnis',
            'email' => 'andi.setiawan@polines.ac.id',
        ],
        // Komisi I — Pendidikan dan Pengajaran
        'Tri Yudantoro, S.Kom., M.Kom.' => [
            'nip' => '196810252000121001',
            'jurusan' => 'Teknik Elektro',
            'email' => 'tri.yudantoro@polines.ac.id',
        ],
        'Tri Raharjo Yudantoro, S.Kom., M.Kom.' => [
            'nip' => '196810252000121001',
            'jurusan' => 'Teknik Elektro',
            'email' => 'tri.yudantoro@polines.ac.id',
        ],
        'Anis Roihatin, S.T., M.T.' => [
            'nip' => '198701252014042001',
            'jurusan' => 'Teknik Mesin',
            'email' => 'anis.roihatin@polines.ac.id',
        ],
        'Prof. Dr. Ir. Dwiana Hendrawati, S.T., M.T.' => [
            'nip' => '196908141998022001',
            'jurusan' => 'Teknik Mesin',
            'email' => 'dwiana.hendrawati@polines.ac.id',
        ],
        'Yusmar Ardhi Hidayat, S.E., M.Si., Ph.D.' => [
            'nip' => '198101312006041003',
            'jurusan' => 'Administrasi Bisnis',
            'email' => 'yusmar.ardhi@polines.ac.id',
        ],
        'Dr. Yusmar Ardhi Hidayat, S.E., M.Si., Ph.D.' => [
            'nip' => '198101312006041003',
            'jurusan' => 'Administrasi Bisnis',
            'email' => 'yusmar.ardhi@polines.ac.id',
        ],
        'Resi Yudaningsih, S.E., M.Si., Akt.' => [
            'nip' => '197211021998022001',
            'jurusan' => 'Akuntansi',
            'email' => 'resi.yudaningsih@polines.ac.id',
        ],
        'Resi Yudhaningsih, S.E., M.Si., Akt.' => [
            'nip' => '197211021998022001',
            'jurusan' => 'Akuntansi',
            'email' => 'resi.yudaningsih@polines.ac.id',
        ],
        'Dr. Bagus Yunianto W., S.E., S.Kom., M.M.' => [
            'nip' => '199106262019031017',
            'jurusan' => 'Administrasi Bisnis',
            'email' => 'bagusyuniantowibowo@polines.ac.id',
        ],
        'Dr. Bagus Yunianto Wibowo, S.E., S.Kom., M.M.' => [
            'nip' => '199106262019031017',
            'jurusan' => 'Administrasi Bisnis',
            'email' => 'bagusyuniantowibowo@polines.ac.id',
        ],
        'Nanang Adie Setyawan, S.E., M.M.' => [
            'nip' => '199107102019031017',
            'jurusan' => 'Administrasi Bisnis',
            'email' => 'nanang.adie@polines.ac.id',
        ],
        // Komisi II — Penelitian dan Pengabdian kepada Masyarakat
        'Dr. Jumi, S.Kom., M.Kom.' => [
            'nip' => '197006142005012001',
            'jurusan' => 'Administrasi Bisnis',
            'email' => 'jumi@polines.ac.id',
        ],
        'Fajar Surya Herlambang, S.T., M.T.' => [
            'nip' => '197206291999031001',
            'jurusan' => 'Teknik Sipil',
            'email' => 'fajar.surya@polines.ac.id',
        ],
        'Yusnan Badruzzaman, S.T., M.Eng.' => [
            'nip' => '197503132006041001',
            'jurusan' => 'Teknik Elektro',
            'email' => 'yusnan.badruzzaman@polines.ac.id',
        ],
        'Mohammad Hasanudin, S.E., M.Si.' => [
            'nip' => '197608012000121001',
            'jurusan' => 'Akuntansi',
            'email' => 'mohamad.hasanudin@polines.ac.id',
        ],
        'Mohamad Hasanudin, S.E., M.Si., Akt.' => [
            'nip' => '197608012000121001',
            'jurusan' => 'Akuntansi',
            'email' => 'mohamad.hasanudin@polines.ac.id',
        ],
        'Vinda Setya Kartika, S.T., M.T.' => [
            'nip' => '199108112019032024',
            'jurusan' => 'Teknik Elektro',
            'email' => 'vinda.setya@polines.ac.id',
        ],
        'Ir. Teguh Mulyo Wicaksono, S.Pd., M.Eng.' => [
            'nip' => '199012022019031014',
            'jurusan' => 'Teknik Sipil',
            'email' => 'teguh.mulyo@polines.ac.id',
        ],
        // Komisi III — Kelembagaan dan Sumber Daya Manusia
        'Ir. Riles Melvy Wattimena, M.T., IPP.' => [
            'nip' => '196709031994031001',
            'jurusan' => 'Teknik Mesin',
            'email' => 'riles.wattimena@polines.ac.id',
        ],
        'Iwan Budiyono, S.E., M.Si.Ak' => [
            'nip' => '198410192010121003',
            'jurusan' => 'Akuntansi',
            'email' => 'iwan.budiyono@polines.ac.id',
        ],
        'Iwan Budiyono, S.E., M.Si., Akt.' => [
            'nip' => '198410192010121003',
            'jurusan' => 'Akuntansi',
            'email' => 'iwan.budiyono@polines.ac.id',
        ],
        'Sahid, S.T., M.T.' => [
            'nip' => '197005121996011001',
            'jurusan' => 'Teknik Mesin',
            'email' => 'sahid@polines.ac.id',
        ],
        'Dedi Budi Setyawan, S.T., M.T.' => [
            'nip' => '197712062001121001',
            'jurusan' => 'Teknik Mesin',
            'email' => 'dedi.budi@polines.ac.id',
        ],
        'Dedi Budi Setiawan, S.T., M.T.' => [
            'nip' => '197712062001121001',
            'jurusan' => 'Teknik Mesin',
            'email' => 'dedi.budi@polines.ac.id',
        ],
        'Dr. Muhammad Anif, S.T., M.Eng.' => [
            'nip' => '197710092005011001',
            'jurusan' => 'Teknik Elektro',
            'email' => 'muhammad.anif@polines.ac.id',
        ],
        'Muhammad Anif, S.T., M.Eng.' => [
            'nip' => '197710092005011001',
            'jurusan' => 'Teknik Elektro',
            'email' => 'muhammad.anif@polines.ac.id',
        ],
        'Septian Yudha Kusuma, S.E., M.Si.Ak' => [
            'nip' => '199009032019031016',
            'jurusan' => 'Akuntansi',
            'email' => 'septian.yudha@polines.ac.id',
        ],
        'Septian Yudha Kusuma, S.E., M.Si., Ak.' => [
            'nip' => '199009032019031016',
            'jurusan' => 'Akuntansi',
            'email' => 'septian.yudha@polines.ac.id',
        ],
        // Komisi IV — Pengembangan dan Kerja Sama
        'Dr. Ir. FX Arif Wahyudianto, S.T., M.T., IPM.' => [
            'nip' => '197412111994031001',
            'jurusan' => 'Teknik Mesin',
            'email' => 'arif.wahyudianto@polines.ac.id',
        ],
        'Alvianita Gunawan Putri, S.E., M.Acc., Ak., CA., CPA., ASEAN CPA.' => [
            'nip' => '199201072019032024',
            'jurusan' => 'Akuntansi',
            'email' => 'alvianita.putri@polines.ac.id',
        ],
        'Alvianita Gunawan Putri, S.E., M.Acc., Ak., CA' => [
            'nip' => '199201072019032024',
            'jurusan' => 'Akuntansi',
            'email' => 'alvianita.putri@polines.ac.id',
        ],
        'Prof. Dr. Ir. Kurnianingsih, S.T., M.T.' => [
            'nip' => '197904262003122002',
            'jurusan' => 'Teknik Elektro',
            'email' => 'kurnianingsih@polines.ac.id',
        ],
        'Ir. Abdul Syukur Fauzi, S.T., M.T.' => [
            'nip' => '197005051999031002',
            'jurusan' => 'Teknik Mesin',
            'email' => 'abdul.syukur@polines.ac.id',
        ],
        'Abdul Syukur Alfauzi, S.T., M.T.' => [
            'nip' => '197005051999031002',
            'jurusan' => 'Teknik Mesin',
            'email' => 'abdul.syukur@polines.ac.id',
        ],
        'Leily Fatmawati, S.T., M.T.' => [
            'nip' => '197406122005012001',
            'jurusan' => 'Teknik Sipil',
            'email' => 'leily.fatmawati@polines.ac.id',
        ],
        'Iman Mujiarto, S.T., M.T.' => [
            'nip' => '197009172005011003',
            'jurusan' => 'Teknik Mesin',
            'email' => 'iman.mujiarto@polines.ac.id',
        ],
        // Komisi V — Komisi Etik
        'Drs. Suroso, M.Sc.' => [
            'nip' => '196511051994031002',
            'jurusan' => 'Teknik Sipil',
            'email' => 'suroso@polines.ac.id',
        ],
        'Achmad Zaenuddin, S.E., M.Si.' => [
            'nip' => '197305271999031002',
            'jurusan' => 'Administrasi Bisnis',
            'email' => 'achmad.zaenuddin@polines.ac.id',
        ],
        'Dr. Garup Lambang Goro, S.T., M.T.' => [
            'nip' => '196708121990031003',
            'jurusan' => 'Teknik Mesin (Direktur Polines)',
            'email' => 'garup.lambang@polines.ac.id',
        ],
    ];

    public static function chair(): array
    {
        $info = self::LECTURER_DATA['Dr. Sukamto, S.Kom., M.T.'] ?? [];

        return [
            'slug' => 'sukamto',
            'name' => 'Dr. Sukamto, S.Kom., M.T.',
            'role' => 'Ketua Senat',
            'nip' => $info['nip'] ?? '197101172003121001',
            'jurusan' => $info['jurusan'] ?? 'Teknik Elektro',
            'email' => $info['email'] ?? 'sukamto@polines.ac.id',
            'organ' => 'Pimpinan Senat',
            'focus' => 'Memimpin persidangan pleno, menetapkan agenda normatif, dan merepresentasikan Senat kepada Direktur serta Kemendiktisaintek.',
            'photo' => 'images/members/sukamto.jpg',
        ];
    }

    public static function secretary(): array
    {
        $info = self::LECTURER_DATA['Andi Setiawan, S.E., M.M.'] ?? [];

        return [
            'slug' => 'andi-setiawan',
            'name' => 'Andi Setiawan, S.E., M.M.',
            'role' => 'Sekretaris Senat',
            'nip' => $info['nip'] ?? '197607202008011011',
            'jurusan' => $info['jurusan'] ?? 'Administrasi Bisnis',
            'email' => $info['email'] ?? 'andi.setiawan@polines.ac.id',
            'organ' => 'Pimpinan Senat',
            'focus' => 'Mengelola persidangan, verifikasi risalah keputusan pleno, dan pengelolaan arsip normatif Senat.',
        ];
    }

    /** @return array<int, array<string, mixed>> */
    public static function bph(): array
    {
        $andiInfo = self::LECTURER_DATA['Andi Setiawan, S.E., M.M.'] ?? [];
        $vindaInfo = self::LECTURER_DATA['Vinda Setya Kartika, S.T., M.T.'] ?? [];

        return [
            [
                'slug' => 'bph-andi-setiawan',
                'name' => 'Andi Setiawan, S.E., M.M.',
                'role' => 'Ketua BPH',
                'nip' => $andiInfo['nip'] ?? '197607202008011011',
                'jurusan' => $andiInfo['jurusan'] ?? 'Administrasi Bisnis',
                'email' => $andiInfo['email'] ?? 'andi.setiawan@polines.ac.id',
                'organ' => 'Badan Pekerja Harian',
                'focus' => 'Mengoordinasikan kerja antar-komisi dan menyiapkan berkas sidang.',
            ],
            [
                'slug' => 'vinda-setya-kartika',
                'name' => 'Vinda Setya Kartika, S.T., M.T.',
                'role' => 'Sekretaris BPH',
                'nip' => $vindaInfo['nip'] ?? '199108112019032024',
                'jurusan' => $vindaInfo['jurusan'] ?? 'Teknik Elektro',
                'email' => $vindaInfo['email'] ?? 'vinda.setya@polines.ac.id',
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
            $info = self::LECTURER_DATA[$person['name']] ?? [];
            $members[] = array_merge($info, $person, ['slug' => $slug]);
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
