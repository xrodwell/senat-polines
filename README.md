# Portal Senat Akademik Politeknik Negeri Semarang (Polines)

Portal resmi Senat Akademik Politeknik Negeri Semarang berbasis Laravel 13 dan Tailwind CSS v4, dilengkapi pratinjau panel administrasi sekretariat dan manajemen rapat/publikasi berbasis Livewire 4.

## Menjalankan secara Lokal

Prasyarat: Git, PHP >=8.3, Composer, Node.js dan npm. Pastikan `git`, `php`, `composer`, `node`, dan `npm` tersedia di PATH lingkungan Anda.

Frontend menggunakan Vite + Tailwind CSS v4, sehingga `npm install && npm run build` perlu dijalankan sebelum memulai server Laravel.

### Windows PowerShell

Jalankan perintah berikut secara berurutan:

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

### Linux / macOS (Bash)

```bash
git clone https://github.com/xrodwell/senat-polines.git
cd senat-polines
composer install
npm install
npm run build
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
cp .env.example .env
php artisan key:generate
php artisan serve
```

Buka peramban di:
- **Portal Publik**: `http://127.0.0.1:8000`
- **Portal Anggota**: `http://127.0.0.1:8000/portal/dashboard`
- **Panel Admin Sekretariat**: `http://127.0.0.1:8000/admin`

Untuk unggahan dokumentasi hingga 10 MB di XAMPP, sesuaikan `C:\xampp\php\php.ini`:
```ini
upload_max_filesize=12M
post_max_size=16M
```
Simpan perubahan lalu restart Apache. Nilai ini harus lebih besar dari batas validasi aplikasi agar unggahan tidak ditolak PHP sebelum mencapai Livewire.

## Arsitektur & Interoperabilitas

Sistem mendukung arsitektur **Hybrid Interoperabilitas**:
1. **REST API Backend**: Menghubungkan endpoint API `Backend-Senat-Polines` untuk data komisi, berita, dan regulasi via konfigurasi `.env`:
   ```env
   SENAT_BACKEND_API_URL=http://127.0.0.1:8001/api
   ```
2. **Mock Fallback**: Jika backend API belum dijalankan, sistem otomatis menggunakan repositori mock berbasis JSON di `storage/mock/*.json` tanpa hambatan.
3. **Panel Admin Terpusat**: Panel `/admin` mengintegrasikan seluruh operasional sekretariat (sidang, kuorum, voting, risalah) dan publikasi web (berita, regulasi, arsip, aspirasi).

## Pengujian

Menjalankan test suite:
```bash
./vendor/bin/phpunit
```
