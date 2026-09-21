# Portal Senat Akademik Politeknik Negeri Semarang (Polines)

Platform digital representatif dan operasional Senat Akademik Politeknik Negeri Semarang berbasis **Laravel 11**, **Tailwind CSS**, dan **Alpine.js**, dirancang dengan standar arsitektur bersih dan metodologi desain anti-AI slop (*Impeccable Standard*).

---

## 🏛️ Arsitektur & Prinsip Rekayasa (Clean Architecture)

Proyek ini dibangun menggunakan **Repository Pattern** dengan **Service Contracts** terpisah. Tampilan (*Blade Views*) dan pengendali (*Controllers*) tidak terikat langsung pada database tertentu:

```
[ Blade Views ] ──> [ Controllers ] ──> [ Service Contracts (app/Contracts/) ]
                                                         │
                                      ┌──────────────────┴──────────────────┐
                                      ▼                                     ▼
                        [ FASE 1: JSON Mock Driver ]          [ FASE 2: Backend Masa Depan ]
                        (storage/mock/*.json)                 ├── Eloquent ORM + PostgreSQL
                                                              └── HTTP Client (REST API)
```

### Keuntungan Arsitektur:
1. **Fase 1 (Prototipe Frontend Saat Ini):** Data disajikan secara instan melalui berkas JSON di `storage/mock/` tanpa membutuhkan server database aktif saat presentasi visual ke pengawas.
2. **Fase 2 (Penyambungan Database PostgreSQL):** Cukup buat kelas implementasi baru (`EloquentPostRepository`, dsb.) dan ubah binding di `app/Providers/AppServiceProvider.php`. **100% kode Blade dan Controller tidak perlu diubah sama sekali.**

---

## 🚀 Fitur Unggulan

### 1. Etalase Publik
- **Beranda (`/`):** Hero section institusional, status sidang aktif (peringatan kuorum & sesi berjalan), statistik capaian, agenda musyawarah terdekat, dan warta keputusan senat.
- **Profil & Struktur (`/profil`):** Visi & misi, bagan hierarki interaktif, dan rincian Komisi I–V.
- **Produk Hukum (`/regulasi`):** Repositori SK Pertimbangan & Peraturan Senat, filter status draf (*Disahkan*, *Pembahasan*, *Draf*), dan simulasi unduh PDF.
- **Kanal Aspirasi (`/aspirasi`):** Formulir aduan civitas akademika dengan auto-generated ticket code (`ASP-POLINES-2026-XXXX`), tombol salin kode, dan pelacak status tiket vertikal di `/aspirasi/track`.
- **Arsip Kegiatan (`/arsip`):** Laporan Pertanggungjawaban (LPJ) tahunan dan risalah sidang lampau.

### 2. Portal Internal
- **Portal Anggota (`/portal/attendance`):** Monitoring rekapitulasi kehadiran personal (94%), presensi digital mandiri via kode sesi/QR, dan modul pencatatan notulensi risalah sidang.
- **Portal Admin / Sekretariat (`/portal/session`):** Kontrol sesi sidang proyektor, tampilan dinamis QR Code dengan TTL, countdown timer, kontrol kuorum (28/34 Anggota Hadir), dan tombol buka/tutup sesi.

---

## 🎨 Panduan Desain (Design Tokens)

* **Primary Navy:** `#003366` (Identitas resmi Polines)
* **Secondary Blue:** `#0A66C2` (Aksen navigasi)
* **Accent Orange:** `#F37021` (Aksen resmi Polines, call-to-action)
* **Typography:** `Plus Jakarta Sans` dengan aturan ketat `tabular-nums` untuk perataan angka tanggal dan kuorum.

---

## 📂 Cara Menjalankan

```bash
# 1. Masuk ke direktori proyek
cd /home/rodwell/projek/coba/senat-polines

# 2. Pasang dependensi (bila di mesin yang memiliki PHP & Node)
composer install
npm install

# 3. Jalankan server lokal
php artisan serve
npm run dev
```
