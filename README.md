# Portal Senat Akademik Politeknik Negeri Semarang (Polines)

Web demo berbasis Laravel 11.

## Menjalankan secara lokal

Prasyarat: PHP >=8.2 beserta ekstensi yang dibutuhkan Laravel 11, Composer, dan Git untuk clone.

```bash
git clone https://github.com/xrodwell/senat-polines.git
cd senat-polines
composer install
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
cp .env.example .env
php artisan key:generate
./run.sh
```

Pastikan `storage/` dan `bootstrap/cache/` dapat ditulis oleh proses PHP. Sebagai alternatif `./run.sh`, jalankan `php artisan serve`.

Buka http://127.0.0.1:8000.

## Data dan frontend

Data demo dibaca dari `storage/mock/*.json`; database tidak diperlukan untuk menjalankan demo dengan konfigurasi `.env.example`.

Frontend memakai Tailwind CSS dan Alpine.js melalui CDN, sehingga Node/npm maupun build aset tidak wajib untuk menjalankan web saat ini. Koneksi internet diperlukan untuk memuat aset CDN.
