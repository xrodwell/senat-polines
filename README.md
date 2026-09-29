# Portal Senat Akademik Politeknik Negeri Semarang (Polines)

Web demo berbasis Laravel 11.

## Menjalankan secara lokal

Prasyarat: Git, PHP >=8.2 (sesuai batas versi di `composer.json`/`composer.lock`) beserta ekstensi yang disyaratkan dependensi Composer, termasuk dependensi pengembangan, dan Composer. Pastikan `git`, `php`, dan `composer` tersedia di PATH. `composer install` memeriksa persyaratan tersebut; aktifkan ekstensi yang dilaporkan kurang di `php.ini` PHP CLI, jangan gunakan `--ignore-platform-reqs`.

### Windows PowerShell

Jalankan perintah berikut berurutan untuk clone baru; lanjutkan hanya jika perintah sebelumnya berhasil.

```powershell
git clone https://github.com/xrodwell/senat-polines.git
cd senat-polines
composer install
New-Item -ItemType Directory -Force -Path storage/framework/cache/data, storage/framework/sessions, storage/framework/views, storage/logs, bootstrap/cache | Out-Null
Copy-Item .env.example .env
php artisan key:generate
php artisan serve
```

`./run.sh` dan sintaks Bash `mkdir -p`/`cp` bukan instruksi untuk PowerShell; gunakan `New-Item` dan `Copy-Item` seperti di atas. Jangan timpa `.env` atau buat ulang kunci aplikasi pada instalasi yang sudah digunakan.

### Linux/macOS (Bash)

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

Frontend memakai Tailwind CSS dan Alpine.js melalui CDN, sehingga Node/npm maupun build aset tidak diperlukan untuk menjalankan web saat ini. Koneksi internet diperlukan untuk memuat aset CDN.
