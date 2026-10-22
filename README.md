# Portal Senat Akademik Politeknik Negeri Semarang (Polines)

Web demo berbasis Laravel 13 + Livewire 4.

## Menjalankan secara lokal

Prasyarat: Git, PHP >=8.3 (sesuai batas versi di `composer.json`/`composer.lock`), Composer, Node.js dan npm. Pastikan `git`, `php`, `composer`, `node`, dan `npm` tersedia di PATH. `composer install` memeriksa persyaratan PHP; aktifkan ekstensi yang dilaporkan kurang di `php.ini` PHP CLI, jangan gunakan `--ignore-platform-reqs`.

Frontend memakai Vite + Tailwind CSS v4, jadi `npm install && npm run build` wajib sekali sebelum `php artisan serve` (atau jalankan `npm run dev`).

### Windows PowerShell

Jalankan perintah berikut berurutan untuk clone baru; lanjutkan hanya jika perintah sebelumnya berhasil.

```powershell
git clone https://github.com/xrodwell/senat-polines.git
cd senat-polines
composer install
npm install
npm run build
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
npm install
npm run build
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
cp .env.example .env
php artisan key:generate
./run.sh
```

Pastikan `storage/` dan `bootstrap/cache/` dapat ditulis oleh proses PHP. Sebagai alternatif `./run.sh`, jalankan `php artisan serve`.

Buka http://127.0.0.1:8000. Pratinjau panel admin ada di http://127.0.0.1:8000/admin.

## Data, Interoperabilitas API, dan Frontend

Web ini mendukung arsitektur **Hybrid Interoperabilitas**:
1. **REST API Backend**: Menarik data langsung dari endpoint API `Backend-Senat-Polines` (Komisi, Berita, Regulasi). Konfigurasi URL di `.env`:
   ```env
   SENAT_BACKEND_API_URL=http://127.0.0.1:8001/api
   ```
2. **Graceful Fallback**: Jika backend API offline atau tidak diaktifkan, sistem otomatis beralih (*fallback*) ke dataset lokal di `storage/mock/*.json` tanpa menimbulkan error.
3. **Frontend**: Memakai Vite 8, Tailwind CSS v4 (`@tailwindcss/vite`), dan Livewire 4 (Alpine dibundel Livewire). Aset dibangun lewat `npm run build` ke `public/build`; tidak ada lagi Tailwind/Alpine CDN.

### Menjalankan Backend API (Opsional untuk Interoperabilitas)
Jika ingin menguji penarikan data dinamis dari Backend API:
```bash
# 1. Clone repository backend
git clone https://github.com/UlfanNayakaDipta/Backend-Senat-Polines.git
cd Backend-Senat-Polines

# 2. Setup dependensi dan env
composer install
cp .env.example .env
touch database/database.sqlite
# Set DB_CONNECTION=sqlite di .env
php artisan key:generate
php artisan migrate --seed

# 3. Jalankan server API di port 8001
php artisan serve --port=8001
```

## Test

```bash
./vendor/bin/phpunit
```
